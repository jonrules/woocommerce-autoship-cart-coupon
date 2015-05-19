<?php
/*
Plugin Name: WC Autoship Cart Coupon
Plugin URI: http://wooautoship.com
Description: Apply a coupon to a shopping cart containing autoship items.
Version: 1.0
Author: Patterns in the Cloud
Author URI: http://patternsinthecloud.com
License: Single-site
*/

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce-autoship/woocommerce-autoship.php' ) ) {
	
	function wc_autoship_cart_coupon_install() {

	}
	register_activation_hook( __FILE__, 'wc_autoship_cart_coupon_install' );
	
	function wc_autoship_cart_coupon_deactivate() {
	
	}
	register_deactivation_hook( __FILE__, 'wc_autoship_cart_coupon_deactivate' );
	
	function wc_autoship_cart_coupon_uninstall() {

	}
	register_uninstall_hook( __FILE__, 'wc_autoship_cart_coupon_uninstall' );
	
	/**
	 * Add autoship coupon data tabs
	 * @param array $tabs
	 * @return array
	 */
	function wc_autoship_cart_coupon_coupon_data_tabs( $tabs ) {
		$tabs['wc_autoship'] = array(
			'label'  => __( 'Auto-Ship', 'wc-autoship' ),
			'target' => 'wc_autoship_coupon_data',
			'class'  => 'wc_autoship_coupon_data'
		);
		return $tabs;
	}
	add_filter( 'woocommerce_coupon_data_tabs', 'wc_autoship_cart_coupon_coupon_data_tabs', 100, 1 );
	
	/**
	 * Print autoship coupon data panels
	 */
	function wc_autoship_cart_coupon_coupon_data_panels() {
		?><div id="wc_autoship_coupon_data" class="panel woocommerce_options_panel"><?php

			echo '<div class="options_group">';
			
			woocommerce_wp_checkbox( array(
				'id' => 'wc_autoship_enabled',
				'label' => __( 'Enable for Autoship', 'wc-autoship' ),
				'description' => __( 'Enable this coupon when autoship items are added to the cart.', 'wc-autoship' ),
			) );

			// Usage limit per coupons
			woocommerce_wp_text_input( array( 
				'id' => 'wc_autoship_min_item_quantity', 
				'label' => __( 'Minimum Quantity', 'wc-autoship' ), 
				'description' => __( 'The minimum number of autoship items required in the shopping cart.', 'wc-autoship' ), 
				'type' => 'number', 
				'desc_tip' => true, 
				'class' => 'short', 
				'custom_attributes' => array(
					'step' 	=> '1',
					'min'	=> '1'
				)
			) );
			
			woocommerce_wp_checkbox( array(
				'id' => 'wc_autoship_apply_automatically',
				'label' => __( 'Apply Automatically', 'wc-autoship' ),
				'description' => __( 'Apply this coupon to the cart automatically.', 'wc-autoship' ),
			) );

			echo '</div>';

		?></div><?php 
	}
	add_action( 'woocommerce_coupon_data_panels', 'wc_autoship_cart_coupon_coupon_data_panels' );
	
	/**
	 * Hook into cart totals
	 */
	function wc_autoship_cart_coupon_totals() {
		$woocommerce = WC();
		
		// Get coupons
		$coupons_query_args = array(
			'post_type' => 'shop_coupon',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key' => 'wc_autoship_enabled',
					'value'   => 'yes',
					'compare' => '='
				)
			)
		);
		$coupons_result = get_posts( $coupons_query_args );
		
		foreach ( $coupons_result as $post ) {
			$apply_automatically = get_post_meta( $post->ID, 'wc_autoship_apply_automatically', true );
			if ( $apply_automatically == 'yes' ) {
				$min_item_quantity = (int) get_post_meta( $post->ID, 'wc_autoship_min_item_quantity', true );
				if ( wc_autoship_cart_coupon_check_item_quantity( $min_item_quantity ) ) {
					if ( ! $woocommerce->cart->has_discount( $post->post_title ) ) {
						$woocommerce->cart->add_discount( $post->post_title );
					}
				}
			}
		}
	}
	add_action( 'woocommerce_calculate_totals', 'wc_autoship_cart_coupon_totals' );
	
	/**
	 * Check if coupon is valid
	 * @param boolean $valid
	 * @param WC_Coupon $coupon
	 * @return boolean
	 */
	function wc_autoship_cart_coupon_coupon_is_valid( $valid, $coupon ) {
		$autoship_enabled = get_post_meta( $coupon->id, 'wc_autoship_enabled', true );
		if ( $autoship_enabled == 'yes' ) {
			$min_item_quantity = (int) get_post_meta( $coupon->id, 'wc_autoship_min_item_quantity', true );
			return wc_autoship_cart_coupon_check_item_quantity( $min_item_quantity );
		}
	
		// Return default
		return $valid;
	}
	add_filter( 'woocommerce_coupon_is_valid', 'wc_autoship_cart_coupon_coupon_is_valid', 10, 2 );

	/**
	 * Save custom fields for autoship coupons
	 * @param int $post_id
	 */
	function wc_autoship_cart_coupon_save_custom_fields( $post_id ) {
		$autoship_field_names = array(
			'wc_autoship_enabled',
			'wc_autoship_min_item_quantity',
			'wc_autoship_apply_automatically'
		);
		foreach ( $autoship_field_names as $name ) {
			$value = isset( $_POST[ $name ] ) ? $_POST[ $name ] : '';
			update_post_meta( $post_id, $name, $value );
		}
	}
	add_action( 'woocommerce_process_shop_coupon_meta', 'wc_autoship_cart_coupon_save_custom_fields', 10, 1 );
	
	/**
	 * Check if the cart has the minimum quantity of autoship items
	 * @param int $min_item_quantity
	 * @return boolean
	 */
	function wc_autoship_cart_coupon_check_item_quantity( $min_item_quantity ) {
		$woocommerce = WC();
		$cart_items = $woocommerce->cart->get_cart();
		$item_count = 0;
		$autoship_item_count = 0;
		foreach ( $cart_items as $values ) {
			if ( $values['data'] instanceof WC_Product ) {
				$item_count += $values['quantity'];
				if ( isset( $values['wc_autoship_frequency'] ) ) {
					$autoship_item_count += $values['quantity'];
					if ( $autoship_item_count >= $min_item_quantity ) {
						return true;
					}
				}
			}
		}
		return false;
	}
}
