<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = 'products';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'stock',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];
}
