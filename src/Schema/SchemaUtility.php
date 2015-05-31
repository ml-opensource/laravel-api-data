<?php

namespace Fuzz\Data\Schema;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Connection;

class SchemaUtility
{
	/**
	 * Add a comment at the level of a table.
	 *
	 * @param string     $table
	 * @param string     $comment
	 * @param Connection $connection
	 * @return void
	 */
	public static function commentTable($table, $comment, $connection = null)
	{
		if (is_null($connection)) {
			$connection = DB::connection();
		}

		$connection->statement(
			sprintf(
				'ALTER TABLE `%s` COMMENT = "%s"', $table, addslashes($comment)
			)
		);
	}

	/**
	 * Describe a table's columns.
	 *
	 * @param string     $table
	 * @param Connection $connection
	 * @return array
	 */
	public static function describeTable($table, $connection = null)
	{
		if (is_null($connection)) {
			$connection = DB::connection();
		}

		if ($connection->getDriverName() === 'sqlite') {
			$result = $connection->select(sprintf('PRAGMA table_info(%s)', $table));
			$column_key = 'name';
		} else {
			$result = $connection->select(
				sprintf(
					'SELECT COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'%s\' and TABLE_SCHEMA = \'%s\'', $table, $connection->getDatabaseName()
				)
			);
			$column_key = 'COLUMN_NAME';
		}

		return array_map(
			function ($item) use ($column_key) {
				return $item->$column_key;
			}, $result
		);
	}
}
