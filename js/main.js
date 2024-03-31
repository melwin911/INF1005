(function($) {

	'use strict';

 
  $('.site-menu-toggle').click(function(){
    var $this = $(this);
    if ( $('body').hasClass('menu-open') ) {
      $this.removeClass('open');
      $('.js-site-navbar').fadeOut(400);
      $('body').removeClass('menu-open');
    } else {
      $this.addClass('open');
      $('.js-site-navbar').fadeIn(400);
      $('body').addClass('menu-open');
    }
  });

	
	$('nav.dropdown').hover(function(){
		var $this = $(this);
		$this.addClass('show');
		$this.find('> a').attr('aria-expanded', true);
		$this.find('.dropdown-menu').addClass('show');
	}, function(){
		var $this = $(this);
			$this.removeClass('show');
			$this.find('> a').attr('aria-expanded', false);
			$this.find('.dropdown-menu').removeClass('show');
	});

  $('#dropdown04').on('show.bs.dropdown', function () {
	  console.log('show');
	});

  $(document).ready(function() {
    // Initialize datepickers
    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true,
      todayHighlight: true,
      startDate: new Date()
    });

    // Form submission
    $('#availabilityForm').submit(function(event) {
      event.preventDefault();
      let formData = {
        checkin_date: $('#checkin_date').val(),
        checkout_date: $('#checkout_date').val(),
        rooms: $('#rooms').val(),
        guests: $('#guests').val()
      };

      displayAvailability(roomsData);

      $.ajax({
        type: 'POST',
        url: 'http://35.212.159.11/rooms',
        data: formData,
        success: function(response) {
          $('#availabilityResult').html(`<div class="alert alert-success" role="alert">${response}</div>`);
        },
      });
    });

    // Display availability
    function displayAvailability(availData) {
      let html = '<div class="alert alert-info" role="alert">Availability:</div>';
      availData.forEach(room => {
        html += `<div class="card mb-2">
                    <div class="card-body">
                        <h5 class="card-title">Room Type: ${room.type}</h5>
                        <p class="card-text">Available: ${room.available ? 'Yes' : 'No'}</p>
                        <p class="card-text">Price per night: ${room.price}</p>
                        <p class="card-text">Number of available rooms left: ${room.availability}</p>
                    </div>
                </div>`;
      });
      $('#availabilityResult').html(html);
    }

    // Dynamic room/pax selection
    $('#rooms').change(function() {
      let rooms = parseInt($(this).val());
      let maxGuests = rooms * 4; // Assuming max 4 guests per room
      $('#guests option').each(function() {
        let value = parseInt($(this).val());
        $(this).prop('disabled', value > maxGuests);
      });
    });

  });
	

	// home slider
	$('.home-slider').owlCarousel({
    loop:true,
    autoplay: true,
    margin:10,
    animateOut: 'fadeOut',
    animateIn: 'fadeIn',
    nav:true,
    autoplayHoverPause: true,
    items: 1,
    autoheight: true,
    navText : ["<span class='ion-chevron-left'></span>","<span class='ion-chevron-right'></span>"],
    responsive:{
      0:{
        items:1,
        nav:false
      },
      600:{
        items:1,
        nav:false
      },
      1000:{
        items:1,
        nav:true
      }
    }
	});


  var siteStellar = function() {
    $(window).stellar({
      responsive: false,
      parallaxBackgrounds: true,
      parallaxElements: true,
      horizontalScrolling: false,
      hideDistantElements: false,
      scrollProperty: 'scroll'
    });
  }
  siteStellar();

  var smoothScroll = function() {
    var $root = $('html, body');

    $('a.smoothscroll[href^="#"]').click(function () {
      $root.animate({
        scrollTop: $( $.attr(this, 'href') ).offset().top
      }, 500);
      return false;
    });
  }
  smoothScroll();

  var dateAndTime = function() {
    $('#m_date').datepicker({
      'format': 'm/d/yyyy',
      'autoclose': true
    });
    $('#checkin_date, #checkout_date').datepicker({
      'format': 'd MM, yyyy',
      'autoclose': true
    });
    $('#m_time').timepicker();
  };
  dateAndTime();


  var windowScroll = function() {

    $(window).scroll(function(){
      var $win = $(window);
      if ($win.scrollTop() > 200) {
        $('.js-site-header').addClass('scrolled');
      } else {
        $('.js-site-header').removeClass('scrolled');
      }

    });

  };
  windowScroll();


  var goToTop = function() {

    $('.js-gotop').on('click', function(event){
      
      event.preventDefault();

      $('html, body').animate({
        scrollTop: $('html').offset().top
      }, 500, 'easeInOutExpo');
      
      return false;
    });

    $(window).scroll(function(){

      var $win = $(window);
      if ($win.scrollTop() > 200) {
        $('.js-top').addClass('active');
      } else {
        $('.js-top').removeClass('active');
      }

    });  
  };

  let roomsData = [
    { type: 'Single Room', available: true, price: '$90', availability: '4' },
    { type: 'Family Room', available: true, price: '$120', availability: '2' },
    { type: 'Presidential Room', available: true, price: '$250', availability: '2' },
    { type: 'Courtyard Room', available: true, price: '$150', availability: '4' },
    { type: 'Quay Room', available: true, price: '$200', availability: '1' },
    { type: 'Presidential Suite', available: true, price: '$350', availability: '1' },
    { type: 'Executive Suite', available: true, price: '$450', availability: '2' },
  ];

})(jQuery);

function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
}
