<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory ;
    protected $fillable = [
        'store_name',
        'store_address',
        'latitude',
        'longitude',
        'store_phone',
        'image',
        'facebook',
        'twitter',
        'instagram',
        'category',
        'store_admin_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
