<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreAdmin extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'facebook',
        'twitter',
        'instagram',
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
