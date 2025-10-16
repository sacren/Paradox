<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Like extends Pivot
{
    /** @use HasFactory<\Database\Factories\LikeFactory> */
    use HasFactory;

    /** Explicit table name */
    protected $table = 'likes';

    protected $fillable = ['user_id', 'post_id'];

    /**
     * Get the user that owns the Like
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that owns the Like
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
