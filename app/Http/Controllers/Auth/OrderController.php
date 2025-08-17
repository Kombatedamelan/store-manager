<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search'); // récupère ?search=xxx

        $query = Order::with('orderDetails');

        if ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%');
        }

        $orders = $query->orderBy('name')->get();

        $data = $orders->map(function ($order) {
            $totalQuantity = $order->orderDetails->sum('quantity');

            return [
                'id' => $order->id,
                'date' => $order->created_at->format('d/m/Y H:i:s'),
                'customer' => $order->name,
                'quantity' => $totalQuantity,
                'total' => (float) $order->total,
                'payment' => $order->payment,
                'status' => $order->status,
            ];
        });

        return response()->json($data);
    }

    

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'total' => 0,
        ]);

        $total = 0;
        $qte = 0;

        foreach ($request->products as $productData) {
            $product = Product::findOrFail($productData['id']);
            $quantity = $productData['quantity'];
            $price = $product->price;


            OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
                
            ]);
            $product->decrement('qte', $quantity);
            $total += $price * $quantity;
          
        }

       
        $order->update(['total' => $total]);

        return response()->json($order->load('orderDetails.product'), 201);
    }

    public function show($id)
    {
        $order = Order::with('orderDetails.product')->findOrFail($id);
        return response()->json($order);
    }

    public function destroy($id)
    {
        Order::destroy($id);
        return response()->json(['message' => 'Commande supprimée']);
    }

    public function downloadPdf($id)
    {
        $order = Order::with('orderDetails.product')->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));

        return $pdf->download('facture_commande_' . $order->id . '.pdf');
    }
}
