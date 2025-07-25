<?php

namespace App\Http\Requests;

use App\Constants\ImportRequests;
use Illuminate\Foundation\Http\FormRequest;

class GetImportRequestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required_without:status|integer',
            'status' => 'required_without:id|in:' . implode(',', ImportRequests::STATUS_LABELS),
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'The selected status is invalid. Valid statuses are: ' . implode(', ', ImportRequests::STATUS_LABELS),
        ];
    }
}
