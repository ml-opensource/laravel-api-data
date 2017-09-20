<?php

namespace Fuzz\Data\Transformations\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface RelationTransformer
 *
 * A RelationTransformer provides functionality to transform a model with its relationships.
 *
 * @package Fuzz\Data\Transformations\Contracts
 */
interface RelationTransformer
{
	/**
	 * Get the set of possible includes and their transformers
	 *
	 * @return array
	 */
	public function getPossibleIncludes(): array;

	/**
	 * Run through and transform all subrelations
	 *
	 * @param \Illuminate\Database\Eloquent\Model $instance
	 *
	 * @return array
	 */
	public function processRelations(Model $instance): array;
}
