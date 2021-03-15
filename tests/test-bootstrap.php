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
		$empty     = ['types' => [], 'uris' => []];
		$expected  = [
			'types' => ['Bitbucket' => 'bitbucket_plugin'],
			'uris'  => ['Bitbucket' => 'https://bitbucket.org/'],
		];
		$acutal = (new Bootstrap())->add_repo_parts($empty, 'plugin');

		$this->assertEqualSetsWithIndex($expected, $acutal);
	}

	public function test_set_auth_required() {
		$expected = [
			'bitbucket'         => true,
			'bitbucket_private' => true,
			'bitbucket_server'  => true,
		];
		$acutal = (new Bootstrap())->set_auth_required([]);
		$this->assertEqualSetsWithIndex($expected, $acutal);
	}

	public function test_set_repo_type_data() {
		$org             = new \stdClass();
		$org->git        = 'bitbucket';
		$org->enterprise = null;
		$expected_org    = [
			'git'           => 'bitbucket',
			'base_uri'      => 'https://api.bitbucket.org',
			'base_download' => 'https://bitbucket.org',
		];

		$actual_org   = (new Bootstrap())->set_repo_type_data([], $org);
		$this->assertEqualSetsWithIndex($expected_org, $actual_org);

		$enterprise                 = new \stdClass();
		$enterprise->git            = 'bitbucket';
		$enterprise->enterprise     = 'https://mybitbucket.example.com';
		$enterprise->enterprise_api = 'https://api.mybitbucket.example.com';
		$expected_enterprise        = [
			'git'           => 'bitbucket',
			'base_uri'      => 'https://api.mybitbucket.example.com',
			'base_download' => 'https://mybitbucket.example.com',
		];

		$actual_enterprise   = (new Bootstrap())->set_repo_type_data([], $enterprise);
		$this->assertEqualSetsWithIndex($expected_enterprise, $actual_enterprise);
	}

	public function test_parse_headers() {
		$test = [
			'host' => null,
			'base_uri' => 'https://api.example.com',
		];

		$expected_rest_api = 'https://api.example.com/rest/api';
		$actual   = (new Bootstrap())->parse_headers($test, 'Bitbucket');

		$this->assertSame($expected_rest_api, $actual['enterprise_api']);
	}
}
