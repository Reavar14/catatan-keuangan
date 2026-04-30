<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Semua transaksi hanya bisa diakses oleh pemiliknya.
     * Method ini dipanggil sebelum method lain (before hook).
     * Return null = lanjut ke method spesifik.
     * Return false = tolak semua aksi tanpa cek lebih lanjut.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Tidak ada super-admin, semua user diperlakukan sama.
        return null;
    }

    /**
     * Izinkan user melihat daftar transaksi miliknya sendiri.
     * Dipanggil oleh: $this->authorize('viewAny', Transaction::class)
     */
    public function viewAny(User $user): bool
    {
        return true; // semua user terautentikasi boleh melihat daftar (difilter by user_id di query)
    }

    /**
     * Izinkan user melihat detail transaksi miliknya.
     * Dipanggil oleh: $this->authorize('view', $transaction)
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    /**
     * Izinkan user membuat transaksi baru.
     * Dipanggil oleh: $this->authorize('create', Transaction::class)
     */
    public function create(User $user): bool
    {
        return true; // semua user terautentikasi boleh membuat transaksi
    }

    /**
     * Izinkan user mengedit transaksi miliknya sendiri.
     * Dipanggil oleh: $this->authorize('update', $transaction)
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }

    /**
     * Izinkan user menghapus transaksi miliknya sendiri.
     * Dipanggil oleh: $this->authorize('delete', $transaction)
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->id === $transaction->user_id;
    }
}
