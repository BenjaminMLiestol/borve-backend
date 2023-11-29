<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
        ];        
    
        $query = DB::table('orders')
            ->join('customers', 'orders.customer_id', '=', 'customers.customer_id')
            ->whereNull('orders.deleted_at')
            ->when($queryParams['date_from'], fn ($query) => $query->whereDate('orders.created_at', '>=', $queryParams['date_from']))
            ->when($queryParams['date_to'], fn ($query) => $query->whereDate('orders.created_at', '<=', $queryParams['date_to']))
            ->when($queryParams['customer_id'], fn ($query) => $query->where('orders.customer_id', $queryParams['customer_id']))
            ->when($queryParams['sort'] === 'desc', fn ($query) => $query->orderByDesc('orders.created_at'), fn ($query) => $query->orderBy('orders.created_at'));
    
        $result = $queryParams['paginate']
            ? $query->paginate($queryParams['limit'], ['*'], 'page', $queryParams['page'])
            : $query->limit($queryParams['limit'])->get();
    
        $orderList = $result->map(function ($order) {
            return [
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
            ];
        });
    
        return response()->json($orderList, 200);
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

    // Get the currently authenticated user
    $currentUser = Auth::user();

    $data = $request->json()->all();

    $newOrder = new Order([
        'customer_id' => $data['customer_id'],
        'price' => $data['price'],
        'address_from' => $data['address_from'],
        'address_to' => $data['address_to'],
        'start_time' => $data['start_time'],
        'comment' => $data['comment'],
        'passenger_count' => $data['passenger_count'],
        'created_by' => $currentUser->id, // Set created_by to the current user's ID
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
            'order_id' => 'required|integer',
        ]);

        $orderId = $request->input('order_id');

        $order = Order::find($orderId);

        if ($order) {
            try {
                $order->delete();
                return response()->json(['message' => 'Order deleted successfully'], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }
}
