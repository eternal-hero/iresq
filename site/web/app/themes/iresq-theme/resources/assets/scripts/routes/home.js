export default {
  init() {
    // JavaScript to be fired on the home page
    $('.tabbed-hero-tabs li:first-child').addClass('selected');
    $('.tabbed-hero').hide();
    $('.tabbed-hero:first').show();
    $('.mobile-hero-tabs li:first-child').addClass('selected')
    $('.mobile-hero-tabs li').hide();
    $('.mobile-hero-tabs li:first').show();
  },
  finalize() {
    // JavaScript to be fired on the home page, after the init JS
    const tabs = $('#left-arrow').attr('data-tab');
    const tabLength = tabs.charAt(tabs.length - 1)
    console.log(tabLength)

    $('.tabbed-hero-tabs li').on('click', function() {
      $('.tabbed-hero-tabs li').removeClass('selected')
      $(this).addClass('selected')
      $('.tabbed-hero').hide();

      var activeTab = $(this).find('span').attr('data-tab')
      $(activeTab).fadeIn();
      return false;
    })

    $('.mobile-arrow').on('click', function() {
      /* Set variables and hide objects */
      var activeTab = $(this).attr('data-tab')
      var activeTabNumber = activeTab.charAt(activeTab.length - 1);
      $('.mobile-hero-tabs li').removeClass('selected')
      $('.mobile-hero-tabs li').hide();
      $('.tabbed-hero').hide();

      /* Dring in the selected div */
      $(activeTab).fadeIn();
      $('.mobile-hero-tabs .header-' + activeTabNumber).addClass('selected').show()

      // Actions for a click on the left arrow
      if ( $(this).attr('id') == 'left-arrow') {
        // update the left arrow index
        let leftNumber = activeTab.charAt(activeTab.length - 1);
        (leftNumber == 1) ? leftNumber = tabLength : leftNumber--;
        $(this).attr('data-tab', '#tab-' + leftNumber);

        // Update the right arrow index
        let activeRightTab = $('#right-arrow').attr('data-tab')
        let rightNumber = activeRightTab.charAt(activeRightTab.length - 1);
        (rightNumber == 1) ? rightNumber = tabLength : rightNumber--;
        $('#right-arrow').attr('data-tab', '#tab-' + rightNumber);

      } else if ( $(this).attr('id') == 'right-arrow' ) {
        // update the right arrow index
        let rightNumber = activeTab.charAt(activeTab.length - 1);
        (rightNumber == tabLength) ? rightNumber = 1 : rightNumber++;
        $(this).attr('data-tab', '#tab-' + rightNumber);

        // Update the left arrow index
        let activeLeftTab = $('#left-arrow').attr('data-tab')
        let leftNumber = activeLeftTab.charAt(activeLeftTab.length - 1);
        (leftNumber == tabLength) ? leftNumber = 1 : leftNumber++;
        $('#left-arrow').attr('data-tab', '#tab-' + leftNumber);
      }
    })

    /** card tiles component */
    var card = $('.card');
    card.on('click', function() {
      $(this).toggleClass('active');
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

    $('.homepage-hero-slider').slick({
      dots: true,
      arrows: false,
      draggable: false,
      autoplay: true,
      autoplaySpeed: 8000,
    });
  },
};
