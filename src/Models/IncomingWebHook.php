<?php

namespace Triverla\LaravelMonnify\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingWebHook extends Model
{
    protected $guarded = [];

    protected $fillable = ['transactionReference', 'paymentReference', 'amountPaid', 'totalPayable', 'paidOn', 'paymentStatus', 'paymentDescription', 'transactionHash', 'currency', 'paymentMethod'];
}
