export default {
    init() {
      // JavaScript to be fired on all pages      
    },
    finalize() {
        var card = $('.card');
        card.on('click', function() {
          $(this).toggleClass('active');
        });
    },
  };