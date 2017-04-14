<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Transformations\TransformationFactory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TransformationFactoryTest extends TestCase
{

	/**
	 * @var TransformationFactory
	 */
	protected $transform;

	/**
	 * @var callable
	 */
	protected $returnAsIsTransformer;

	/**
	 * @var Collection
	 */
	protected $collection;

	/**
	 * @var array
	 */
	protected $item;

	/**
	 * @var LengthAwarePaginator
	 */
	protected $paginatedCollection;

	/**
	 * Setup
	 */
	public function setUp()
	{
		parent::setUp();

		$this->transform             = new TransformationFactory();
		$this->returnAsIsTransformer = function($entity) {
			return $entity;
		};

		$this->collection          = new Collection([1, 2, 3, 4, 5]);
		$this->item                = [1, 2, 3, 4, 5];
		$this->paginatedCollection = new LengthAwarePaginator($this->collection, $this->collection->count(), $this->collection->count());
	}

	/**
	 * @test
	 *
	 * Can transform resource if determined to be collection.
	 */
	public function testCanTransformCollectionResourceWith()
	{
		$this->assertInstanceOf(TransformationFactory::class,
			$this->transform->resourceWith($this->collection, $this->returnAsIsTransformer));

		$this->assertTrue(is_array($this->transform->serialize()));
	}

	/**
	 * @test
	 *
	 * Can transform resource if determined to be item.
	 */
	public function testCanTransformItemResourceWith()
	{
		$this->assertInstanceOf(TransformationFactory::class,
			$this->transform->resourceWith($this->item, $this->returnAsIsTransformer));

		$this->assertTrue(is_array($this->transform->serialize()));
	}

	/**
	 * @test
	 *
	 * Can transform collection.
	 */
	public function testCanTransformCollectionWith()
	{
		$this->assertInstanceOf(TransformationFactory::class,
			$this->transform->collectionWith($this->collection, $this->returnAsIsTransformer));

		$this->assertTrue(is_array($this->transform->serialize()));
	}

	/**
	 * @test
	 *
	 * Can transform item.
	 */
	public function testCanTransformItemWith()
	{
		$this->assertInstanceOf(TransformationFactory::class,
			$this->transform->itemWith($this->item, $this->returnAsIsTransformer));

		$this->assertTrue(is_array($this->transform->serialize()));
	}

	/**
	 * @test
	 *
	 * Can transform using paginator.
	 */
	public function testCanTransformUsingPaginator()
	{
		$this->assertInstanceOf(TransformationFactory::class,
			$this->transform->collectionWith($this->paginatedCollection, $this->returnAsIsTransformer));

		$this->assertTrue(is_array($this->transform->usingPaginator()->serialize()));
	}

	/**
	 * @test
	 *
	 * Can transform using paginator if collection is paged.
	 */
	public function testCanTransformUsingPaginatorIfPaged()
	{
		$this->assertInstanceOf(TransformationFactory::class,
			$this->transform->collectionWith($this->paginatedCollection, $this->returnAsIsTransformer));

		$this->assertTrue(is_array($this->transform->usingPaginatorIfPaged()->serialize()));
	}

	/**
	 * @test
	 *
	 * Can transform using paginator even if collection is not paged.
	 */
	public function testCanTransformUsingPaginatorIfPagedIsFalse()
	{
		$this->assertInstanceOf(TransformationFactory::class,
			$this->transform->collectionWith($this->collection, $this->returnAsIsTransformer));

		$this->assertTrue(is_array($this->transform->usingPaginatorIfPaged()->serialize()));
	}

	/**
	 * @test
	 *
	 * usingPaginator throws when invalid argument.
	 */
	public function testUsingPaginatorThrowsInvalidArgumentException()
	{
		$this->expectException(InvalidArgumentException::class);

		$this->transform->itemWith($this->item, $this->returnAsIsTransformer)
			->usingPaginator()->serialize('not a SerializerAbstract');
	}

	/**
	 * @test
	 *
	 * Serialize throws when invalid argument.
	 */
	public function testSerializeThrowsInvalidArgumentException()
	{
		$this->expectException(InvalidArgumentException::class);

		$this->transform->collectionWith($this->collection, $this->returnAsIsTransformer)
			->serialize('not a SerializerAbstract');
	}
}
