export default {
  init() {
    //
  },
  finalize() {
    /**
     * Update cart serial number
     */
    $('body').on('click', '.edit-serial-number', function () {
      if ($(this).children('.edit-action').hasClass('fa-pen')) {
        let existingSerialNumber = $(this)
          .siblings('.cart-serial-number')
          .text();
          existingSerialNumber = existingSerialNumber === 'serial #' ? '' : existingSerialNumber;
        $(this)
          .siblings('#new_serial_number')
          .children('.new-serial-number')
          .val(existingSerialNumber);
        $(this).siblings('.cart-serial-number').hide();
        $(this).siblings('#new_serial_number').css('display', 'grid');
      } else {
        $(this).siblings('.cart-serial-number').show();
        $(this).siblings('#new_serial_number').hide();
      }

      $(this)
        .siblings('#new-serial-number')
        .children('.new-serial-number')
        .trigger('focus');
      $(this)
        .children('.edit-action')
        .toggleClass('fa-pen')
        .toggleClass('fa-times');
    });

    $('body').on('click', '.submit-new-serial-number', function () {
      let selection = $(this);
      let newSN = $(this).siblings('.new-serial-number').val();
      let cartItemKey = $(this)
        .parent('#new_serial_number')
        .parent('.cart-serial-wrapper')
        .parent('.product-name')
        .attr('data-cart-item-id');

      console.log(newSN + ' ' + cartItemKey);
      $.ajax({
        // eslint-disable-next-line no-undef
        url: iresq_ajax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'update_serial_number',
          // eslint-disable-next-line no-undef
          nonce: iresq_ajax.nonce,
          newSN: newSN,
          cartItemKey: cartItemKey,
        },
        success: function (response) {
          console.log(response);
          selection
            .parent('#new_serial_number')
            .siblings('.edit-serial-number')
            .children('.edit-action')
            .toggleClass('fa-pen')
            .toggleClass('fa-times');
          selection
            .parent('#new_serial_number')
            .siblings('.cart-serial-number')
            .show();
          selection.parent('#new_serial_number').hide();
          jQuery('body').trigger('update_checkout');
        },
        error: function (jqXHR) {
          console.log(jqXHR.status);
          console.log(jqXHR.responseText);
        },
      });
    });

    $('body').on('updated_cart_totals', function () {
      $('div[id=new_serial_number]').each(function (i, obj) {
        $(obj).append('<input type=\'text\' class=\'new-serial-number\'>');
      });
    });

    // JavaScript to be fired after initial page load
    $('input[data-type=\'currency\']').on({
      keyup: function () {
        formatCurrency($(this));
      },
      blur: function () {
        formatCurrency($(this), 'blur');
      },
    });

    /**
     * Format a given number
     * @param {number} n
     */
    function formatNumber(n) {
      // format number 1000000 to 1,000,000
      return n.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Format a string to look like USD currency
     * @param {string} input
     * @param {string} blur
     */
    function formatCurrency(input, blur) {
      // appends $ to value, validates decimal side
      // and puts cursor back in right position.

      // get input value
      var input_val = input.val();

      // don't validate empty input
      if (input_val === '') {
        return;
      }

      // original length
      var original_len = input_val.length;

      // initial caret position
      var caret_pos = input.prop('selectionStart');

      // check for decimal
      if (input_val.indexOf('.') >= 0) {
        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        var decimal_pos = input_val.indexOf('.');

        // split number by decimal point
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);

        // add commas to left side of number
        left_side = formatNumber(left_side);

        // validate right side
        right_side = formatNumber(right_side);

        // On blur make sure 2 numbers after decimal
        if (blur === 'blur') {
          right_side += '00';
        }

        // Limit decimal to only 2 digits
        right_side = right_side.substring(0, 2);

        // join number by .
        input_val = '$' + left_side + '.' + right_side;
      } else {
        // no decimal entered
        // add commas to number
        // remove all non-digits
        input_val = formatNumber(input_val);
        input_val = '$' + input_val;

        // final formatting
        if (blur === 'blur') {
          input_val += '.00';
        }
      }

      // send updated string to input
      input.val(input_val);

      // put caret back in the right position
      var updated_len = input_val.length;
      caret_pos = updated_len - original_len + caret_pos;
      input[0].setSelectionRange(caret_pos, caret_pos);
    }
  },
};
