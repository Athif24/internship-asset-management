<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 'assets';

    public $timestamps = false;

    protected $fillable = [
        'assets_code',
        'name',
        'category_id',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function loans()
    {
        return $this->hasMany(AssetLoan::class, 'assets_id');
    }

    public function activeLoan()
    {
        return $this->hasOne(AssetLoan::class, 'assets_id')->whereNull('returned_at');
    }
}
