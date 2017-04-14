<?php

namespace Fuzz\Data\Transformations\Serialization;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

class DefaultModelTransformer extends TransformerAbstract
{
	/**
	 * Transform the model into beautiful JSON
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 *
	 * @return array
	 */
	public function transform(Model $model): array
	{
		return $model->toArray();
	}
}
