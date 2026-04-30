<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'gt:0'],
            'type'             => ['required', 'in:income,expense'],
            'category_id'      => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $this->user()->id),
            ],
            'transaction_date' => ['required', 'date_format:Y-m-d'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'            => 'Judul transaksi wajib diisi.',
            'amount.required'           => 'Nominal wajib diisi.',
            'amount.numeric'            => 'Nominal harus berupa angka.',
            'amount.gt'                 => 'Nominal harus lebih dari 0.',
            'type.required'             => 'Tipe transaksi wajib dipilih.',
            'type.in'                   => 'Tipe transaksi harus pemasukan (income) atau pengeluaran (expense).',
            'category_id.required'      => 'Kategori wajib dipilih.',
            'category_id.exists'        => 'Kategori tidak valid atau bukan milik Anda.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'notes.max'                 => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
