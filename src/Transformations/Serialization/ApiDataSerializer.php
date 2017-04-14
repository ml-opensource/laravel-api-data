<?php

namespace Fuzz\Data\Transformations\Serialization;


use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Serializer\DataArraySerializer as FractalSerializer;

class ApiDataSerializer extends FractalSerializer
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
	public function item($resourceKey, array $data): array
	{
		return ['data' => $data];
	}

	/**
	 * Meta data.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public function meta(array $array): array
	{
		return $array;
	}

	/**
	 * Serialize the paginator.
	 *
	 * @param PaginatorInterface $paginator
	 *
	 * @return array
	 */
	public function paginator(PaginatorInterface $paginator): array
	{
		$pagination = [
			'page'        => (int) $paginator->getCurrentPage(),
			'total'       => (int) $paginator->getTotal(),
			'per_page'    => (int) $paginator->getPerPage(),
			'total_pages' => $paginator->getLastPage(),
		];

		return ['pagination' => $pagination];
	}
}
