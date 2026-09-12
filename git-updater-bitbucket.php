<?php
/**
 * Git Updater Bitbucket.
 * Requires Git Updater plugin.
 *
 * @package git-updater-bitbucket
 * @author  Andy Fragen
 * @link    https://github.com/afragen/git-updater-bitbucket
 * @link    https://github.com/afragen/github-updater
 */

/**
 * Plugin Name:       Git Updater - Bitbucket
 * Plugin URI:        https://github.com/afragen/git-updater-bitbucket
 * Description:       Add Bitbucket and Bitbucket Server repositories to the Git Updater plugin.
 * Version:           2.8.0.1
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * Domain Path:       /languages
 * Text Domain:       git-updater-bitbucket
 * GitHub Plugin URI: https://github.com/afragen/git-updater-bitbucket
 * GitHub Languages:  https://github.com/afragen/git-updater-bitbucket-translations
 * Primary Branch:    main
 * Requires at least: 5.9
 * Requires PHP:      8.0
 */

namespace Fragen\Git_Updater\Bitbucket;

/*
 * Exit if called directly.
 * PHP version check and exit.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load custom autoloader (plugin src/).
require_once __DIR__ . '/autoloader.php';
git_updater_register_autoloader( __DIR__, 'Bitbucket' );

// Load Composer autoloader for vendor packages, if installed (gitignored; created by composer install).
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

( new Bootstrap() )->load_hooks();

add_action(
	'init',
	function () {
		( new Bootstrap() )->run();
	}
);
