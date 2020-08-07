<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Transformations\Serialization\ApiDataSerializer;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Pagination\PaginatorInterface;
use Mockery;

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

		$this->paginatedCollection = new IlluminatePaginatorAdapter(new LengthAwarePaginator($this->collection, count($this->collection), count($this->collection)));
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
		$paginator          = Mockery::mock(PaginatorInterface::class);
		$abstract_paginator = Mockery::mock(AbstractPaginator::class);
		$request            = Mockery::mock(\Illuminate\Http\Request::class);

		$paginator->shouldReceive('getCurrentPage')->once()->andReturn(3);
		$paginator->shouldReceive('getLastPage')->once()->andReturn(10);
		$paginator->shouldReceive('getTotal')->once()->andReturn(100);
		$paginator->shouldReceive('getCount')->once()->andReturn(100);
		$paginator->shouldReceive('getPerPage')->once()->andReturn(10);
		$paginator->shouldReceive('getPaginator')->once()->andReturn($abstract_paginator);

		$abstract_paginator->shouldReceive('appends')->once()->with(ApiDataSerializer::PAGINATION_PER_PAGE, 10);
		$abstract_paginator->shouldReceive('perPage')->once()->andReturn(10);

		$paginator->shouldReceive('getUrl')->once()->with(2)->andReturn('foo');
		$paginator->shouldReceive('getUrl')->once()->with(4)->andReturn('bar');

		Request::shouldReceive('instance')->once()->andReturn($request);
		$request->query = $request;
		$request->shouldReceive('all')->andReturn([]);

		$pagination = $this->serializer->paginator($paginator);

		$this->assertArrayHasKey('pagination', $pagination);
		$this->assertSame([
			'pagination' => [
				'total'        => 100,
				'count'        => 100,
				'per_page'     => 10,
				'current_page' => 3,
				'total_pages'  => 10,
				'links'        => [
					'next'     => 'bar',
					'previous' => 'foo',
				],
			],
		], $pagination);
	}
}
