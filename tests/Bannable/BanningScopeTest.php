<?php

namespace Fuzz\Data\Tests\Bannable;

use Fuzz\Data\Bannable\BanningScope;
use Fuzz\Data\Tests\TestCase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Mockery;


class BanningScopeTest extends TestCase
{
	/**
	 * @test
	 *
	 * Apply scope to builder.
	 */
	public function testApplyingScopeToABuilder()
	{
		$scope   = Mockery::mock(BanningScope::class . '[extend]');
		$builder = Mockery::mock(EloquentBuilder::class);
		$model   = Mockery::mock(Model::class);
		$model->shouldReceive('getQualifiedBannedAtColumn')->once()->andReturn('table.banned_at');
		$builder->shouldReceive('whereNull')->once()->with('table.banned_at');

		$scope->apply($builder, $model);
	}

	/**
	 * @test
	 *
	 * Adds ban extension.
	 */
	public function testBanExtension()
	{
		$builder = new EloquentBuilder(new QueryBuilder(
			Mockery::mock(ConnectionInterface::class),
			Mockery::mock(Grammar::class),
			Mockery::mock(Processor::class)
		));

		$query        = new \stdClass();
		$query->joins = [];

		$scope = new BanningScope;
		$scope->extend($builder);
		$model = Mockery::mock(\stdClass::class);

		$callback = $builder->getMacro('ban');

		$givenBuilder = Mockery::mock(EloquentBuilder::class);
		$givenBuilder->shouldReceive('getQuery')->andReturn($query);
		$givenBuilder->shouldReceive('getModel')->twice()->andReturn($model);
		$givenBuilder->shouldReceive('update')->once()->with(['banned_at' => '1900-01-01 12:00:00']);

		$model->shouldReceive('getBannedAtColumn')->once()->andReturn('banned_at');
		$model->shouldReceive('freshTimestampString')->andReturn('1900-01-01 12:00:00');

		$callback($givenBuilder);
	}

	/**
	 * @test
	 *
	 * Adds ban extension.
	 */
	public function testBanExtensionWhenHavingJoins()
	{
		$builder = new EloquentBuilder(new QueryBuilder(
			Mockery::mock(ConnectionInterface::class),
			Mockery::mock(Grammar::class),
			Mockery::mock(Processor::class)
		));

		$query        = new \stdClass();
		$query->joins = [1];

		$scope = new BanningScope;
		$scope->extend($builder);
		$model = Mockery::mock(\stdClass::class);

		$callback = $builder->getMacro('ban');

		$givenBuilder = Mockery::mock(EloquentBuilder::class);
		$givenBuilder->shouldReceive('getQuery')->andReturn($query);
		$givenBuilder->shouldReceive('getModel')->twice()->andReturn($model);
		$givenBuilder->shouldReceive('update')->once()->with(['table.banned_at' => '1900-01-01 12:00:00']);

		$model->shouldReceive('getQualifiedBannedAtColumn')->once()->andReturn('table.banned_at');
		$model->shouldReceive('freshTimestampString')->andReturn('1900-01-01 12:00:00');

		$callback($givenBuilder);
	}

	/**
	 * @test
	 *
	 * Adds unban extension.
	 */
	public function testUnbanExtension()
	{
		$builder = new EloquentBuilder(new QueryBuilder(
			Mockery::mock(ConnectionInterface::class),
			Mockery::mock(Grammar::class),
			Mockery::mock(Processor::class)
		));

		$scope = new BanningScope;
		$scope->extend($builder);
		$callback     = $builder->getMacro('unban');
		$givenBuilder = Mockery::mock(EloquentBuilder::class);
		$givenBuilder->shouldReceive('withBanned')->once();
		$givenBuilder->shouldReceive('getModel')->once()->andReturn($model = Mockery::mock(\stdClass::class));
		$model->shouldReceive('getBannedAtColumn')->once()->andReturn('banned_at');
		$givenBuilder->shouldReceive('update')->once()->with(['banned_at' => null]);

		$callback($givenBuilder);
	}

	/**
	 * @test
	 *
	 * Adds withBanned extension.
	 */
	public function testWithBannedExtension()
	{
		$builder = new EloquentBuilder(new QueryBuilder(
			Mockery::mock(ConnectionInterface::class),
			Mockery::mock(Grammar::class),
			Mockery::mock(Processor::class)
		));

		$scope = Mockery::mock(BanningScope::class . '[remove]');
		$scope->extend($builder);
		$callback     = $builder->getMacro('withBanned');
		$givenBuilder = Mockery::mock(EloquentBuilder::class);
		$givenBuilder->shouldReceive('withoutGlobalScope')->with($scope)->andReturnSelf();
		$result = $callback($givenBuilder);

		$this->assertEquals($givenBuilder, $result);
	}

	/**
	 * @test
	 *
	 * Adds withoutBanned extension.
	 */
	public function testWithoutBannedExtension()
	{
		$builder = new EloquentBuilder(new QueryBuilder(
			Mockery::mock(ConnectionInterface::class),
			Mockery::mock(Grammar::class),
			Mockery::mock(Processor::class)
		));

		$model = Mockery::mock(Model::class);
		$model->shouldReceive('getQualifiedBannedAtColumn')->once()->andReturn('table.banned_at');

		$scope = Mockery::mock(BanningScope::class . '[remove]');
		$scope->extend($builder);

		$givenBuilder = Mockery::mock(EloquentBuilder::class);
		$givenBuilder->shouldReceive('withoutGlobalScope')->with($scope)->andReturnSelf();
		$givenBuilder->shouldReceive('whereNull')->once()->with('table.banned_at');
		$givenBuilder->shouldReceive('getModel')->once()->andReturn($model);


		$callback = $builder->getMacro('withoutBanned');
		$results  = $callback($givenBuilder);

		$this->assertEquals($givenBuilder, $results);
	}

	/**
	 * @test
	 *
	 * Adds onlyBanned extension.
	 */
	public function testOnlyBannedExtension()
	{
		$builder = new EloquentBuilder(new QueryBuilder(
			Mockery::mock(ConnectionInterface::class),
			Mockery::mock(Grammar::class),
			Mockery::mock(Processor::class)
		));

		$model = Mockery::mock(Model::class);
		$model->shouldReceive('getQualifiedBannedAtColumn')->once()->andReturn('table.banned_at');

		$scope = Mockery::mock(BanningScope::class . '[remove]');
		$scope->extend($builder);

		$givenBuilder = Mockery::mock(EloquentBuilder::class);
		$givenBuilder->shouldReceive('withoutGlobalScope')->with($scope)->andReturnSelf();
		$givenBuilder->shouldReceive('whereNotNull')->once()->with('table.banned_at');
		$givenBuilder->shouldReceive('getModel')->once()->andReturn($model);


		$callback = $builder->getMacro('onlyBanned');
		$results  = $callback($givenBuilder);

		$this->assertEquals($givenBuilder, $results);
	}

	/**
	 * Tear down.
	 */
	public function tearDown()
	{
		Mockery::close();
	}
}
