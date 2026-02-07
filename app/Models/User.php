<?php

namespace App\Models;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'phone',
        'avatar',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'type' => UserTypeEnum::class,
            'status' => UserStatusEnum::class,
        ];
    }

    public function developerProfile(): HasOne
    {
        return $this->hasOne(DeveloperProfile::class);
    }

    public function lenderProfile(): HasOne
    {
        return $this->hasOne(LenderProfile::class);
    }

    public function isAdmin(): bool
    {
        return $this->type === UserTypeEnum::ADMIN;
    }

    public function isDeveloper(): bool
    {
        return $this->type === UserTypeEnum::DEVELOPER;
    }

    public function isLender(): bool
    {
        return $this->type === UserTypeEnum::LENDER;
    }
}
