<?php

namespace Packages\Abuse\App\Email;

use App\Entity\LookupService;
use App\Ip\IpAddressRangeContract;
use App\Ip\IpService;
use App\Log;
use App\Mail\Imap\MessageIterator;
use Ddeboer\Imap\Exception\MessageDoesNotExistException;
use Ddeboer\Imap\Message;
use Ddeboer\Transcoder\Exception\UndetectableEncodingException;
use Illuminate\Support\Collection;
use Packages\Abuse\App\Report\Events;
use Packages\Abuse\App\Report\ReportService;

class EmailSynchronizer
{
    use \App\Support\Resolver;

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
            'abuse@ladedicated.com',
            'abuse@losangelesdedicated.net',
            'admin@losangelesdedicated.net',
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
        $iterator = $this->getMessages();

        while ($iterator && $iterator->valid()) {
            $iterator->next();
            try {
                $this->reportIpsIn(
                    $iterator->current()
                );
            } catch (MessageDoesNotExistException $exc) {
                continue;
            } catch (\Exception $exc) {
                $this->logException($exc);
            }
        }
    }

    /**
     * Filter the Messages.
     *
     * @return MessageIterator <Message>
     */
    private function getMessages()
    {
        if (!$items = $this->emails->get()) {
            return;
        }

        $forget = function ($msgNum) use ($items) {
            $items->offsetUnset($msgNum);
        };

        // remove already seen items.
        $this->report
            ->matching(collection($items->keys()))
            ->distinct('msg_num')
            ->pluck('msg_num')
            ->each($forget)
        ;

        return $items;
    }

    /**
     * @param Message $mail
     */
    private function reportIpsIn(Message $mail)
    {
        $mail->keepUnseen();

        $from = (string)$mail->getFrom();

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

        return $this->ips->find($search);
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
        $report->from = (string)$mail->getFrom();
        $report->body = $mail->getBodyText();
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
        $mail->keepUnseen(false)
             ->getContent(false)
        ;
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
