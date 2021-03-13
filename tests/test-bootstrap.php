<?php

/**
 * Class BootstrapTest
 *
 * @package Git_Updater_Bitbucket
 */

use Fragen\Git_Updater\Bitbucket\Bootstrap;

/**
 * Sample test case.
 */
class BootstrapTest extends WP_UnitTestCase {
	/**
	 * A single example test.
	 */
	public function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue(true);
	}

	public function test_add_repo_parts() {
		$empty = ['types' => '', 'uris' => ''];
		$test  = [
			'types' => ['Bitbucket' => 'bitbucket_plugin'],
			'uris'  => ['Bitbucket' => 'https://bitbucket.org/'],
		];

		$this->assertSame($test, (new Bootstrap())->add_repo_parts($empty, 'plugin'));
	}
}
