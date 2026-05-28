<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ArticleValidationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'author';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|max:255|min:6',
            'excerpt' => 'required|max:80|min:10',
            'body' => 'required|min:30|max:50000',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required',
            'scheduled_hours' => 'nullable|integer|min:1|max:48',
            'scheduled_minutes' => 'required_if:status,scheduled|nullable|integer|min:1|max:59',
            'cover_path' => 'nullable|image|',
        ];
    }
}
