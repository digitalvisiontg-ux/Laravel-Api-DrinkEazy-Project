<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Str;

class Table extends Model
{
    //
    protected $fillable = [
        'numero_table',
        'token',
        'actif'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            $table->token = Str::uuid();
        });
    }
}
