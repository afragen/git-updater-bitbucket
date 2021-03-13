<?php
/**
 * Git Updater - Bitbucket
 *
 * @author    Andy Fragen
 * @license   MIT
 * @link      https://github.com/afragen/git-updater-bitbucket
 * @package   git-updater-bitbucket
 */

namespace Fragen\Git_Updater\Bitbucket;

use Fragen\GitHub_Updater\API\Bitbucket_API;
use Fragen\GitHub_Updater\API\Bitbucket_Server_API;

/*
 * Exit if called directly.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load textdomain.
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'git-updater-bitbucket' );
	}
);

/**
 * Class Bootstrap
 */
class Bootstrap {
	/**
	 * Run the bootstrap.
	 *
	 * @return bool|void
	 */
	public function run() {
		// Exit if GitHub Updater not running.
		if ( ! class_exists( '\\Fragen\\GitHub_Updater\\Bootstrap' ) ) {
			return false;
		}

		new Bitbucket_API();
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_filter( 'gu_get_repo_parts', [ $this, 'add_repo_parts' ], 10, 2 );
		add_filter( 'gu_settings_auth_required', [ $this, 'set_auth_required' ], 10, 1 );
		add_filter( 'gu_api_repo_type_data', [ $this, 'set_repo_type_data' ], 10, 2 );
		add_filter( 'gu_api_url_type', [ $this, 'set_api_url_data' ], 10, 4 );
		add_filter( 'gu_git_servers', [ $this, 'set_git_servers' ], 10, 1 );
		add_filter( 'gu_installed_apis', [ $this, 'set_installed_apis' ], 10, 1 );
		add_filter( 'gu_install_remote_install', [ $this, 'set_remote_install_data' ], 10, 2 );
		add_filter( 'gu_get_language_pack_json', [ $this, 'set_language_pack_json' ], 10, 4 );
		add_filter( 'gu_post_process_language_pack_package', [ $this, 'process_language_pack_data' ], 10, 4 );
	}

	/**
	 * Add API specific data to `get_repo_parts()`.
	 *
	 * @param array  $repos Array of repo data.
	 * @param string $type  plugin|theme.
	 *
	 * @return array
	 */
	public function add_repo_parts( $repos, $type ) {
		$repos['types'] = array_merge( $repos['types'], [ 'Bitbucket' => 'bitbucket_' . $type ] );
		$repos['uris']  = array_merge( $repos['uris'], [ 'Bitbucket' => 'https://bitbucket.org/' ] );

		return $repos;
	}

	/**
	 * Add API specific auth required data.
	 *
	 * @param array $auth_required Array of authentication required data.
	 *
	 * @return array
	 */
	public function set_auth_required( $auth_required ) {
		return array_merge(
			$auth_required,
			[
				'bitbucket'         => true,
				'bitbucket_private' => true,
				'bitbucket_server'  => true,
			]
		);
	}

	/**
	 * Add API specific repo data.
	 *
	 * @param array     $arr  Array of repo API data.
	 * @param \stdClass $repo Repository object.
	 *
	 * @return array
	 */
	public function set_repo_type_data( $arr, $repo ) {
		if ( 'bitbucket' === $repo->git ) {
			$arr['git'] = 'bitbucket';
			if ( empty( $repo->enterprise ) ) {
				$arr['base_uri']      = 'https://api.bitbucket.org';
				$arr['base_download'] = 'https://bitbucket.org';
			} else {
				$arr['base_uri']      = $repo->enterprise_api;
				$arr['base_download'] = $repo->enterprise;
			}
		}

		return $arr;
	}

	/**
	 * Add API specific URL data.
	 *
	 * @param array     $type          Array of API type data.
	 * @param \stdClass $repo          Repository object.
	 * @param bool      $download_link Boolean indicating a download link.
	 * @param string    $endpoint      API URL endpoint.
	 *
	 * @return array
	 */
	public function set_api_url_data( $type, $repo, $download_link, $endpoint ) {
		if ( 'bitbucket' === $type['git'] ) {
			$method = ( new Bitbucket_API() )->get_class_vars( 'API\Bitbucket_API', 'method' );
			do {
				if ( $repo->enterprise_api ) {
					if ( $download_link ) {
						$type['base_download'] = $type['base_uri'];
						break;
					}
					$type['base_uri'] = $repo->enterprise_api;
				}
			} while ( false );
			if ( $download_link && 'release_asset' === $method ) {
				$type['base_download'] = $type['base_uri'];
			}
		}

		return $type;
	}

	/**
	 * Add API as git server.
	 *
	 * @param array $git_servers Array of git servers.
	 *
	 * @return array
	 */
	public function set_git_servers( $git_servers ) {
		return array_merge( $git_servers, [ 'bitbucket' => 'Bitbucket' ] );
	}

	/**
	 * Add API data to $installed_apis.
	 *
	 * @param array $installed_apis Array of installed APIs.
	 *
	 * @return array
	 */
	public function set_installed_apis( $installed_apis ) {
		return array_merge(
			$installed_apis,
			[
				'bitbucket_api'        => true,
				'bitbucket_server_api' => true,
			]
		);
	}

	/**
	 * Set remote installation data for specific API.
	 *
	 * @param array $install Array of remote installation data.
	 * @param array $headers Array of repository header data.
	 *
	 * @return array
	 */
	public function set_remote_install_data( $install, $headers ) {
		if ( 'bitbucket' === $install['github_updater_api'] ) {
			$install = ( new Bitbucket_API() )->remote_install( $headers, $install );
			$install = ( new Bitbucket_Server_API() )->remote_install( $headers, $install );
		}

		return $install;
	}

	/**
	 * Filter to return API specific language pack data.
	 *
	 * @param \stdClass $response Object of Language Pack API response.
	 * @param string    $git      Name of git host.
	 * @param array     $headers  Array of repo headers.
	 * @param \stdClass $obj      Current class object.
	 *
	 * @return \stdClass
	 */
	public function set_language_pack_json( $response, $git, $headers, $obj ) {
		if ( 'bitbucket' === $git ) {
			$response = $obj->api( '/2.0/repositories/' . $headers['owner'] . '/' . $headers['repo'] . '/src/master/language-pack.json' );
		}

		return $response;
	}

	/**
	 * Filter to post process API specific language pack data.
	 *
	 * @param null|string $package URL to language pack.
	 * @param string      $git     Name of git host.
	 * @param \stdClass   $locale  Object of language pack data.
	 * @param array       $headers Array of repository headers.
	 *
	 * @return string
	 */
	public function process_language_pack_data( $package, $git, $locale, $headers ) {
		if ( 'bitbucket' === $git ) {
			$package = [ $headers['uri'], 'raw/master' ];
			$package = implode( '/', $package ) . $locale->package;
		}

		return $package;
	}
}
