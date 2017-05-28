<?php

namespace Hareku\LaravelFollow\Traits;

use Hareku\LaravelFollow\Models\FollowRelationship;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

trait Followable
{
    /**
     * @return HasMany
     */
    public function followerRelationships(): HasMany
    {
        return $this->hasMany(FollowRelationship::class, 'followee_id');
    }

    /**
     * @return HasMany
     */
    public function followeeRelationships(): HasMany
    {
        return $this->hasMany(FollowRelationship::class, 'follower_id');
    }

    /**
     * @return BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
                config('follow.user'),
                config('follow.table_name'),
                'followee_id',
                'follower_id'
            )
            ->withPivot('followed_at');
    }

    /**
     * @return BelongsToMany
     */
    public function followees(): BelongsToMany
    {
        return $this->belongsToMany(
                config('follow.user'),
                config('follow.table_name'),
                'follower_id',
                'followee_id'
            )
            ->withPivot('followed_at');
    }

    /**
     * Follow.
     *
     * @param  array|int  $ids
     * @return array
     */
    public function follow($ids): array
    {
        $ids = $this->mergeFollowedAt((array) $ids);

        return $this->followees()->syncWithoutDetaching($ids);
    }

    /**
     * Unfollow.
     *
     * @param  mixed  $ids
     * @return int
     */
    public function unfollow($ids): int
    {
        return $this->followees()->detach($ids);
    }

    /**
     * Merge followed_at to array for relationships table.
     *
     * @param  array  $ids
     * @return array
     */
    private function mergeFollowedAt(array $ids): array
    {
        $followedAt = new Carbon;

        foreach ($ids as $id) {
            $mergedIds[$id] = ['followed_at' => $followedAt];
        }

        return $mergedIds;
    }

    /**
     * Check if it is following.
     *
     * @param  array|int  $id
     * @return bool
     */
    public function isFollowing($id): bool
    {
        if (is_array($id)) {
            return count($id) === $this->followees()->whereIn('followee_id', $id)->count();
        }

        return $this->followees()->where('followee_id', $id)->exists();
    }

    /**
     * Check if it is being followed.
     *
     * @param  array|int  $id
     * @return bool
     */
    public function isFollowedBy($id): bool
    {
        if (is_array($id)) {
            return count($id) === $this->followers()->whereIn('follower_id', $id)->count();
        }

        return $this->followers()->where('follower_id', $id)->exists();
    }

    /**
     * Check if it is mutual follow.
     *
     * @param  array|int  $id
     * @return bool
     */
    public function isMutual($id): bool
    {
        return $this->isFollowing($id) && $this->isFollowedBy($id);
    }

    /**
     * Reject IDs that is not a follower from the given array.
     *
     * @param  array  $ids
     * @return array
     */
    public function rejectNotFollower(array $ids): array
    {
        return FollowRelationship::where('followee_id', $this->id)
                                ->whereIn('follower_id', $ids)
                                ->pluck('follower_id')
                                ->toArray();
    }

    /**
     * Reject an ID that is not followee from the given array.
     *
     * @param  array  $ids
     * @return array
     */
    public function rejectNotFollowee(array $ids): array
    {
        return FollowRelationship::where('follower_id', $this->id)
                                ->whereIn('followee_id', $ids)
                                ->pluck('followee_id')
                                ->toArray();
    }
}
