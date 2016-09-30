<?php

namespace Packages\Abuse\App\Report\Comment;

use App\Api\Transformer;

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

    public function author(Comment $item)
    {
        return $item->author->expose('id', 'name');
    }
}
