<?php

namespace Fuzz\Data\Eloquent;

use Carbon\Carbon;
use Fuzz\Data\Schema\SchemaUtility;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
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
	 * Unhide hidden properties from the model via a public interface.
	 *
	 * @param string|array $appends
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
