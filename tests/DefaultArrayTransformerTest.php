<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Transformations\Serialization\DefaultArrayTransformer;
use Fuzz\Data\Transformations\Serialization\DefaultModelTransformer;
use Illuminate\Database\Eloquent\Model;

class DefaultArrayTransformerTest extends TestCase
{
	/**
	 * @var DefaultArrayTransformer
	 */
	protected $transformer;

	/**
	 * @var Model
	 */
	protected $model;

	/**
	 * Setup
	 */
	public function setUp()
	{
		parent::setUp();

		$this->transformer = new DefaultArrayTransformer();
	}

	/**
	 * @test
	 *
	 * @see DefaultModelTransformer::transform()
	 */
	public function testTransform()
	{
		$transformed = $this->transformer->transform([
			'foo' => 'bar',
			'bar' => [
				'baz',
			],
		]);

		$this->assertTrue(is_array($transformed));
		$this->assertEquals([
			'foo' => 'bar',
			'bar' => [
				'baz',
			],
		], $transformed);
	}
}
