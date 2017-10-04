<?php

namespace Fuzz\Data\Transformations;

use Fuzz\Data\Traits\Transformations;
use Fuzz\Data\Transformations\Contracts\RelationTransformer;
use Fuzz\Data\Transformations\Serialization\SimpleNestedModelSerializer;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

/**
 * Class BaseModelTransformer
 *
 * BaseModelTransformer is the base transformer for models.
 *
 * @package Fuzz\Data\Transformations
 */
abstract class BaseModelTransformer extends TransformerAbstract implements RelationTransformer
{
	use Transformations;

	/**
	 * Transform the model into beautiful JSON
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 *
	 * @return array
	 */
	public function transform(Model $model): array
	{
		// Serialize
		$arrayed_model = $model->attributesToArray();

		return array_merge($arrayed_model, $this->processRelations($model));
	}

	/**
	 * Run through and transform all subrelations
	 *
	 * @param \Illuminate\Database\Eloquent\Model $instance
	 *
	 * @return array
	 */
	public function processRelations(Model $instance): array
	{
		$relations = [];

		foreach ($this->getPossibleIncludes() as $key => $transformer) {
			if (! $instance->relationLoaded($key) || is_null($instance->getRelation($key))) {
				continue;
			}

			$transformed     = $this->transformEntity($instance->$key, $transformer, SimpleNestedModelSerializer::class);
			$relations[$key] = $transformed;
		}

		return $relations;
	}
}
