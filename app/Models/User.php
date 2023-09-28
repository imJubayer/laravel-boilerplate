<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use LucasDotVin\Soulbscription\Models\Concerns\HasSubscriptions;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasSubscriptions;
    
    protected $guard_name = 'sanctum';
    protected $appends = ['full_name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Define an accessor for the profile picture URL.
     */
    public function getProfilePictureAttribute()
    {
        return $this->profile ? $this->profile->profile_picture : null;
    }

    /**
     * Define an accessor for the full name.
     */
    public function getFullNameAttribute()
    {
        return $this->profile->first_name . ' ' . $this->profile->last_name;
    }
}