<?php

namespace Packages\Abuse\App\Contact;

use App\Http\Requests\FormRequest;

class ContactFormRequest extends FormRequest
{
    public function rules() {
        return [
            'email' => 'email',
            'enabled' => 'boolean',
        ];
    }
}
