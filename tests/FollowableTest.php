<?php

namespace Hareku\LaravelFollow\Tests;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FollowableTest extends TestCase
{
    use DatabaseTransactions;

    // /**
    //  * Create users.
    //  *
    //  * @param  int  $amount
    //  * @return Collection
    //  */
    // protected function createUsers(int $amount = 3)
    // {
    //     return factory(User::class, $amount)->create();
    // }
    //
    // /**
    //  * Create a user.
    //  *
    //  * @param  array  $override
    //  * @return User
    //  */
    // protected function createUser(array $override = []): User
    // {
    //     return factory(User::class)->create($override);
    // }

    /** @test */
    public function it_follows_user()
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
    public function it_add_follower()
    {
        $followee = $this->createUser();
        $follower = $this->createUser();

        $followee->addFollowers($follower->id);

        $this->assertDatabaseHas(config('follow.table_name'), [
            'follower_id' => $follower->id,
            'followee_id' => $followee->id,
        ]);
    }

    /** @test */
    public function it_add_many_followers()
    {
        $followee = $this->createUser();
        $followers = $this->createUsers(3);

        $followee->addFollowers($followers->pluck('id')->toArray());

        foreach ($followers as $follower) {
            $this->assertDatabaseHas(config('follow.table_name'), [
                'follower_id' => $follower->id,
                'followee_id' => $followee->id,
            ]);
        }
    }

    /** @test */
    public function it_follows_many_users()
    {
        $follower = $this->createUser();
        $followees = $this->createUsers(3);

        $follower->follow($followees->pluck('id')->toArray());

        foreach ($followees as $followee) {
            $this->assertDatabaseHas(config('follow.table_name'), [
                'follower_id' => $follower->id,
                'followee_id' => $followee->id,
            ]);
        }
    }

    /** @test */
    public function it_follows_same_user()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();

        $follower->follow($followee->id);
        $follower->follow($followee->id);

        $this->assertSame(1, $follower->followees()->count());
    }

    /** @test */
    public function it_gets_followers_and_followees()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();

        $follower->follow($followee->id);

        $this->assertSame(1, $follower->followees()->count());
        $this->assertSame(1, $follower->followeeRelationships()->count());
        $this->assertSame(0, $follower->followers()->count());
        $this->assertSame(0, $follower->followerRelationships()->count());
    }

    /** @test */
    public function it_unfollows_user()
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
    public function it_unfollows_many_users()
    {
        $follower = $this->createUser();
        $followees = $this->createUsers(3);

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
    public function it_checks_if_user_is_following()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();
        $notFollowee = $this->createUser();

        $follower->follow($followee->id);

        $this->assertTrue($follower->isFollowing($followee->id));
        $this->assertFalse($follower->isFollowing($notFollowee->id));
    }

    /** @test */
    public function it_checks_if_user_is_following_for_array()
    {
        $follower = $this->createUser();
        $followees = $this->createUsers(3);
        $notFollowee = $this->createUser();
        $followeeIds = $followees->pluck('id')->toArray();

        $follower->follow($followeeIds);

        $this->assertTrue($follower->isFollowing($followeeIds));

        $followeeIds[] = $notFollowee->id;
        $this->assertFalse($follower->isFollowing($followeeIds));
    }

    /** @test */
    public function it_checks_if_user_is_being_followed()
    {
        $followee = $this->createUser();
        $follower = $this->createUser();
        $notFollower = $this->createUser();

        $follower->follow($followee->id);

        $this->assertTrue($followee->isFollowedBy($follower->id));
        $this->assertFalse($followee->isFollowedBy($notFollower->id));
    }

    /** @test */
    public function it_checks_if_user_is_being_followed_for_array()
    {
        $followee = $this->createUser();
        $followers = $this->createUsers(3);
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
    public function it_checks_if_user_is_mutual_follow()
    {
        $follower = $this->createUser();
        $followee = $this->createUser();

        $follower->follow($followee->id);
        $followee->follow($follower->id);

        $this->assertTrue($follower->isMutual($followee->id));
        $this->assertTrue($followee->isMutual($follower->id));
    }

    /** @test */
    public function it_gets_follower_ids()
    {
        $followee = $this->createUser();
        $followers = $this->createUsers(3);

        foreach ($followers as $follower) {
            $follower->follow($followee->id);
        }

        $this->assertEquals(
            $followee->followerIds(),
            $followers->pluck('id')->toArray()
        );

        $this->assertEquals(
            $followee->followerIds(true),
            $followers->pluck('id')
        );
    }

    /** @test */
    public function it_gets_followee_ids()
    {
        $follower = $this->createUser();
        $followees = $this->createUsers(3);
        $followeeIds = $followees->pluck('id');

        $follower->follow($followeeIds->toArray());

        $this->assertEquals(
            $follower->followeeIds(),
            $followeeIds->toArray()
        );

        $this->assertEquals(
            $follower->followeeIds(true),
            $followeeIds
        );
    }

    /** @test */
    public function it_rejects_not_follower_ids()
    {
        $followee = $this->createUser();
        $followers = $this->createUsers(3);
        $followerIds = $followers->pluck('id')->toArray();
        $notFollowerIds = $this->createUsers(3)->pluck('id')->toArray();

        foreach ($followers as $follower) {
            $follower->follow($followee->id);
        }

        $this->assertSame(
            $followerIds,
            $followee->rejectNotFollower(array_merge($followerIds, $notFollowerIds))
        );
    }

    /** @test */
    public function it_rejects_not_followee_ids()
    {
        $follower = $this->createUser();
        $followeeIds = $this->createUsers(3)->pluck('id')->toArray();
        $notFolloweeIds = $this->createUsers(3)->pluck('id')->toArray();

        $follower->follow($followeeIds);

        $this->assertSame(
            $followeeIds,
            $follower->rejectNotFollowee(array_merge($followeeIds, $notFolloweeIds))
        );
    }
}
