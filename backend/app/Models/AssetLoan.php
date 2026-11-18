<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLoan extends Model
{
    protected $table = 'assets_loan';

    public $timestamps = false;

    protected $fillable = [
        'assets_id',
        'borrower_id',
        'borrowed_at',
        'returned_at',
        'status',
    ];

    protected $hidden = ['active_assets_id'];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'assets_id');
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }
}
