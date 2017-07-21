<?php

namespace Packages\Abuse\App\Report;

use App\Api\Transformer;
use App\Client\Client;
use App\Entity\Entity;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Packages\Abuse\App\Report\Comment\CommentTransformer;
use App\Server\ServerFilterService;

class ReportTransformer
extends Transformer
{
    /**
     * @var CommentTransformer
     */
    protected $comment;
    
    /**
     * @var ServerFilterService
     */
    protected $serverFilter;

    /**
     * @param CommentTransformer $comment
     * @param ServerFilterService $serverFilter
     */
    public function boot(
        CommentTransformer $comment,
        ServerFilterService $serverFilter
    ) {
        $this->comment = $comment;
        $this->serverFilter = $serverFilter;
    }

    /**
     * Transform an Report into an array for the Abuse Report List.
     *
     * @param Report $item
     *
     * @return array
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     */
    public function item(Report $item)
    {
        $data = $item->expose('id', 'addr', 'subject') + [
            'date' => $this->dateArr($item->reported_at),
            'date_resolved' => $this->dateArr($item->resolved_at),
            'server' => $this->itemServer($item),
            'client' => $this->itemClient($item),
            'excerpt' => $this->excerpt($item),
            'sender' => $item->from,
        ];

        if (!$this->viewerIsAdmin()) {
            return $data;
        }

        return $data + [
            'entity' => $this->itemEntity($item),
        ];
    }

    /**
     * Preload Abuse Report data for the Abuse Report List.
     *
     * @param Collection|LengthAwarePaginator $items
     *
     * @return array
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     */

    protected function itemPreload($items)
    {
        $preload = collection([
            'client',
            'lastComment.author',
            $this->viewerIsAdmin() ? 'entity' : null,
        ])->filter()->all();

        return $items->load(['server' => function($query) {
            $this->serverFilter->viewable($query->getQuery());
        } ])->load($preload);
    }

    /**
     * @param Report $item
     *
     * @return array
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     */
    private function excerpt(Report $item)
    {
        if ($comment = $item->lastComment) {
            return $this->comment->excerpt($comment);
        }

        $body = str_replace("=\r\n", '', $item->body);
        $body = strip_tags($body);
        $body = str_limit($body, 100);

        return [
            'from' => 'Original Email',
            'body' => $body,
        ];
    }

    /**
     * Single Report's server.
     *
     * @param Report $item
     *
     * @return array
     */
    public function itemServer(Report $item)
    {
        return !$item->server ? null : $item->server->expose([
            'id',
            'name',
        ]);
    }

    /**
     * Single Report.
     *
     * @param Report $item
     *
     * @return array
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     */
    public function resource(Report $item)
    {
        return $this->item($item) + $item->expose('body');
    }

    /**
     * Transform a Report's IP Entity into an array for the Abuse Report List.
     *
     * @param Report $item
     *
     * @return array
     */
    private function itemEntity(Report $item)
    {
        return !$item->entity ? null : $item->entity->expose([
            'id',
            'name',
        ]);
    }

    /**
     * Transform a Client into an array for the Abuse Report List.
     *
     * @param Report $item
     *
     * @return array
     */
    private function itemClient(Report $item)
    {
        return !$item->client ? null : $item->client->expose([
            'id',
            'name',
        ]);
    }

    # View
    /**
     * Transform an Report into an array for the view Abuse Report page.
     *
     * @param Report $item
     *
     * @return array
     * @throws \App\Api\Exceptions\ApiKeyNotFound
     */
    public function view(Report $item)
    {
        return [
            'show_comment_form' => !$item->isResolved(),
            'report' => $this->resource($item),
        ];
    }

}
