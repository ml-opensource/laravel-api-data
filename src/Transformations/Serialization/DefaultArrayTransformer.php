<?php

namespace Fuzz\Data\Transformations\Serialization;

use League\Fractal\TransformerAbstract;

class DefaultArrayTransformer extends TransformerAbstract
{
	/**
	 * Transform the array into beautiful JSON
	 *
	 * @param array $array
	 *
	 * @return array
	 *
	 */
	public function transform(array $array): array
	{
		return $array;
	}
}
