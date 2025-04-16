<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
   protected $fillable = [
        'license_key',
        'user_id',
        'product_id',
        'validation_rules',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'validation_rules' => 'array',
      	'update_payload' => 'array',
    ];
  
  	public function user()
    {
        return $this->belongsTo(User::class);
    }
  
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
