<?php
/**
 * Plugin Name: InPost Pay
 * Plugin URI:
 * Description:
 * Version: 2.0.9
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Author: iLabs LTD
 * Author URI: iLabs.dev
 * Text Domain: inpost-pay
 * Domain Path: /lang/
 *
 * Copyright 2026 iLabs LTD
 *
 * @package InPost Pay
 * @author iLabs
 */

declare( strict_types=1 );

use Ilabs\Inpost_Pay\Container\ServiceContainer;
use function Ilabs\Inpost_Pay\inpost_pay;
use function Ilabs\Inpost_Pay\inpost_pay_container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/src/ShutdownHandler.php';
Ilabs\Inpost_Pay\ShutdownHandler::register( __FILE__ );
const WOOCOMMERCE_INPOST_PAY_PLUGIN_FILE = __FILE__;

$headers = get_file_data(
	__FILE__,
	array(
		'name'             => 'Plugin Name',
		'description'      => 'Description',
		'version'          => 'Version',
		'author'           => 'Author',
		'author_uri'       => 'Author URI',
		'requires_php'     => 'Requires PHP',
		'requires_plugins' => 'Requires Plugins',
		'tested_up_to'     => 'Tested up to',
		'text_domain'      => 'Text Domain',
		'domain_path'      => 'Domain Path',
	),
	'plugin'
);

$config = array(
	'__FILE__'                => __FILE__,
	'name'                    => 'Inpost Pay',
	'slug'                    => 'inpost_pay',
	'lang_dir'                => 'lang',
	'text_domain'             => 'inpost-pay',
	'min_php_int'             => 70400,
	'min_php'                 => 7.4,
	'required_plugins'        => array( 'woocommerce' ),
	'required_php_extensions' => array( 'curl' ),
);

require_once __DIR__ . '/system.php';

if ( ( new __Inpost_Pay_System( $config ) )->evaluate_system() ) {
	require_once __DIR__ . '/vendor/autoload.php';
	require_once 'dependencies.php';
	require_once __DIR__ . '/src/Helpers.php';

	$container = new ServiceContainer();
	$container->initialize_defaults( $config, $headers );

	inpost_pay_container( $container );

	try {
		inpost_pay()->execute( $config );
	} catch ( \Throwable $e ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( 'InPost Pay bootstrap error: ' . $e->getMessage() );
	}
}
