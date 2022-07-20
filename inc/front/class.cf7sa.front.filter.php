<?php
/**
 * CF7SA_Front_Filter Class
 *
 * Handles the Frontend Filters.
 *
 * @package WordPress
 * @subpackage Contact Form 7 - Stripe Add-on
 * @since 1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'CF7SA_Front_Filter' ) ) {

	/**
	 *  The CF7SA_Front_Filter Class
	 */
	class CF7SA_Front_Filter {

		function __construct() {

			/**
			 * Wrap form
			 */
			add_filter( 'wpcf7_form_class_attr', array( $this, 'filter__wpcf7_form_class_attr' ), 10 );

		}

		/*
		######## #### ##       ######## ######## ########   ######
		##        ##  ##          ##    ##       ##     ## ##    ##
		##        ##  ##          ##    ##       ##     ## ##
		######    ##  ##          ##    ######   ########   ######
		##        ##  ##          ##    ##       ##   ##         ##
		##        ##  ##          ##    ##       ##    ##  ##    ##
		##       #### ########    ##    ######## ##     ##  ######
		*/

		function filter__wpcf7_form_class_attr( $classes ) {

			$form =  WPCF7_ContactForm::get_current();
			$form_id = $form->id();

			if ( !empty( $form_id )
				&& !empty( get_post_meta( $form_id, CF7SA_META_PREFIX . 'use_stripe', true ) )
			) {
				return $classes . ' cf7sa';
			}

			return $classes;
		}

		/*
		######## ##     ## ##    ##  ######  ######## ####  #######  ##    ##  ######
		##       ##     ## ###   ## ##    ##    ##     ##  ##     ## ###   ## ##    ##
		##       ##     ## ####  ## ##          ##     ##  ##     ## ####  ## ##
		######   ##     ## ## ## ## ##          ##     ##  ##     ## ## ## ##  ######
		##       ##     ## ##  #### ##          ##     ##  ##     ## ##  ####       ##
		##       ##     ## ##   ### ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##        #######  ##    ##  ######     ##    ####  #######  ##    ##  ######
		*/

	}
}
