<?php

namespace Fuzz\Data\Bannable;


use Illuminate\Database\Eloquent\Builder;
use Fuzz\Data\Bannable\Contracts\CanBeBanned;


trait Bannable
{
	/**
	 * Ban the model.
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function ban(): bool
	{
		if (is_null($this->getKeyName())) {
			throw new \Exception('No primary key defined on model.');
		}

		if ($this->fireModelEvent(CanBeBanned::BANNING_EVENT) === false) {
			return false;
		}

		// Here, we'll touch the owning models, verifying these timestamps get updated
		// for the models. This will allow any caching to get broken on the parents
		// by the timestamp. THen we will go ahead and ban the model instance.
		$this->touchOwners();

		$this->performBanOnModel();

		// Once the model has been banned, we will fire off the banned event so that
		// the developers may hook into post-ban operations. We will then return
		// a boolean true as the ban is presumably successful on the database.
		$this->fireModelEvent(CanBeBanned::BANNED_EVENT, false);

		return true;
	}

	/**
	 * Get the name of the "banned at" column.
	 *
	 * @return string
	 */
	public function getBannedAtColumn(): string
	{
		return defined('static::BANNED_AT') ? static::BANNED_AT : CanBeBanned::BANNED_AT;
	}

	/**
	 * Un-ban a banned model instance.
	 *
	 * @return bool
	 */
	public function unban(): bool
	{
		// If the unbanning event does not return false, we will proceed with this
		// un-ban operation. Otherwise, we bail out so the developer will stop
		// the unban totally. We will clear the banned timestamp and save.
		if ($this->fireModelEvent(CanBeBanned::UNBANNING_EVENT) === false) {
			return false;
		}

		$this->{$this->getBannedAtColumn()} = null;

		// Once we have saved the model, we will fire the "unbanned" event so this
		// developer will do anything they need to after an un-ban operation is
		// totally finished. Then we will return the result of the save call.
		$this->exists = true;

		$result = $this->save();

		$this->fireModelEvent(CanBeBanned::UNBANNED_EVENT, false);

		return $result;
	}

	/**
	 * Determine if the model instance has been banned.
	 *
	 * @return bool
	 */
	public function isBanned(): bool
	{
		return ! is_null($this->{$this->getBannedAtColumn()});
	}

	/**
	 * Get a fully qualified "banned at" column.
	 *
	 * @return string
	 */
	public function getQualifiedBannedAtColumn(): string
	{
		return $this->getTable() . '.' . $this->getBannedAtColumn();
	}

	/**
	 * Query scope for banned users.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeBanned(Builder $query): Builder
	{
		return $query->whereNotNull($this->getBannedAtColumn());
	}

	/**
	 * Query scope for unbanned users.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeNotBanned(Builder $query): Builder
	{
		return $query->whereNull($this->getBannedAtColumn());
	}

	/**
	 * Boot the bannable trait for a model.
	 */
	public static function bootBannable()
	{
		static::addGlobalScope(new BanningScope);
	}

	/**
	 * Register a unbanning model event with the dispatcher.
	 *
	 * @param  \Closure|string $callback
	 *
	 * @return void
	 */
	public static function unbanningEvent($callback)
	{
		static::registerModelEvent(CanBeBanned::UNBANNING_EVENT, $callback);
	}

	/**
	 * Register a unbanned model event with the dispatcher.
	 *
	 * @param \Closure|string $callback
	 *
	 * @return void
	 */
	public static function unbannedEvent($callback)
	{
		static::registerModelEvent(CanBeBanned::UNBANNED_EVENT, $callback);
	}

	/**
	 * Register a banning model event with the dispatcher.
	 *
	 * @param \Closure|string $callback
	 */
	public static function banningEvent($callback)
	{
		static::registerModelEvent(CanBeBanned::BANNING_EVENT, $callback);
	}

	/**
	 * Register a banned model event with the dispatcher.
	 *
	 * @param \Closure|string $callback
	 */
	public static function bannedEvent($callback)
	{
		static::registerModelEvent(CanBeBanned::BANNED_EVENT, $callback);
	}

	/**
	 * Perform the actual ban query on this model instance
	 */
	protected function performBanOnModel()
	{
		$this->runBan();
	}

	/**
	 * Run the actual ban query on this model instance.
	 */
	protected function runBan()
	{
		$query = $this->newQueryWithoutScopes()->where($this->getKeyName(), $this->getKey());

		$this->{$this->getBannedAtColumn()} = $time = $this->freshTimestamp();

		$query->update([$this->getBannedAtColumn() => $this->fromDateTime($time)]);
	}
}
