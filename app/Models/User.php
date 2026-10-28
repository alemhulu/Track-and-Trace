<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'position',
        'organization_id',
        'access_level',
        'country_id',
        'region_id',
        'zone_id',
        'woreda_id',
        'profile_photo_path'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public const ACCESS_LEVEL_NATIONAL = 'national';
    public const ACCESS_LEVEL_REGION = 'region';
    public const ACCESS_LEVEL_ZONE = 'zone';
    public const ACCESS_LEVEL_WOREDA = 'woreda';
    public const ACCESS_LEVEL_ORGANIZATION = 'organization';

    public const ACCESS_LEVEL_RANK = [
        self::ACCESS_LEVEL_ORGANIZATION => 1,
        self::ACCESS_LEVEL_WOREDA => 2,
        self::ACCESS_LEVEL_ZONE => 3,
        self::ACCESS_LEVEL_REGION => 4,
        self::ACCESS_LEVEL_NATIONAL => 5,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    // On the user table change user_id to organization_id
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function woreda()
    {
        return $this->belongsTo(Woreda::class, 'woreda_id');
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function effectiveAccessLevel(): string
    {
        if (! empty($this->access_level)) {
            return $this->access_level;
        }

        if ($this->hasRole('Super-Admin')) {
            return self::ACCESS_LEVEL_NATIONAL;
        }

        if (! empty($this->woreda_id)) {
            return self::ACCESS_LEVEL_WOREDA;
        }

        if (! empty($this->zone_id)) {
            return self::ACCESS_LEVEL_ZONE;
        }

        if (! empty($this->region_id)) {
            return self::ACCESS_LEVEL_REGION;
        }

        if (! empty($this->organization_id)) {
            return self::ACCESS_LEVEL_ORGANIZATION;
        }

        return self::ACCESS_LEVEL_NATIONAL;
    }

    public function hasNationalAccess(): bool
    {
        return $this->hasRole('Super-Admin') || $this->effectiveAccessLevel() === self::ACCESS_LEVEL_NATIONAL;
    }

    // School staff receive shipments and keep school records; they never send books on
    public function isSchoolUser(): bool
    {
        if ($this->hasRole('School User')) {
            return true;
        }

        return $this->effectiveAccessLevel() === self::ACCESS_LEVEL_ORGANIZATION
            && $this->organization?->organizationType?->name === 'School';
    }

    // Book titles are national reference data
    public function canManageBookTitles(): bool
    {
        return $this->hasAnyRole(['Super-Admin', 'Admin']);
    }

    /**
     * The region, zone and woreda this user is confined to; null means not confined at that
     * depth. A level whose location is missing yields 0, which matches nothing.
     *
     * @return array{region_id: ?int, zone_id: ?int, woreda_id: ?int}
     */
    public function locationScope(): array
    {
        if ($this->hasNationalAccess()) {
            return ['region_id' => null, 'zone_id' => null, 'woreda_id' => null];
        }

        $level = $this->effectiveAccessLevel();

        // Organization users sit wherever their organization does
        if ($level === self::ACCESS_LEVEL_ORGANIZATION) {
            $organization = $this->organization;

            return [
                'region_id' => (int) ($organization->region_id ?? 0),
                'zone_id' => $organization?->zone_id ? (int) $organization->zone_id : null,
                'woreda_id' => $organization?->woreda_id ? (int) $organization->woreda_id : null,
            ];
        }

        $rank = self::accessLevelRank($level);

        return [
            'region_id' => (int) ($this->region_id ?? 0),
            'zone_id' => $rank <= self::accessLevelRank(self::ACCESS_LEVEL_ZONE) ? (int) ($this->zone_id ?? 0) : null,
            'woreda_id' => $rank <= self::accessLevelRank(self::ACCESS_LEVEL_WOREDA) ? (int) ($this->woreda_id ?? 0) : null,
        ];
    }

    public static function accessLevelRank(?string $level): int
    {
        return self::ACCESS_LEVEL_RANK[$level ?? ''] ?? 0;
    }

    public function canManageAccessLevel(string $targetLevel): bool
    {
        if ($this->hasRole('Super-Admin')) {
            return true;
        }

        $selfRank = self::accessLevelRank($this->effectiveAccessLevel());
        $targetRank = self::accessLevelRank($targetLevel);

        return $targetRank > 0 && $targetRank <= $selfRank;
    }

    public function scopeAccessibleBy($query, User $actor)
    {
        if ($actor->hasNationalAccess()) {
            return $query;
        }

        $level = $actor->effectiveAccessLevel();

        if ($level === self::ACCESS_LEVEL_REGION && ! empty($actor->region_id)) {
            return $query->where('region_id', $actor->region_id);
        }

        if ($level === self::ACCESS_LEVEL_ZONE && ! empty($actor->zone_id)) {
            return $query->where('region_id', $actor->region_id)->where('zone_id', $actor->zone_id);
        }

        if ($level === self::ACCESS_LEVEL_WOREDA && ! empty($actor->woreda_id)) {
            return $query
                ->where('region_id', $actor->region_id)
                ->where('zone_id', $actor->zone_id)
                ->where('woreda_id', $actor->woreda_id);
        }

        if ($level === self::ACCESS_LEVEL_ORGANIZATION && ! empty($actor->organization_id)) {
            return $query->where('organization_id', $actor->organization_id);
        }

        return $query->where('id', $actor->id);
    }
}
