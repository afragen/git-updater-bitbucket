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

use Fragen\Git_Updater\API\Bitbucket_API;
use Fragen\Git_Updater\API\Bitbucket_Server_API;

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
		// Exit if Git Updater not running.
		if ( ! class_exists( '\\Fragen\\Git_Updater\\Bootstrap' ) ) {
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
		add_filter( 'gu_parse_enterprise_headers', [ $this, 'parse_headers' ], 10, 2 );
		add_filter( 'gu_settings_auth_required', [ $this, 'set_auth_required' ], 10, 1 );
		add_filter( 'gu_display_repos', [ $this, 'set_display_bitbucket_repos' ], 10, 3 );
		add_filter( 'gu_get_repo_api', [ $this, 'set_repo_api' ], 10, 3 );
		add_filter( 'gu_api_repo_type_data', [ $this, 'set_repo_type_data' ], 10, 2 );
		add_filter( 'gu_api_url_type', [ $this, 'set_api_url_data' ], 10, 4 );
		add_filter( 'gu_post_get_credentials', [ $this, 'set_credentials' ], 10, 2 );
		add_filter( 'gu_get_auth_header', [ $this, 'set_auth_header' ], 10, 2 );
		add_filter( 'gu_decode_response', [ $this, 'decode_response' ], 10, 2 );
		add_filter( 'gu_git_servers', [ $this, 'set_git_servers' ], 10, 1 );
		add_filter( 'gu_running_git_servers', [ $this, 'set_running_enterprise_servers' ], 10, 2 );
		add_filter( 'gu_installed_apis', [ $this, 'set_installed_apis' ], 10, 1 );
		add_filter( 'gu_post_api_response_body', [ $this, 'convert_remote_body_response' ], 10, 2 );
		add_filter( 'gu_parse_api_branches', [ $this, 'parse_branches' ], 10, 2 );
		add_filter( 'gu_parse_release_asset', [ $this, 'parse_release_asset' ], 10, 4 );
		add_filter( 'gu_install_remote_install', [ $this, 'set_remote_install_data' ], 10, 2 );
		add_filter( 'gu_get_language_pack_json', [ $this, 'set_language_pack_json' ], 10, 4 );
		add_filter( 'gu_post_process_language_pack_package', [ $this, 'process_language_pack_data' ], 10, 4 );
		add_filter( 'gu_get_git_icon_data', [ $this, 'set_git_icon_data' ], 10, 2 );
		add_filter( 'gua_addition_types', [ $this, 'add_addition_types' ], 10, 1 );
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
	 * Modify enterprise API header data.
	 *
	 * @param array  $header Array of repo data.
	 * @param string $git    Name of git host.
	 *
	 * @return string
	 */
	public function parse_headers( $header, $git ) {
		if ( 'Bitbucket' === $git && false === strpos( $header['host'], 'bitbucket.org' ) ) {
			$header['enterprise_uri']  = $header['base_uri'];
			$header['enterprise_api']  = trim( $header['enterprise_uri'], '/' );
			$header['enterprise_api'] .= '/rest/api';
		}

		return $header;
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
	 * Return bbserver repo types to display in Settings.
	 *
	 * @param  array  $type_repos Array of repo objects to display.
	 * @param  array  $repos      Array of repos.
	 * @param  string $git        Name    of API, eg 'github'.
	 * @return array
	 */
	public function set_display_bitbucket_repos( $type_repos, $repos, $git ) {
		$bbserver_repos = [];

		if ( in_array( $git, [ 'bitbucket', 'bbserver' ], true ) ) {
			$bbserver_repos = array_filter(
				$repos,
				function ( $e ) use ( $git ) {
					if ( ! empty( $e->enterprise ) ) {
						return false !== stripos( $e->git, 'bitbucket' );
					}
				}
			);
		}
		if ( 'bitbucket' === $git ) {
			return array_diff_key( $type_repos, $bbserver_repos );
		}
		if ( 'bbserver' === $git ) {
			return $bbserver_repos;
		}

		return $type_repos;
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
	 * Return git host API object.
	 *
	 * @param \stdClass $repo_api Git API object.
	 * @param string    $git      Name of git host.
	 * @param \stdClass $repo     Repository object.
	 *
	 * @return \stdClass
	 */
	public function set_repo_api( $repo_api, $git, $repo ) {
		if ( 'bitbucket' === $git ) {
			if ( ! empty( $repo->enterprise ) ) {
				$repo_api = new Bitbucket_Server_API( $repo );
			} else {
				$repo_api = new Bitbucket_API( $repo );
			}
		}

		return $repo_api;
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
	 * Add credentials data for API.
	 *
	 * @param array $credentials Array of repository credentials data.
	 * @param array $args        Hook args.
	 *
	 * @return array
	 */
	public function set_credentials( $credentials, $args ) {
		if ( isset( $args['type'], $args['headers'], $args['options'], $args['slug'], $args['object'] ) ) {
			$type    = $args['type'];
			$headers = $args['headers'];
			$options = $args['options'];
			$slug    = $args['slug'];
			$object  = $args['object'];
		} else {
			return $credentials;
		}
		if ( 'bitbucket' === $type || $object instanceof Bitbucket_API || $object instanceof Bitbucket_Server_API ) {
			$bitbucket_org   = in_array( $headers['host'], [ 'bitbucket.org', 'api.bitbucket.org' ], true );
			$bitbucket_token = ! empty( $options['bitbucket_access_token'] ) ? $options['bitbucket_access_token'] : null;
			$bbserver_token  = ! empty( $options['bbserver_access_token'] ) ? $options['bbserver_access_token'] : null;
			$token           = ! empty( $options[ $slug ] ) ? $options[ $slug ] : null;
			$token           = null === $token && $bitbucket_org ? $bitbucket_token : $token;
			$token           = null === $token && ! $bitbucket_org ? $bbserver_token : $token;

			$credentials['type']       = 'bitbucket';
			$credentials['isset']      = true;
			$credentials['token']      = isset( $token ) ? $token : null;
			$credentials['enterprise'] = ! $bitbucket_org;
		}

		return $credentials;
	}

	/**
	 * Add Basic Authentication header.
	 *
	 * @param array $headers     HTTP GET headers.
	 * @param array $credentials Repository credentials.
	 *
	 * @return array
	 */
	public function set_auth_header( $headers, $credentials ) {
		if ( 'bitbucket' === $credentials['type'] ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$headers['headers']['Authorization'] = 'Basic ' . base64_encode( $credentials['token'] );
		}

		return $headers;
	}

	/**
	 * Decode API response.
	 *
	 * @param \stdClass $response API response object.
	 * @param string    $git      Name  of API, eg 'github'.
	 *
	 * @return \stdClass
	 */
	public function decode_response( $response, $git ) {
		if ( 'bbserver' === $git ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$response = isset( $response->content ) ? base64_decode( $response->content ) : $response;
		}

		return $response;
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
	 * Set running enterprise git servers.
	 *
	 * @param array $servers Array of repository git types.
	 * @param array $repos   Array of repositories objects.
	 *
	 * @return array
	 */
	public function set_running_enterprise_servers( $servers, $repos ) {
		$bbservers = array_map(
			function ( $e ) {
				if ( ! empty( $e->enterprise ) ) {
					if ( 'bitbucket' === $e->git ) {
						return 'bbserver';
					}
				}
			},
			$repos
		);
		$bbservers = array_filter( $bbservers );

		return array_merge( $servers, $bbservers );
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
	 * Convert HHTP remote body response to JSON.
	 *
	 * @param array     $response HTTP GET response.
	 * @param \stdClass $obj      API object.
	 *
	 * @return array
	 */
	public function convert_remote_body_response( $response, $obj ) {
		if ( $obj instanceof Bitbucket_API || $obj instanceof Bitbucket_Server_API ) {
			$body = wp_remote_retrieve_body( $response );
			if ( null === json_decode( $body ) ) {
				$response['body'] = json_encode( $body );
			}
		}

		return $response;
	}

	/**
	 * Parse API response that returns as stdClass.
	 *
	 * @param  array|\stdClass $response API response.
	 * @param  string          $git      Name of API, eg 'github'.
	 * @return array|\stdClass
	 */
	public function parse_branches( $response, $git ) {
		if ( in_array( $git, [ 'bitbucket', 'bbserver' ], true ) ) {
			$response = isset( $response->values ) ? $response->values : $response;
		}

		return $response;
	}

	/**
	 * Parse API release asset.
	 *
	 * @param \stdClass $response API response object.
	 * @param string    $git      Name of git host.
	 * @param string    $request  Schema of API request.
	 * @param \stdClass $obj      Current class object.
	 *
	 * @return string|null
	 */
	public function parse_release_asset( $response, $git, $request, $obj ) {
		if ( 'bitbucket' === $git ) {
			do {
				$assets = isset( $response->values ) ? $response->values : [];
				foreach ( $assets as $asset ) {
					if ( 1 === count( $assets ) || 0 === strpos( $asset->name, $obj->type->slug ) ) {
						$response = $asset->links->self->href;
						break;
					}
				}
			} while ( false );
			$response                    = is_string( $response ) ? $response : null;
			$asset->browser_download_url = $response;
			$asset->download_count       = $asset->downloads;
			$obj->set_repo_cache( 'release_asset_response', $asset );
		}
		if ( 'bbserver' === $git ) {
			// TODO: make work.
		}

		return $response;
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
		if ( 'bitbucket' === $install['git_updater_api'] ) {
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

	/**
	 * Set API icon data for display.
	 *
	 * @param array  $icon_data Header data for API.
	 * @param string $type_cap  Plugin|Theme.
	 *
	 * @return array
	 */
	public function set_git_icon_data( $icon_data, $type_cap ) {
		$icon_data['headers'] = array_merge(
			$icon_data['headers'],
			[ "Bitbucket{$type_cap}URI" => "Bitbucket {$type_cap} URI" ]
		);
		$icon_data['icons']   = array_merge(
			$icon_data['icons'],
			[ 'bitbucket' => basename( dirname( __DIR__ ) ) . '/assets/bitbucket-logo.svg' ]
		);

		return $icon_data;
	}

	/**
	 * Add repository types to Git Updater Additions.
	 *
	 * @param array $addition_types Array of Git Updater Additions repository types.
	 *
	 * @return array
	 */
	public function add_addition_types( $addition_types ) {
		return array_merge( $addition_types, [ 'bitbucket_plugin', 'bitbucket_theme' ] );
	}
}
