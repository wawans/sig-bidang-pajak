<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use Filterable;
    use HasApiTokens, HasRoles, HasUuids;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    use Impersonate, InteractsWithMedia;
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_updated_at',
        'password_expired_at',
        'setting',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_updated_at',
        'password_expired_at',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'media',
        'setting',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
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
            'password_updated_at' => 'datetime',
            'password_expired_at' => 'datetime',
            'setting' => 'json',
        ];
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    protected function getDefaultGuardName(): string
    {
        return 'web';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->dontLogIfAttributesChangedOnly([
                'password',
                'password_updated_at',
                'remember_token',
                'two_factor_recovery_codes',
                'two_factor_secret',
                'updated_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->useFallbackUrl($this->getDefaultAvatar())
            ->singleFile();
    }

    public function getCachedRoleNames()
    {
        return Cache::remember(
            'user.role_names.'.$this->id.$this->updated_at?->unix(), now()->addYear(),
            fn () => $this->getRoleNames()
        );
    }

    public function getCachedPermissionNames()
    {
        return Cache::remember(
            'user.permission_names.'.$this->id.$this->updated_at?->unix(), now()->addYear(),
            fn () => $this->getAllPermissions()->pluck('name')
        );
    }

    public function getDefaultAvatar()
    {
        return Cache::remember('avatar.default.'.$this->id.$this->updated_at?->unix(), now()->addYear(), function () {
            $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
                return mb_substr($segment, 0, 1);
            })->join(' '));

            return 'https://ui-avatars.com/api/?name='.urlencode($name).'&background=random'; // color=7F9CF5&background=EBF4FF
        });
    }

    public function canImpersonate()
    {
        return $this->isAdmin();
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->isAdmin();
    }

    public function isAdmin()
    {
        return $this->hasAnyRole(['SUPER ADMIN', 'ADMIN']);
    }

    final public function toAuth(): string
    {
        return Cache::remember(
            'auth.'.$this->id.$this->updated_at?->unix(),
            now()->addMonth(),
            fn () => collect(
                [
                    ...$this->toArray(),
                    'password_updated_at' => $this->password_updated_at,
                    'password_expired_at' => $this->password_expired_at,
                    'isAdmin' => $this->isAdmin(),
                    'setting' => $this->setting,
                    'roles' => $this->getCachedRoleNames(),
                    'permissions' => $this->getCachedPermissionNames(),
                ])
                ->transform(function ($item, $key) {
                    return base64_encode(json_encode([$key => $item]));
                })
                ->join('|')
        );
    }
}
