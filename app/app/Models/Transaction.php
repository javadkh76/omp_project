<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'charge_id',
        'card_id',
        'payment_id',
        'wage',
        'wage',
        'ref_number',
        'tracking_code',
        'payment_gateway',
        'status'
    ];
}
