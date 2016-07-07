<?php

namespace Fuzz\Data\Serialization;

use League\Fractal\TransformerAbstract;

class FuzzArrayTransformer extends TransformerAbstract
{
	/**
	 * Transform the model into beautiful JSON
	 *
	 * @param array $data
	 * @return array
	 * @internal param \Fuzz\Data\Eloquent\Model $model
	 */
	public function transform(array $data)
	{
		return $data;
	}
}
