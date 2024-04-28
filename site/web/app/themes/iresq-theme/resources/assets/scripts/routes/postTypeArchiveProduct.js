export default {
  init() {

  },
  finalize() {
    $(document.body).on('click input', 'input.qty', function() {
        $(this).parent().parent().find('a.ajax_add_to_cart').attr('data-quantity', $(this).val());

        // (optional) Removing other previous "view cart" buttons
        $('.added_to_cart').remove();
    });

    $(document.body).on('click input', '.single_serial_number input', function() {
        $(this).parent().parent().find('a.ajax_add_to_cart').attr('data-serial-number', $(this).val());

        // (optional) Removing other previous "view cart" buttons
        $('.added_to_cart').remove();
    });
  },
}