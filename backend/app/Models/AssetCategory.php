<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $table = 'assets_categories';

    public $timestamps = false;

    protected $fillable = ['name'];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
