<?php

namespace Fuzz\Data\Eloquent;

/**
 * Interface SerializedModel
 *
 * @package Fuzz\Data\Eloquent
 */
interface SerializedModel
{
	/**
	 * Return a serialized implementation of a model.
	 *
	 * @return mixed
	 */
	public function serialized();
}
