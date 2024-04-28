export default {
  init() {
    //
  },
  finalize() {
    $('body').on('blur', '.serial-number-input input', function() {
      let inputEl = $(this);
      let newSn = $(this).val();
      let oldSn = $(this).attr('data-old-serial-number');

      $.ajax({
        // eslint-disable-next-line no-undef
        url: iresq_ajax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'update_serial_number',
          // eslint-disable-next-line no-undef
          nonce: iresq_ajax.nonce,
          newSN: newSn,
          oldSn: oldSn,
        },
        complete: function() {
          inputEl.attr('data-old-serial-number', newSn);
        },
      })
    })
  },
}