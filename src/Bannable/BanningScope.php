<?php


namespace Fuzz\Data\Bannable;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;


class BanningScope implements Scope
{
	/**
	 * All of the extensions to be added to the builder.
	 *
	 * @var array
	 */
	protected $extensions = ['Ban', 'Unban', 'WithBanned', 'WithoutBanned', 'OnlyBanned'];

	/**
	 * Apply the scope to a given Eloquent query builder.
	 *
	 * @param Builder $builder
	 * @param Model   $model
	 *
	 * @return void
	 */
	public function apply(Builder $builder, Model $model)
	{
		$builder->whereNull($model->getQualifiedBannedAtColumn());
	}

	/**
	 * Extend the query builder with the needed functions.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	public function extend(Builder $builder)
	{
		foreach ($this->extensions as $extension) {
			$this->{"add{$extension}"}($builder);
		}
	}

	/**
	 * Get the "banned at" column for the builder.
	 *
	 * @param Builder $builder
	 *
	 * @return string
	 */
	protected function getBannedAtColumn(Builder $builder): string
	{
		if (count($builder->getQuery()->joins) > 0) {
			return $builder->getModel()->getQualifiedBannedAtColumn();
		}

		return $builder->getModel()->getBannedAtColumn();
	}

	/**
	 * Add the ban extension to the builder.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	protected function addBan(Builder $builder)
	{
		$builder->macro('ban', function(Builder $builder) {
			$column = $this->getBannedAtColumn($builder);

			return $builder->update([
				$column => $builder->getModel()->freshTimestampString(),
			]);
		});
	}

	/**
	 * Add the unban extension to the builder.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	protected function addUnban(Builder $builder)
	{
		$builder->macro('unban', function(Builder $builder) {
			$builder->withBanned();

			return $builder->update([$builder->getModel()->getBannedAtColumn() => null]);
		});
	}

	/**
	 * Add the with-banned extension to the builder.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	protected function addWithBanned(Builder $builder)
	{
		$builder->macro('withBanned', function(Builder $builder) {
			return $builder->withoutGlobalScope($this);
		});
	}

	/**
	 * Add the without-banned extension to the builder.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	protected function addWithoutBanned(Builder $builder)
	{
		$builder->macro('withoutBanned', function(Builder $builder) {
			$builder->withoutGlobalScope($this)
				->whereNull($builder->getModel()->getQualifiedBannedAtColumn());

			return $builder;
		});
	}

	/**
	 * Add the only-banned extension to the builder.
	 *
	 * @param Builder $builder
	 *
	 * @return void
	 */
	protected function addOnlyBanned(Builder $builder)
	{
		$builder->macro('onlyBanned', function(Builder $builder) {
			$builder->withoutGlobalScope($this)
				->whereNotNull($builder->getModel()->getQualifiedBannedAtColumn());

			return $builder;
		});
	}
}
