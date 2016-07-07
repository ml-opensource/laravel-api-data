<?php

namespace Fuzz\Data\Serialization;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class FuzzModelTransformer extends TransformerAbstract
{
	/**
	 * Transform the model into beautiful JSON
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @return array
	 */
	public function transform(Model $model)
	{
		return $model->toArray();
	}
}
