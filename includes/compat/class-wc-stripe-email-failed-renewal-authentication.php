<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Failed Renewal/Pre-Order Authentication Notification
 *
 * @extends WC_Email_Customer_Invoice
 */
class WC_Stripe_Email_Failed_Renewal_Authentication extends WC_Stripe_Email_Failed_Authentication {
	/**
	 * Constructor.
	 *
	 * @param WC_Email[] $email_classes All existing instances of WooCommerce emails.
	 */
	public function __construct( $email_classes = array() ) {
		$this->id             = 'failed_renewal_authentication';
		$this->title          = __( 'Failed Subscription Renewal SCA Authentication', 'woocommerce-gateway-stripe' );
		$this->description    = __( 'Sent to a customer when a renewal fails because the transaction requires an SCA verification. The email contains renewal order information and payment links.', 'woocommerce-gateway-stripe' );
		$this->customer_email = true;

		$this->template_html  = 'emails/failed-renewal-authentication.php';
		$this->template_plain = 'emails/plain/failed-renewal-authentication.php';
		$this->template_base  = plugin_dir_path( WC_STRIPE_MAIN_FILE ) . 'templates/';

		// Triggers the email at the correct hook.
		add_action( 'wc_gateway_stripe_process_payment_error', array( $this, 'trigger' ), 10, 2 );

		if ( isset( $email_classes['WCS_Email_Customer_Renewal_Invoice'] ) ) {
			$this->original_email = $email_classes['WCS_Email_Customer_Renewal_Invoice'];
		}

		// We want all the parent's methods, with none of its properties, so call its parent's constructor, rather than my parent constructor.
		parent::__construct();
	}

	/**
	 * Triggers the email while also disconnecting the original Subscriptions email.
	 *
	 * @param WC_Stripe_Exception $error The exception that occured.
	 * @param WC_Order            $order The order that is being paid.
	 */
	public function trigger( $error, $order ) {
		if ( function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order->get_id() ) || wcs_is_subscription( $order->get_id() ) || wcs_order_contains_renewal( $order->get_id() ) ) ) {
			// Prevent the renewal email from WooCommerce Subscriptions from being sent.
			if ( isset( $this->original_email ) ) {
				remove_action( 'woocommerce_generated_manual_renewal_order_renewal_notification', array( $this->original_email, 'trigger' ) );
				remove_action( 'woocommerce_order_status_failed_renewal_notification', array( $this->original_email, 'trigger' ) );
			}

			parent::trigger( $error, $order );
		}
	}

	/**
	 * Returns the default subject of the email (modifyable in settings).
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( 'Payment authorization needed for renewal order {order_number}', 'woocommerce-gateway-stripe' );
	}

	/**
	 * Returns the default heading of the email (modifyable in settings).
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Payment authorization needed for renewal order {order_number}', 'woocommerce-gateway-stripe' );
	}
}
