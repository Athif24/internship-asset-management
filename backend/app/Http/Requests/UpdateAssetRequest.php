<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $asset = $this->route('asset');

        return [
            'assets_code' => [
                'required', 'string', 'max:40',
                Rule::unique('assets', 'assets_code')->ignore($asset),
            ],
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:assets_categories,id'],
            // BORROWED tidak boleh diset manual: hanya stored procedure yang boleh.
            'status' => ['sometimes', 'in:AVAILABLE,MAINTENANCE'],
            'purchase_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'purchase_year' => ['sometimes', 'nullable', 'integer', 'between:1900,'.(date('Y') + 1)],
        ];
    }
}
