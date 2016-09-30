<?php

namespace Packages\Abuse\App\Report\Comment;

use App\Http\Requests\FormRequest;

class CommentFormRequest
extends FormRequest
{
    public function rules()
    {
        return [
            'body' => 'required|min:5',
        ];
    }
}
