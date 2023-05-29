<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://http://likes-kopen.nl/
 * @since             1.0.0
 * @package           Woo_Smm_Addon
 *
 * @wordpress-plugin
 * Plugin Name:       Woo SMM AddOn
 * Plugin URI:        https://http://likes-kopen.nl/
 * Description:       Connects your WooCommerce store with other multiple SMM Panels through API. Fully automated order system including full dashboard to check order status and create new orders. Process all type of orders; dripfeed, custom comments and default services and auto translate language with wpml features

 * Version:           1.0.0
 * Author:            Sumit Walia
 * Author URI:        https://http://likes-kopen.nl/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-smm-addon
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_SMM_ADDON_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-smm-addon-activator.php
 */
function activate_woo_smm_addon() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-smm-addon-activator.php';
	Woo_Smm_Addon_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-smm-addon-deactivator.php
 */
function deactivate_woo_smm_addon() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-smm-addon-deactivator.php';
	Woo_Smm_Addon_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_smm_addon' );
register_deactivation_hook( __FILE__, 'deactivate_woo_smm_addon' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-smm-addon.php';

/**
 *
 * Add Woo SMM AddOn
 *
 */

add_action('admin_menu', 'image_gallery');
function image_gallery()
{
	add_menu_page('Woo SMM', 'Woo SMM API', 'add_users', 'ftb', 'woo_smm_api_option', 'dashicons-money', 13 );
}

/**
 *
 * Call function of Admin Menu bar
 *
 */
function woo_smm_api_option() {
	require plugin_dir_path(__FILE__) . 'admin/partials/woo-smm-addon-admin-display.php';
}


/**
 *
 * include function files to handle data
 *
 */

include plugin_dir_path(__FILE__) .  'admin/partials/woo-smm-addon-admin-api.php';
include plugin_dir_path(__FILE__) .  'admin/partials/woo-smm-addon-admin_api_callback.php';
include plugin_dir_path(__FILE__) .  'admin/partials/woo-smm-addon-payments.php';
include plugin_dir_path(__FILE__) .  'admin/partials/woo-smm-addon-admin-meta_functions.php';
include plugin_dir_path(__FILE__) .  'admin/partials/woo-smm-addon-admin_custom_field.php';
include plugin_dir_path(__FILE__) .  'admin/partials/woo-smm-addon-global-cart.php';


/**
 *
 * Call translater function of autodetect WPML
 *
 */

include plugin_dir_path(__FILE__) .  'admin/partials/woo-smm-translater/Tradutor.php';


/***************** Second function with language and label parameters ***********/
function tsl_lg($label) {
    // Initialize the translator
    $language = ICL_LANGUAGE_CODE;

    $tradutor = new Tradutor ();

    // Translate the label based on the provided language
    $translated_label = $tradutor->traduzLang(null, $language, $label);

    // Use the translated label in your code
    return $translated_label;
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woo_smm_addon() {

	$plugin = new Woo_Smm_Addon();
	$plugin->run();

}
run_woo_smm_addon();

/**
 *
 * a function to collect products and return product IDs
 *
 */

function get_all_product_ids() {
    $product_ids = get_posts(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ));

    return $product_ids;
}

/**
 *
 * Get all product IDs and get original product data update to tranlated products one by one
 *
 */ 
$product_ids = get_all_product_ids();

foreach ($product_ids as $post_id) {

	$trid = apply_filters( 'wpml_element_trid', NULL, $post_id , 'post_page' );
	if($trid){

	    $posted_field_value = get_post_meta($trid, '_Service', true);
	    if( ! empty( $posted_field_value ) )
	        update_post_meta( $post_id, '_Service', esc_attr( $posted_field_value ) );

	    $posted_field_value = get_post_meta($trid, '_service_type', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_service_type', esc_attr( $posted_field_value ) );
	         
	    $posted_field_value = get_post_meta($trid, '_service_parent', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_service_parent', esc_attr( $posted_field_value ) );
	    
	    $posted_field_value = get_post_meta($trid, '_link_label', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_link_label', __( $posted_field_value ) );
	         
	    $posted_field_value = get_post_meta($trid, '_service_holder', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_service_holder', esc_attr( $posted_field_value ) );
	    
        $_service_help_text = get_post_meta($trid, '_service_help_text', true);
	    if(! empty( $_service_help_text))
	        update_post_meta( $post_id, '_service_help_text', esc_attr( $_service_help_text ) );

	    $posted_field_value = get_post_meta($trid, '_service_with', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_service_with', esc_attr( $posted_field_value ) );

	    $posted_field_value = get_post_meta($trid, '_service_with_holder', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_service_with_holder', esc_attr( $posted_field_value ) );

	    $posted_field_value = get_post_meta($trid, '_service_without', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_service_without', esc_attr( $posted_field_value ) );

	    $posted_field_value = get_post_meta($trid, '_service_without_holder', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_service_without_holder', esc_attr( $posted_field_value ) );  

     	$posted_field_value = get_post_meta($trid, '_enable_smm', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_enable_smm', esc_attr( $posted_field_value ) );

       	$posted_field_value = get_post_meta($trid, '_link_type', true);
	    if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_link_type', esc_attr( $posted_field_value ) ); 
	    
		$posted_field_value = get_post_meta($trid, '_smm_upsell_text', true);
		if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_smm_upsell_text', esc_attr( $posted_field_value ) );
	    
		$posted_field_value = get_post_meta($trid, '_smm_upsell_help_text', true);
	  	if( ! empty( $posted_field_value ) )
	         update_post_meta( $post_id, '_smm_upsell_help_text', esc_attr( $posted_field_value ) );
	    
		$posted_field_value = get_post_meta($trid, '_smm_upsell_help_text', true);
	    if(! empty( $posted_field_value )){
	         update_post_meta( $post_id, '_smm_upsell_product', esc_attr( $posted_field_value ) );	
	         if(empty($posted_field_value)){
	             update_post_meta( $post_id, '_smm_upsell_text', '' );
	             update_post_meta( $post_id, '_smm_upsell_help_text', '' );
	         }
	    }
	    
        $posted_field_value = get_post_meta($trid, '_smm_upsell2_text', true);
	   if( ! empty( $posted_field_value ) )
	        update_post_meta( $post_id, '_smm_upsell2_text', esc_attr( $posted_field_value ) );
	    
        $posted_field_value = get_post_meta($trid, '_smm_upsell2_help_text', true);
	    if( ! empty( $posted_field_value ) )
	        update_post_meta( $post_id, '_smm_upsell2_help_text', esc_attr( $posted_field_value ) );
	    
        $posted_field_value = get_post_meta($trid, '_smm_upsell_product2', true);
	    if(! empty( $posted_field_value )){
	        update_post_meta( $post_id, '_smm_upsell_product2', esc_attr( $posted_field_value ) );
	        if(empty($posted_field_value)){
	             update_post_meta( $post_id, '_smm_upsell2_text', '' );
	             update_post_meta( $post_id, '_smm_upsell2_help_text', '' );
	        }
	    }
	}

}
