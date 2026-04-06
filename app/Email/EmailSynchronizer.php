<?php

namespace Packages\Abuse\App\Email;

use App\Entity\LookupService;
use App\Ip\IpAddressRangeContract;
use App\Ip\IpService;
use App\Log;
use App\Support\Resolver;
use function base64_encode;
use Ddeboer\Imap\Exception\MessageDoesNotExistException;
use Illuminate\Database\QueryException;
use Ddeboer\Imap\Message;
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
    }

    /**
     * Run the Synchronizer.
     */
    public function start()
    {
        $this->log
            ->create('Abuse email sync started')
            ->save();

        $iterator = $this->emails->get();

        if (!$iterator) {
            $this->log
                ->create('Abuse email sync: could not connect to mailbox')
                ->save();
            return;
        }

        $total = 0;
        $processed = 0;
        $errors = 0;

        while ($iterator->valid()) {
            $message = $iterator->current();
            $iterator->next();
            $total++;

            try {
                $this->reportIpsIn($message);
                $this->emails->archive($message);
                $processed++;
            } catch (MessageDoesNotExistException $exc) {
                // Silently ignore
            } catch (\Exception $exc) {
                $errors++;
                $this->logException($exc, $message);
            }
        }

        $this->log
            ->create('Abuse email sync completed')
            ->setData([
                'total_emails' => $total,
                'processed' => $processed,
                'errors' => $errors,
            ])
            ->save();
    }

    /**
     * @param Message $mail
     */
    private function reportIpsIn(Message $mail)
    {
        $from = $mail->getFrom()->getFullAddress();

        if ($this->ignoreFrom->contains($from)) {
            $this->log
                ->create('Abuse email sync: skipped email from ignored sender')
                ->setData(['from' => $from, 'subject' => $mail->getSubject()])
                ->save();
            return;
        }

        $ips = $this->findIpsIn($mail);

        if ($ips->isEmpty()) {
            $this->log
                ->create('Abuse email sync: no IPs found in email')
                ->setData(['from' => $from, 'subject' => $mail->getSubject()])
                ->save();
        }

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

    }

    /**
     * Find All IP Addresses in the given Mail object.
     *
     * Checks attachments first (XARF JSON, then XML) for a source IP.
     * Falls back to scraping the subject and body if no attachment IPs found.
     *
     * @param Message $mail
     *
     * @return Collection
     */
    private function findIpsIn(Message $mail)
    {
        // Try extracting IPs from attachments first.
        $attachmentIps = $this->findIpsInAttachments($mail);
        if ($attachmentIps->count()) {
            $this->log
                ->create('Abuse email sync: extracted IPs from attachment')
                ->setData([
                    'source' => 'attachment',
                    'ip_count' => $attachmentIps->count(),
                    'ips' => $attachmentIps->map(function ($addr) { return (string) $addr; })->values()->all(),
                    'subject' => $mail->getSubject(),
                ])
                ->save();
            return $attachmentIps;
        }

        // Fall back to scraping subject + body.
        $search = [
            $mail->getSubject(),
            $this->getBodyContent($mail),
        ];

        $ips = $this->ips
            ->find($search)
            ->map(function ($addr) {
                // Ignore ranges:
                return $addr->start();
            })
            ->unique(function ($addr) {
                return (string) $addr;
            })
            ->values();

        if ($ips->count()) {
            $this->log
                ->create('Abuse email sync: extracted IPs from email body')
                ->setData([
                    'source' => 'body',
                    'ip_count' => $ips->count(),
                    'ips' => $ips->map(function ($addr) { return (string) $addr; })->values()->all(),
                    'subject' => $mail->getSubject(),
                ])
                ->save();
        }

        return $ips;
    }

    /**
     * Try to extract source IPs from email attachments.
     *
     * Priority: XARF JSON attachments first, then XML attachments.
     *
     * @param Message $mail
     *
     * @return Collection
     */
    private function findIpsInAttachments(Message $mail)
    {
        try {
            $attachments = $mail->getAttachments();
        } catch (\Exception $exc) {
            $this->log
                ->create('Abuse email sync: failed to read attachments')
                ->setData(['subject' => $mail->getSubject()])
                ->setException($exc)
                ->save();
            return collection();
        }

        if (empty($attachments)) {
            return collection();
        }

        // 1. Check for XARF JSON attachments.
        foreach ($attachments as $attachment) {
            $ip = $this->extractIpFromXarfJson($attachment);
            if ($ip) {
                $search = [$ip];
                return $this->ips
                    ->find($search)
                    ->map(function ($addr) {
                        return $addr->start();
                    });
            }
        }

        // 2. Check for XML attachments.
        foreach ($attachments as $attachment) {
            $ip = $this->extractIpFromXml($attachment);
            if ($ip) {
                $search = [$ip];
                return $this->ips
                    ->find($search)
                    ->map(function ($addr) {
                        return $addr->start();
                    });
            }
        }

        return collection();
    }

    /**
     * Extract source IP from an XARF JSON attachment.
     *
     * @param mixed $attachment
     *
     * @return string|null
     */
    private function extractIpFromXarfJson($attachment)
    {
        $filename = $attachment->getFilename();
        if (!$filename) {
            return null;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension !== 'json') {
            return null;
        }

        try {
            $content = $attachment->getDecodedContent();
            $data = json_decode($content, true);

            if (!is_array($data)) {
                return null;
            }

            // XARF JSON uses "Source-IP" or "source-ip" for the reported IP.
            foreach (['Source-IP', 'source-ip', 'Source_IP', 'source_ip', 'SourceIP', 'sourceip'] as $key) {
                if (!empty($data[$key]) && filter_var($data[$key], FILTER_VALIDATE_IP)) {
                    return $data[$key];
                }
            }

            // Also check nested under "Report" key (some XARF formats).
            if (isset($data['Report']) && is_array($data['Report'])) {
                foreach (['Source-IP', 'source-ip', 'Source_IP', 'source_ip', 'SourceIP', 'sourceip'] as $key) {
                    if (!empty($data['Report'][$key]) && filter_var($data['Report'][$key], FILTER_VALIDATE_IP)) {
                        return $data['Report'][$key];
                    }
                }
            }
        } catch (\Exception $exc) {
            $this->log
                ->create('Abuse email sync: failed to parse XARF JSON attachment')
                ->setData(['filename' => $filename])
                ->setException($exc)
                ->save();
        }

        return null;
    }

    /**
     * Extract source IP from an XML attachment.
     *
     * @param mixed $attachment
     *
     * @return string|null
     */
    private function extractIpFromXml($attachment)
    {
        $filename = $attachment->getFilename();
        if (!$filename) {
            return null;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension !== 'xml') {
            return null;
        }

        try {
            $content = $attachment->getDecodedContent();

            // Search for common source IP patterns in XML.
            // Matches tags like <Source-IP>, <SourceIP>, <source_ip>, <ip-source>, etc.
            $patterns = [
                '/<Source-?IP[^>]*>\s*([^<]+)\s*<\//i',
                '/<source_ip[^>]*>\s*([^<]+)\s*<\//i',
                '/<Source[^>]*>\s*([^<]+)\s*<\//i',
                '/<ip[^>]*>\s*([^<]+)\s*<\//i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    $ip = trim($matches[1]);
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        } catch (\Exception $exc) {
            $this->log
                ->create('Abuse email sync: failed to parse XML attachment')
                ->setData(['filename' => $filename])
                ->setException($exc)
                ->save();
        }

        return null;
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
        $report->body = $this->getBodyContent($mail);
        $report->msg_id = $mail->getId();
        $report->msg_num = $mail->getNumber();
        $report->subject = $mail->getSubject();
        $report->reported_at = $mail->getDate();

        try {
            $report->save();
        } catch (QueryException $exc) {
            // Skip duplicate reports (already processed in a previous sync).
            if ($exc->errorInfo[1] === 1062) {
                return;
            }
            throw $exc;
        }

        $this->log
            ->create('Abuse email sync: report created')
            ->setData([
                'ip' => (string) $addr,
                'from' => $report->from,
                'subject' => $report->subject,
                'msg_num' => $report->msg_num,
            ])
            ->save();

        event(new Events\ReportCreated($report));
    }

    /**
     * Get the email body text, falling back to HTML body with tags stripped
     * if no text/plain part exists.
     *
     * @param Message $mail
     *
     * @return string
     */
    private function getBodyContent(Message $mail)
    {
        try {
            $text = $mail->getBodyText();
            if ($text) {
                return $text;
            }
        } catch (UndetectableEncodingException $exc) {
            // Fall through to try HTML.
        }

        try {
            $html = $mail->getBodyHtml();
            if ($html) {
                return strip_tags($html);
            }
        } catch (UndetectableEncodingException $exc) {
            // Unable to decode either part.
        }

        return '';
    }

    private function logException(\Exception $exc, Message $mail)
    {
        $this->log
            ->create('Abuse error while saving report')
            ->setData(['mail' => base64_encode(substr($mail->getRawMessage(), 1000))])
            ->setException($exc)
            ->save()
        ;
    }
}
