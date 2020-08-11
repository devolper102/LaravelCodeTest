<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Orchid\Platform\Models\User as Authenticatable;

class User extends Authenticatable {
    use Notifiable, SoftDeletes;

    protected $casts = [
        'last_updates' => 'array',
        'permissions' => 'array'
    ];

    public function role() {
        return $this->belongsToMany(Role::class, 'role_users');
    }
}