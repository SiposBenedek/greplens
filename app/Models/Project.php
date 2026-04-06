<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'api_key_hash',
        'url',
        'description',
        'is_active',
        'created_by',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key_hash',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    public function latestFindings(): HasMany
    {
        return $this->hasMany(Finding::class)
            ->whereRaw('scanned_at = (select max(f2.scanned_at) from findings as f2 where f2.project_id = findings.project_id)');
    }

    /**
     * Generate a new API key. Returns the plain-text key (show once).
     */
    public static function generateApiKey(): string
    {
        return 'glp_' . Str::random(40);
    }

    /**
     * Set the API key — stores the hash.
     * Returns the plain-text key for one-time display.
     */
    public function setApiKey(): string
    {
        $plain = self::generateApiKey();

        $this->api_key_hash = hash('sha256', $plain);

        return $plain;
    }

    /**
     * Verify a plain-text key against the stored hash.
     */
    public function verifyApiKey(string $plain): bool
    {
        return hash_equals($this->api_key_hash, hash('sha256', $plain));
    }

    /**
     * Find an active project by API key
     */
    public static function findByApiKey(string $plain): ?self
    {
        return static::where('is_active', true)
            ->where('api_key_hash', hash('sha256', $plain))
            ->first();
    }
}
