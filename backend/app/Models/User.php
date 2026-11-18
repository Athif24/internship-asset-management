<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'division_id',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * Schema memakai password_hash, bukan password seperti default Laravel.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function loans()
    {
        return $this->hasMany(AssetLoan::class, 'borrower_id');
    }
}
