<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChannelsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category' => [
                'nullable',
                'string',
            ],
            'min_subscribers' => [
                'nullable',
                'integer',
            ],
            'max_subscribers' => [
                'nullable',
                'integer',
            ],
            'language' => [
                'nullable',
                'string',
            ],
            'region' => [
                'nullable',
                'string',
            ],
            'last_video_period' => [
                'nullable',
                'string',
                'in:last_7_days,last_month,last_year',
            ],
            'sort_key' => [
                'nullable',
                'required_with:sort_direction',
                'string',
                'in:engagement_rate,average_views',
            ],
            'sort_direction' => [
                'nullable',
                'required_with:sort_key',
                'string',
                'in:asc,desc',
            ],
        ];
    }
}
