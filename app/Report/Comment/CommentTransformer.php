<?php

namespace Packages\Abuse\App\Report\Comment;

use App\Api\Transformer;
use Illuminate\Database\Eloquent;

/**
 * Transform Abuse Report Comments for the API.
 */
class CommentTransformer
extends Transformer
{
    /**
     * Transform an Comment into an array.
     *
     * @param  Comment $item
     *
     * @return array
     */
    public function item(Comment $item)
    {
        return $item->expose(['id', 'body']) + [
            'date' => $this->dateForViewer($item->created_at),
            'author' => $this->author($item),
        ];
    }

    /**
     * @param Eloquent\Collection $items
     */
    public function itemPreload($items)
    {
        $items->load('author');
    }

    /**
     * @param Comment $item
     *
     * @return array
     */
    public function author(Comment $item)
    {
        return $item->author->expose('id') + [
            'name' => $this->authorName($item),
        ];
    }

    /**
     * @param Comment $item
     *
     * @return string
     */
    public function authorName(Comment $item)
    {
        return $this->viewerIsAdmin()
            || !$item->author->isAdmin()
             ? $item->author->name
             : 'Administrator'
             ;
    }

    /**
     * @param Comment $item
     *
     * @return array
     */
    public function excerpt(Comment $item)
    {
        return [
            'from' => $this->author($item)['name'],
            'body' => str_limit($item->body, 100),
        ];
    }
}
