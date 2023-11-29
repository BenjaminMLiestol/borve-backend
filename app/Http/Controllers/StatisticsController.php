<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function totalCustomers(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = DB::table('customers')->whereNull('deleted_at');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $totalCustomers = $query->count();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Total customers',
            'total_customers' => $totalCustomers,
        ], 200);
    }

    public function totalRevenue(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $customerID = $request->input('customer_id');

        $query = DB::table('orders')->whereNull('deleted_at');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        if ($customerID) {
            $query->where('customer_id', $customerID);
        }

        $totalRevenue = $query->sum('price');

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Total revenue',
            'total_revenue' => $totalRevenue,
        ], 200);
    }

    public function totalOrders(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $customerID = $request->input('customer_id');

        $query = DB::table('orders')->whereNull('deleted_at');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        if ($customerID) {
            $query->where('customer_id', $customerID);
        }

        $totalOrders = $query->count();

        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Total orders',
            'total_orders' => $totalOrders,
        ], 200);
    }
}

