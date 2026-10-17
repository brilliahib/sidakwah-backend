<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateCommentRequest extends FormRequest
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
            'material_content_id' => 'required|exists:material_contents,id',
            'parent_id' => 'nullable|exists:comments,id',
            'content' => 'required|string|max:5000',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!is_string($this->input('content')) || trim($this->input('content')) === '') {
                $validator->errors()->add('content', 'Komentar tidak boleh kosong.');
            }

            if (!$this->filled('parent_id')) {
                return;
            }

            $parent = Comment::find($this->input('parent_id'));
            if (!$parent) {
                return;
            }

            if ((int) $parent->material_content_id !== (int) $this->input('material_content_id')) {
                $validator->errors()->add('parent_id', 'Balasan harus berada di materi yang sama.');
            }

            if ($parent->status !== Comment::STATUS_APPROVED) {
                $validator->errors()->add('parent_id', 'Balasan hanya dapat dibuat pada diskusi yang disetujui.');
            }
        });
    }
}
