<?php

namespace Fuzz\Data\Tests\Bannable;

use Carbon\Carbon;
use Fuzz\Data\Bannable\Bannable;
use Fuzz\Data\Bannable\Contracts\CanBeBanned;
use Fuzz\Data\Tests\ApplicationTestCase;
use Fuzz\Data\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery;


class BannableTest extends ApplicationTestCase
{
	/**
	 * @test
	 * tests unbanning event identifier.
	 */
	public function testConstUNBANNING_EVENT()
	{
		$this->assertTrue(is_string(CanBeBanned::BANNING_EVENT));
		$this->assertEquals('unbanning', CanBeBanned::UNBANNING_EVENT);
	}

	/**
	 * @test
	 * tests unbanned event identifier.
	 */
	public function testConstUNBANNED_EVENT()
	{
		$this->assertTrue(is_string(CanBeBanned::UNBANNED_EVENT));
		$this->assertEquals('unbanned', CanBeBanned::UNBANNED_EVENT);
	}

	/**
	 * @test
	 * tests banning event identifier
	 */
	public function testConstBANNING_EVENT()
	{
		$this->assertTrue(is_string(CanBeBanned::BANNING_EVENT));
		$this->assertEquals('banning', CanBeBanned::BANNING_EVENT);
	}

	/**
	 * @test
	 * tests banned event identifier.
	 */
	public function testConstBANNED_EVENT()
	{
		$this->assertTrue(is_string(CanBeBanned::BANNED_EVENT));
		$this->assertEquals('banned', CanBeBanned::BANNED_EVENT);
	}

	/**
	 * @test
	 * tests banned at column name.
	 */
	public function testConstBANNED_AT()
	{
		$this->assertEquals('banned_at', CanBeBanned::BANNED_AT);
	}

	/**
	 * @test
	 * tests can register an event function for unbanning.
	 */
	public function testCanRegisterUnbanningEvent()
	{
		$model = new MockBannableTraitStub();

		MockBannableTraitStub::unbanningEvent(function() {
			return null;
		});

		$this->assertTrue($model->getEventDispatcher()
			->hasListeners('eloquent.' . CanBeBanned::UNBANNING_EVENT . ': ' . get_class($model)));
	}

	/**
	 * @test
	 * tests can register an event function for unbanned.
	 */
	public function testCanRegisterUnbannedEvent()
	{
		$model = new MockBannableTraitStub();
		MockBannableTraitStub::unbannedEvent(function() {
			return null;
		});

		$this->assertTrue($model->getEventDispatcher()
			->hasListeners('eloquent.' . CanBeBanned::UNBANNED_EVENT . ': ' . get_class($model)));
	}

	/**
	 * @test
	 * tests can register an event function for banning.
	 */
	public function testCanRegisterBanningEvent()
	{
		$model = new MockBannableTraitStub();
		MockBannableTraitStub::banningEvent(function() {
			return null;
		});

		$this->assertTrue($model->getEventDispatcher()
			->hasListeners('eloquent.' . CanBeBanned::BANNING_EVENT . ': ' . get_class($model)));
	}

	/**
	 * @test
	 * tests can register an event function for banned.
	 */
	public function testCanRegisterBannedEvent()
	{
		$model = new MockBannableTraitStub();
		MockBannableTraitStub::bannedEvent(function() {
			return null;
		});

		$this->assertTrue($model->getEventDispatcher()
			->hasListeners('eloquent.' . CanBeBanned::BANNED_EVENT . ': ' . get_class($model)));
	}

	/**
	 * @test
	 * Tests a model can ban.
	 */
	public function testCanBan()
	{
		$model = Mockery::mock(MockBannableTraitStub::class);
		$model->shouldDeferMissing();
		$model->shouldReceive('newQueryWithoutScopes')->once()->andReturn($query = Mockery::mock(StdClass::class));
		$model->shouldReceive('getKey')->once()->andReturn(1);
		$query->shouldReceive('where')->once()->with('id', 1)->andReturn($query);
		$query->shouldReceive('update')->once()->with(['banned_at' => $model->freshTimeStamp()]);

		$model->ban();

		$this->assertInstanceOf(Carbon::class, $model->banned_at);
	}

	/**
	 * @test
	 * Tests exception thrown when no primary key defined on model.
	 */
	public function testBanCanThrowInvalidKey()
	{
		$this->expectException(\Exception::class);

		$model = Mockery::mock(MockBannableTraitStub::class);
		$model->shouldDeferMissing();
		$model->shouldReceive('getKeyName')->once()->andReturn(null);

		$model->ban();
	}

	/**
	 * @test
	 * Tests that ban will return false when the event does not fire.
	 */
	public function testBanReturnsFalseWhenEventMisfires()
	{
		$model = Mockery::mock(BannableTraitStub::class);
		$model->shouldDeferMissing();
		$model->shouldReceive('fireModelEvent')->once()->with(CanBeBanned::BANNING_EVENT)->andReturn(false);

		$ban = $model->ban();

		$this->assertFalse($ban);
	}

	/**
	 * @test
	 * Tests that unban will return false when the event does not fire.
	 */
	public function testUnbanReturnsFalseWhenEventMisfires()
	{
		$model = Mockery::mock(BannableTraitStub::class);
		$model->shouldDeferMissing();
		$model->shouldReceive('fireModelEvent')->once()->with(CanBeBanned::UNBANNING_EVENT)->andReturn(false);

		$unban = $model->unban();

		$this->assertFalse($unban);
	}

	/**
	 * @test
	 * Tests a model can unban.
	 */
	public function testCanUnban()
	{
		$model = Mockery::mock(BannableTraitStub::class);
		$model->shouldDeferMissing();
		$model->shouldReceive('fireModelEvent')->once()->with(CanBeBanned::UNBANNING_EVENT)->andReturn(true);
		$model->shouldReceive('fireModelEvent')->once()->with(CanBeBanned::UNBANNED_EVENT, false);
		$model->shouldReceive('save')->once()->andReturn(true);

		$model->unban();
		$this->assertNull($model->banned_at);
	}

	/**
	 * @test
	 * Tests that the model can see if it is banned.
	 */
	public function testCanCheckIsBanned()
	{
		$model = new MockBannableTraitStub();

		$model->{$model->getBannedAtColumn()} = Carbon::now();

		$this->assertTrue($model->isBanned());
	}

	/**
	 * @test
	 * Tests that the model can see if it is not banned.
	 */
	public function testCanCheckIsNotBanned()
	{
		$model = new MockBannableTraitStub();

		$model->{$model->getBannedAtColumn()} = null;

		$this->assertFalse($model->isBanned());
	}

	/**
	 * @test
	 * Tests that the banned at column can be returned.
	 */
	public function testCanGetBannedAtColumn()
	{
		$model = new MockBannableTraitStub();
		$this->assertTrue(is_string($model->getBannedAtColumn()));
		$this->assertEquals(CanBeBanned::BANNED_AT, $model->getBannedAtColumn());
	}

	/**
	 * @test
	 * Tests that a custom banned at column can be set.
	 */
	public function testCanSetCustomBannedAtColumn()
	{
		$model = new class extends MockBannableTraitStub
		{
			const BANNED_AT = 'custom_banned_at';
		};

		$this->assertTrue(is_string($model->getBannedAtColumn()));
		$this->assertEquals('custom_banned_at', $model->getBannedAtColumn());
	}

	/**
	 * @test
	 * Tests that a qualified banned at column can be returned.
	 */
	public function testCanGetQualifiedGetBannedColumn()
	{
		$model = new MockBannableTraitStub();
		$this->assertTrue(is_string($model->getQualifiedBannedAtColumn()));
		$this->assertEquals('users' . '.' . CanBeBanned::BANNED_AT, $model->getQualifiedBannedAtColumn());
	}

	/**
	 * @test
	 * Tests that the a model query can be scoped to banned users.
	 */
	public function testCanScopeBanned()
	{
		$model   = new MockBannableTraitStub();
		$builder = Mockery::mock(Builder::class);
		$builder->shouldReceive('whereNotNull')->once()->with($model->getBannedAtColumn())->andReturnSelf();

		$this->assertInstanceOf(Builder::class, $model->scopeBanned($builder));
	}

	/**
	 * @test
	 * Tests that a model query can be scoped to unbanned users.
	 */
	public function testCanScopeNotBanned()
	{
		$model   = new MockBannableTraitStub();
		$builder = Mockery::mock(Builder::class);
		$builder->shouldReceive('whereNull')->once()->with($model->getBannedAtColumn())->andReturnSelf();

		$this->assertInstanceOf(Builder::class, $model->scopeNotBanned($builder));
	}
}


class MockBannableTraitStub extends Model implements CanBeBanned
{
	use Bannable;

	public static $BANNED_AT;

	protected $table = 'users';
}


class BannableTraitStub
{
	use Bannable;

	public function newQuery()
	{

	}

	public function getKey()
	{
		return 1;
	}

	public function getKeyName()
	{
		return 'id';
	}

	public function save()
	{

	}

	public function fireModelEvent()
	{

	}

	public function freshTimestamp()
	{
		return Carbon::now();
	}

	public function fromDateTime()
	{
		return 'date-time';
	}
}
