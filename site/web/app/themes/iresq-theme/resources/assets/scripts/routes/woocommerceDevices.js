export default {
  init() {
    // JavaScript to be fired on all pages
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
    var userId = $('#device-delete-button').attr('data-user_id')
    var deviceToRemove = $('#device-delete-button').attr('data-remove')
    $('body').on('click', '#device-delete-button', function() {

      $.ajax({
        // eslint-disable-next-line no-undef
        url: iresq_ajax.ajax_url,
        type: 'POST',
        data: {
          'action': 'remove_meta_device',
          // eslint-disable-next-line no-undef
          'nonce': iresq_ajax.nonce,
          'user_id': userId,
          'deviceToRemove': deviceToRemove,
        },
        success: function() {
          location.reload();
        },
        error: function() {
          $('<p>Sorry, your device could not be deleted right now</p><br>').insertBefore('.devices-table')
        },
      })
    })
  },
};
