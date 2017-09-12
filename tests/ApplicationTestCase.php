<?php

namespace Fuzz\Data\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class ApplicationTestCase extends OrchestraTestCase
{
	/**
	 * Get base path.
	 *
	 * @return string
	 */
	protected function getBasePath()
	{
		// reset base path to point to our package's src directory
		return __DIR__ . '/../vendor/orchestra/testbench/fixture';
	}
}
