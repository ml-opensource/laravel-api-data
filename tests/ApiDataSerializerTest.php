<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Transformations\Serialization\ApiDataSerializer;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ApiDataSerializerTest extends TestCase
{
	/**
	 * @var ApiDataSerializer
	 */
	protected $serializer;

	/**
	 * @var array
	 */
	protected $collection;

	/**
	 * @var IlluminatePaginatorAdapter
	 */
	protected $paginatedCollection;

	/**
	 * @var array
	 */
	protected $item;

	/**
	 * Setup
	 */
	public function setUp()
	{
		parent::setUp();

		$this->serializer = new ApiDataSerializer();

		$this->collection = [
			[
				'id' => 1,
			],
			[
				'id' => 2,
			],
			[
				'id' => 3,
			],
		];

		$this->item = [
			'id' => 1,
		];

		$this->paginatedCollection = new IlluminatePaginatorAdapter(
			new LengthAwarePaginator($this->collection, count($this->collection), count($this->collection))
		);
	}

	/**
	 * @test
	 *
	 * @see ApiDataSerializer::collection()
	 */
	public function testCollection()
	{
		$this->assertEquals(['data' => $this->collection], $this->serializer->collection('not_used', $this->collection));
		$this->assertEquals(['data' => $this->item], $this->serializer->collection('not_used', $this->item));
	}

	/**
	 * @test
	 *
	 * @see ApiDataSerializer::item()
	 */
	public function testItem()
	{
		$this->assertEquals(['data' => $this->item], $this->serializer->item('not_used', $this->item));
		$this->assertEquals(['data' => $this->collection], $this->serializer->item('not_used', $this->collection));
	}

	/**
	 * @test
	 *
	 * @see ApiDataSerializer::meta()
	 */
	public function testMeta()
	{
		$this->assertEquals([], $this->serializer->meta([]));
	}

	/**
	 * @test
	 *
	 * @see ApiDataSerializer::paginator()
	 */
	public function testPaginator()
	{
		$paginator = $this->serializer->paginator($this->paginatedCollection);

		$this->assertArrayHasKey('pagination', $paginator);
		$this->assertArraySubset(['page', 'total', 'per_page', 'total_pages'], array_keys($paginator['pagination']));

		$this->assertArraySubset([
			'pagination' => [
				'page' => 1, 'total' => 3, 'per_page' => 3, 'total_pages' => 1,
			],
		], $paginator);
	}
}
