<?php

namespace Fuzz\Data\Serialization;

use Fuzz\Data\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class FuzzModelTransformer extends TransformerAbstract
{
	/**
	 * Transform the model into beautiful JSON
	 *
	 * @param \Fuzz\Data\Eloquent\Model $model
	 * @return array
	 */
	public function transform(Model $model)
	{
		return $model->accessibleAttributesToArray();
	}
}
