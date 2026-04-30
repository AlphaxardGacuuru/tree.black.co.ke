<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'family_tree_id',
        'inviter_id',
        'relationship_type',
        'used_by_id',
        'used_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function familyTree(): BelongsTo
    {
        return $this->belongsTo(FamilyTree::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_id');
    }

    public static function generateToken(): string
    {
        return Str::random(32);
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && $this->used_at === null;
    }
}
