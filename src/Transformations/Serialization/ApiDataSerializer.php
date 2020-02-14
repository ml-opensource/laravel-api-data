<?php

namespace Fuzz\Data\Transformations\Serialization;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\AbstractPaginator;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Serializer\DataArraySerializer as FractalSerializer;

class ApiDataSerializer extends FractalSerializer
{
	/**
	 * Parameter name for pagination controller: items per page.
	 *
	 * @var string
	 */
	const PAGINATION_PER_PAGE = 'per_page';
	/**
	 * Parameter name for pagination controller: current page.
	 *
	 * @var string
	 */
	const PAGINATION_CURRENT_PAGE = 'page';

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
	 * Serialize the paginator.
	 *
	 * @param PaginatorInterface $paginator
	 *
	 * @return array
	 */
	public function paginator(PaginatorInterface $paginator)
	{
		$currentPage = (int) $paginator->getCurrentPage();
		$lastPage    = (int) $paginator->getLastPage();

		$pagination = [
			'total'        => (int) $paginator->getTotal(),
			'count'        => (int) $paginator->getCount(),
			'per_page'     => (int) $paginator->getPerPage(),
			'current_page' => $currentPage,
			'total_pages'  => $lastPage,
		];

		$pagination['links'] = [
			'next'     => null,
			'previous' => null,
		];

		$this->applyRequestQueryParams($paginator->getPaginator());

		if ($currentPage > 1) {
			$pagination['links']['previous'] = $paginator->getUrl($currentPage - 1);
		}

		if ($currentPage < $lastPage) {
			$pagination['links']['next'] = $paginator->getUrl($currentPage + 1);
		}

		return ['pagination' => $pagination];
	}

	/**
	 * Find all the query parameters passed in the request and apply them to the paginator so we can build
	 * a useful URL.
	 *
	 * @param \Illuminate\Pagination\AbstractPaginator $paginator
	 */
	public function applyRequestQueryParams(AbstractPaginator $paginator)
	{
		// Pass in any additional query variables
		foreach (
			Arr::except(
				Request::instance()->query->all(), [
					self::PAGINATION_CURRENT_PAGE,
					self::PAGINATION_PER_PAGE
				]
			) as $key => $value
		) {
			$paginator->appends($key, $value);
		}
		// Add our "per page" pagination parameter to the constructed URLs
		$paginator->appends(self::PAGINATION_PER_PAGE, $paginator->perPage());
	}
}
