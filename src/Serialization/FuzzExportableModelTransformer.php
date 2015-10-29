<?php

namespace Fuzz\Data\Serialization;

use Fuzz\Data\Eloquent\Model;
use Fuzz\Data\Traits\Exportable;
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
	 * @param \Fuzz\Data\Eloquent\Model $model
	 * @return array
	 */
	public function transform(Model $model)
	{
		if (! method_exists($model, 'getCsvExportMap')) {
			$exportable_trait = Exportable::class;
			throw new \LogicException("This model does not support csv export serialization. Does it use the $exportable_trait trait?");
		}

		return $this->buildRow($model->accessibleAttributesToArray(), $model->getCsvExportMap());
	}

	/**
	 * Pull out column mappings from the current row
	 *
	 * @param array $row
	 * @param array $column_mappings
	 * @return array
	 */
	protected function buildRow(array $row, array $column_mappings)
	{
		//$output = [];
		$row_data = [];

		// Map row data to columns
		foreach ($column_mappings as $column => $header) {
			$path = explode('.', $column);

			// Accept dot nested notations
			if (count($path) > 1) {
				$location = $row;

				foreach ($path as $step) {
					$location = isset($location[$step])? $location[$step] : null;
				}

				$row_data[$header] = $location;
			} else {
				$row_data[$header] = isset($row[$column]) ? $row[$column] : null;
			}
		}

		return $row_data;
	}
}
