<?php


namespace Fuzz\Data\Transformations;


use Fuzz\Data\Transformations\Serialization\ApiDataSerializer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as LaravelCollection;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;
use League\Fractal\TransformerAbstract;


/**
 * The Transformation Factory builds and processes transformations as well
 * as serializations through intuitive method chaining.
 *
 * @example
 *
 *       $transform = new TransformationFactory();
 *
 *       $transformed = $transform->collectionWith($items, new ItemTransformer())->usingPaginator()->serialize();
 *
 * The methods have smart signatures allowing you to forgo constantly setting the same values.
 * Custom values can be passed when required. When used with the transformsData Trait, you can
 * set a $transformer property on the class and then only have to pass in the data you want to transform.
 *
 * The TransformationFactory is smart enough to figure out what type of resource, and paginate if it should.
 *
 *              use TransformsData;
 *
 *              $transformed = $this->transform()->resourceWith($data)->usingPaginatorIfPaged()->serialize();
 *
 * The `resourceWith()` method will determine if the data is a collection or an item and use a paginator
 * adapter if the data is paged. The TransformsData trait comes with a shortcut method that does this
 * all for you with one succinct call.
 *
 *              $transformed = $this->transformEntity($data);
 *
 *
 * @package Fuzz\Data\Transformations
 */
class TransformationFactory
{
	/**
	 * Holds the data that will be transformed.
	 *
	 * @var mixed $entity
	 */
	protected $entity;

	/**
	 * The transformer that will be applied.
	 *
	 * @var TransformerAbstract $transformer
	 */
	protected $transformer;

	/**
	 * Item or Collection Resource.
	 *
	 * @var ResourceInterface $resource
	 */
	protected $resource;

	/**
	 * Transformation Manager.
	 *
	 * @var Manager $manager
	 */
	protected $manager;

	/**
	 * The data serializer to use.
	 *
	 * @var SerializerAbstract $serializer
	 */
	protected $serializer;


	public function __construct()
	{
		$this->manager = new Manager();
	}

	/**
	 * Adds data and transformer, and chooses whether to use the Item or Collection Resource
	 * based on what's most suitable for the data passed in.
	 *
	 * @param mixed                        $entity
	 * @param TransformerAbstract|callable $transformer
	 * @param null|mixed                   $key
	 *
	 * @return \Fuzz\Data\Transformations\TransformationFactory
	 */
	public function resourceWith($entity, $transformer, $key = null): TransformationFactory
	{
		// if collection, paginator, or array with first value being an array use collection resource.
		if ((is_array($entity) && isset($entity[0]) && is_array($entity[0])) || $entity instanceof LaravelCollection || $entity instanceof LengthAwarePaginator) {
			return $this->collectionWith($entity, $transformer, $key);
		}

		return $this->itemWith($entity, $transformer, $key);
	}

	/**
	 * Uses a Collection resource on the data and transformer passed in.
	 *
	 * @param mixed                        $entity
	 * @param TransformerAbstract|callable $transformer
	 * @param null|mixed                   $key
	 *
	 * @return \Fuzz\Data\Transformations\TransformationFactory
	 */
	public function collectionWith($entity, $transformer, $key = null): TransformationFactory
	{
		$this->entity      = $entity;
		$this->transformer = $transformer;
		$this->resource    = new Collection($entity, $transformer, $key);

		return $this;
	}

	/**
	 * Uses an Item resource on the data and transformer passed in.
	 *
	 * @param mixed                        $entity
	 * @param TransformerAbstract|callable $transformer
	 * @param null|mixed                   $key
	 *
	 * @return \Fuzz\Data\Transformations\TransformationFactory
	 */
	public function itemWith($entity, $transformer, $key = null): TransformationFactory
	{
		$this->entity      = $entity;
		$this->transformer = $transformer;
		$this->resource    = new Item($entity, $transformer, $key);

		return $this;
	}

	/**
	 * Completes the the transformation and serialization flow, returning the data.
	 *
	 * This could be thought of as a `flush` method.
	 *
	 * // @TODO it does too much currently. toArray is really the `flush` method and should be moved into it's own
	 * method.
	 *
	 * @param \League\Fractal\Serializer\SerializerAbstract|string $serializer
	 *
	 * @return array
	 */
	public function serialize($serializer = ApiDataSerializer::class): array
	{
		if (! is_a($serializer, SerializerAbstract::class, true)) {
			throw new \InvalidArgumentException(
				sprintf('The serializer must either be an instance,
				or string representation of %s. The passed value %s is neither.',
					SerializerAbstract::class,
					$serializer
				)
			);
		}

		$this->serializer = is_string($serializer) ? new $serializer : $serializer;

		$this->manager->setSerializer($this->serializer);

		return $this->manager->createData($this->resource)->toArray();
	}

	/**
	 * Determines if the set data is paged, if so it will apply the
	 * `usingPaginator()` method, if not it will just continue.
	 *
	 * @return \Fuzz\Data\Transformations\TransformationFactory
	 */
	public function usingPaginatorIfPaged(): TransformationFactory
	{
		if ($this->resource instanceof Collection && $this->entity instanceof LengthAwarePaginator) {
			return $this->usingPaginator();
		}

		return $this;
	}

	/**
	 * Applies a LengthAwarePaginator for paged resources.
	 *
	 * @param null $paginator
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return \Fuzz\Data\Transformations\TransformationFactory
	 */
	public function usingPaginator($paginator = null): TransformationFactory
	{
		if (! ($this->resource instanceof Collection) || ! ($this->entity instanceof LengthAwarePaginator)) {
			throw new \InvalidArgumentException('Bad Request Issued');
		}

		$this->resource->setPaginator(new IlluminatePaginatorAdapter($this->entity));

		return $this;
	}
}
