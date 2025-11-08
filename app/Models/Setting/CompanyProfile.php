<?php

namespace App\Models\Setting;

use App\Concerns\Blamable;
use App\Concerns\CompanyOwned;
use App\Enums\Setting\EntityType;
use App\Models\Common\Address;
use Database\Factories\Setting\CompanyProfileFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyProfile extends Model
{
    use Blamable;
    use CompanyOwned;
    use HasFactory;

    protected $table = 'company_profiles';

    protected $fillable = [
        'company_id',
        'name',
        'logo',
        'phone_number',
        'email',
        'tax_id',
        'entity_type',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'entity_type' => EntityType::class,
        'is_default' => 'boolean',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $profile) {
            if (blank($profile->name)) {
                $profile->name = 'Default';
            }

            if ($profile->is_default === null) {
                $profile->is_default = false;
            }

            if (! $profile->is_default) {
                $profile->is_default = ! static::query()
                    ->where('company_id', $profile->company_id)
                    ->where('is_default', true)
                    ->exists();
            }

            $profile->name = Str::of($profile->name)->trim()->value();
        });

        static::saved(function (self $profile) {
            if ($profile->is_default) {
                static::query()
                    ->where('company_id', $profile->company_id)
                    ->whereKeyNot($profile->getKey())
                    ->update(['is_default' => false]);

                return;
            }

            $hasDefault = static::query()
                ->where('company_id', $profile->company_id)
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                $profile->updateQuietly(['is_default' => true]);
            }
        });

        static::deleted(function (self $profile) {
            $hasDefault = static::query()
                ->where('company_id', $profile->company_id)
                ->where('is_default', true)
                ->exists();

            if ($hasDefault) {
                return;
            }

            $nextDefault = static::query()
                ->where('company_id', $profile->company_id)
                ->first();

            if ($nextDefault) {
                $nextDefault->updateQuietly(['is_default' => true]);
            }
        });
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(static function (mixed $value, array $attributes): ?string {
            if ($attributes['logo']) {
                return Storage::disk('public')->url($attributes['logo']);
            }

            return null;
        });
    }

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    protected static function newFactory(): Factory
    {
        return CompanyProfileFactory::new();
    }
}
