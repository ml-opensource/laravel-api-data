<?php

namespace Fuzz\Data\Bannable\Contracts;

interface CanBeBanned
{
	/**
	 * Identifier for the unbanning event.
	 */
	const UNBANNING_EVENT = 'unbanning';

	/**
	 * Identifier for the unbanned event.
	 */
	const UNBANNED_EVENT = 'unbanned';

	/**
	 * Identifier for the banning event.
	 */
	const BANNING_EVENT = 'banning';

	/**
	 * Identifier for the banned event.
	 */
	const BANNED_EVENT = 'banned';

	/**
	 * The default column name for banning.
	 */
	const BANNED_AT = 'banned_at';

	/**
	 * Ban a model instance.
	 */
	public function ban();

	/**
	 * Un-ban a model instance.
	 */
	public function unban();

	/**
	 * Determine if the model instance has been banned.
	 */
	public function isBanned();
}
