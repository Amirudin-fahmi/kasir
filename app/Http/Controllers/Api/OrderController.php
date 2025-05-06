<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index()
    {
        $orders  = Order::with('OrderProducts', 'paymentMethod')->get();

        $orders->transform(function ($order) {
            $order->payment_method = $order->paymentMethod->name ?? '-';
            $order->orderProducts->transform(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? '-',
                    'quantity' => $item->quantity ?? 0,
                    'unit_price' => $item->product->price ?? 0
                ];
            });

            return $order;
        });

        return response()->json($orders);
    }


    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'nullable|email',
            'gender' => 'required|in:male,female',
            'birthday' => 'nullable|date',
            'phone' => 'nullable|string',
            'total_price' => 'required|numeric',
            'notes' => 'nullable|string',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'order_products' => 'required|array',
            'order_products.*.product_id' => 'required|exists:products,id',
            'order_products.*.quantity' => 'required|integer|min:1',
            'order_products.*.unit_price' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ada kesalahan dalam validasi',
                'error' => $validator->errors(),
            ], 422);
        }

        foreach ($request->items ?? [] as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $item['quantity'] > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok barang kosong ' . $product->name,
                ], 422);
            }
        }

        $order = Order::create([
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'phone' => $request->phone,
            'total_price' => $request->total_price,
            'notes' => $request->notes,
            'payment_method_id' => $request->payment_method_id,
            'paid_amount' => $request->paid_amount ?? 0,
            'change_amount' => $request->change_amount ?? 0,
        ]);        

        foreach ($request->items ?? [] as $item) {
            $order->orderProducts()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dibuat',
            'data' => $order
        ]);
    }
}
