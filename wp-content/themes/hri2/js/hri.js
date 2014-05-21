
var $=jQuery.noConflict();

$(document).ready(function() {

	var $=jQuery.noConflict();

	$("a[href*='#']").click(function() {

		if( jQuery(this).attr("href") != "#") {
			jQuery.scrollTo(jQuery(this).attr("href").substr(jQuery(this).attr("href").indexOf("#")), 400);
		}

	});

	$('blockquote').append( $('<div class="circle-icon-quote"></div>') ).each(function(){

		if($(this).find('p').size() > 0) {
			$(this).find('p:last').append( $('<div class="small-icon-quote"></div>') );
		} else {
			$(this).append( $('<div class="small-icon-quote"></div>') );
		}

	});

	$('.infobox-row').each(function(){

		var max_h=0;

		$(this).find('.infobox').each(function(){
			if( $(this).height() > max_h ) max_h = $(this).height();
		});

		$(this).find('.infobox').height( max_h );

	});

	// style checkboxes
	function style_hri_inputs(){

		$('input.cb').each(function() {
			$(this).wrap(function() {
				return ($(this).is(':checked')) ? '<div class="hri_checkbox hri_checkbox_checked" />' : '<div class="hri_checkbox" />';
			});
		});

		$('input.radio').each(function() {
			$(this).wrap(function() {
				return ($(this).is(':checked')) ? '<div class="hri_radio hri_radio_checked" />' : '<div class="hri_radio" />';
			});
		});

		$('.hri_checkbox input').click(function () {
			$(this).parent().toggleClass('hri_checkbox_checked');
		});


		$('.hri_radio input').click(function () {
			$(this).parent().siblings().removeClass('hri_radio_checked');
			$(this).parent().addClass('hri_radio_checked');
		});

		$('input.cb,input.radio').focus(function(){
			$(this).parent().addClass('hri_focus');
		}).blur(function(){
			$(this).parent().removeClass('hri_focus');
		});


	}

	style_hri_inputs();

	$('html.old-ie input.radio, html.old-ie input.cb').fadeTo(1,0.001);

});

function scroll_nav($) {

	if( $('html').hasClass('no-touch') ) {

		if ($(window).scrollTop() < 128) {
			$('#main-nav-c').removeClass( 'new-scroll-nav' );
			$('.top-scroll').removeClass( 'top-scroll-visible' );
		} else {
			$('#main-nav-c').addClass( 'new-scroll-nav' );
			$('.top-scroll').addClass( 'top-scroll-visible' );
		}

		if( $(window).scrollTop() < ( $('footer').offset().top - parseInt( $(window).height(), 10 ) ) ){
			$('.top-scroll').removeClass( 'top-scroll-bottom' );
		} else {
			$('.top-scroll').addClass( 'top-scroll-bottom' );
		}

	}

}



jQuery(window).scroll(function(){
	scroll_nav(jQuery);
});