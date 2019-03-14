<?php

namespace Packages\Abuse\App\Contact;

use App\Client\Client;
use App\Mail\Mailable;

class ClientAbuseContact implements Mailable
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var bool
     */
    private $ignoreOptOut = false;

    /**
     * ClientAbuseContact constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return null|string
     */
    public function mailAddress()
    {
        return $this->client->pkg_abuse_receive_email || $this->ignoreOptOut
            ? ($this->client->pkg_abuse_contact_email ?: $this->client->mailAddress())
            : null;
    }

    /**
     * @return null|string
     */
    public function mailName()
    {
        return $this->client->mailName();
    }

    /**
     * @param bool $ignoreOptOut
     *
     * @return ClientAbuseContact
     */
    public function setIgnoreOptOut($ignoreOptOut)
    {
        $this->ignoreOptOut = $ignoreOptOut;

        return $this;
    }
}

