<?php

namespace Packages\Abuse\App\Contact;

use App\Api\ApiAuthService;
use App\Api\Controller;
use App\Client\Client;

class ContactController
extends Controller
{
    /**
     * @var ApiAuthService
     */
    protected $auth;

    /**
     * @param ApiAuthService $auth
     */
    public function boot(ApiAuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return array
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     * @throws \App\Auth\Exceptions\InvalidIpAddress
     */
    public function index()
    {
        // We have to get the fresh user data here, possibly due to caching of the user?
        return $this->data($this->user()->fresh());
    }

    /**
     * @param ContactFormRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     * @throws \App\Auth\Exceptions\InvalidIpAddress
     */
    public function store(ContactFormRequest $request)
    {
        $user = $this->user();
        $user->pkg_abuse_contact_email = $request->input('email');
        $user->pkg_abuse_receive_email = $request->boolean('enabled');
        $user->save();

        return $this->success(trans('pkg.abuse::contact.saved'), $this->data($user));
    }

    /**
     * @param Client $user
     *
     * @return array
     */
    protected function data(Client $user)
    {
        return [
            'email' => $user->pkg_abuse_contact_email,
            'enabled' => (bool)$user->pkg_abuse_receive_email,
        ];
    }

    /**
     * @return Client
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     * @throws \App\Auth\Exceptions\InvalidIpAddress
     */
    protected function user()
    {
        $this->auth->only('client');

        return $this->auth->user();
    }
}
