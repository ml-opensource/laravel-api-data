<?php

namespace Fuzz\Data\Eloquent;

use Carbon\Carbon;
use Fuzz\Data\Schema\SchemaUtility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
	/**
	 * Whether we must paginate lists.
	 *
	 * @var bool
	 */
	const PAGINATE_LISTS = true;

	/**
	 * Add additional appended properties to the model via a public interface.
	 *
	 * @param string|array $appends
	 * @return static
	 */
	public function addAppends($appends)
	{
		if (is_string($appends)) {
			$appends = func_get_args();
		}

		$this->appends = array_merge($this->appends, $appends);

		return $this;
	}

	/**
	 * Remove appended properties to the model via a public interface.
	 *
	 * @param string|array $appends
	 * @return static
	 */
	public function removeAppends($appends)
	{
		if (is_string($appends)) {
			$appends = func_get_args();
		}

		$this->appends = array_diff($this->appends, $appends);

		return $this;
	}

	/**
	 * Unhide hidden properties from the model via a public interface.
	 *
	 * @param string|array $hidden
	 * @return static
	 */
	public function removeHidden($hidden)
	{
		if (is_string($hidden)) {
			$hidden = func_get_args();
		}

		$this->hidden = array_diff($this->hidden, $hidden);

		return $this;
	}

	/**
	 * "Safe" version of with eager-loading.
	 *
	 * Checks if relations exist before loading them.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $builder
	 * @param string|array                          $relations
	 */
	public function scopeSafeWith(Builder $builder, $relations)
	{
		if (is_string($relations)) {
			$relations = func_get_args();
			array_shift($relations);
		}

		// Loop through all relations to check for valid relationship signatures
		foreach ($relations as $name => $constraints) {
			// Constraints may be passed in either form:
			// 2 => 'relation.nested'
			// or
			// 'relation.nested' => function() { ... }
			$constraints_are_name = is_numeric($name);
			$relation_name        = $constraints_are_name ? $constraints : $name;

			// Expand the dot-notation to see all relations
			$nested_relations     = explode('.', $relation_name);
			$model                = $builder->getModel();

			foreach ($nested_relations as $index => $relation) {
				if (method_exists($model, $relation)) {
					// Iterate through relations if they actually exist
					$model = $model->$relation()->getRelated();
				} elseif ($index > 0) {
					// If we found any valid relations, pass them through
					$safe_relation = implode('.', array_slice($nested_relations, 0, $index));
					if ($constraints_are_name) {
						$relations[$name] = $safe_relation;
					} else {
						unset($relations[$name]);
						$relations[$safe_relation] = $constraints;
					}
				} else {
					// If we didn't, remove this relation specification
					unset($relations[$name]);
					break;
				}
			}
		}

		$builder->with($relations);
	}

	/**
	 * Mutator for datetime attributes.
	 *
	 * @param        string /int $date
	 *                      A parsable datetime string or timestamp
	 * @param string $attribute
	 *                      The name of the attribute we're setting
	 * @return \Carbon\Carbon
	 */
	final protected function mutateDateTimeAttribute($date, $attribute)
	{
		if (is_numeric($date)) {
			return $this->attributes[$attribute] = Carbon::createFromTimestamp($date);
		}

		return $this->attributes[$attribute] = Carbon::parse($date)->toDateTimeString();
	}

	/**
	 * Accessor for datetime attributes. Ensures we always send datetimes as UNIX timestamps.
	 *
	 * @param string $value
	 *        A datetime value
	 * @return int
	 */
	final protected function accessDateTimeAttribute($value)
	{
		return strtotime($value);
	}

	/**
	 * Cast as a float, unless null.
	 *
	 * @param mixed $value
	 * @return null|float
	 */
	final protected function asFloat($value)
	{
		return is_null($value) ? $value : floatval($value);
	}

	/**
	 * Cast as an int, unless null.
	 *
	 * @param mixed $value
	 * @return null|int
	 */
	final protected function asInt($value)
	{
		return is_null($value) ? $value : intval($value);
	}

	/**
	 * Cast as a string, unless null.
	 *
	 * @param mixed $value
	 * @return null|string
	 */
	final protected function asString($value)
	{
		return is_null($value) ? $value : strval($value);
	}

	/**
	 * Return this model's fields.
	 *
	 * @return array
	 */
	final public function getFields()
	{
		return SchemaUtility::describeTable($this->getTable(), $this->getConnection());
	}
}
