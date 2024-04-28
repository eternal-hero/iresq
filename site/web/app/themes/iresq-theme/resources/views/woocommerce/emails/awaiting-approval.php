<?php
/**
 * Custom iResQ Email for No Shipping Notification.
 */
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

// @hooked WC_Emails::email_header() Output the email header
do_action('woocommerce_email_header', $email_heading, $email);

echo '<br />';
echo wp_kses_post(wpautop(wptexturize($customer_content)));
?>
<p>Click the link below to approve and pay for order:</p>
<p><a href="<?php echo $order->get_checkout_payment_url(); ?>" style="color:blue;">View Order Changes & Make a Payment</a></p>
<?php

// Show user-defined additional content - this is set in each email's settings.
if ($additional_content) {
    echo wp_kses_post(wpautop(wptexturize($additional_content)));
}

// @hooked WC_Emails::email_footer() Output the email footer
do_action('woocommerce_email_footer', $email);
