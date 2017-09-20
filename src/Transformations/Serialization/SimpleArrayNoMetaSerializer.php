<?php

namespace Fuzz\Data\Transformations\Serialization;

use League\Fractal\Serializer\ArraySerializer;

class SimpleArrayNoMetaSerializer extends ArraySerializer
{
	/**
	 * Serialize a collection.
	 *
	 * @param string $resourceKey
	 * @param array  $data
	 *
	 * @return array
	 */
	public function collection($resourceKey, array $data): array
	{
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
	public function item($resourceKey, array $data): array
	{
		return [$data];
	}

	/**
	 * Never add any meta information.
	 *
	 * @param array $meta
	 *
	 * @return array
	 */
	public function meta(array $meta)
	{
		return [];
	}
}
