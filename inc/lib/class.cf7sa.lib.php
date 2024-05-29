<?php
/**
 * CF7SA_Lib Class
 *
 * Handles the Library functionality.
 *
 * @package WordPress
 * @subpackage Contact Form 7 - Stripe Add-on
 * @since 1.2
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

// Include Stripe PHP library
require_once( CF7SA_DIR . '/inc/lib/init.php' );

if ( !class_exists( 'CF7SA_Lib' ) ) {

	class CF7SA_Lib {

		private $lib_version = '6.40.0[9c22ffa]';

		var $data_fields = array(
			'_form_id'              => 'Form ID/Name',
			'_email'                => 'Email Address',
			'_transaction_id'       => 'Transaction ID',
			'_invoice_no'           => 'Invoice ID',
			'_amount'               => 'Amount',
			'_quantity'             => 'Quantity',
			'_total'                => 'Total',
			'_submit_time'          => 'Submit Time',
			'_request_Ip'           => 'Request IP',
			'_currency'             => 'Currency code',
			'_form_data'            => 'Form data',
			'_transaction_response' => 'Transaction response',
			'_transaction_status'   => 'Transaction status',
		);

		function __construct() {
			add_action( 'init', array( $this, 'action__init' ) );
			add_action( 'wpcf7_init', array( $this, 'action__wpcf7_init' ), 10, 0 );
			add_action( 'wpcf7_save_contact_form', array( $this, 'action__wpcf7_save_contact_form' ), 999, 3 );
			add_action( 'wpcf7_before_send_mail', array( $this, 'action__wpcf7_before_send_mail' ), 20, 3 );
			add_shortcode( 'stripe-details', array( $this, 'shortcode__stripe_details' ) );
			add_filter( 'wpcf7_ajax_json_echo', array( $this, 'filter__wpcf7_ajax_json_echo' ), 20, 2 );
			add_action( 'wp_ajax_get_stripe_intent', array( $this, 'action__get_stripe_intent' ) );
			add_action( 'wp_ajax_nopriv_get_stripe_intent', array( $this, 'action__get_stripe_intent' ) );

		}

		/*
		   ###     ######  ######## ####  #######  ##    ##  ######
		  ## ##   ##    ##    ##     ##  ##     ## ###   ## ##    ##
		 ##   ##  ##          ##     ##  ##     ## ####  ## ##
		##     ## ##          ##     ##  ##     ## ## ## ##  ######
		######### ##          ##     ##  ##     ## ##  ####       ##
		##     ## ##    ##    ##     ##  ##     ## ##   ### ##    ##
		##     ##  ######     ##    ####  #######  ##    ##  ######
		*/

		/**
		 * Action: init
		 *
		 * - Start session to store the data into session.
		 *
		 * @method action__init
		 *
		 */
		function action__init() {

			if ( !isset( $_SESSION ) || session_status() == PHP_SESSION_NONE ) {
				session_start();
			}
		}

		/**
		 * Action: wpcf7_init
		 *
		 * - Added new form tag and render the form into frontend with validation.
		 *
		 * @method action__wpcf7_init
		 *
		 */
		function action__wpcf7_init() {
			wpcf7_add_form_tag( array( 'stripe', 'stripe*' ), array( $this, 'wpcf7_add_form_tag_stripe_net' ), array( 'name-attr' => true ) );

			add_filter( 'wpcf7_validate_stripe',  array( $this, 'wpcf7_stripe_validation_filter' ), 10, 2 );
			add_filter( 'wpcf7_validate_stripe*', array( $this, 'wpcf7_stripe_validation_filter' ), 10, 2 );
		}

		function action__get_stripe_intent(){

			require_once( CF7SA_DIR . '/inc/lib/init.php' );

			if ( $_POST ) {
				// CF7 posted data
				$posted_data = $_POST;
			}

			$form_ID = $_POST['_wpcf7'];

			if ( !empty( $form_ID ) ) {

					$form_instance = WPCF7_ContactForm::get_instance( $form_ID );
					$wpcf7_submission = $_POST; // CF7 Submission Instance

					$use_stripe = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'use_stripe', true );

					if ( empty( $use_stripe ) )
						return;

					$enable_test_mode     = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'enable_test_mode', true );
					$test_publishable_key = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'test_publishable_key', true );
					$test_secret_key      = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'test_secret_key', true );
					$live_publishable_key = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'live_publishable_key', true );
					$live_secret_key      = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'live_secret_key', true );
					$amount               = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'amount', true );
					$quantity             = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'quantity', true );
					$email                = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'email', true );
					$description          = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'description', true );

					//get Client Details
					$first_name		= get_post_meta( $form_ID, CF7SA_META_PREFIX . 'first_name', true );
					$last_name      = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'last_name', true );
					$company_name   = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'company_name', true );
					$address        = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'address', true );
					$city           = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'city', true );
					$state          = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'state', true );
					$zip_code       = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'zip_code', true );
					$country        = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'country', true );

					// Set some example data for the payment.
					$currency             = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'currency', true );
					$customer_details     = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'customer_details', true );

					$first_name  = ( ( !empty( $first_name ) && array_key_exists( $first_name, $posted_data ) ) ? $posted_data[$first_name] : '' );
					$email       = ( ( !empty( $email ) && array_key_exists( $email, $posted_data ) ) ? $posted_data[$email] : '' );
					$description = ( ( !empty( $description ) && array_key_exists( $description, $posted_data ) ) ? $posted_data[$description] : get_bloginfo( 'name' ) );
					
					if( isset( $posted_data[$amount.'_free_text'] ) ) {
						$amount_val = $posted_data[$amount.'_free_text'];
					} else {
						$amount_val  = ( ( !empty( $amount ) && array_key_exists( $amount, $posted_data ) ) ? floatval( $posted_data[$amount] ) : '0' );
						//Remove special Character from amount / price field
						$amount_val = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $posted_data[$amount]);
					}

					$quanity_val = ( ( !empty( $quantity ) && array_key_exists( $quantity, $posted_data ) ) ? floatval( $posted_data[$quantity] ) : '' );

					if (
						!empty( $amount )
						&& array_key_exists( $amount, $posted_data )
						&& is_array( $posted_data[$amount] )
						&& !empty( $posted_data[$amount] )
					) {
						$val = 0;
						foreach ( $posted_data[$amount] as $k => $value ) {
							$val = $val + floatval($value);
						}
						$amount_val = $val;
					}

					if (
						!empty( $quantity )
						&& array_key_exists( $quantity, $posted_data )
						&& is_array( $posted_data[$quantity] )
						&& !empty( $posted_data[$quantity] )
					) {
						$qty_val = 0;
						foreach ( $posted_data[$quantity] as $k => $qty ) {
							$qty_val = $qty_val + floatval($qty);
						}
						$quanity_val = $qty_val;
					}

					$amountPayable = (float) ( empty( $quanity_val ) ? $amount_val : ( $quanity_val * $amount_val ) );

					if ( empty( $amountPayable ) ) {
						add_filter( 'wpcf7_skip_mail', array( $this, 'filter__wpcf7_skip_mail' ), 20 );
						$_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $form_ID ] = __( 'Empty Amount field or Invalid configuration.', CF7SA_PREFIX );
						return;
					}

					if ( $amountPayable < 0 && $amountPayable != 0 )  {
						add_filter( 'wpcf7_skip_mail', array( $this, 'filter__wpcf7_skip_mail' ), 20 );
						$_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $form_ID ] = __( 'Please enter the valid amount.', CF7SA_PREFIX );
						return;
					}

					$amountPayable = sprintf('%0.2f', $amountPayable) * 100;

					$secret_key = ( !empty( $enable_test_mode ) ? $test_secret_key : $live_secret_key );

					\Stripe\Stripe::setApiKey( $secret_key );

					try {
						// Use Stripe's library to make requests...
						$clients = \Stripe\Customer::all([ 'limit' => 10000, 'email' => $email ]);

						if ( !empty( $clients )	&& !empty( $clients->data ) ) {
							$customer = $clients->data[0];
						} else {

							$customer = array(
								'name' => $first_name,
								'description' => $description,
								'email' => $email,
								'source' => $token,
								"address" => [
									"city" => $city,
									"country" => $country,
									"line1" => $address,
									"line2" => "",
									"postal_code" => $zip_code,
									"state" => $state
								]
							);

							$customer = apply_filters( CF7SA_PREFIX . '/stripe/customer', $customer );

							// Create a Customer
							$customer = \Stripe\Customer::create( $customer );

						}

						if( !empty( $description ) ) {
							$posted_data['description'] = $description;
						}

						if( !empty( $customer_details ) ) {

							$posted_data['metadata'] = array();

							if (
								!empty( $first_name )
								and $first_name_data = ( ( !empty( $first_name ) && array_key_exists( $first_name, $posted_data ) ) ? $posted_data[$first_name] : '' )
							)
								$posted_data['metadata']['first_name'] = $first_name_data;

							if (
								!empty( $last_name )
								and $last_name_data = ( ( !empty( $last_name ) && array_key_exists( $last_name, $posted_data ) ) ? $posted_data[$last_name] : '' )
							)
								$posted_data['metadata']['last_name'] = $last_name_data;

							if (
								!empty( $company_name )
								and $company_name_data = ( ( !empty( $company_name ) && array_key_exists( $company_name, $posted_data ) ) ? $posted_data[$company_name] : '' )
							)
								$posted_data['metadata']['company_name'] = $company_name_data;

							if (
								!empty( $address )
								and $address_data = ( ( !empty( $address ) && array_key_exists( $address, $posted_data ) ) ? $posted_data[$address] : '' )
							)
								$posted_data['metadata']['address'] = $address_data;

							if (
								!empty( $city )
								and $city_data = ( ( !empty( $city ) && array_key_exists( $city, $posted_data ) ) ? $posted_data[$city] : '' )
							)
								$posted_data['metadata']['city'] = $city_data;

							if (
								!empty( $state )
								and $state_data = ( ( !empty( $state ) && array_key_exists( $state, $posted_data ) ) ? $posted_data[$state] : '' )
							)
								$posted_data['metadata']['state'] = $state_data;

							if (
								!empty( $zip_code )
								and $zip_code_data = ( ( !empty( $zip_code ) && array_key_exists( $zip_code, $posted_data ) ) ? $posted_data[$zip_code] : '' )
							)
								$posted_data['metadata']['zip_code'] = $zip_code_data;

							if (
								!empty( $country )
								and $country_data = ( ( !empty( $country ) && array_key_exists( $country, $posted_data ) ) ? $posted_data[$country] : '' )
							)
								$posted_data['metadata']['country'] = $country_data;

						}

						$posted_data = apply_filters( CF7SA_PREFIX . '/stripe/change', $posted_data );

						// Save the customer id in your own database!
						$paymentIntent = \Stripe\PaymentIntent::create([
							'payment_method_types' => ['card'],
							'amount' => $amountPayable,
							'description'=> $description,
							'currency' => $currency,
							'customer' => $customer->id,
							'metadata' => isset($posted_data['metadata']) ? $posted_data['metadata'] : [],
						]);

						$client_secret = $paymentIntent->client_secret;

					} catch ( Exception $e ) {
						
						//If the paymentIntent fails (payment unsuccessful), this code will get triggered.
						$client_secret = "";
					}
			}

			echo $client_secret;
			die();
		}

		/**
		 * Action: CF7 before send email
		 *
		 * @method action__wpcf7_before_send_mail
		 *
		 * @param  object $contact_form WPCF7_ContactForm::get_instance()
		 * @param  bool   $abort
		 * @param  object $contact_form WPCF7_Submission class
		 *
		 */

		function action__wpcf7_before_send_mail( $contact_form, $abort, $wpcf7_submission ) {

			$submission    = WPCF7_Submission::get_instance(); // CF7 Submission Instance
			$form_ID       = $contact_form->id();
			$form_instance = WPCF7_ContactForm::get_instance( $form_ID ); // CF7 From Instance

			if ( $submission ) {

				if ( $_POST ) {
					// CF7 posted data
					$posted_data = $_POST;
				}

			}

			if ( !empty( $form_ID ) ) {

				$use_stripe = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'use_stripe', true );

				if ( empty( $use_stripe ) )
					return;

				$enable_test_mode     = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'enable_test_mode', true );
				$test_publishable_key = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'test_publishable_key', true );
				$test_secret_key      = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'test_secret_key', true );
				$live_publishable_key = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'live_publishable_key', true );
				$live_secret_key      = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'live_secret_key', true );
				$amount               = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'amount', true );
				$quantity             = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'quantity', true );
				$email                = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'email', true );
				$description          = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'description', true );
				$success_returnURL    = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'success_returnurl', true );
				$cancel_returnURL     = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'cancel_returnurl', true );
				$message              = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'message', true );
				// Set some example data for the payment.
				$currency             = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'currency', true );
				$customer_details     = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'customer_details', true );

				$email       = ( ( !empty( $email ) && array_key_exists( $email, $posted_data ) ) ? $posted_data[$email] : '' );
				$description = ( ( !empty( $description ) && array_key_exists( $description, $posted_data ) ) ? $posted_data[$description] : get_bloginfo( 'name' ) );

				if( isset($posted_data[$amount][0]) ) {
					$posted_data[$amount][0] = str_replace("Other", "", $posted_data[$amount][0]);
					$posted_data[$amount][0] = str_replace(" ", "", $posted_data[$amount][0]);
				}

				$amount_val  = ( ( !empty( $amount ) && array_key_exists( $amount, $posted_data ) ) ? floatval( $posted_data[$amount] ) : '0' );
				//Remove special Character from amount / price field
				$amount_val = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', $posted_data[$amount]);
				$quanity_val = ( ( !empty( $quantity ) && array_key_exists( $quantity, $posted_data ) ) ? floatval( $posted_data[$quantity] ) : '' );


				if (
					!empty( $amount )
					&& array_key_exists( $amount, $posted_data )
					&& is_array( $posted_data[$amount] )
					&& !empty( $posted_data[$amount] )
				) {
					$val = 0;
					foreach ( $posted_data[$amount] as $k => $value ) {
						$val = $val + floatval($value);
					}
					$amount_val = $val;
				}

				if (
					!empty( $quantity )
					&& array_key_exists( $quantity, $posted_data )
					&& is_array( $posted_data[$quantity] )
					&& !empty( $posted_data[$quantity] )
				) {
					$qty_val = 0;
					foreach ( $posted_data[$quantity] as $k => $qty ) {
						$qty_val = $qty_val + floatval($qty);
					}
					$quanity_val = $qty_val;
				}

				$amountPayable = (float) ( empty( $quanity_val ) ? $amount_val : ( $quanity_val * $amount_val ) );

				if ( empty( $amountPayable ) ) {
					add_filter( 'wpcf7_skip_mail', array( $this, 'filter__wpcf7_skip_mail' ), 20 );
					$_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $form_ID ] = __( 'Empty Amount field or Invalid configuration.', CF7SA_PREFIX );
					return;
				}

				if (
					$amountPayable < 0
					&& $amountPayable != 0
				)  {
					add_filter( 'wpcf7_skip_mail', array( $this, 'filter__wpcf7_skip_mail' ), 20 );
					$_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $form_ID ] = __( 'Please enter the valid amount.', CF7SA_PREFIX );
					return;
				}

				$amountPayable = sprintf('%0.2f', $amountPayable) * 100;


				/**
				 * Generate stripe token then only when
				 * Check whether stripe token is not empty and 
				 * Above validation msg is empty for cases like : On entering amount as zero first and then entering amount > 0 
				 */
				if ( empty($_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $form_ID ]) && array_key_exists( 'stripeClientSecret', $posted_data )
					&& !empty( $posted_data['stripeClientSecret'] )
				) {

					// Retrieve stripe token, card and user info from the submitted form data
					$token  = $posted_data['stripeClientSecret'];

					$secret_key = ( !empty( $enable_test_mode ) ? $test_secret_key : $live_secret_key );

					if( empty( $secret_key ) ) {
						// Needs to add validation.
						return;
					}

					\Stripe\Stripe::setApiKey( $secret_key );

					$PaymentIntent = \Stripe\PaymentIntent::retrieve($token);

					$attachent = '';

					if ( !empty( $submission->uploaded_files() ) ) {
						$uploaded_files = $this->zw_cf7_upload_files( $submission->uploaded_files() );

						if ( !empty( $uploaded_files ) ) {
							$attachent = serialize( $uploaded_files );
						}
					}

					$latest_charge = $PaymentIntent->latest_charge;
					$charge = \Stripe\Charge::retrieve($latest_charge, []);

					// Check whether the charge is successful
					if (
						$charge->amount_refunded == 0
						&& empty( $charge->failure_code )
						&& $charge->paid == 1
						&& $charge->captured == 1
					) {

						// Order details
						$invoice_no =  $charge->id;
						$txn_id = $charge->balance_transaction;
						$paidAmount = $charge->amount;
						$paidCurrency = $charge->currency;
						$payment_status = $charge->status;


						$sa_post_id = wp_insert_post( array (
							'post_type' => 'cf7sa_data',
							'post_title' => ( !empty( $email ) ? $email : $invoice_no ), // email/invoice_no
							'post_status' => 'publish',
							'comment_status' => 'closed',
							'ping_status' => 'closed',
						) );

						if ( !empty( $sa_post_id ) ) {

							$stored_data = $posted_data;
							unset( $stored_data['stripeClientSecret'] );

							add_post_meta( $sa_post_id, '_form_id', $form_ID );
							add_post_meta( $sa_post_id, '_email', $email );
							add_post_meta( $sa_post_id, '_transaction_id', $txn_id );
							add_post_meta( $sa_post_id, '_invoice_no', $invoice_no );
							add_post_meta( $sa_post_id, '_amount', $amount_val );
							add_post_meta( $sa_post_id, '_quantity', $quanity_val );
							add_post_meta( $sa_post_id, '_total', ($paidAmount/100) );
							add_post_meta( $sa_post_id, '_request_Ip', $this->getUserIpAddr() );
							add_post_meta( $sa_post_id, '_currency', $paidCurrency );
							add_post_meta( $sa_post_id, '_form_data', serialize( $stored_data ) );
							add_post_meta( $sa_post_id, '_transaction_response', json_encode( $charge ) );
							add_post_meta( $sa_post_id, '_transaction_status', $payment_status );
							add_post_meta( $sa_post_id, '_attachment', $attachent );
						}

						add_filter( 'wpcf7_mail_tag_replaced', function( $replaced, $submitted, $html, $mail_tag ) use ( $txn_id, $payment_status, $invoice_no ) {

							$corresponding_form_tag = $mail_tag->corresponding_form_tag();
							if (is_object($corresponding_form_tag) && property_exists($corresponding_form_tag, 'basetype')) {
								if ('stripe' == $corresponding_form_tag->basetype) {

									$data = array();
									$data[] = 'Transaction ID: ' . $txn_id;
									$data[] = 'Transaction Status: ' . $payment_status;
									$data[] = 'Invoice Number: ' . $invoice_no;

									if ( !empty( $html ) ) {
										return implode( '<br/>', $data );
									} else {
										return implode( "\n", $data );
									}
								}
							}

							return $replaced;
						}, 10, 5 );

						$_SESSION[ CF7SA_META_PREFIX . 'failed' . $form_ID ] = false;

						if (
							   $payment_status == 'succeeded'
							|| $payment_status == 'paid'
						) {
							$_SESSION[ CF7SA_META_PREFIX . 'form_message' . $form_ID ] = serialize( __( 'Transaction is Successfully Completed.', CF7SA_PREFIX ) );
						} else if ( $payment_status == 'pending' ) {
							$_SESSION[ CF7SA_META_PREFIX . 'form_message' . $form_ID ] = serialize( __( 'Transaction is in pending.', CF7SA_PREFIX ) );
						} else {

							add_filter( 'wpcf7_skip_mail', array( $this, 'filter__wpcf7_skip_mail' ), 20 );
							$wpcf7_submission->set_status( 'mail_failed' );
							$wpcf7_submission->set_response( $contact_form->message( 'mail_sent_ng' ) );

							$_SESSION[ CF7SA_META_PREFIX . 'form_message' . $form_ID ] = serialize(  __( 'Transaction is failed...', CF7SA_PREFIX ) );
						}

						if ( !empty( $success_returnURL ) && $success_returnURL != "Select page" ) {

							$redirect_url = add_query_arg(
								array(
									'form'      => $form_ID,
									'invoice'   => $invoice_no,
									'txn_id' 	=>  $txn_id,
								),
								esc_url( get_permalink( $success_returnURL ) )
							);

							$_SESSION[ CF7SA_META_PREFIX . 'return_url' . $form_ID ] = $redirect_url;

							if ( !$submission->is_restful() ) {
								wp_redirect( $redirect_url );
								exit;
							}
						} else {

							$_SESSION[ CF7SA_META_PREFIX . 'return_url' . $form_ID ] = "";

						}

					} else {
						add_filter( 'wpcf7_skip_mail', array( $this, 'filter__wpcf7_skip_mail' ), 20 );
						$wpcf7_submission->set_status( 'mail_failed' );
						$wpcf7_submission->set_response( $contact_form->message( 'mail_sent_ng' ) );

						$_SESSION[ CF7SA_META_PREFIX . 'form_message' . $form_ID ] = serialize(  __( 'Transaction is failed..', CF7SA_PREFIX ) );
						$_SESSION[ CF7SA_META_PREFIX . 'failed' . $form_ID ] = true;
					}

				} else {
					add_filter( 'wpcf7_skip_mail', array( $this, 'filter__wpcf7_skip_mail' ), 20 );
					$wpcf7_submission->set_status( 'mail_failed' );
					$wpcf7_submission->set_response( $contact_form->message( 'mail_sent_ng' ) );

					$_SESSION[ CF7SA_META_PREFIX . 'form_message' . $form_ID ] = serialize(  __( 'Transaction is failed. Please configure plugin properly as show in document.', CF7SA_PREFIX ) );
					$_SESSION[ CF7SA_META_PREFIX . 'failed' . $form_ID ] = true;
				}

				return $submission;

			}
		}

		function action__wpcf7_save_contact_form( $contact_form, $args, $context ) {

			$form_ID = $contact_form->id();

			$use_stripe = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'use_stripe', true );

			if ( empty( $use_stripe ) )
				return;

			$enable_test_mode     = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'enable_test_mode', true );
			$test_secret_key      = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'test_secret_key', true );
			$live_secret_key      = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'live_secret_key', true );

			$secreat_key = ( !empty( $enable_test_mode ) ? $test_secret_key : $live_secret_key );

			if ( empty( $secreat_key ) )
				return;

			\Stripe\Stripe::setApiKey( $secreat_key );

			$webhooks = array();

			try {
				$list_all = \Stripe\WebhookEndpoint::all(["limit" => 9999]);

				if( !empty( $list_all ) && !empty( $list_all->data ) ) {

					foreach ( $list_all->data as $data ) {
						$webhooks[$data->id] = $data->url;
					}

				}

				$enabled_events= ['charge.dispute.updated', 'charge.dispute.created', 'charge.dispute.closed', 'charge.updated', 'charge.expired', 'charge.pending', 'charge.refund.updated', 'charge.refunded', 'charge.failed', 'charge.succeeded'];

				$new_webhook_url = home_url( '/cf7sa-webhook/' . $form_ID );

				if (
					empty( $webhooks )
					|| (
						!empty( $webhooks )
						&& !in_array( $new_webhook_url, $webhooks )
					)
				) {

					\Stripe\WebhookEndpoint::create([
						'url' => $new_webhook_url,
						'enabled_events' => $enabled_events
					]);

				} else if(
					!empty( $webhooks )
					&& in_array( $new_webhook_url, $webhooks )
				) {
					$hook_id = array_search( $new_webhook_url, $webhooks );
					if ( !empty( $hook_id ) ) {
						\Stripe\WebhookEndpoint::update(
							$hook_id,
							[
								'url' => $new_webhook_url,
								'enabled_events' => $enabled_events
							]
						);
					}
				}

			} catch( Exception $e ) {
				//$message = $e->getMessage();
			}
		}


		function shortcode__stripe_details() {

			$form_id = (int)( isset( $_REQUEST['form'] ) ? $_REQUEST['form'] : '' );
			$txn_id = ( isset( $_REQUEST['txn_id'] ) ? $_REQUEST['txn_id'] : '' );
			$invoice_no = ( isset( $_REQUEST['invoice'] ) ? $_REQUEST['invoice'] : '' );
			$failure_code = ( isset( $_REQUEST['failure_code'] ) ? $_REQUEST['failure_code'] : '' );
			$failure_message = ( isset( $_REQUEST['failure_message'] ) ? $_REQUEST['failure_message'] : '' );

			if (
				!empty( $failure_code )
				|| !empty( $failure_message )
			)
				return '<p style="color: #f00">' .
					__( 'Something goes wrong! Please try again.', 'contact-form-7-stripe-addon' ) .
					'<br/>' .
					$charge->failure_code . ": " . $failure_message .
				'</p>';

			if (
				empty( $form_id )
				&& empty( $txn_id )
				&& !empty( $failure_code )
			) {
				return '<p style="color: #f00">' .
					__( 'Something goes wrong! Please try again.', 'contact-form-7-stripe-addon' ) .
				'</p>';
			}

			$use_stripe           = get_post_meta($form_id, CF7SA_META_PREFIX . 'use_stripe', true);

			if ( empty( $use_stripe ) )
				return '<p style="color: #f00">' . __( 'Something goes wrong! Please try again.', 'contact-form-7-stripe-addon' ) . '</p>';

			ob_start();

			$enable_test_mode     = get_post_meta($form_id, CF7SA_META_PREFIX . 'enable_test_mode', true);
			$test_publishable_key = get_post_meta($form_id, CF7SA_META_PREFIX . 'test_publishable_key', true);
			$test_secret_key      = get_post_meta($form_id, CF7SA_META_PREFIX . 'test_secret_key', true);
			$live_publishable_key = get_post_meta($form_id, CF7SA_META_PREFIX . 'live_publishable_key', true);
			$live_secret_key      = get_post_meta($form_id, CF7SA_META_PREFIX . 'live_secret_key', true);

			$secreat_key = !empty( $enable_test_mode ) ? $test_secret_key : $live_secret_key;

			\Stripe\Stripe::setApiKey( $secreat_key );

			$retrieve_charge = new stdClass();

			try {
				$retrieve_charge = \Stripe\Charge::retrieve( $invoice_no );

			} catch ( Exception $e ) {
				//If the charge fails (payment unsuccessful), this code will get triggered.
				if ( ! empty( $retrieve_charge->failure_code ) ) {

					return '<p style="color: #f00">' . $retrieve_charge->failure_code . ": " . $retrieve_charge->failure_message . '</p>';

				} else {

					return '<p style="color: #f00">' . $e->getMessage() . '</p>';
				}
			}

			echo '<table class="cf7sa-transaction-details" align="left">' .
				'<tr>'.
					'<th align="left">' . __( 'Transaction Amount :', 'contact-form-7-stripe-addon' ) . '</th>'.
					'<td align="left">' . ( $retrieve_charge->amount / 100 ) . ' ' . $retrieve_charge->currency . '</td>'.
				'</tr>' .
				'<tr>'.
					'<th align="left">' . __( 'Payment Status :', 'contact-form-7-stripe-addon' ) . '</th>'.
					'<td align="left">' . $retrieve_charge->status . '</td>'.
				'</tr>' .
				'<tr>'.
					'<th align="left">' . __( 'Transaction Id :', 'contact-form-7-stripe-addon' ) . '</th>'.
					'<td align="left">' . $retrieve_charge->balance_transaction . '</td>'.
				'</tr>' .
				(
					!empty( $retrieve_charge->card->type )
					? '<tr>'.
						'<th align="left">' . __( 'Card Type :', 'contact-form-7-stripe-addon' ) . '</th>'.
						'<td align="left">' . $retrieve_charge->card->type . '</td>'.
					'</tr>'
					: ''
				) .
			'</table>';

			return ob_get_clean();

		}

		/**
		 * Filter: Modify the contact form 7 response.
		 *
		 * @method filter__wpcf7_ajax_json_echo
		 *
		 * @param  array $response
		 * @param  array $result
		 *
		 * @return array
		 */
		function filter__wpcf7_ajax_json_echo( $response, $result ) {

			if (
				array_key_exists( 'contact_form_id', $result )
				&& array_key_exists( 'status', $result )
				&& !empty( $result['contact_form_id'] )
				&& !empty( $_SESSION[ CF7SA_META_PREFIX . 'form_message' . $result['contact_form_id'] ] )
				&& $result[ 'status' ] == 'mail_sent'
			) {

				$tmp                 = $response['message'];
				$response[ 'message' ] = unserialize( $_SESSION[ CF7SA_META_PREFIX . 'form_message' . $result[ 'contact_form_id' ] ] );
				unset( $_SESSION[ CF7SA_META_PREFIX . 'form_message' . $result[ 'contact_form_id' ] ] );

				if ( isset( $_SESSION[ CF7SA_META_PREFIX . 'return_url' . $result[ 'contact_form_id' ] ] ) ) {
					$response[ 'redirection_url' ] = $_SESSION[ CF7SA_META_PREFIX . 'return_url' . $result[ 'contact_form_id' ] ];
					unset( $_SESSION[ CF7SA_META_PREFIX . 'return_url' . $result[ 'contact_form_id' ] ] );
				}

				if ( !empty( $_SESSION[ CF7SA_META_PREFIX . 'failed' . $result[ 'contact_form_id' ] ] ) ) {
					$response[ 'status' ] = 'mail_failed';
					unset( $_SESSION[ CF7SA_META_PREFIX . 'failed' . $result[ 'contact_form_id' ] ] );
				} else {
					$response[ 'message' ] = $response[ 'message' ] . ' ' . $tmp;
				}

			}

			if (
				array_key_exists( 'contact_form_id', $result )
				&& array_key_exists( 'status', $result )
				&& !empty( $result[ 'contact_form_id' ] )
				&& !empty( $_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $result[ 'contact_form_id' ] ] )
				&& $result[ 'status' ] == 'mail_sent'
			) {

				$response[ 'message' ] = $_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $result[ 'contact_form_id' ] ];
				$response[ 'status' ]  = 'mail_failed';
				unset( $_SESSION[ CF7SA_META_PREFIX . 'amount_error' . $result[ 'contact_form_id' ] ] );
			}

			return $response;
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

		/**
		 * Filter: wpcf7_validate_stripe
		 *
		 * - Perform Validation on stripe card details.
		 *
		 * @param  object  $result WPCF7_Validation
		 * @param  object  $tag    Form tag
		 *
		 * @return object
		 */
		function wpcf7_stripe_validation_filter( $result, $tag ) {

			$stripe = isset( $_POST[ 'stripeClientSecret' ] ) ? $_POST[ 'stripeClientSecret' ] : '';

			$id = isset( $_POST[ '_wpcf7' ] ) ? $_POST[ '_wpcf7' ] : '';

			if ( !empty( $id ) ) {
				$id = ( int ) $_POST[ '_wpcf7' ];
			} else {
				return $result;
			}

			$use_stripe = get_post_meta( $id, CF7SA_META_PREFIX . 'use_stripe', true );

			if ( empty( $use_stripe ) )
				return $result;

			$error = array();

			if ( empty( $stripe ) )
				$result->invalidate( $tag, 'Please enter correct card details. ' );

			return $result;
		}

		/**
		 * Filter: Skip email when Stripe enable.
		 *
		 * @method filter__wpcf7_skip_mail
		 *
		 * @param  bool $bool
		 *
		 * @return bool
		 */
		function filter__wpcf7_skip_mail( $bool ) {
			return true;
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

		/**
		 * - Render CF7 Shortcode on front end.
		 *
		 * @method wpcf7_add_form_tag_stripe_net
		 *
		 * @param $tag
		 *
		 * @return html
		 */
		function wpcf7_add_form_tag_stripe_net( $tag ) {

			if ( empty( $tag->name ) ) {
				return '';
			}

			$validation_error = wpcf7_get_validation_error( $tag->name );

			$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );

			if (
				in_array(
					$tag->basetype,
					array(
						'email',
						'url',
						'tel'
					)
				)
			) {
				$class .= ' wpcf7-validates-as-' . $tag->basetype;
			}

			if ( $validation_error ) {
				$class .= ' wpcf7-not-valid';
			}

			$atts = array();

			if ( $tag->is_required() ) {
				$atts['aria-required'] = 'true';
			}

			$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

			$atts['value'] = 1;

			$atts['type'] = 'hidden';
			$atts['name'] = $tag->name;
			$atts         = wpcf7_format_atts($atts);

			$form_instance = WPCF7_ContactForm::get_current();
			$form_id       = $form_instance->id();

			$use_stripe           = get_post_meta($form_id, CF7SA_META_PREFIX . 'use_stripe', true);
			$enable_test_mode     = get_post_meta($form_id, CF7SA_META_PREFIX . 'enable_test_mode', true);
			$test_publishable_key = get_post_meta($form_id, CF7SA_META_PREFIX . 'test_publishable_key', true);
			$test_secret_key      = get_post_meta($form_id, CF7SA_META_PREFIX . 'test_secret_key', true);
			$live_publishable_key = get_post_meta($form_id, CF7SA_META_PREFIX . 'live_publishable_key', true);
			$live_secret_key      = get_post_meta($form_id, CF7SA_META_PREFIX . 'live_secret_key', true);

			if ( empty( $use_stripe ) ) {
				return;
			}

			if ( !empty( $this->_validate_fields( $form_id ) ) )
				return $this->_validate_fields( $form_id );

			$value = ( string ) reset( $tag->values );

			$found = 0;
			$html  = '';

			ob_start();

			if ( $contact_form = wpcf7_get_current_contact_form() ) {
				$form_tags = $contact_form->scan_form_tags();

				foreach ( $form_tags as $k => $v ) {

					if ( $v['type'] == $tag->type ) {
						$found++;
					}

					if ( $v['name'] == $tag->name ) {
						if ( $found <= 1 ) {

							echo '<div class="cf7sa-form-code">' .
									sprintf(
										'<span class="credit_card_details wpcf7-form-control-wrap %1$s">%2$s%3$s</span>',
										sanitize_html_class( $tag->name ),
										'<label for="card-element-' . esc_attr( $form_id ) . '">' . __('Credit or Debit card', 'contact-form-7-stripe-addon').'</label>
										<div id="card-element-' . esc_attr( $form_id ) . '">
											<!-- a Stripe Element will be inserted here. -->
										</div>' .
										'<input type="hidden" name="stripeToken" value="" />' .
										'<input type="hidden" name="stripeClientSecret" value="" />' .
										'<input type="hidden" name="action" value="get_stripe_intent" />' .
										'<noscript>' . __( 'Stripe Payments requires Javascript to be supported by the browser in order to operate.', 'contact-form-7-stripe-addon' ) . '</noscript>',
										'<span id="card-errors-' . esc_attr( $form_id ) . '" class="wpcf7-not-valid-tip"></span>' .
										$validation_error
									) .
								'</div>';
						}
						break;
					}
				}
			}

			return ob_get_clean();
		}

		/**
		 * Function: _validate_fields
		 *
		 * @method _validate_fields
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		function _validate_fields( $form_id ) {

			$enable_test_mode     = get_post_meta( $form_id, CF7SA_META_PREFIX . 'enable_test_mode', true );
			$test_publishable_key = get_post_meta( $form_id, CF7SA_META_PREFIX . 'test_publishable_key', true );
			$test_secret_key      = get_post_meta( $form_id, CF7SA_META_PREFIX . 'test_secret_key', true );
			$live_publishable_key = get_post_meta( $form_id, CF7SA_META_PREFIX . 'live_publishable_key', true );
			$live_secret_key      = get_post_meta( $form_id, CF7SA_META_PREFIX . 'live_secret_key', true );

			if ( !empty( $enable_test_mode ) ) {

				if ( empty( $test_publishable_key ) )
					return __( 'Please enter Test Publishable Key.', 'contact-form-7-stripe-addon' );

				if ( empty( $test_secret_key ) )
					return __( 'Please enter Test Secret Key.', 'contact-form-7-stripe-addon' );

			} else {

				if ( empty( $live_publishable_key ) )
					return __( 'Please enter Live Publishable Key.', 'contact-form-7-stripe-addon' );

				if ( empty( $live_secret_key ) )
					return __( 'Please enter Live Secret Key.', 'contact-form-7-stripe-addon' );

			}
			return false;
		}

		/**
		 * Function: getUserIpAddr
		 *
		 * @method getUserIpAddr
		 *
		 * @return string
		 */
		function getUserIpAddr() {
			$ip = '';
			if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				//ip from share internet
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} else if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				//ip pass from proxy
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			return $ip;
		}

		/**
		 * Get the attachment upload directory from plugin.
		 *
		 * @method zw_wpcf7_upload_tmp_dir
		 *
		 * @return string
		 */
		function zw_wpcf7_upload_tmp_dir() {

			$upload = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$cf7sa_upload_dir = $upload_dir . '/cf7sa-uploaded-files';

			if ( !is_dir( $cf7sa_upload_dir ) ) {
				mkdir( $cf7sa_upload_dir, 0400 );
			}

			return $cf7sa_upload_dir;
		}

		/**
		 * Copy the attachment into the plugin folder.
		 *
		 * @method zw_cf7_upload_files
		 *
		 * @param  array $attachment
		 *
		 * @uses $this->zw_wpcf7_upload_tmp_dir(), WPCF7::wpcf7_maybe_add_random_dir()
		 *
		 * @return array
		 */
		function zw_cf7_upload_files( $attachment ) {
			if ( empty( $attachment ) || $attachment === "" ) {
				return;
			}
			$new_attachment = $attachment;
			foreach ( $attachment as $key => $value ) {
				// Check if $value is a non-empty string before proceeding
				if ( is_string( $value ) && $value !== "" ) {
					$tmp_name = $value;
					$uploads_dir = wpcf7_maybe_add_random_dir( $this->zw_wpcf7_upload_tmp_dir() );
					$new_file = path_join( $uploads_dir, end( explode( '/', $value ) ) );
		
					if ( copy( $value, $new_file ) ) {
						chmod( $new_file, 0400 );
						$new_attachment[$key] = $new_file;
					}
				}
			}
		
			return $new_attachment;
		}

		function handlePaymentIntentSucceeded( $paymentIntent ) {
			$invoice_id = $paymentIntent->id;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'succeeded' );
				}
			}
		}

		function handlePaymentMethodFailed( $paymentIntent ) {
			$invoice_id = $paymentIntent->id;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'failed' );
				}
			}
		}

		function handlePaymentMethodRefunded( $paymentIntent ) {
			$invoice_id = $paymentIntent->id;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'refunded' );
				}
			}
		}

		function handlePaymentMethodPending( $paymentIntent ) {
			$invoice_id = $paymentIntent->id;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'pending' );
				}
			}
		}

		function handlePaymentMethodExpiered( $paymentIntent ) {
			$invoice_id = $paymentIntent->id;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'expired' );
				}
			}
		}

		function handlePaymentMethodDisputeCreated( $paymentIntent ) {
			$invoice_id = $paymentIntent->charge;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'needs_response' );
				}
			}
		}

		function handlePaymentMethodDisputeUpdated( $paymentIntent ) {
			$invoice_id = $paymentIntent->charge;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'disputed' );
				}
			}
		}

		function handlePaymentMethodDisputeClosed( $paymentIntent ) {
			$invoice_id = $paymentIntent->charge;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
					update_post_meta( $data_id, '_transaction_status', 'disputed' );
				}
			}
		}

		function handlePaymentMethodUpdated( $paymentIntent ) {
			$invoice_id = $paymentIntent->id;

			$cf7sa_data = get_posts( array(
				'post_type' => 'cf7sa_data',
				'posts_per_page' => -1,
				'meta_key' => '_invoice_no',
				'meta_value' => $invoice_id,
				'fields'           => 'ids',
			) );

			if ( !empty( $cf7sa_data ) ) {
				foreach ( $cf7sa_data as $data_id ) {
					update_post_meta( $data_id, '_transaction_response', json_encode( $paymentIntent ) );
				}
			}
		}

	}
}
