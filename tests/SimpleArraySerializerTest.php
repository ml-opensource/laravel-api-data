<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Transformations\Serialization\SimpleArraySerializer;

class SimpleArraySerializerTest extends TestCase
{
	/**
	 * @var SimpleArraySerializer
	 */
	protected $serializer;

	/**
	 * Setup
	 */
	public function setUp()
	{
		parent::setUp();

		$this->serializer = new SimpleArraySerializer();
	}

	/**
	 * @test
	 *
	 * @see SimpleArraySerializer::collection()
	 */
	public function testCollection()
	{
		$this->assertEquals([1, 2, 3, 4], $this->serializer->collection('not_used', [1, 2, 3, 4]));
	}

	/**
	 * @test
	 *
	 * @see SimpleArraySerializer::item()
	 */
	public function testItem()
	{
		$this->assertEquals([[1, 2, 3, 4]], $this->serializer->item('not_used', [1, 2, 3, 4]));
	}
}
