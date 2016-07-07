<?php

namespace Fuzz\Data\Support;

use Fuzz\Data\Eloquent\SerializedModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Class CollectionUtility
 *
 * @package Fuzz\Data\Support
 */
class CollectionUtility
{
	/**
	 * Collapse a keyed Eloquent Collection.
	 *
	 * @param \Illuminate\Database\Eloquent\Collection $collection
	 * @return \Illuminate\Support\Collection
	 */
	public static function collapseSerialized(EloquentCollection $collection)
	{
		return $collection->map(
			function (SerializedModel $menu_item) {
				return $menu_item->serialized();
			}
		)->collapse();
	}
}
