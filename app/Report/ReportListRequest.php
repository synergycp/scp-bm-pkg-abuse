<?php

namespace Packages\Abuse\App\Report;

use App\Http\Requests\ListRequest;

class ReportListRequest
extends ListRequest
{
    public function boot()
    {
        $this->orders = [
            'addr' => 'addr',
            'date' => 'reported_at',
            'server' => 'server_id',
            'client' => 'client_id',
            'updated' => 'updated_at',
            'default' => ['date', 'desc'],
        ];
    }
}
