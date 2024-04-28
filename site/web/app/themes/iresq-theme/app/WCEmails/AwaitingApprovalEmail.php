<?php

namespace App\WCEmails;

class AwaitingApprovalEmail extends \WC_Email
{
    /**
     * Set email defaults.
     *
     * @since 0.1
     */
    public function __construct()
    {
        $this->id = 'iresq-awaiting-approval';

        // this is the title in WooCommerce Email settings
        $this->title = 'Awaiting Approval';
        $this->customer_email = true;
        $this->placeholders = [
            '{message}' => '',
            '{fm_user}' => '',
            '{invoice_no}' => '',
            '{fm_user_first_name}' => '',
        ];

        // this is the description in WooCommerce email settings
        $this->description = 'Awaiting Approval emails are delivered when the appropriate trigger is selected in FileMaker.';

        // Triggers
        add_action('iresq_trigger_awaiting_approval_email', [$this, 'trigger'], 10, 5);

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = $this->get_option('heading', $this->get_default_heading());
        $this->subject = $this->get_option('subject', $this->get_default_subject());

        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
        $this->template_html = 'emails/awaiting-approval.php';
        $this->email_type = 'html';

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject()
    {
        return 'Awaiting Approval for Your Item, #{invoice_no}';
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading()
    {
        return 'Your Diagnosis is Complete!';
    }

    /**
     * Determine if the email should actually be sent and setup email merge variables.
     *
     * @since 0.1
     *
     * @param int   $order_id
     * @param mixed $order
     * @param mixed $message
     * @param mixed $fileMakerUser
     * @param mixed $itemNo
     */
    public function trigger($order_id, $message, $fileMakerUser, $itemNo, $order = false)
    {
        if ($order_id && !is_a($order, 'WC_Order')) {
            $order = wc_get_order($order_id);
        }

        if (is_a($order, 'WC_Order')) {
            $this->object = $order;
            $this->recipient = $this->object->get_billing_email();
            $fmUserName = explode(' ', trim($fileMakerUser));

            $this->placeholders['{message}'] = $message;
            $this->placeholders['{internal_user}'] = $fileMakerUser;
            $this->placeholders['{fm_user}'] = $fileMakerUser;
            $this->placeholders['{fm_user_first_name}'] = $fmUserName[0];
            $this->placeholders['{invoice_no}'] = $itemNo;
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
                'additional_content' => $this->get_additional_content(),
                'customer_content' => $this->format_string($this->get_default_customer_content()),
                'sent_to_admin' => false,
                'plain_text' => false,
                'email' => $this,
            ]
        );
    }

    public function get_default_customer_content()
    {
        return 'Your diagnosis is complete! {fm_user_first_name} has performed a full diagnosis of your device and determined the following: {message}<br><br>{fm_user}';
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
            'content' => [
                'title' => __('Email Content', 'woocommerce'),
                'type' => 'text',
                'desc_tip' => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_customer_content(),
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
