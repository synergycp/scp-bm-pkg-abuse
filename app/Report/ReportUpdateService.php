<?php

namespace Packages\Abuse\App\Report;

use App\Support\Http\UpdateService;
use Illuminate\Support\Collection;
use App\Api\ApiAuthService;

class ReportUpdateService extends UpdateService
{
    /**
     * @var ApiAuthService
     */
    protected $auth;

    /**
     * @var ReportPatchRequest
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestClass = ReportPatchRequest::class;

    public function boot(ApiAuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Update all Abuse Reports using the given request.
     *
     * @param Collection $items
     */
    public function updateAll(Collection $items)
    {
        $this->setResolved($items);
        $this->setClient($items);
        $this->setServer($items);
    }

    /**
     * @param Collection $items
     */
    private function setClient(Collection $items)
    {
        $inputs = [
            'client_id' => $this->input('client_id', 'int') ?: null,
        ];
        $createEvent = $this->queueHandler(
            Events\ReportClientReassigned::class
        );

        $this->successItems(
            'abuse.admin.report.client-reassigned',
            $items->filter($this->changed($inputs))
                ->reject([$this, 'isCreating'])
                ->each($createEvent)
        );
    }

    /**
     * @param Collection $items
     */
    private function setServer(Collection $items)
    {
        $inputs = [
            'server_id' => $this->input('server_id', 'int') ?: null,
        ];
        $createEvent = $this->queueHandler(
            Events\ReportClientReassigned::class
        );

        $this->successItems(
            'abuse.admin.report.server-reassigned',
            $items->filter($this->changed($inputs))
                ->reject([$this, 'isCreating'])
                ->each($createEvent)
        );
    }

    /**
     * @param Collection $items
     */
    private function setResolved(Collection $items)
    {
        if ($this->auth->is('admin', 'integration')) {
            $inputs = [
                'is_resolved' => $this->input('is_resolved', 'bool'),
            ];
            $createEvent = $this->queueHandler(
                Events\ReportStatusChanged::class
            );

            $this->successItems(
                'abuse.'.($inputs['is_resolved'] ? 'resolved' : 'unresolved'),
                $items->filter($this->changed($inputs))
                    ->reject([$this, 'isCreating'])
                    ->each($createEvent)
            );
        }

    }
}
