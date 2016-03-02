<?php

namespace Fuzz\Data\Serialization;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;

/**
 * Class FuzzExportableModelTransformer
 *
 * @package Fuzz\Data\Serialization
 */
class FuzzExportableModelTransformer extends TransformerAbstract
{
	/**
	 * Transform the model into beautiful JSON
	 *
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @return array
	 */
	public function transform(Model $model)
	{
		if (! method_exists($model, 'getCsvExportMap')) {
			throw new \LogicException("This model does not support csv export serialization.");
		}

		return $this->buildRow($model->toArray(), $model->getCsvExportMap(), $model);
	}

	/**
	 * Pull out column mappings from the current row
	 *
	 * @param array                               $row
	 * @param array                               $column_mappings
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @return array
	 */
	protected function buildRow(array $row, array $column_mappings, Model $model)
	{
		$row_data = [];

		$mutators = isset($column_mappings['mutators']) ? $column_mappings['mutators'] : [];
		unset($column_mappings['mutators']);

		// Map row data to columns
		foreach ($column_mappings as $column => $header) {
			$path = explode('.', $column);

			// Accept dot nested notations
			if (count($path) > 1) {
				$location = $row;

				foreach ($path as $step) {
					$location = isset($location[$step]) ? $location[$step] : null;
				}

				$row_data[$header] = $this->getValue($location, $header, $model, $mutators);
			} else {
				$value             = isset($row[$column]) ? $row[$column] : null;
				$row_data[$header] = $this->getValue($value, $header, $model, $mutators);
			}
		}

		return $row_data;
	}

	/**
	 * Apply mutators to get values
	 *
	 * @param string                              $value
	 * @param string                              $header
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param array                               $mutators
	 * @return mixed
	 */
	protected function getValue($value, $header, Model $model, $mutators)
	{
		if (! isset($mutators[$header])) {
			return $value;
		}

		$mutator = $mutators[$header];

		if (! is_callable($mutator)) {
			throw new \LogicException('The mutator is not callable. Check the ' . $header . ' mutator.');
		}

		return $mutator($value, $model);
	}
}
