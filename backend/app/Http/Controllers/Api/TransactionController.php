<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\IndexTransactionRequest;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * GET /api/transactions
     * Daftar transaksi milik user yang sedang login,
     * dengan dukungan filter, search, sorting, dan pagination.
     */
    public function index(IndexTransactionRequest $request): JsonResponse
    {
        $query = Transaction::with('category')
            ->where('user_id', $request->user()->id);

        // ── Filter ───────────────────────────────────────────────────────────
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('transaction_date_from')) {
            $query->whereDate('transaction_date', '>=', $request->transaction_date_from);
        }

        if ($request->filled('transaction_date_to')) {
            $query->whereDate('transaction_date', '<=', $request->transaction_date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // ── Sorting ───────────────────────────────────────────────────────────
        $allowedSortColumns = ['transaction_date', 'amount', 'created_at'];
        $sortBy    = in_array($request->input('sort_by'), $allowedSortColumns)
            ? $request->input('sort_by')
            : 'transaction_date';
        $sortOrder = $request->input('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        // ── Pagination ────────────────────────────────────────────────────────
        $perPage = (int) $request->input('per_page', 10);
        $result  = $query->paginate($perPage);

        return response()->json([
            'data' => TransactionResource::collection($result),
            'meta' => [
                'current_page' => $result->currentPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
                'last_page'    => $result->lastPage(),
                'from'         => $result->firstItem(),
                'to'           => $result->lastItem(),
            ],
        ]);
    }

    /**
     * POST /api/transactions
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = Transaction::create([
            'user_id'          => $request->user()->id,
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'amount'           => $request->amount,
            'type'             => $request->type,
            'transaction_date' => $request->transaction_date,
            'notes'            => $request->notes,
        ]);

        $transaction->load('category');

        return response()->json([
            'message' => 'Transaksi berhasil dibuat.',
            'data'    => new TransactionResource($transaction),
        ], 201);
    }

    /**
     * GET /api/transactions/{transaction}
     * Route model binding — Laravel otomatis resolve Transaction by ID.
     * Policy memastikan hanya pemilik yang bisa melihat.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);

        $transaction->load('category');

        return response()->json([
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * PUT /api/transactions/{transaction}
     * Policy memastikan hanya pemilik yang bisa mengedit.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update([
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'amount'           => $request->amount,
            'type'             => $request->type,
            'transaction_date' => $request->transaction_date,
            'notes'            => $request->notes,
        ]);

        $transaction->load('category');

        return response()->json([
            'message' => 'Transaksi berhasil diperbarui.',
            'data'    => new TransactionResource($transaction),
        ]);
    }

    /**
     * DELETE /api/transactions/{transaction}
     * Policy memastikan hanya pemilik yang bisa menghapus.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }
}
