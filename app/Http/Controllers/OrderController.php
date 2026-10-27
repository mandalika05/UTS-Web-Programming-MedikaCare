<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Sale::where('customer_id', $request->user()->id)
            ->withCount('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('shop.orders', compact('orders'));
    }

    public function show(Request $request, Sale $sale)
    {
        $this->authorizeOwner($request, $sale);

        $sale->load('items.product');

        return view('shop.order', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale)
    {
        $this->authorizeOwner($request, $sale);

        try {
            DB::transaction(function () use ($sale) {
                $locked = Sale::lockForUpdate()->findOrFail($sale->id);

                // kalau Staff sudah memproses lebih dulu, pembatalan ditolak
                if ($locked->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'status' => 'Pesanan ini sudah diproses, jadi tidak bisa dibatalkan lagi.',
                    ]);
                }

                $locked->update(['status' => 'cancelled']);
            });
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('orders.show', $sale)
            ->with('success', 'Pesanan dibatalkan.');
    }

    public function receipt(Request $request, Sale $sale)
    {
        $this->authorizeOwner($request, $sale);
        abort_unless($sale->status === 'completed', 404, 'Struk hanya tersedia untuk pesanan yang sudah selesai.');

        $sale->load(['user', 'items.product']);

        return view('sales.receipt', [
            'sale' => $sale,
            'backUrl' => route('orders.show', $sale),
        ]);
    }

    // Customer hanya boleh membuka pesanan miliknya sendiri
    private function authorizeOwner(Request $request, Sale $sale): void
    {
        abort_unless($sale->customer_id === $request->user()->id, 403, 'Pesanan ini bukan milik Anda.');
    }
}