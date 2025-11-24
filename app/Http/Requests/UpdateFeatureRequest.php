<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Feature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFeatureRequest extends FormRequest
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
            'title' => 'sometimes|string|min:5|max:200',
            'description' => 'sometimes|nullable|string|max:5000',
            'status' => ['sometimes', Rule::in(Feature::STATUSES)],
            'meta' => 'sometimes|nullable|array',
        ];
    }
}
