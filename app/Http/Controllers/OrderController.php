<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
{
    $parser = [
        'date_from' => 'nullable|date_format:Y-m-d',
        'date_to' => 'nullable|date_format:Y-m-d',
        'customer_id' => 'nullable|string',
        'sort' => 'nullable|in:asc,desc',
        'paginate' => 'nullable|boolean',
        'limit' => 'nullable|integer',
        'page' => 'nullable|integer',
        'sort_by' => 'nullable|string'
    ];

    $validatedData = $request->validate($parser);

    $queryParams = [
        'date_from' => $validatedData['date_from'] ?? null,
        'date_to' => $validatedData['date_to'] ?? null,
        'customer_id' => $validatedData['customer_id'] ?? null,
        'sort' => $validatedData['sort'] ?? 'asc',
        'paginate' => $validatedData['paginate'] ?? true,
        'limit' => $validatedData['limit'] ?? 10,
        'page' => $validatedData['page'] ?? 1,
        'sort_by' => $validatedData['sort_by'] ?? 'created_at',
    ];

$query = DB::table('orders')
    ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
    ->whereNull('orders.deleted_at')
    ->when($queryParams['date_from'], function ($query) use ($queryParams) {
        return $query->whereDate('orders.created_at', '>=', $queryParams['date_from']);
    })
    ->when($queryParams['date_to'], function ($query) use ($queryParams) {
        return $query->whereDate('orders.created_at', '<=', $queryParams['date_to']);
    })
    ->when($queryParams['customer_id'], function ($query) use ($queryParams) {
        return $query->where('orders.customer_id', $queryParams['customer_id']);
    });

// Order by clause based on the requested sort column
if ($queryParams['sort_by'] === 'created_at') {
    $query->orderBy('orders.created_at', $queryParams['sort']);
} else {
    $query->orderBy($queryParams['sort_by'], $queryParams['sort']);
}

// Execute the query
$result = $queryParams['paginate']
    ? $query->paginate($queryParams['limit'], ['*'], 'page', $queryParams['page'])
    : $query->limit($queryParams['limit'])->get();

    $orderList = $result->map(function ($order) {
        return [
            'order_id' => $order->order_id,
            'customer' => $order->company_name,
            'price' => $order->price,
            'address_from' => $order->address_from,
            'address_to' => $order->address_to,
            'start_time' => date('d.m.Y H:i:s', strtotime($order->start_time)),
            'time_spent' => $order->time_spent,
            'km_driven' => $order->km_driven,
            'comment' => $order->comment,
            'status' => 0,
            'passenger_count' => $order->passenger_count,
            'created_at' => date('d.m.Y H:i:s', strtotime($order->created_at)),
            'updated_at' => date('d.m.Y H:i:s', strtotime($order->updated_at)),
            'completed_at' => $order->completed_at ? date('d.m.Y H:i:s', strtotime($order->completed_at)) : null,
        ];
    });

    $totalOrders = $result->total();
    $totalPages = ceil($totalOrders / $queryParams['limit']);
    $currentPage = $queryParams['page'];

    return response()->json([
        'orders' => $orderList,
        'total_pages' => $totalPages,
        'total_orders' => $totalOrders,
        'current_page' => $currentPage
    ], 200);
}
  

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|string',
            'price' => 'required',
            'address_from' => 'required|string',
            'address_to' => 'required|string',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'comment' => 'nullable|string',
            'passenger_count' => 'required|integer',
        ]);

        $data = $request->json()->all();

        $newOrder = new Order([
            'order_id' => Str::uuid(),
            'customer_id' => $data['customer_id'],
            'price' => $data['price'],
            'address_from' => $data['address_from'],
            'address_to' => $data['address_to'],
            'start_time' => $data['start_time'],
            'comment' => $data['comment'],
            'passenger_count' => $data['passenger_count'],
            'created_by' => auth()->user()->user_id, 
        ]);

        try {
            $newOrder->save();
            return response()->json(['message' => 'Order created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'order_number' => 'required|string',
            'product_name' => 'required|string',
            'quantity' => 'required|integer',
        ]);

        $orderId = $request->input('order_id');
        $data = $request->json()->all();

        $order = Order::find($orderId);

        if ($order) {
            $order->order_number = $data['order_number'];
            $order->product_name = $data['product_name'];
            $order->quantity = $data['quantity'];

            try {
                $order->save();
                return response()->json(['message' => 'Order updated successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $orderId = $request->input('order_id');

        $order = Order::where('order_id', $orderId)->first();

        if ($order) {
            try {
                $order->delete();
                return response()->json(['message' => 'Ordre slettet'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Fant ikke ordre, prøv igjen senere'], 404);
        }
    }
}
