<?php

namespace App\Http\Requests\ExampleFile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ExampleFileStoreRequest extends FormRequest
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
            'name' => ['required', 'string:256'],
            'file' => ['required' , File::types(['csv', 'xls', 'xlsx'])->max(10*1024)] //('10mb')
        ];
    }
}
