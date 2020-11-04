<?php

/**
 * Plugin Name: NET-30 Terms for Woocommerce
 * Plugin URI: https://github.com/gerrgg/woocommerce-net-30-terms
 * Description: Allows the admin to give users a NET-30 checkout option.
 * Version: 1.1
 * Author: Greg Bastianelli   
 * Author URI: http://gerrg.com/
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Ensure the admin sets up the cheque option.
add_action( 'admin_notices', 'WCNETD_check_for_cheque_option', 100 );

//  Add the Net 30 Input Checkbox to the Edit User screen.
add_action( 'show_user_profile', 'WCNETD_add_net30_metabox', 1 );
add_action( 'edit_user_profile', 'WCNETD_add_net30_metabox', 1 );

// Save the value of the NET 30 checkbox to the users meta
add_action( 'personal_options_update', 'WCNETD_update_user_to_net30_terms', 5 );
add_action( 'edit_user_profile_update', 'WCNETD_update_user_to_net30_terms', 5 );

// Removes the 'NET 30' checkout option if user does not have NET 30 terms enabled.
add_filter( 'woocommerce_available_payment_gateways', 'WCNETD_enable_net30', 999 );

function WCNETD_check_for_cheque_option( ){
    /**
     * Provides the admin with a warning if the cheque payment option is not enabled.
     */
    $available_payment_methods = WC()->payment_gateways->get_available_payment_gateways();

    if( ! isset( $available_payment_methods['cheque'] ) ){
        $class = 'notice notice-warning is-dismissible';
        $message = __( '<b>Woocommerce NET 30 Terms</b> requires the cheque option be enabled. <a href="'. admin_url( 'admin.php?page=wc-settings&tab=checkout&section=cheque' ) .'">Enable cheque here</a> or <i>NET 30 TERMS</i> wont work.', 'WCNETD' );
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
    }
}

function WCNETD_add_net30_metabox($user){
    /**
     * Add a checkbox that will indicate whether or not a user is on NET 30 Terms.
     */
    $is_net30 = get_user_meta( $user->ID, '_has_net_30_terms', true ); ?>

    <h1><?php esc_html_e( 'Activate NET 30 TERMS', 'WCNETD' ) ?></h1>

    <table class="table" style="background-color: red; color: #fff;">
            <tr>
                <th><label for="has_net_30_terms"><?php esc_html_e( 'Activate NET 30', 'WCNETD' ); ?></label></th>
                <td><input type="checkbox" id="has_net_30_terms" name="_has_net_30_terms" value="1" <?php if ( $is_net30 ) echo ' checked="checked"'; ?> /></td>
            </tr>
        </table>
    <?php
}

function WCNETD_enable_net30( $available_gateways ){
    /**
     * If the user does not have NET 30 terms enabled, remove the cheque payment option
     * @param array $available_gateways - A list of payments methods set at /wp-admin/admin.php?page=wc-settings&tab=checkout
     * @return array
     */
    $user = wp_get_current_user();
    $is_net30 = get_user_meta( $user->ID, '_has_net_30_terms', true );

    if( isset( $available_gateways['cheque'] ) && ! $is_net30 ){
        unset( $available_gateways['cheque'] );
    }

    return $available_gateways;
}

function WCNETD_update_user_to_net30_terms( $user_id ){
    /**
     * @param int $user_id
     * Save $_POST['_has_net_30_terms'] to user meta
     */
    if( current_user_can( 'edit_user', $user_id ) ){

        update_user_meta( 
            $user_id, 
            '_has_net_30_terms', 
            sanitize_text_field($_POST['_has_net_30_terms']) 
        );
    }
}