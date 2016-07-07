<?php

namespace Fuzz\Data\Serialization;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Request;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Serializer\DataArraySerializer;

class FuzzDataArraySerializer extends DataArraySerializer
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
		$lastPage = (int) $paginator->getLastPage();

		$pagination = [
			'total' => (int) $paginator->getTotal(),
			'count' => (int) $paginator->getCount(),
			'per_page' => (int) $paginator->getPerPage(),
			'current_page' => $currentPage,
			'total_pages' => $lastPage,
		];

		$pagination['links'] = [
			'next' => null,
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
			array_except(
				Request::instance()->query->all(), [
					self::PAGINATION_CURRENT_PAGE,
					self::PAGINATION_PER_PAGE
				]
			) as $key => $value
		) {
			$paginator->addQuery($key, $value);
		}

		// Add our "per page" pagination parameter to the constructed URLs
		$paginator->addQuery(self::PAGINATION_PER_PAGE, $paginator->perPage());
	}
}
