<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Charge extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'amount',
        'user_id',
        'status'
    ];
    /**
     * Get the transactions for the charge request.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
