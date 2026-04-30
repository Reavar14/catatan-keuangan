<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                   => ['nullable', 'in:income,expense'],
            'category_id'            => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'transaction_date_from'  => ['nullable', 'date_format:Y-m-d'],
            'transaction_date_to'    => ['nullable', 'date_format:Y-m-d', 'after_or_equal:transaction_date_from'],
            'sort_by'                => ['nullable', 'in:transaction_date,amount'],
            'sort_order'             => ['nullable', 'in:asc,desc'],
            'per_page'               => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'                   => ['nullable', 'integer', 'min:1'],
            'search'                 => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in'                        => 'Filter tipe harus income atau expense.',
            'category_id.exists'             => 'Kategori filter tidak valid atau bukan milik Anda.',
            'transaction_date_from.date_format' => 'Format tanggal mulai harus YYYY-MM-DD.',
            'transaction_date_to.date_format'   => 'Format tanggal akhir harus YYYY-MM-DD.',
            'transaction_date_to.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal mulai.',
            'sort_by.in'                     => 'Kolom sorting harus transaction_date atau amount.',
            'sort_order.in'                  => 'Arah sorting harus asc atau desc.',
            'per_page.integer'               => 'Per halaman harus berupa angka.',
            'per_page.min'                   => 'Per halaman minimal 1.',
            'per_page.max'                   => 'Per halaman maksimal 100.',
        ];
    }
}
