<?php

namespace Hareku\LaravelFollow\Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FollowableTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_follow_user()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();

        $follower->follow($followee->id);

        $this->assertDatabaseHas(config('follow.table_name'), [
            'follower_id' => $follower->id,
            'followee_id' => $followee->id,
        ]);
    }

    /** @test */
    public function it_follow_many_users()
    {
        $follower = $this->createUser();
        $followees = $this->createUser([], 3);

        $follower->follow($followees->pluck('id')->toArray());

        foreach ($followees as $followee) {
            $this->assertDatabaseHas(config('follow.table_name'), [
                'follower_id' => $follower->id,
                'followee_id' => $followee->id,
            ]);
        }
    }

    /** @test */
    public function it_follow_same_user()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();

        $follower->follow($followee->id);
        $follower->follow($followee->id);

        $this->assertEquals(1, $follower->followees()->count());
    }

    /** @test */
    public function it_get_followers()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();

        $follower->follow($followee->id);

        $this->assertEquals(1, $follower->followees()->count());
        $this->assertEquals(1, $follower->followeeRelationships()->count());
        $this->assertEquals(0, $follower->followers()->count());
        $this->assertEquals(0, $follower->followerRelationships()->count());
    }

    /** @test */
    public function it_get_followees()
    {
        $followee = $this->createUser();
        $follower = $this->createUser();

        $follower->follow($followee->id);

        $this->assertEquals(1, $followee->followers()->count());
        $this->assertEquals(1, $followee->followerRelationships()->count());
        $this->assertEquals(0, $followee->followees()->count());
        $this->assertEquals(0, $followee->followeeRelationships()->count());
    }

    /** @test */
    public function it_unfollow_user()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();

        $follower->follow($followee->id);
        $follower->unfollow($followee->id);

        $this->assertDatabaseMissing(config('follow.table_name'), [
            'follower_id' => $follower->id,
            'followee_id' => $followee->id,
        ]);
    }

    /** @test */
    public function it_unfollow_many_users()
    {
        $follower = $this->createUser();
        $followees = $this->createUser([], 3);

        $followeeIds = $followees->pluck('id')->toArray();
        $follower->follow($followeeIds);
        $follower->unfollow($followeeIds);

        foreach ($followees as $followee) {
            $this->assertDatabaseMissing(config('follow.table_name'), [
                'follower_id' => $follower->id,
                'followee_id' => $followee->id,
            ]);
        }
    }

    /** @test */
    public function it_check_user_is_following()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();
        $notFollowee = $this->createUser();

        $follower->follow($followee->id);

        $this->assertTrue($follower->isFollowing($followee->id));
        $this->assertFalse($follower->isFollowing($notFollowee->id));
    }

    /** @test */
    public function it_check_user_is_following_if_array()
    {
        $follower = $this->createUser();
        $followees = $this->createUser([], 3);
        $notFollowee = $this->createUser();
        $followeeIds = $followees->pluck('id')->toArray();

        $follower->follow($followeeIds);

        $this->assertTrue($follower->isFollowing($followeeIds));

        $followeeIds[] = $notFollowee->id;
        $this->assertFalse($follower->isFollowing($followeeIds));
    }

    /** @test */
    public function it_check_user_is_being_followed()
    {
        $followee = $this->createUser();
        $follower = $this->createUser();
        $notFollower = $this->createUser();

        $follower->follow($followee->id);

        $this->assertTrue($followee->isFollowedBy($follower->id));
        $this->assertFalse($followee->isFollowedBy($notFollower->id));
    }

    /** @test */
    public function it_check_user_is_being_followed_if_array()
    {
        $followee = $this->createUser();
        $followers = $this->createUser([], 3);
        $notFollower = $this->createUser();
        $followerIds = $followers->pluck('id')->toArray();

        foreach ($followers as $follower) {
            $follower->follow($followee->id);
        }

        $this->assertTrue($followee->isFollowedBy($followerIds));

        $followerIds[] = $notFollower->id;
        $this->assertFalse($followee->isFollowedBy($followerIds));
    }

    /** @test */
    public function it_reject_now_follower()
    {
        $followee = $this->createUser();
        $followers = $this->createUser([], 3);
        $followerIds = $followers->pluck('id')->toArray();
        $notFollowerIds = $this->createUser([], 3)->pluck('id')->toArray();

        foreach ($followers as $follower) {
            $follower->follow($followee->id);
        }

        $this->assertEquals(
            $followerIds,
            $followee->rejectNotFollower(array_merge($followerIds, $notFollowerIds))
        );
    }

    /** @test */
    public function it_reject_now_followee()
    {
        $follower = $this->createUser();
        $followeeIds = $this->createUser([], 3)->pluck('id')->toArray();
        $notFolloweeIds = $this->createUser([], 3)->pluck('id')->toArray();

        $follower->follow($followeeIds);

        $this->assertEquals(
            $followeeIds,
            $follower->rejectNotFollowee(array_merge($followeeIds, $notFolloweeIds))
        );
    }
}