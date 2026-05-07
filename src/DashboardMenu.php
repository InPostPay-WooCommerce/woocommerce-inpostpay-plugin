<?php

namespace Ilabs\Inpost_Pay;

class DashboardMenu {
	public static function registerMenu(): void {
			$settingsPage = new SettingsPage();
			add_menu_page(
				inpost_pay()->get_plugin_basename(),
				'InPost Pay',
				'manage_options',
				inpost_pay()->get_plugin_basename(),
				array(
					$settingsPage,
					'displayPluginAdminDashboard',
				),
				'dashicons-admin-settings',
				26
			);

			add_submenu_page(
				inpost_pay()->get_plugin_basename(),
				'InPost Pay - Hot Products',
				__( 'Settings', 'inpost-pay' ),
				'manage_options',
				inpost_pay()->get_plugin_basename(),
				array(
					$settingsPage,
					'displayPluginAdminDashboard',
				),
			);

			add_submenu_page(
				inpost_pay()->get_plugin_basename(),
				__( 'InPost Pay - Hot Products', 'inpost-pay' ),
				__( 'Hot Products', 'inpost-pay' ),
				'manage_options',
				inpost_pay()->get_plugin_basename() . '-hot-products',
				array(
					new HotProductsPage(),
					'displayHotProductsPage',
				)
			);

			add_submenu_page(
				inpost_pay()->get_plugin_basename(),
				__( 'InPost Pay - Unavailable Products', 'inpost-pay' ),
				__( 'Unavailable Products', 'inpost-pay' ),
				'manage_options',
				inpost_pay()->get_plugin_basename() . '-unavailable-products',
				array(
					new UnavailablePage(),
					'displayUnavailableProductsPage',
				)
			);
			add_submenu_page(
				inpost_pay()->get_plugin_basename(),
				__( 'InPost Pay - Unavailable Categories', 'inpost-pay' ),
				__( 'Unavailable Categories', 'inpost-pay' ),
				'manage_options',
				inpost_pay()->get_plugin_basename() . '-unavailable-categories',
				array(
					new UnavailablePage(),
					'displayUnavailableCategoriesPage',
				)
			);
	}
}
