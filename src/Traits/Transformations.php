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
	 * Controller transformer
	 *
	 * @var string
	 */
	public $transformer = DefaultModelTransformer::class;

	/**
	 * Shortcut method for serializing and transforming an entity.
	 *
	 * @param                                          $entity
	 * @param TransformerAbstract|callable|string|null $transformer
	 * @param SerializerAbstract|callable|string|null  $serializer
	 *
	 * @return array
	 */
	public function transformEntity($entity, $transformer = null, $serializer = null): array
	{
		$transformer = is_null($transformer) ? $this->getTransformerFromClassProperty() :
			$this->getOrBuildTransformer($transformer);

		$results = $this->transformationFactory()->resourceWith($entity, $transformer)->usingPaginatorIfPaged();

		if ($serializer) {
			return $results->serialize($this->getOrBuildSerializer($serializer));
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
	protected function getTransformerFromClassProperty()
	{
		if (! isset($this->transformer) || ! $this->isTransformer($this->transformer)) {
			throw new \InvalidArgumentException('You cannot transform the entity without providing a valid Transformer. Verify your transformer extends ' . TransformerAbstract::class);
		}


		return $this->getOrBuildTransformer($this->transformer);
	}

	/**
	 * Checks if it is a valid transformer.
	 *
	 * @param TransformerAbstract|callable|string $transformer
	 *
	 * @return bool
	 */
	protected function isTransformer($transformer)
	{
		return is_a($transformer, TransformerAbstract::class, true) || is_callable($transformer);
	}

	/**
	 * Build or return an instance of a tranformer
	 *
	 * @param TransformerAbstract|callable|string $transformer
	 *
	 * @return \League\Fractal\TransformerAbstract
	 */
	protected function getOrBuildTransformer($transformer): TransformerAbstract
	{
		if (! $this->isTransformer($transformer)) {
			throw new \InvalidArgumentException('You cannot transform the entity without providing a valid Transformer. Verify your transformer extends ' . TransformerAbstract::class);
		}

		return ! is_object($transformer) ? new $transformer : $transformer;
	}

	/**
	 * Creates a new TransformationFactory
	 *
	 * @return TransformationFactory
	 */
	public function transformationFactory(): TransformationFactory
	{
		return new TransformationFactory();
	}

	/**
	 * Build or return an instance of a serializer
	 *
	 * @param SerializerAbstract|callable|string $serializer
	 *
	 * @return \League\Fractal\Serializer\SerializerAbstract
	 */
	protected function getOrBuildSerializer($serializer): SerializerAbstract
	{
		if (! $this->isSerializer($serializer)) {
			throw new \InvalidArgumentException('You cannot transform the entity without providing a valid Serializer. Verify your serializer extends ' . SerializerAbstract::class);
		}

		return ! is_object($serializer) ? new $serializer : $serializer;
	}

	/**
	 * Checks if it is a valid serializer.
	 *
	 * @param SerializerAbstract|callable|string $serializer
	 *
	 * @return bool
	 */
	protected function isSerializer($serializer)
	{
		return is_a($serializer, SerializerAbstract::class, true) || is_callable($serializer);
	}
}
