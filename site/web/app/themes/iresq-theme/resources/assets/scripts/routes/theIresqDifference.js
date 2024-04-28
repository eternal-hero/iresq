export default {
  init() {
    // JavaScript to be fired on all pages
    //$('.single_add_to_cart_button').addClass('iresq-button solid-red')
    
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired

    //LIGHT UP STARS WHEN SCROLLED
    $(window).on('scroll', function() {
      var y_scroll_pos = window.pageYOffset;
      var scroll_pos_test = 900;        
      if(y_scroll_pos > scroll_pos_test) {
          $('.stars').addClass('active');
      }
    })

    var images = $('.image-wrapper');
    var arrows = $('.arrow-icon');

    images.last().addClass('active');
//SHOW ARROW ON START
    for(var i = 0; i < images.length; i++) {
        if(i === (images.length - 1)) {
            $('.right.arrow-'+i).css('opacity', '1');
        }
    }
//SWITCH ARROWS AND IMAGES ON HORIZONTAL SLIDERS
    images.on('click', function() {
        images.removeClass('active');
        arrows.css('opacity', '0');
        var slideNum = $(this).data('slide-number');
        var toLeft = slideNum - 1;
        var toRight = slideNum + 1;
        var clicked = $(this);
        clicked.addClass('active'); 
        setTimeout(function(){
          $('.right.arrow-'+toLeft).css('opacity', '1');
          $('.left.arrow-'+toRight).css('opacity', '1');
        }, 800);
        
    });

        //components/horizontal-text-accordion
        var accordion = $('.accordion');
        accordion.on('click', function(){
          var $this = $(this);
            accordion.removeClass('active');
            accordion.find('.fade').removeClass('active');
            $this.addClass('active');
            setTimeout(function(){
              $this.find('.fade').addClass('active');
            }, 1000);
        });
  },
};