<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // AST-01: divalidasi di server, unique index di DB sebagai pengaman akhir.
            'assets_code' => ['required', 'string', 'max:40', 'unique:assets,assets_code'],
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:assets_categories,id'],
            'status' => ['sometimes', 'in:AVAILABLE,MAINTENANCE'],
        ];
    }
}
