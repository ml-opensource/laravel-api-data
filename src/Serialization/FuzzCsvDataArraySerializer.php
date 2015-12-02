<?php

namespace Fuzz\Data\Serialization;

use League\Fractal\Serializer\DataArraySerializer;

/**
 * Class FuzzCsvDataArraySerializer
 *
 * @package Fuzz\Data\Serialization
 */
class FuzzCsvDataArraySerializer extends DataArraySerializer
{
	/**
	 * Serialize a collection.
	 *
	 * @param string $resourceKey
	 * @param array  $data
	 *
	 * @return array
	 */
	public function collection($resourceKey, array $data)
	{
		if (count($data) === 0) {
			return [];
		}

		// This infers the CSV headers and isn't perfect..
		array_unshift($data, $this->buildCsvHeaders(array_keys($data[0])));
		return $data;
	}

	/**
	 * Serialize an item.
	 *
	 * @param string $resourceKey
	 * @param array  $data
	 *
	 * @return array
	 */
	public function item($resourceKey, array $data)
	{
		return $data;
	}

	/**
	 * Build the first row of a CSV
	 *
	 * @param array $column_mappings
	 * @return string
	 */
	protected function buildCsvHeaders($column_mappings)
	{
		return implode(',', $column_mappings) . PHP_EOL;
	}
}
