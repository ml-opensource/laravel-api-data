<?php


namespace Fuzz\Data\Traits;

use Fuzz\Data\Transformations\Serialization\DefaultModelTransformer;
use Fuzz\Data\Transformations\TransformationFactory;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;

/**
 * Trait Transformations
 *
 * Provides methods for data transformations
 *
 * @package Fuzz\Data\Transformations
 */
trait Transformations
{
	/**
	 * The transformer to use for the class.
	 *
	 * @return string | TransformerAbstract
	 */
	public function getTransformerClass()
	{
		return $this->transformer ?? DefaultModelTransformer::class;
	}

	/**
	 * Shortcut method for serializing and transforming an entity.
	 *
	 * @param                                          $entity
	 * @param TransformerAbstract|callable|string|null $transformer
	 * @param SerializerAbstract|null                  $serializer
	 *
	 * @return array
	 */
	public function transformEntity($entity, $transformer = null, SerializerAbstract $serializer = null): array
	{
		$transformer = $transformer ?: $this->getTransformerForClass();

		$results = $this->transform()->resourceWith($entity, $transformer)->usingPaginatorIfPaged();

		if ($serializer) {
			return $results->serialize($serializer);
		}

		return $results->serialize();
	}

	/**
	 * Gets the transformer from the class and validates it's a correct serializer.
	 *
	 * @throws \InvalidArgumentException - If the class property does not set a proper Transformer.
	 *
	 * @return TransformerAbstract
	 */
	protected function getTransformerForClass()
	{
		$transformer = $this->getTransformerClass();

		if (! $this->isTransformer($transformer)) {
			throw new \InvalidArgumentException('You cannot transform the entity without providing a valid Transformer. Verify your transformer extends ' . TransformerAbstract::class);
		}


		return (is_a($transformer, TransformerAbstract::class, true) && ! is_object($transformer)) ?
			new $transformer : $transformer;
	}

	/**
	 * Checks if it is a valid transformer.
	 *
	 * @param $transformer
	 *
	 * @return bool
	 */
	protected function isTransformer($transformer)
	{
		return is_a($transformer, TransformerAbstract::class, true) || is_callable($transformer);
	}

	/**
	 * Creates a new TransformationFactory
	 *
	 * @return TransformationFactory
	 */
	public function transform(): TransformationFactory
	{
		return new TransformationFactory();
	}
}
