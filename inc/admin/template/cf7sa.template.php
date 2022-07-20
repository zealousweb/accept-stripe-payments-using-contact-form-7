<?php
	$post_id = ( isset( $_REQUEST[ 'post' ] ) ? sanitize_text_field( $_REQUEST[ 'post' ] ) : '' );

	if ( empty( $post_id ) ) {
		$wpcf7 = WPCF7_ContactForm::get_current();
		$post_id = $wpcf7->id();
	}

	if ( !function_exists( 'cf7sa_inlineScript_select2' ) ) {
		function cf7sa_inlineScript_select2() {
			ob_start();
			?>
			( function($) {
				jQuery('#cf7sa_currency, #cf7sa_success_returnurl, #cf7sa_cancel_returnurl' ).select2();
			} )( jQuery );
			<?php
			return ob_get_clean();
		}
	}

	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wp-pointer' );

	wp_enqueue_style( 'select2' );
	wp_enqueue_script( 'select2' );
	wp_add_inline_script( 'select2', cf7sa_inlineScript_select2() );

	wp_enqueue_style( CF7SA_PREFIX . '_admin_css' );

	$use_stripe              = get_post_meta( $post_id, CF7SA_META_PREFIX . 'use_stripe', true );
	$debug_stripe            = get_post_meta( $post_id, CF7SA_META_PREFIX . 'debug', true );
	$enable_test_mode        = get_post_meta( $post_id, CF7SA_META_PREFIX . 'enable_test_mode', true );
	$test_publishable_key    = get_post_meta( $post_id, CF7SA_META_PREFIX . 'test_publishable_key', true );
	$test_secret_key         = get_post_meta( $post_id, CF7SA_META_PREFIX . 'test_secret_key', true );
	$live_publishable_key    = get_post_meta( $post_id, CF7SA_META_PREFIX . 'live_publishable_key', true );
	$live_secret_key         = get_post_meta( $post_id, CF7SA_META_PREFIX . 'live_secret_key', true );
	$amount                  = get_post_meta( $post_id, CF7SA_META_PREFIX . 'amount', true );
	$quantity                = get_post_meta( $post_id, CF7SA_META_PREFIX . 'quantity', true );
	$email                   = get_post_meta( $post_id, CF7SA_META_PREFIX . 'email', true );
	$description             = get_post_meta( $post_id, CF7SA_META_PREFIX . 'description', true );
	$success_returnURL       = get_post_meta( $post_id, CF7SA_META_PREFIX . 'success_returnurl', true );
	$cancel_returnURL        = get_post_meta( $post_id, CF7SA_META_PREFIX . 'cancel_returnurl', true );
	$message                 = get_post_meta( $post_id, CF7SA_META_PREFIX . 'message', true );
	$currency                = get_post_meta( $post_id, CF7SA_META_PREFIX . 'currency', true );
	$customer_details        = get_post_meta( $post_id, CF7SA_META_PREFIX . 'customer_details', true );
	$first_name              = get_post_meta( $post_id, CF7SA_META_PREFIX . 'first_name', true );
	$last_name               = get_post_meta( $post_id, CF7SA_META_PREFIX . 'last_name', true );
	$company_name            = get_post_meta( $post_id, CF7SA_META_PREFIX . 'company_name', true );
	$address                 = get_post_meta( $post_id, CF7SA_META_PREFIX . 'address', true );
	$city                    = get_post_meta( $post_id, CF7SA_META_PREFIX . 'city', true );
	$state                   = get_post_meta( $post_id, CF7SA_META_PREFIX . 'state', true );
	$zip_code                = get_post_meta( $post_id, CF7SA_META_PREFIX . 'zip_code', true );
	$country                 = get_post_meta( $post_id, CF7SA_META_PREFIX . 'country', true );


	$currency_code = array(
		'AUD' => 'Australian Dollar',
		'BRL' => 'Brazilian Real',
		'CAD' => 'Canadian Dollar',
		'CHF' => 'Swiss Franc',
		'DKK' => 'Danish Krone',
		'EUR' => 'Euro',
		'GBP' => 'Pound Sterling',
		'HKD' => 'Hong Kong Dollar',
		'INR' => 'Indian Rupee',
		'JPY' => 'Japanese Yen',
		'MXN' => 'Mexican Peso',
		'MYR' => 'Malaysian Ringgit',
		'NOK' => 'Norwegian Krone',
		'NZD' => 'New Zealand Dollar',
		'SEK' => 'Swedish Krona',
		'SGD' => 'Singapore Dollar',
		'USD' => 'U.S. Dollar'
	);

	$selected = '';


	$args = array(
			'post_type'      => array( 'page' ),
			'orderby'        => 'title',
			'posts_per_page' => -1
	);

	$pages = get_posts( $args );
	$all_pages = array();

	if ( !empty( $pages ) ) {
		foreach ( $pages as $page ) {
			$all_pages[$page->ID] = $page->post_title;
		}
	}

	echo '<div class="cf7sa-settings">' .
		'<div class="left-box postbox">' .
			'<input style="display: none;" id="' . CF7SA_META_PREFIX . 'customer_details" name="' . CF7SA_META_PREFIX . 'customer_details" type="checkbox" value="1" ' . checked( $customer_details, 1, false ) . ' />' .
			'<table class="form-table">' .
				'<tbody>' .
					'<tr class="form-field">' .
						'<th scope="row">' .
							'<label for="' . CF7SA_META_PREFIX . 'use_stripe">' .
								__( 'Enable Stripe Payment Form', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'use_stripe" name="' . CF7SA_META_PREFIX . 'use_stripe" type="checkbox" class="enable_required" value="1" ' . checked( $use_stripe, 1, false ) . '/>' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th scope="row">' .
							'<label for="' . CF7SA_META_PREFIX . 'debug">' .
								__( 'Enable Debug Mode', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'debug" name="' . CF7SA_META_PREFIX . 'debug" type="checkbox" value="1" ' . checked( $debug_stripe, 1, false ) . '/>' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th scope="row">' .
							'<label for="' . CF7SA_META_PREFIX . 'enable_test_mode">' .
								__( 'Enable Test Mode', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'enable_test_mode" name="' . CF7SA_META_PREFIX . 'enable_test_mode" type="checkbox" class="enable_required" value="1" ' . checked( $enable_test_mode, 1, false ) . '/>' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'test_publishable_key">' .
								__( 'Test Publishable key (Required)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
							'<span class="cf7sa-tooltip hide-if-no-js" id="cf7sa-test-publishable-key"></span>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'test_publishable_key" name="' . CF7SA_META_PREFIX . 'test_publishable_key" type="text" class="large-text" value="' . esc_attr( $test_publishable_key ) . '" ' . ( ( !empty( $enable_test_mode ) && !empty( $use_stripe ) ) ? 'required' : '' ) . ' />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'test_secret_key">' .
								__( 'Test Secret key (Required)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
							'<span class="cf7sa-tooltip hide-if-no-js" id="cf7sa-test-secret-key"></span>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'test_secret_key" name="' . CF7SA_META_PREFIX . 'test_secret_key" type="text" class="large-text" value="' . esc_attr( $test_secret_key ) . '" ' . ( ( !empty( $enable_test_mode ) && !empty( $use_stripe ) ) ? 'required' : '' ) . ' />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'live_publishable_key">' .
								__( 'Live Publishable key (Required)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
							'<span class="cf7sa-tooltip hide-if-no-js" id="cf7sa-live-publishable-key"></span>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'live_publishable_key" name="' . CF7SA_META_PREFIX . 'live_publishable_key" type="text" class="large-text" value="' . esc_attr( $live_publishable_key ) . '" ' . ( ( empty( $enable_test_mode ) && !empty( $use_stripe ) ) ? 'required' : '' ) . ' />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'live_secret_key">' .
								__( 'Live Secret key (Required)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
							'<span class="cf7sa-tooltip hide-if-no-js" id="cf7sa-live-secret-key"></span>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'live_secret_key" name="' . CF7SA_META_PREFIX . 'live_secret_key" type="text" class="large-text" value="' . esc_attr( $live_secret_key ) . '" ' . ( ( empty( $enable_test_mode ) && !empty( $use_stripe ) ) ? 'required' : '' ) . ' />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'email">' .
								__( 'Customer Email Field Name (Required)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'email" name="' . CF7SA_META_PREFIX . 'email" type="text" value="' . esc_attr( $email ) . '" ' . ( !empty( $email ) ? 'required' : '' ) . ' />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'amount">' .
								__( 'Amount Field Name (Required)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
							'<span class="cf7sa-tooltip hide-if-no-js" id="cf7sa-amount-field"></span>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'amount" name="' . CF7SA_META_PREFIX . 'amount" type="text" value="' . esc_attr( $amount ) . '" ' . ( !empty( $use_stripe ) ? 'required' : '' ) . ' />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'quantity">' .
								__( 'Quantity Field Name (Optional)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'quantity" name="' . CF7SA_META_PREFIX . 'quantity" type="text" value="' . esc_attr( $quantity ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'description">' .
								__( 'Description Field Name (Optional)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'description" name="' . CF7SA_META_PREFIX . 'description" type="text" value="' . esc_attr( $description ) . '" />' .
						'</td>' .
					'</tr>' .
						'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'currency">' .
								__( 'Select Currency', 'contact-form-7-stripe-addon' ) .
							'</label>' .
							'<span class="cf7sa-tooltip hide-if-no-js" id="cf7sa-select-currency"></span>' .
						'</th>' .
						'<td>' .
							'<select id="' . CF7SA_META_PREFIX . 'currency" name="' . CF7SA_META_PREFIX . 'currency">';

								if ( !empty( $currency_code ) ) {
									foreach ( $currency_code as $key => $value ) {
										echo '<option value="' . esc_attr( $key ) . '" ' . selected( $currency, $key, false ) . '>' . esc_attr( $value ) . '</option>';
									}
								}

							echo '</select>' .
						'</td>' .
					'</tr/>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'success_returnurl">' .
								__( 'Success Return URL (Optional)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<select id="' . CF7SA_META_PREFIX . 'success_returnurl" name="' . CF7SA_META_PREFIX . 'success_returnurl">' .
								'<option>' . __( 'Select page', 'contact-form-7-stripe-addon' ) . '</option>';

								if( !empty( $all_pages ) ) {
									foreach ( $all_pages as $page_id => $title ) {
										echo '<option value="' . esc_attr( $page_id ) . '" ' . selected( $success_returnURL, $page_id, false )  . '>' . $title . '</option>';
									}
								}

							echo '</select>' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'cancel_returnurl">' .
								__( 'Cancel Return URL (Optional)', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<select id="' . CF7SA_META_PREFIX . 'cancel_returnurl" name="' . CF7SA_META_PREFIX . 'cancel_returnurl">' .
								'<option>' . __( 'Select page', 'contact-form-7-stripe-addon' ) . '</option>';

								if( !empty( $all_pages ) ) {
									foreach ( $all_pages as $page_id => $title ) {
										echo '<option value="' . esc_attr( $page_id ) . '" ' . selected( $cancel_returnURL, $page_id, false )  . '>' . $title . '</option>';
									}
								}

							echo '</select>' .
						'</td>' .
					'</tr>';

					/**
					 * - Add new field at the middle.
					 *
					 * @var int $post_id
					 */
					do_action(  CF7SA_PREFIX . '/add/fields/middle', $post_id );

					echo '<tr class="form-field">' .
						'<th colspan="2">' .
							'<label for="' . CF7SA_META_PREFIX . 'customer_details">' .
								'<h3 style="margin: 0;">' .
									__( 'Customer Details', 'contact-form-7-stripe-addon' ) .
									'<span class="arrow-switch"></span>' .
								'</h3>' .
							'</label>' .
						'</th>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'first_name">' .
								__( 'First Name', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'first_name" name="' . CF7SA_META_PREFIX . 'first_name" type="text" class="regular-text" value="' . esc_attr( $first_name ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'last_name">' .
								__( 'Last Name', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'last_name" name="' . CF7SA_META_PREFIX . 'last_name" type="text" class="regular-text" value="' . esc_attr( $last_name ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'company_name">' .
								__( 'Company Name', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'company_name" name="' . CF7SA_META_PREFIX . 'company_name" type="text" class="regular-text" value="' . esc_attr( $company_name ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'address">' .
								__( 'Address', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'address" name="' . CF7SA_META_PREFIX . 'address" type="text" class="regular-text" value="' . esc_attr( $address ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'city">' .
								__( 'City', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'city" name="' . CF7SA_META_PREFIX . 'city" type="text" class="regular-text" value="' . esc_attr( $city ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'state">' .
								__( 'State', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'state" name="' . CF7SA_META_PREFIX . 'state" type="text" class="regular-text" value="' . esc_attr( $state ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'zip_code">' .
								__( 'Zip Code', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'zip_code" name="' . CF7SA_META_PREFIX . 'zip_code" type="text" class="regular-text" value="' . esc_attr( $zip_code ) . '" />' .
						'</td>' .
					'</tr>' .
					'<tr class="form-field hide-show">' .
						'<th>' .
							'<label for="' . CF7SA_META_PREFIX . 'country">' .
								__( 'Country', 'contact-form-7-stripe-addon' ) .
							'</label>' .
						'</th>' .
						'<td>' .
							'<input id="' . CF7SA_META_PREFIX . 'country" name="' . CF7SA_META_PREFIX . 'country" type="text" class="regular-text" value="' . esc_attr( $country ) . '" />' .
						'</td>' .
					'</tr>';

					/**
					 * - Add new field at the end.
					 *
					 * @var int $post_id
					 */
					do_action(  CF7SA_PREFIX . '/add/fields/end', $post_id );

					echo '<input type="hidden" name="post" value="' . esc_attr( $post_id ) . '">' .
				'</tbody>' .
			'</table>' .
		'</div>' .
		'<div class="right-box">';

		/**
		 * Add new post box to display the information.
		 */
		do_action( CF7SA_PREFIX . '/postbox' );


		echo '</div>' .
	'</div>';

	add_action('admin_print_footer_scripts', function() {
		ob_start();
		?>
			<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					//jQuery selector to point to
					jQuery( '#cf7sa-test-publishable-key' ).on( 'hover click', function() {
						jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
						jQuery( '#cf7sa-test-publishable-key' ).pointer({
							pointerClass: 'wp-pointer cf7sa-pointer',
							content: '<?php
					_e( '<h3>Get Your Publishable Key</h3>' .
					'<p>Get it from <a href="https://dashboard.stripe.com/" target="_blank"> Stripe</a> then <strong> Developers > API Keys </strong> page  in your Stripe account.</p>',
					'contact-form-7-stripe-addon'
					); ?>',
							position: 'left center',
						} ).pointer('open');
					} );

					jQuery( '#cf7sa-live-publishable-key' ).on( 'hover click', function() {
						jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
						jQuery( '#cf7sa-live-publishable-key' ).pointer({
							pointerClass: 'wp-pointer cf7sa-pointer',
							content: '<?php
					_e( '<h3>Get Your Publishable Key</h3>' .
					'<p>Get it from <a href="https://dashboard.stripe.com/" target="_blank"> Stripe</a> then <strong> Developers > API Keys </strong> page  in your Stripe account.</p>',
					'contact-form-7-stripe-addon'
					); ?>',
							position: 'left center',
						} ).pointer('open');
					} );

					jQuery( '#cf7sa-test-secret-key' ).on( 'hover click', function() {
						jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
						jQuery( '#cf7sa-test-secret-key' ).pointer({
							pointerClass: 'wp-pointer cf7sa-pointer',
							content: '<?php
					_e( '<h3>Get Your Secret Key</h3>' .
					'<p>Get it from <a href="https://dashboard.stripe.com/" target="_blank"> Stripe</a> then <strong> Developers > API Keys </strong> page  in your Stripe account.</p>',
					'contact-form-7-stripe-addon'
					); ?>',
							position: 'left center',
						} ).pointer('open');
					} );



					jQuery( '#cf7sa-amount-field' ).on( 'hover click', function() {
						jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
						jQuery( '#cf7sa-amount-field' ).pointer({
							pointerClass: 'wp-pointer cf7sa-pointer',
							content: '<?php
					_e( '<h3>Add Amount Name</h3>' .
					'<p>Add here the Name of amount field</p>',
					'contact-form-7-stripe-addon'
					); ?>',
							position: 'left center',
						} ).pointer('open');
					} );

					jQuery( '#cf7sa-select-currency' ).on( 'hover click', function() {
						jQuery( 'body .wp-pointer-buttons .close' ).trigger( 'click' );
						jQuery( '#cf7sa-select-currency' ).pointer({
							pointerClass: 'wp-pointer cf7sa-pointer',
							content: '<?php
					_e( '<h3>Select Currency</h3>' .
					'<p>Select the currency which is selected from your stripe.net merchant account.<br/><strong>Note:</strong>Stripe dont provide multiple currencies for single account</p>',
					'contact-form-7-stripe-addon'
					); ?>',
							position: 'left center',
						} ).pointer('open');
					} );
				} );
				//]]>
			</script>
		<?php
		echo ob_get_clean();
	} );
