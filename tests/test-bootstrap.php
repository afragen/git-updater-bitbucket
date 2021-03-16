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

		$actual_enterprise = (new Bootstrap())->set_repo_type_data([], $enterprise);
		$this->assertEqualSetsWithIndex($expected_enterprise, $actual_enterprise);
	}

	public function test_parse_headers() {
		$test = [
			'host'     => null,
			'base_uri' => 'https://api.example.com',
		];

		$expected_rest_api = 'https://api.example.com/rest/api';
		$actual            = (new Bootstrap())->parse_headers($test, 'Bitbucket');

		$this->assertSame($expected_rest_api, $actual['enterprise_api']);
	}

	public function test_set_credentials() {
		$credentials = [
			'api.wordpress' => false,
			'isset'         => false,
			'token'         => null,
			'type'          => null,
			'enterprise'    => null,
		];
		$args = [
			'type'          => 'bitbucket',
			'headers'       => ['host' => 'bitbucket.org'],
			'options'       => ['bitbucket_access_token' => 'xxxx'],
			'slug'          => '',
			'object'        => new \stdClass,
		];
		$args_enterprise = [
			'type'          => 'bitbucket',
			'headers'       => ['host' => 'mybitbucket.org'],
			'options'       => ['bbserver_access_token' => 'yyyy'],
			'slug'          => '',
			'object'        => new \stdClass,
		];

		$credentials_expected =[
			'api.wordpress' => false,
			'type'          => 'bitbucket',
			'isset'         => true,
			'token'         => 'xxxx',
			'enterprise'    => false,
		];
		$credentials_expected_enterprise =[
			'api.wordpress' => false,
			'type'          => 'bitbucket',
			'isset'         => true,
			'token'         => 'yyyy',
			'enterprise'    => true,
		];

		$actual            = (new Bootstrap())->set_credentials($credentials, $args);
		$actual_enterprise = (new Bootstrap())->set_credentials($credentials, $args_enterprise);

		$this->assertEqualSetsWithIndex($credentials_expected, $actual);
		$this->assertEqualSetsWithIndex($credentials_expected_enterprise, $actual_enterprise);
	}

	public function test_get_icon_data() {
		$icon_data           = ['headers' => [], 'icons'=>[]];
		$expected['headers'] = ['BitbucketPluginURI' => 'Bitbucket Plugin URI'];
		$expected['icons']   = ['bitbucket' => 'git-updater-bitbucket/assets/bitbucket-logo.svg' ];

		$actual = (new Bootstrap())->set_git_icon_data($icon_data, 'Plugin');

		$this->assertEqualSetsWithIndex($expected, $actual);
	}
}
