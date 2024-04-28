<?php
/**
 * Custom iResQ Email for Admin Repayment Notifications.
 */
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

// @hooked WC_Emails::email_header() Output the email header
do_action('woocommerce_email_header', $email_heading, $email);

echo '<br />';
?>
<?php

// Show user-defined additional content - this is set in each email's settings.
if ($main_content) {
    echo wp_kses_post(wpautop(wptexturize($main_content)));
}

// @hooked WC_Emails::email_footer() Output the email footer
do_action('woocommerce_email_footer', $email);
