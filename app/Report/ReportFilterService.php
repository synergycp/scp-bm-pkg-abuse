<?php

namespace Packages\Abuse\App\Report;

use App\Api\ApiAuthService;
use App\Server\ServerRepository;
use App\Support\Http\FilterService;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter Reports for the current Request.
 */
class ReportFilterService
extends FilterService
{
    /**
     * @var ApiAuthService
     */
    protected $auth;

    /**
     * @var ServerRepository
     */
    protected $servers;

    /**
     * @var ReportListRequest
     */
    protected $request;
    protected $requestClass = ReportListRequest::class;

    /**
     * @param ApiAuthService   $auth
     * @param ServerRepository $servers
     */
    public function boot(
        ApiAuthService $auth,
        ServerRepository $servers
    ) {
        $this->auth = $auth;
        $this->servers = $servers;
    }

    /**
     * @param Builder $query
     */
    public function viewable(Builder $query)
    {
        $this->auth->only([
            'client' => function ($clientId) use ($query) {
                $this->clientHasAccess($query, $clientId);
                $query->where(function ($query) use ($clientId) {
                    $query->where('abuse_reports.client_id', $clientId)
                        ->orWhere(_call('open'));
                });
            },
            'admin',
            'integration',
        ]);
    }

    /**
     * @param Builder $query
     * @param int     $clientId
     * @param string  $joinType
     * @param string  $alias
     *
     * @return Builder
     */
    public function clientHasAccess(
        Builder $query,
        $clientId,
        $joinType = 'inner',
        $alias = 'access'
    ) {
        $query
            ->groupBy('abuse_reports.id')
            ->select('abuse_reports.*')
            ->joinServer($joinType, $serverAlias = $alias.'_server')
            ;

        $this->servers
            ->make()
            ->scopeJoinClientAccess($query, $clientId, $joinType, $alias, $serverAlias)
            ;
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function query(Builder $query)
    {
        $request = $this->prepare();
        $request->apply($query);

        if ($serverId = $request->input('server')) {
            $query->where('abuse_reports.server_id', $serverId);
        }

        if ($clientId = $request->input('client_id')) {
            $this->clientHasAccess($query, $clientId, 'inner', 'access_input');
        }

        if ($search = $request->query('search')) {
            $query->search($search);
        }

        $archiveType = $request->bool('archive') ? 'archived' : 'open';

        $query->$archiveType();

        if ($request->has('pending_client')) {
            $query->pendingClient();
        }

        if ($request->has('pending_admin')) {
            $query->pendingAdmin();
        }

        $query->orderBy('updated_at', 'desc');

        return $query;
    }
}
