<?php

namespace App\WCEmails;

class AdminRepaymentEmail extends \WC_Email
{
    /**
     * Set email defaults.
     *
     * @since 0.1
     */
    public function __construct()
    {
        $this->id = 'iresq-admin-approval';

        // this is the title in WooCommerce Email settings
        $this->title = 'Repayment Admin Notification';
        $this->customer_email = false;
        $this->placeholders = [
            '{amount}' => '',
            '{order_no}' => '',
            '{invoice_no}' => '',
        ];

        // this is the description in WooCommerce email settings
        $this->description = 'Admin only notifications for additional payments made by a customer.';

        // Triggers
        add_action('iresq_trigger_admin_repayment_email', [$this, 'trigger'], 10, 5);

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = $this->get_option('heading', $this->get_default_heading());
        $this->subject = $this->get_option('subject', $this->get_default_subject());

        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
        $this->template_html = 'emails/admin-repayment-email.php';
        $this->email_type = 'html';

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
        return 'Additional Payment Processed, #{invoice_no}';
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading()
    {
        return 'Additional Payment Processed';
    }

    /**
     * Determine if the email should actually be sent and setup email merge variables.
     *
     * @since 0.1
     *
     * @param mixed $order_id
     * @param mixed $invoiceRecord
     * @param mixed $amount
     * @param mixed $order
     */
    public function trigger($order_id, $invoiceRecord, $amount, $order = false)
    {
        if ($order_id && !is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }

        if (is_a($order, 'WC_Order')) {
            $this->object = $order;

            $this->placeholders['{amount}'] = $amount;
            $this->placeholders['{invoice_no}'] = $invoiceRecord->field('Item No');
            $this->placeholders['{order_no}'] = $order_id;
        }

        if ($this->is_enabled() && $this->get_recipient()) {
            $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
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
                'main_content' => $this->format_string($this->get_default_main_content()),
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => $this,
            ]
        );
    }

    public function get_default_main_content()
    {
        return 'An additional payment for the Invoice Number, #{invoice_no}, for the Order Number, #{order_no}, has been made for the amount of ${amount} and has been recorded in Filemaker. You may receive additional emails for other invoices that had payments applied for this order.';
    }

    /**
     * Initialize Settings Form Fields.
     *
     * @since 0.1
     */
    public function init_form_fields()
    {
        // translators: %s: list of placeholders
        $placeholder_text = sprintf(__('Available placeholders: %s', 'woocommerce'), '<code>' . implode('</code>, <code>', array_keys($this->placeholders)) . '</code>');
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
                'description' => sprintf(__('Enter recipients (comma separated) for this email. Defaults to %s.', 'woocommerce'), '<code>' . esc_attr(get_option('admin_email')) . '</code>'),
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
            'main_content' => [
                'title' => __('Main content', 'woocommerce'),
                'description' => __('Main email content.', 'woocommerce') . ' ' . $placeholder_text,
                'css' => 'width:400px; height: 150px;',
                'type' => 'textarea',
                'placeholder' => $this->get_default_main_content(),
                'desc_tip' => true,
                'default' => '',
            ],
        ];
    }
}
