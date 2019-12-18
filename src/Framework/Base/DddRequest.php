<?php

namespace Ronghz\LaravelDdd\Framework\Base;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class DddRequest extends FormRequest
{
    protected function failedValidation($validator)
    {
        throw new ValidationException($validator);
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('This action is unauthorized.');
    }
}

