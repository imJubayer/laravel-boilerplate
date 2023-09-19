<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'zipcode',
        'bio',
        'profile_picture',
    ];

    public function getProfilePictureAttribute()
    {
        if ($this->attributes['profile_picture']) {
            return Storage::disk('public')->url($this->attributes['profile_picture']);
        }

        return null;
    }
}