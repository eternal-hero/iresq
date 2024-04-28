<?php

namespace App\WCEmails;

class NoShippingEmail extends \WC_Email
{
    /**
     * Set email defaults.
     *
     * @since 0.1
     */
    public function __construct()
    {
        $this->id = 'iresq_no_shipping';

        // this is the title in WooCommerce Email settings
        $this->title = 'No Shipping Selected';
        $this->customer_email = false;
        $this->placeholders = [
            '{order_date}' => '',
            '{order_number}' => '',
        ];

        // this is the description in WooCommerce email settings
        $this->description = 'No Shipping Selected notification emails are sent when a customer places an order without selecting a shipping method. Usually due to a large order.';

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = 'No Shipping Selected';
        $this->subject = 'No Shipping Selected';

        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
        $this->template_html = 'emails/no-shipping-email.php';
        $this->email_type = 'html';

        // Trigger on new paid orders
        add_action('woocommerce_order_status_pending_to_processing_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_pending_to_completed_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_pending_to_on-hold_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_failed_to_processing_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_failed_to_completed_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_failed_to_on-hold_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_cancelled_to_processing_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_cancelled_to_completed_notification', [$this, 'trigger'], 10, 2);
        add_action('woocommerce_order_status_cancelled_to_on-hold_notification', [$this, 'trigger'], 10, 2);

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();

        $this->recipient = $this->get_option('recipient', get_option('admin_email'));
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject()
    {
        return '[{site_title}]: No Shipping Selected for Order #{order_number}';
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading()
    {
        return 'No Shipping Selected: #{order_number}';
    }

    /**
     * Determine if the email should actually be sent and setup email merge variables.
     *
     * @since 0.1
     *
     * @param int   $order_id
     * @param mixed $order
     */
    public function trigger($order_id, $order = false)
    {
        if ($order_id && !is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }

        if (is_a($order, 'WC_Order')) {
            $this->object = $order;
            $this->placeholders['{order_date}'] = wc_format_datetime($this->object->get_date_created());
            $this->placeholders['{order_number}'] = $this->object->get_order_number();

            $email_already_sent = $order->get_meta('_no_ship_selected_email_sent');
        }

        /*
         * Controls if new order emails can be resend multiple times.
         *
         * @since 5.0.0
         * @param bool $allows Defaults to false.
         */
        if ('true' === $email_already_sent) {
            return;
        }

        // bail if shipping method is not expedited
        if (count($this->object->get_shipping_methods()) > 0) {
            return;
        }

        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());

            $order->update_meta_data('_no_ship_selected_email_sent', 'true');
            $order->save();
        }
    }

    /**
     * get_content_html function.
     *
     * @since 0.1
     *
     * @return string
     */
    public function get_content_html()
    {
        return wc_get_template_html(
            $this->template_html,
            [
                'order' => $this->object,
                'email_heading' => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => $this,
            ]
        );
    }

    /**
     * Initialize Settings Form Fields.
     *
     * @since 0.1
     */
    public function init_form_fields()
    {
        // translators: %s: list of placeholders
        $placeholder_text = sprintf(__('Available placeholders: %s', 'woocommerce'), '<code>'.implode('</code>, <code>', array_keys($this->placeholders)).'</code>');
        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable/Disable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable this email notification', 'woocommerce'),
                'default' => 'yes',
            ],
            'recipient' => [
                'title' => __('Recipient(s)', 'woocommerce'),
                'type' => 'text',
                // translators: %s: WP admin email
                'description' => sprintf(__('Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce'), '<code>'.esc_attr(get_option('admin_email')).'</code>'),
                'placeholder' => '',
                'default' => '',
                'desc_tip' => true,
            ],
            'subject' => [
                'title' => __('Subject', 'woocommerce'),
                'type' => 'text',
                'desc_tip' => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default' => '',
            ],
            'heading' => [
                'title' => __('Email heading', 'woocommerce'),
                'type' => 'text',
                'desc_tip' => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default' => '',
            ],
            'email_type' => [
                'title' => __('Email type', 'woocommerce'),
                'type' => 'select',
                'description' => __('Choose which format of email to send.', 'woocommerce'),
                'default' => 'html',
                'class' => 'email_type wc-enhanced-select',
                'options' => $this->get_email_type_options(),
                'desc_tip' => true,
            ],
        ];
    }
}
