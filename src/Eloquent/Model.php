<?php

namespace Fuzz\Data\Eloquent;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Fuzz\Data\Schema\SchemaUtility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;

abstract class Model extends Eloquent
{
	/**
	 * Model access rules
	 *
	 * @var array
	 */
	protected $access_rules = [];

	/**
	 * Model modification rules
	 *
	 * @var array
	 */
	protected $modify_rules = [];

	/**
	 * Access authorizer
	 *
	 * @var object
	 */
	protected $access_authorizer;

	/**
	 * Modification authorizer
	 *
	 * @var object
	 */
	protected $modify_authorizer;

	/**
	 * Determine if the app is running in the console (seeding, tinkering)
	 *
	 * @var bool
	 */
	protected $console_mode = false;

	/**
	 * Whether we must paginate lists.
	 *
	 * @var bool
	 */
	const PAGINATE_LISTS = true;

	/**
	 * Boot parent and local logic
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function setAttribute($key, $value)
	{
		// First we will check for the presence of a mutator for the set operation
		// which simply lets the developers tweak the attribute as it is set on
		// the model, such as "json_encoding" an listing of data for storage.
		if ($this->hasSetMutator($key)) {
			$method = 'set' . Str::studly($key) . 'Attribute';

			return $this->{$method}($value);
		}

		// If an attribute is listed as a "date", we'll convert it from a DateTime
		// instance into a form proper for storage on the database tables using
		// the connection grammar's date format. We will auto set the values.
		elseif (in_array($key, $this->getDates()) && $value) {
			$value = $this->fromDateTime($value);
		}

		// Only set if the user can currently access
		if ($this->console_mode || $this->modify_authorizer->canAccess($key)) {
			if ($this->isJsonCastable($key) && ! is_null($value)) {
				$value = json_encode($value);
			}

			$this->attributes[$key] = $value;
		}
	}

	/**
	 * Constructor. Set up authorizers.
	 */
	public function __construct()
	{
		parent::__construct();

		// Construct Authorizers
		$authorizer              = config('auth.authorizer');
		$this->access_authorizer = new $authorizer($this->access_rules, $this);
		$this->modify_authorizer = new $authorizer($this->modify_rules, $this);
		$this->console_mode      = App::runningInConsole();
	}

	/**
	 * Filter attributes which can and can't be accessed by the user
	 *
	 * @param array $attributes
	 * @return array
	 */
	public function filterAccessibleAttributes(array $attributes)
	{
		$filtered = [];
		foreach ($attributes as $key => $attribute) {
			$accesible = $this->access_authorizer->canAccess($key);

			if ($accesible) {
				$filtered[$key] = $attribute;
			}
		}

		return $filtered;
	}

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
			$nested_relations = explode('.', $relation_name);
			$model            = $builder->getModel();

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

	/**
	 * Return this model's access restrictions
	 *
	 * @return array
	 */
	public function getAccessRestrictions()
	{
		return $this->access_rules;
	}

	/**
	 * Return this model's modify restrictions
	 *
	 * @return array
	 */
	public function getModifyRestrictions()
	{
		return $this->modify_rules;
	}
}
