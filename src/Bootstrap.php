<?php
/**
 * Git Updater - Bitbucket
 *
 * @author    Andy Fragen
 * @license   MIT
 * @link      https://github.com/afragen/git-updater-bitbucket
 * @package   git-updater-bitbucket
 */

namespace Fragen\GitHub_Updater\Bitbucket;

use Fragen\GitHub_Updater\API\Bitbucket_API;

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
	 * Holds main plugin file.
	 *
	 * @var $file
	 */
	protected $file;

	/**
	 * Holds main plugin directory.
	 *
	 * @var $dir
	 */
	protected $dir;

	/**
	 * Constructor.
	 *
	 * @param  string $file Main plugin file.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file = $file;
		$this->dir  = dirname( $file );
	}

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
		add_filter(
			'gu_get_repo_parts',
			function ( $repos, $type ) {
				$repos['types'] = array_merge( $repos['types'], [ 'Bitbucket' => 'bitbucket_' . $type ] );
				$repos['uris']  = array_merge( $repos['uris'], [ 'Bitbucket' => 'https://bitbucket.org/' ] );

				return $repos;
			},
			10,
			2
		);

		add_filter(
			'gu_settings_auth_required',
			function ( $auth_required ) {
				return \array_merge(
					$auth_required,
					[
						'bitbucket_private' => false,
						'bitbucket_server'  => false,
					]
				);
			},
			10,
			1
		);

		add_filter(
			'gu_api_repo_type_data',
			function ( $false, $git ) {
				if ( 'bitbucket' === $git ) {
					return [
						'api'      => 'https://api.bitbucket.org',
						'download' => 'https://bitbucket.org',
					];
				}

				return $false;
			},
			10,
			2
		);

		add_filter(
			'gu_git_servers',
			function ( $git_servers ) {
				return array_merge( $git_servers, [ 'bitbucket' => 'Bitbucket' ] );
			},
			10,
			1
		);

		add_filter(
			'gu_installed_apis',
			function ( $installed_apis ) {
				return array_merge(
					$installed_apis,
					[
						'bitbucket_api'        => true,
						'bitbucket_server_api' => true,
					]
				);
			},
			10,
			1
		);
	}
}
