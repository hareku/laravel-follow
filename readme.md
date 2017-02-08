# Laravel 5 Follow System

This package helps you to add user based follow system to your model.

* So simply and easy.
* Use "ON DELETE CASCADE" in follow relationships table.

*Support Laravel 5.4~*

## Installation

First, pull in the package through Composer.

Run `composer require hareku/laravel-follow`

And then, include the service provider within `config/app.php`.

```php
'providers' => [
    Hareku\LaravelFollow\FollowServiceProvider::class,
];
```

Publish the migrations and config.

* create_follow_relationships_table.php (migrations)
* follow.php (config)

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

### Follow a user or users.

```php
$user->follow(1);
$user->follow([1,2,3,4]);
```

### Unfollow a user or users.

```php
$user->unfollow(1);
$user->unfollow([1,2,3,4]);
```

### Get followers.

```php
$user->followers()->paginate();
$user->followerRelationships()->paginate();
```

### Get followees.

```php
$user->followees()->paginate();
$user->followeeRelationships()->paginate();
```

### Check if follow.
```php
$user->isFollowing(1);
$user->isFollowing([1,2,3,4]);
```

### Check if followed by.

```php
$user->isFollowedBy(1);
$user->isFollowedBy([1,2,3,4]);
```

## License

MIT

## Author

hareku (hareku908@gmail.com)
