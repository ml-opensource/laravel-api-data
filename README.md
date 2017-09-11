## Fuzz Laravel Data

Provides model and scope boilerplate for common implementations of Laravel API projects.

## TODOs
1. Readme



# Bannable

In addition to actually soft deleting records from your database, Fuzz Data can also "ban" models. When models are "banned", a `banned_at` attribute is set on the model and inserted into the database. If a model has a non-null `banned_at` value, the model has been "banned". To enable banning for a model, use the Fuzz\Data\Bannable trait, add the `Fuzz\Data\Bannable\Contracts\CanBeBanned` implementation on the model, and optionally add the `banned_at` column to your $dates property:

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Fuzz\Data\Bannable\Contracts\CanBeBanned;
use Fuzz\Data\Bannable\Bannable;

class User extends Model implements CanBeBanned
{
	use Bannable;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['banned_at'];
}
```

Of course, you should add the `banned_at` column to your database table.

```php
Schema::table('users', function ($table) {
    $table->timestamp('banned_at')->nullable();
});
```

Now, when you call the `ban` method on the model, the `banned_at` column will be set to the current date and time. And, when querying a model that uses bannable, the banned models will automatically be excluded from all query results.

To determine if a given model instance has been banned, use the `isBanned` method:

```php
if ($user->isBanned()) {
    //
}
```

## Querying Banned Models

### Including Soft Deleted Models

As noted above, banned models will automatically be excluded from query results. However, you may force banned models to appear in a result set using the `withBanned` method on the query:

```php
$users = App\User::withBanned()->get();
```

The `withBanned` method may also be used on a relationship query:

```php
$comment->user()->withBanned()->get();
```

### Retrieving Only Soft Deleted Models

The `onlyBanned` method will retrieve only banned models:

```php
$users = App\User::onlyBanned()->get();
```

### Unbanning

Sometimes you may wish to "un-ban" a model. To restore a banned model into an active state, use the `unban` method on a model instance:

```php
$user->unban();
```

You may also use the `unban` method in a query to quickly unban multiple models. Again, like other "mass" operations, this will not fire any model events for the models that are unbanned:

```php
App\User::withBanned()
        ->has('comments')
        ->unban();
```

Like the `withBanned` method, the `unban` method may also be used on relationships:

```php
$comment->user()->unban();
```

## Bannable Events

Bannable will add the following events to the model: `banning`, `banned`, `unbanning`, `unbanned`.

See [Laravel Event Documentation](https://laravel.com/docs/master/eloquent#events)
