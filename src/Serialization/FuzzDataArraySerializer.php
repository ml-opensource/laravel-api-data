<?php

namespace Fuzz\Data\Serialization;

use League\Fractal\Serializer\DataArraySerializer;

class FuzzDataArraySerializer extends DataArraySerializer
{
	/**
	 * Class constructor
	 *
	 * @param string|null $baseUrl
	 */
	public function __construct($baseUrl = null)
	{
		$this->baseUrl = $baseUrl;
		$this->rootObjects = [];
	}

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
		return ['data' => $data];
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
		return ['data' => $data];
	}
}
