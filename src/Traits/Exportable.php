<?php

namespace Fuzz\Data\Traits;

use Fuzz\ApiServer\Exception\NotImplementedException;

/**
 * Class Exportable
 *
 * @package Fuzz\Data\Traits
 */
trait Exportable
{
	/**
	 * Find and return the export mapping for this class
	 *
	 * @return array
	 * @throws \Fuzz\ApiServer\Exception\NotImplementedException
	 */
	public function getCsvExportMap()
	{
		if (! isset($this->csv_export_map) || ! is_array($this->csv_export_map)) {
			$class = self::class;
			throw new NotImplementedException("The $class class does not define an export map or it is invalid.");
		}

		return $this->csv_export_map;
	}
}
