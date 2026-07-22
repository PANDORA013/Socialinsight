<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\SocialInsight\SocialPlatform;

class AnalyzeTrendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'topic' => ['required', 'string', 'min:2', 'max:120'],
            'platforms' => ['nullable', 'array', 'max:4'],
            'platforms.*' => ['string', Rule::in(['youtube', 'twitter', 'tiktok', 'instagram'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $platforms = $this->input('platforms', SocialPlatform::ACTIVE);

        if (! is_array($platforms)) {
            $platforms = [$platforms];
        }

        $this->merge([
            'topic' => trim((string) $this->input('topic')),
            'platforms' => array_values(array_unique(array_filter($platforms))),
        ]);
    }
}
