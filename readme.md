# Laravel 5 Follow System

This package helps you to add user follow system to your project.

* So simply and easy.
* Use "ON DELETE CASCADE" in follow relationships table.

## Require
- *Support Laravel 5.4~*  
- *Required php >=7.0* (v1.* >=5.6.4)

## Installation

First, pull in the package through Composer.

Run `composer require hareku/laravel-follow`

And then, include the service provider within `config/app.php`.

```php
'providers' => [
    Hareku\LaravelFollow\FollowServiceProvider::class,
];
```

Publish the config file. (follow.php)

```sh
$ php artisan vendor:publish --provider="Hareku\LaravelFollow\FollowServiceProvider"
```

Finally, use Followable trait in User model.

```php
use Hareku\LaravelFollow\Traits\Followable;

class User extends Model
{
    use Followable;
}
```

## Usage

### Follow a user or users

```php
$user->follow(1);
$user->follow([1,2,3,4]);
```

### Unfollow a user or users

```php
$user->unfollow(1);
$user->unfollow([1,2,3,4]);
```

### Get followers / followees

```php
// followers
$user->followers()->get(); // Get follower user models.
$user->followerRelationships()->get(); // Get follower relationship models.

// followees
$user->followees()->get();
$user->followeeRelationships()->get();
```

### Check if follow
```php
$user->isFollowing(1);
$user->isFollowing([1,2,3,4]);
```

### Check if followed by

```php
$user->isFollowedBy(1);
$user->isFollowedBy([1,2,3,4]);
```

### Reject user ids

```php
$user->follow([1,2,3]);
$user->rejectNotFollowee([1,2,3,4,5]); // [1,2,3]
```

```php
$user->followers()->pluck('id')->all(); // [1,2,3]
$user->rejectNotFollower([1,2,3,4,5]); // [1,2,3]
```

## License

MIT

## Author

hareku (hareku908@gmail.com)
