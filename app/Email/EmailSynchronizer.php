<?php

namespace Packages\Abuse\App\Email;

use App\Services\Ip\IpService;
use App\Services\Ip\IpAddressRangeContract;
use App\Services\Mail\Imap\MessageIterator;
use Packages\Abuse\App\Report\ReportService;
use Illuminate\Support\Collection;
use Ddeboer\Imap\Message;
use Ddeboer\Transcoder\Exception\UndetectableEncodingException;

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
     * @var Collection
     */
    protected $ignoreFrom;

    /**
     * @var Collection
     */
    protected $ignoreIps;

    public function __construct(Email $email)
    {
        $this->email = $email;
        $this->ignoreFrom = collect([
            'abuse@ladedicated.com',
            'abuse@losangelesdedicated.net',
            'admin@losangelesdedicated.net',
        ]);
        // TODO: accept ranges
        $this->ignoreIps = collect([
            '127.0.0.1',
            '127.0.0.190',
            '127.0.0.192',
            '127.0.0.199',
        ]);

        $this->resolve();
    }

    public function boot(
        IpService $ips,
        EmailFetcher $emails,
        ReportService $report
    ) {
        $this->ips = $ips;
        $this->report = $report;
        $this->emails = $emails;

        $this->filterAfterLastSeen();
    }

    public function start()
    {
        foreach ($this->getMessages() as $item) {
            $this->reportIpsIn($item);
        }
    }

    private function reportIpsIn(Message $mail)
    {
        $mail->keepUnseen();

        $from = (string) $mail->getFrom();

        if ($this->ignoreFrom->contains($from)) {
            return;
        }

        $ips = $this->findIpsIn($mail);
        $report = function (IpAddressRangeContract $addr) use ($mail) {
            $this->report($addr, $mail);
        };
        $shouldReport = function (IpAddressRangeContract $addr) {
            $start = $addr->start();

            // Ranged addresses should always be reported.
            if ((string) $start != (string) $addr->end()) {
                return true;
            }

            // If the IP is a hard-coded ignored IP, don't report it.
            if ($this->ignoreIps->contains((string) $start)) {
                return false;
            }

            return true;
        };

        if ($ips->filter($shouldReport)->each($report)->count()) {
            $this->whenIpFound($mail);
        }
    }

    /**
     * Filter the Messages.
     *
     * @return MessageIterator of Message
     */
    private function getMessages()
    {
        $items = $this->emails->get();
        $forget = function ($msgNum) use ($items) {
            $items->offsetUnset($msgNum);
        };

        // remove already seen items.
        $this->report
            ->matching(collect($items->keys()))
            ->distinct('msg_num')
            ->lists('msg_num')
            ->each($forget)
            ;

        return $items;
    }

    /**
     * Generate and save an Report for the given address.
     *
     * @param IpAddressRangeContract $addr
     * @param Message                $mail
     */
    private function report(IpAddressRangeContract $addr, Message $mail)
    {
        $report = $this->report->make($addr);
        $report->from = (string) $mail->getFrom();
        $report->body = $mail->getBodyText();
        $report->msg_id = $mail->getId();
        $report->msg_num = $mail->getNumber();
        $report->subject = $mail->getSubject();
        $report->reported_at = $mail->getDate();

        $report->save();

        event(new Events\ReportCreated($report));
    }

    /**
     * Find All IP Addresses in the given Mail object.
     *
     * @param Mail $mail
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

    private function whenIpFound(Message $mail)
    {
        // Mark as read.
        $mail->keepUnseen(false)->getContent(false);
    }

    private function filterAfterLastSeen()
    {
        // TODO: latest based on $this->email
        $latestReport = $this->report->latest();

        if ($latestReport) {
            $this->emails->after($latestReport->date->subSeconds(1));
        }
    }
}
