<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Transformations\Serialization\DefaultModelTransformer;
use Illuminate\Database\Eloquent\Model;

class DefaultModelTransformerTest extends TestCase
{
	/**
	 * @var DefaultModelTransformer
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

		$this->transformer = new DefaultModelTransformer();
		$this->model       = new class extends Model
		{
			protected $fillable = [
				'first_name', 'last_name', 'email',
				'date_of_birth', 'about', 'phone_number', 'city', 'state',
			];
		};
	}

	/**
	 * @test
	 *
	 * @see DefaultModelTransformer::transform()
	 */
	public function testTransform()
	{
		$transformed = $this->transformer->transform($this->model->fill([
			'first_name' => 'bob', 'last_name' => 'theTesster',
		]));

		$this->assertTrue(is_array($transformed));
		$this->assertEquals($transformed, $this->model->toArray());
	}
}
