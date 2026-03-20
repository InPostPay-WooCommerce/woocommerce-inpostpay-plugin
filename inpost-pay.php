<?php

declare( strict_types=1 );

/**
 * Plugin Name: InPost Pay
 * Plugin URI:
 * Description:
 * Version: 2.0.7
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Author: iLabs LTD
 * Author URI: iLabs.dev
 * Text Domain: inpost-pay
 * Domain Path: /lang/
 *
 * Copyright 2026 iLabs LTD
 */

use Ilabs\Inpost_Pay\Container\ServiceContainer;
use function Ilabs\Inpost_Pay\inpost_pay_container;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

$config = [
	'__FILE__'                => __FILE__,
	'name'                    => 'Inpost Pay',
	'slug'                    => 'inpost_pay',
	'lang_dir'                => 'lang',
	'text_domain'             => 'inpost-pay',
	'min_php_int'             => 70400,
	'min_php'                 => 7.4,
	'required_plugins'        => ['woocommerce'],
	'required_php_extensions' => ['curl'],
];

require_once __DIR__ . '/system.php';

if ( ( new __Inpost_Pay_System( $config ) )->evaluate_system() ) {
	require_once __DIR__ . '/vendor/autoload.php';
	require_once 'dependencies.php';
	require_once __DIR__ . '/src/Helpers.php';

	$container = new ServiceContainer();
	$container->initialize_defaults( $config, $headers );

	function inpost_pay(): Ilabs\Inpost_Pay\Plugin {
		return new Ilabs\Inpost_Pay\Plugin();
	}

	inpost_pay_container( $container );
	inpost_pay()->execute( $config );
}
