<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialContentRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'youtube_link' => 'nullable|string',
            'article_title' => 'nullable|string',
            'article_content' => 'nullable|string',
            'article_images' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
