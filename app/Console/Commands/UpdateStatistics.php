<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Statistic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStatistics extends Command
{
    protected $signature = 'statistics:update';

    protected $description = 'Update statistics daily';

    public function handle()
    {

        $this->totalCustomers();
        $this->totalRevenue();
        $this->totalOrders();
    }

    protected function totalCustomers()
    {
        $dateFrom = Carbon::today()->toDateString();
        $dateTo = Carbon::today()->toDateString();

        $query = DB::table('customers')->whereNull('deleted_at');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $totalCustomers = $query->count();

         Statistic::create([
            'type' => 'customer',
            'date' => Carbon::today(),
            'count' => $totalCustomers,
        ]);
    }

    protected function totalRevenue()
    {
        $dateFrom = Carbon::today()->toDateString();
        $dateTo = Carbon::today()->toDateString();

        $query = DB::table('orders')->whereNull('deleted_at');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $totalRevenue = $query->sum('price');

       Statistic::create([
            'type' => 'revenue',
            'date' => Carbon::today(),
            'count' => $totalRevenue,
        ]);
    }

    protected function totalOrders()
    {
        $dateFrom = Carbon::today()->toDateString();
        $dateTo = Carbon::today()->toDateString();

        $query = DB::table('orders')->whereNull('deleted_at');

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $totalOrders = $query->count();

        Statistic::create([
            'type' => 'order',
            'date' => Carbon::today(),
            'count' => $totalOrders,
        ]);
    }
}
