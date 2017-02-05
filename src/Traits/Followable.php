<?php

namespace Hareku\LaravelFollow\Traits;

use Hareku\LaravelFollow\Models\FollowRelationship;
use Hareku\LaravelFollow\Models\FollowRelationshipForPivot;
use Carbon\Carbon;

trait Followable
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function followerRelationships()
    {
        return $this->hasMany(FollowRelationship::class, 'followee_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function followeeRelationships()
    {
        return $this->hasMany(FollowRelationship::class, 'follower_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(config('follow.user'), config('follow.table_name'), 'followee_id', 'follower_id')
                    ->withPivot('followed_at');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followees()
    {
        return $this->belongsToMany(config('follow.user'), config('follow.table_name'), 'follower_id', 'followee_id')
                    ->withPivot('followed_at');
    }

    /**
     * Follow.
     *
     * @param  array|int  $ids
     * @return array
     */
    public function follow($ids)
    {
        $ids = $this->mergeFollowedAt((array) $ids);

        return $this->followees()->syncWithoutDetaching($ids);
    }

    /**
     * Unfollow.
     *
     * @param  array|int  $ids
     * @return array
     */
    public function unfollow($ids)
    {
        return $this->followees()->detach((array) $ids);
    }

    /**
     * Merge followed_at to array for intermediate table.
     *
     * @param  array  $ids
     * @return array
     */
    private function mergeFollowedAt(array $ids)
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
    public function isFollowing($id)
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
    public function isFollowedBy($id)
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
    public function isMutual($id)
    {
        return $this->isFollowing($id) && $this->isFollowedBy($id);
    }
}
