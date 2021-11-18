<?php
/*
Plugin Name: Vende Más
Description: El plugin necesario para vender mas en Woocommerce
Version: 1.0.0
Contributors: daciomedina
Author: Dacio Medina
License: GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: veindemas
*/

class AdminNotification {
	
	protected $min_wc = '5.0.0'; 
    protected $plugin_name = "Vende Mas";
	
	/**
     * Register the activation hook
     */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'ft_install' ) );
	}
	
	/**
     * Check the dependent plugin version
     */
	protected function ft_is_wc_compatible() {			
		return defined( 'WC_VERSION' ) && version_compare( WC_VERSION, $this->min_wc, '>=' );
	}
	
	/**
     * Function to deactivate the plugin
     */
	protected function ft_deactivate_plugin() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}
	
	/**
     * Deactivate the plugin and display a notice if the dependent plugin is not compatible or not active.
     */
	public function ft_install() {
		if ( ! $this->ft_is_wc_compatible() || ! class_exists( 'WooCommerce' ) ) {
			$this->ft_deactivate_plugin();
			wp_die( 'No puede ser activado. ' . $this->get_ft_admin_notices() );
		} else {
			wp_die("Ahora si esta Activado");
		}
	}
	
	/**
     * Writing the admin notice
     */
	protected function get_ft_admin_notices() {
		return sprintf(
			'%1$s requires WooCommerce version %2$s or higher installed and active. You can download WooCommerce latest version %3$s OR go back to %4$s.',
			'<strong>' . $this->plugin_name . '</strong>',
			$this->min_wc,
			'<strong><a href="https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip">from here</a></strong>',
			'<strong><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">plugins page</a></strong>'
		);
	}

}

new AdminNotification();