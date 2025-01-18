<?php

namespace App\Http\Requests\Article;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->hasRole(Role::SUPER_ADMIN_ROLE->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string:100',
                Rule::unique('articles')->where('category_id', $this->category_id),
            ],
            'stock' => ['sometimes', 'integer'],
            'category_id' => ['required', 'exists:categories,id'],
            'price_unit' => ['sometimes', 'decimal:2'],
        ];
    }
}
