<?php

namespace SultanovSolutions\LaravelBase\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:d.m.Y / H:i:s',
        'updated_at' => 'datetime:d.m.Y / H:i:s',
    ];
}
