<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'company_name',
        'contact_name',
        'address',
        'city',
        'postal_code',
        'contact_email',
        'contact_phone',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // Generate a unique customer_id before saving
        static::creating(function ($customer) {
            $customer->customer_id = Str::uuid();
        });
    }
}
