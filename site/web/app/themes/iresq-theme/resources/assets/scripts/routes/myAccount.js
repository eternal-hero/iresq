import 'jquery-validation';

export default {
    init() {
        // $(window).on('load', () => {
        //     const billingPhone = $('input#billing_phone');
        //     billingPhone.attr('maxlength', 10);
        //     billingPhone.attr('minlength', 10);
        // })

        $('form.woocommerce-form-register').validate({
            rules: {
                'billing_phone': {
                    required: true,
                    minlength: 10,
                    maxlength: 10
                },
                'afreg_additional_450': {
                    digits: true
                }
            }
        })
    },
    finalize() {
    },
  };
  