
export default {
  init() {
    // JavaScript to be fired on all pages
    /** * handle mobile nav */
    $('.mobile-action-bg').on('click', () => {
      $('.mobile-menu').toggleClass('retracted');
      $('#mobile-repair-link').toggleClass('outlined-light', 'outlined-red');
      $('.mobile-action-bar').toggleClass('expanded');
      $('.banner').toggleClass('expanded');
      $('.mobile-home').toggleClass('white-logo');
      $('.mobile-box-shadow').toggleClass('expanded');

      if ($('.mobile-home').hasClass('white-logo')) {
        // eslint-disable-next-line no-undef
        $('.mobile-home').attr('src', `${iresq_logos.small_white_logo}`);
      } else {
        // eslint-disable-next-line no-undef
        $('.mobile-home').attr('src', `${iresq_logos.small_red_logo}`);
      }

      setTimeout(() => { $('.mobile-action-indicator').toggleClass('fa-bars').toggleClass('fa-times'); }, 333);
    });

    $(document).ready(() => {
      $('.product--select-device select').each(function () {
        $(this).parent().parent().find('.single_serial_number input')
          .val($(this).find('option:selected').attr('data-serial-number'));
        $(this).parent().parent().parent()
          .find('a.ajax_add_to_cart')
          .attr('data-serial-number', $(this).find('option:selected').attr('data-serial-number'));
        $(this).parent().parent().parent()
          .find('a.ajax_add_to_cart')
          .attr('data-device-number', $(this).val());
      });
    });

    $('.shop-listing').on('change', '.product--select-device select', function () {
      const optionSerialNumber = $(this).find('option:selected').attr('data-serial-number') ? $(this).find('option:selected').attr('data-serial-number') : '';
      $(this).parent().parent().find('.single_serial_number input')
        .val(optionSerialNumber);
      $(this).parent().parent().parent()
        .find('a.ajax_add_to_cart')
        .attr('data-serial-number', optionSerialNumber);
      $(this).parent().parent().parent()
        .find('a.ajax_add_to_cart')
        .attr('data-device-number', $(this).val());
    });

    $('.single_serial_number input').change(function () {
      $(this).parent().parent().parent()
        .find('a.ajax_add_to_cart')
        .attr('data-serial-number', $(this).val());
    });

    $('.shop-listing').on('click', '.add_to_cart_button', function () {
      const deviceNumber = $(this).attr('data-device-number');
      const serialNumber = $(this).attr('data-serial-number');
      const selects = $('.shop-listing').find('.product--select-device select');
      selects.each(function () {
        let lastDeviceNumber;
        $(this).find('option').each(function () {
          if ($(this).val() == deviceNumber) {
            $(this).attr('data-serial-number', serialNumber);
            $(this).text(`Device #${deviceNumber} (#${serialNumber})`);
          }
          lastDeviceNumber = $(this).val();
        });
        lastDeviceNumber++;
        const addDeviceOption = $(this).find('option').filter(function () {
          return $(this).text() === 'Add New Device';
        });
        if (addDeviceOption.length == 0) {
          $(this).append(`<option value="${lastDeviceNumber}">Add New Device</option>`);
        }
        $(this).val(deviceNumber).change();
      });
    });
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};
