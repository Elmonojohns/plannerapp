<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $with = ['getEstateIndicatorResponsability', 'getEstateIndicatorAdviser', 'getAdviserOffice'];
    protected $fillable = [
        'code',
        'name',
        'email',
        'password',
        'role_id',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getEstateIndicatorResponsability(){
        return $this->hasOne( Estate::class, 'responsible_id', 'id');
    }

    public function getEstateIndicatorAdviser(){
        return $this->hasMany(Estate::class, 'adviser_id', 'id');
    }

    public function getRole()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function getAdviserOffice(){
        return $this->hasMany( AdvisorOffices::class, 'advisor_id');
    }

}
