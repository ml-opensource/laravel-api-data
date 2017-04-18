<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Traits\Transformations;
use Fuzz\Data\Transformations\Serialization\DefaultModelTransformer;
use Fuzz\Data\Transformations\Serialization\SimpleArraySerializer;
use Fuzz\Data\Transformations\TransformationFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class TransformationsTest extends TestCase
{
	/**
	 * @var Transformations
	 */
	protected $trait;

	/**
	 * Setup
	 */
	public function setUp()
	{
		parent::setUp();

		$this->trait = new class
		{
			use Transformations;
		};
	}

	/**
	 * @test
	 *
	 * It can return a new transformation factory.
	 */
	public function testTransform()
	{
		$this->assertInstanceOf(TransformationFactory::class, $this->trait->transform());
	}

	/**
	 * @test
	 *
	 * It can return a new transformation factory.
	 */
	public function testCanTransformEntity()
	{
		$transformed = $this->trait->transformEntity([[1, 2, 3, 4, 5]], 'array_values', new SimpleArraySerializer);

		$this->assertTrue(is_array($transformed));
		$this->assertEquals([[1, 2, 3, 4, 5]], $transformed);
	}

	/**
	 * @test
	 *
	 * Can use the class property to determine the transformer.
	 */
	public function testCanGetTransformerFromClassProperty()
	{
		$this->trait->transformer = DefaultModelTransformer::class;

		$model = (new class extends Model
		{
		})->forceFill(['name' => 'blah']);

		$transformed = $this->trait->transformEntity($model);

		$this->assertTrue(is_array($transformed));
		$this->assertEquals(['data' => $model->toArray()], $transformed);
	}

	/**
	 * @test
	 *
	 * Can use the class property to determine the transformer if it is a simple callable.
	 */
	public function testCanGetTransformerFromClassPropertyAsCallable()
	{
		$this->trait->transformer = 'array_values';

		$transformed = $this->trait->transformEntity([1, 2, 3, 4, 5]);

		$this->assertTrue(is_array($transformed));
		$this->assertEquals(['data' => [1, 2, 3, 4, 5]], $transformed);
	}

	/**
	 * @test
	 *
	 * Will throw InvalidArgumentException when trying to use invalid transformer.
	 */
	public function testGetTransformerFromClassPropertyThrowsInvalidArgumentException()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->trait->transformer = 'InvalidArg!';

		$this->trait->transformEntity([1, 2, 3, 4, 5]);
	}
}
