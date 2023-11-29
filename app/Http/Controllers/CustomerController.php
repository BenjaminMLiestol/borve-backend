<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function create(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'company_name' => 'required|string',
            'contact_name' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|string',
            'country' => 'required|string',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string',
        ]);

        // Create a new customer
        $newCustomer = new Customer($validatedData);

        try {
            $newCustomer->save();
            return response()->json(['message' => 'Customer created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        // Define validation rules for query parameters
        $parser = [
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
            'customer_id' => 'nullable|string',
            'sort' => 'nullable|in:asc,desc',
            'paginate' => 'nullable|boolean',
            'limit' => 'nullable|integer',
            'page' => 'nullable|integer',
        ];
    
        // Validate the request data
        $validatedData = $request->validate($parser);
    
        // Build the query parameters
        $queryParams = [
            'date_from' => $validatedData['date_from'] ?? null,
            'date_to' => $validatedData['date_to'] ?? null,
            'customer_id' => $validatedData['customer_id'] ?? null,
            'sort' => $validatedData['sort'] ?? 'asc',
            'paginate' => $validatedData['paginate'] ?? true,
            'limit' => $validatedData['limit'] ?? 10,
            'page' => $validatedData['page'] ?? 1,
        ];
    
        // Query customers based on the parameters using the Query Builder
        $query = DB::table('customers')
            ->when($queryParams['date_from'], fn ($query) => $query->whereDate('created_at', '>=', $queryParams['date_from']))
            ->when($queryParams['date_to'], fn ($query) => $query->whereDate('created_at', '<=', $queryParams['date_to']))
            ->when($queryParams['customer_id'], fn ($query) => $query->where('customer_id', $queryParams['customer_id']))
            ->orderBy('created_at', $queryParams['sort'] === 'desc' ? 'desc' : 'asc');
    
        // Paginate the results if needed
        $customers = $queryParams['paginate']
            ? $query->paginate($queryParams['limit'], ['*'], 'page', $queryParams['page'])
            : $query->get();
    
        // Map the customers to the desired format
        $customerList = $customers->map(function ($customer) {
            return [
                'id' => $customer->customer_id,
                'company_name' => $customer->company_name,
                'contact_name' => $customer->contact_name,
                'address' => $customer->address,
                'city' => $customer->city,
                'zip_code' => $customer->zip_code,
                'country' => $customer->country,
                'contact_email' => $customer->contact_email,
                'contact_phone' => $customer->contact_phone,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
            ];
        });
    
        return response()->json($customerList, 200);
    }
}