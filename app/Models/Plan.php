<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'description',
        'permission_id',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
