<?php

$form_ID = get_query_var( 'cf7sa-webhook', '' );

if ( empty( $form_ID ) )
	return;

$use_stripe = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'use_stripe', true );

if ( empty( $use_stripe ) )
	return;

$enable_test_mode = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'enable_test_mode', true );
$test_secret_key  = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'test_secret_key', true );
$live_secret_key  = get_post_meta( $form_ID, CF7SA_META_PREFIX . 'live_secret_key', true );

$secreat_key = ( !empty( $enable_test_mode ) ? $test_secret_key : $live_secret_key );

if( empty( $secreat_key ) )
	return;

require_once( CF7SA_DIR . '/inc/lib/init.php' );

\Stripe\Stripe::setApiKey( $secreat_key );

if (!function_exists('write_log')) {

	function write_log($log) {
		if (true === WP_DEBUG) {
			if (is_array($log) || is_object($log)) {
				error_log(print_r($log, true));
			} else {
				error_log($log);
			}
		}
	}

}

$payload = @file_get_contents('php://input');
$event = null;

try {
	$event = \Stripe\Event::constructFrom(
		json_decode($payload, true)
	);
} catch(\UnexpectedValueException $e) {
	http_response_code(400);
	exit();
}

// write_log( $event );

// Handle the event
switch ( $event->type ) {
	case 'charge.succeeded':
		$paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
		CF7SA()->lib->handlePaymentIntentSucceeded($paymentIntent);
		break;
	case 'charge.failed':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodFailed($paymentMethod);
		break;
	case 'charge.refunded':
	case 'charge.refund.updated':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodRefunded($paymentMethod);
		break;
	case 'charge.pending':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodPending($paymentMethod);
		break;
	case 'charge.expired':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodExpiered($paymentMethod);
		break;
	case 'charge.dispute.created':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodDisputeCreated($paymentMethod);
		break;
	case 'charge.dispute.updated':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodDisputeUpdated($paymentMethod);
		break;
	case 'charge.dispute.closed':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodDisputeClosed($paymentMethod);
		break;
	case 'charge.updated':
		$paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
		CF7SA()->lib->handlePaymentMethodUpdated($paymentMethod);
		break;
	default:
		// Unexpected event type
		http_response_code(400);
		exit();
}

http_response_code(200);
?>
