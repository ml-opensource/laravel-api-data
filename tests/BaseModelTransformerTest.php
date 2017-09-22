<?php

namespace Fuzz\Data\Tests;

use Fuzz\Data\Traits\Transformations;
use Fuzz\Data\Transformations\BaseModelTransformer;
use Illuminate\Database\Eloquent\Model;

class BaseModelTransformerTest extends TestCase
{
	use Transformations;

	public function testItProcessesRelations()
	{
		$model       = new BaseModelTransformerTestModelStub;
		$transformer = new TransformerWithIncludesStub;

		$relations = $transformer->processRelations($model);

		$this->assertSame(1, count($relations));
		$this->assertArrayHasKey('relation', $relations);
	}

	public function testItDoesNotProcessRelationsThatShouldBeIgnored()
	{
		$model    = new BaseModelTransformerTestModelStub;
		$transformer = new TransformerWithOutIncludesStub;

		$relations = $transformer->processRelations($model);

		$this->assertSame(0, count($relations));
		$this->assertArrayNotHasKey('relation', $relations);
	}

	public function testItDoesNotProcessRelationsThatAreNotLoaded()
	{
		$model = new BaseModelTransformerTestModelStub;
		$transformer = new TransformerWithOutIncludesStub;

		$relations = $transformer->processRelations($model);

		$this->assertSame(0, count($relations));
		$this->assertArrayNotHasKey('not_loaded_relation', $relations);
	}

	public function testItDoesNotProcessRelationsThatAreNull()
	{
		$model              = new BaseModelTransformerTestModelStub;
		$transformer = new TransformerWithOutIncludesStub;

		$relations = $transformer->processRelations($model);

		$this->assertSame(0, count($relations));
		$this->assertArrayNotHasKey('not_loaded_relation', $relations);
	}
}

class TransformerWithIncludesStub extends BaseModelTransformer
{
	public function getPossibleIncludes(): array
	{
		return [
			'relation'       => TransformerWithOutIncludesStub::class,
			'other_relation' => TransformerWithOutIncludesStub::class,
			'not_loaded'     => TransformerWithIncludesStub::class,
		];
	}
}

class TransformerWithOutIncludesStub extends BaseModelTransformer
{
	/**
	 * Transform the model into beautiful JSON
	 *
	 * @param \Illuminate\Database\Eloquent\Model $instance
	 *
	 * @return array
	 */
	public function transform(Model $instance): array
	{
		// Serialize
		$arrayed_model = $instance->toArray();

		return array_merge($arrayed_model, $this->processRelations($instance));
	}

	public function getPossibleIncludes(): array
	{
		return [];
	}
}

class BaseModelTransformerTestModelStub extends Model
{
	public $relation;

	public $not_loaded_relation = null;

	public function __construct()
	{
		parent::__construct();

		$this->relation = new BaseModelTransformerTestModelRelationStub;
	}

	public function relationLoaded($relation)
	{
		switch ($relation) {
			case 'relation':
				return true;
			default:
				return false;
		}
	}

	public function getRelation($relation)
	{
		switch ($relation) {
			case 'relation':
				return $this->relation;
			default:
				return null;
		}
	}
}

class BaseModelTransformerTestModelRelationStub extends Model
{

}