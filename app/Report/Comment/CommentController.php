<?php

namespace Packages\Abuse\App\Report\Comment;

use App\Api;
use App\Support\ApiResponse;
use Packages\Abuse\App\Report\Report;
use Packages\Abuse\App\Report\ReportRepository;
use Packages\Abuse\App\Report\ReportFilterService;

/**
 * Routing for Abuse Report Comments.
 */
class CommentController extends Api\Controller
{
    /**
     * @var Api\ApiAuthService
     */
    protected $auth;

    /**
     * @var ReportFilterService
     */
    protected $filter;

    /**
     * @var ReportRepository
     */
    protected $reports;

    /**
     * @var CommentTransformer
     */
    protected $transform;

    /**
     * @param Api\ApiAuthService      $auth
     * @param ReportRepository    $reports
     * @param CommentTransformer  $transform
     * @param ReportFilterService $filterReports
     */
    public function boot(
        Api\ApiAuthService $auth,
        ReportRepository $reports,
        CommentTransformer $transform,
        ReportFilterService $filterReports
    ) {
        $this->auth = $auth;
        $this->reports = $reports;
        $this->transform = $transform;
        $this->filterReports = $filterReports;
    }

    /**
     * @param int|string $reportId
     *
     * @return ApiResponse
     */
    public function index($reportId)
    {
        $report = $this->getReport($reportId);
        $comments = $report->comments()
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ;
        $data = $this->transform->collection($comments, 'item');

        return response()->api($data);
    }

    /**
     * @param CommentFormRequest $request
     * @param int|string         $reportId
     *
     * @return ApiResponse
     */
    public function store(CommentFormRequest $request, $reportId)
    {
        $report = $this->getReport($reportId);
        $comment = new Comment($request->only('body'));

        $comment->report()->associate($report);
        $comment->author()->associate(
            $this->auth->user()
        );

        $comment->save();

        event(new Events\CommentCreated($comment));

        $msg = trans('abuse.admin.comment.saved');

        return response()->success($msg);
    }

    /**
     * @param int $reportId
     *
     * @return Report
     */
    private function getReport($reportId)
    {
        return $this->reports
            ->filter([$this->filterReports, 'viewable'])
            ->findOrFail($reportId);
    }
}
