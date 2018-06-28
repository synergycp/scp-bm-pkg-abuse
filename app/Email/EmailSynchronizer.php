<?php

namespace Packages\Abuse\App\Email;

use App\Entity\LookupService;
use App\Ip\IpAddressRangeContract;
use App\Ip\IpService;
use App\Log;
use App\Support\Resolver;
use Ddeboer\Imap;
use Ddeboer\Imap\Exception\MessageDoesNotExistException;
use Ddeboer\Imap\Message;
use Ddeboer\Imap\MessageInterface;
use Ddeboer\Transcoder\Exception\UndetectableEncodingException;
use Illuminate\Support\Collection;
use Packages\Abuse\App\Report\Events;
use Packages\Abuse\App\Report\ReportService;

class EmailSynchronizer
{
    use Resolver;

    /**
     * Find and generate IP address objects.
     *
     * @var IpService
     */
    protected $ips;

    /**
     * @var ReportService
     */
    protected $report;

    /**
     * @var EmailFetcher
     */
    protected $emails;

    /**
     * @var Log\Factory
     */
    protected $log;

    /**
     * @var Collection
     */
    protected $ignoreFrom;

    /**
     * @var Collection
     */
    protected $ignoreIps;

    /**
     * @var LookupService
     */
    protected $lookup;

    /**
     * @var Email
     */
    private $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
        $this->ignoreFrom = collection([
            app('Settings')->{'pkg.abuse.auth.user'},
        ]);
        // TODO: accept ranges
        $this->ignoreIps = collection([
            '127.0.0.1',
            '127.0.0.190',
            '127.0.0.192',
            '127.0.0.199',
        ]);

        $this->resolve();
    }

    /**
     * @param IpService     $ips
     * @param Log\Factory   $log
     * @param EmailFetcher  $emails
     * @param ReportService $report
     * @param LookupService $lookup
     */
    public function boot(
        IpService $ips,
        Log\Factory $log,
        EmailFetcher $emails,
        ReportService $report,
        LookupService $lookup
    ) {
        $this->ips = $ips;
        $this->log = $log;
        $this->report = $report;
        $this->emails = $emails;
        $this->lookup = $lookup;

        $this->emails->after(
            $this->report->minDate($this->email)
        );
    }

    /**
     * Run the Synchronizer.
     */
    public function start()
    {
        $iterator = $this->emails->get();

        while ($iterator && $iterator->valid()) {
            $message = $iterator->current();
            $messageNumber = $message->getNumber();
            $iterator->next();

            // remove already seen items.
            if ($this->report->messageNumberExists($messageNumber)) {
                continue;
            }

            try {
                $this->reportIpsIn($message);
            } catch (MessageDoesNotExistException $exc) {
                // Silently ignore
            } catch (\Exception $exc) {
                $this->logException($exc);
            }
        }
    }

    /**
     * @param Message $mail
     */
    private function reportIpsIn(Message $mail)
    {
        $from = $mail->getFrom()->getFullAddress();

        if ($this->ignoreFrom->contains($from)) {
            return;
        }

        $ips = $this->findIpsIn($mail);
        $report = function (IpAddressRangeContract $addr) use ($mail) {
            $this->report($addr, $mail);
        };
        $shouldReport = function (IpAddressRangeContract $addr) {
            $start = (string) $addr->start();
            $end = (string) $addr->end();

            // Ranged addresses should always be reported.
            if ($start != $end) {
                return true;
            }

            // If the IP is a hard-coded ignored IP, don't report it.
            if ($this->ignoreIps->contains($start)) {
                return false;
            }

            return true;
        };
        $hasEntity = function (IpAddressRangeContract $addr) {
            return (bool)$this->lookup->addr($addr);
        };
        $getAddress = function (IpAddressRangeContract $addr) {
            return (string)$addr;
        };
        $ipsWithEntities = $ips
            ->filter($hasEntity)
            ->map($getAddress)
        ;
        $shouldEntities = function (IpAddressRangeContract $addr) use ($ipsWithEntities) {
            if ($ipsWithEntities->count()) {
                return $ipsWithEntities->contains(
                    (string) $addr
                );
            }

            return true;
        };
        $reportedIps = $ips
            ->filter($shouldReport)
            ->filter($shouldEntities)
            ->each($report)
        ;

        if ($reportedIps->count()) {
            $this->whenIpFound($mail);
        }
    }

    /**
     * Find All IP Addresses in the given Mail object.
     *
     * @param Message $mail
     *
     * @return Collection
     */
    private function findIpsIn(Message $mail)
    {
        $body = null;

        try {
            $body = $mail->getBodyText();
        } catch (UndetectableEncodingException $exc) {
            // TODO:
        }

        $search = [
            $mail->getSubject(),
            $body,
        ];

        return $this->ips
            ->find($search)
            ->map(function ($addr) {
                // Ignore ranges:
                return $addr->start();
            });
    }

    /**
     * Generate and save an Report for the given address.
     *
     * @param IpAddressRangeContract $addr
     * @param Message                $mail
     */
    private function report(IpAddressRangeContract $addr, Message $mail)
    {
        $report = $this->report->makeWithEntity($addr, $this->lookup->addr($addr));
        $report->from = $mail->getFrom()->getFullAddress();
        $report->body = $mail->getBodyText() ?: '';
        $report->msg_id = $mail->getId();
        $report->msg_num = $mail->getNumber();
        $report->subject = $mail->getSubject();
        $report->reported_at = $mail->getDate();

        $report->save();

        event(new Events\ReportCreated($report));
    }

    private function whenIpFound(Message $mail)
    {
        // Mark as read.
        $mail->markAsSeen();
    }

    private function logException(\Exception $exc)
    {
        $this->log
            ->create('Abuse error while saving report')
            ->setException($exc)
            ->save()
        ;
    }
}
