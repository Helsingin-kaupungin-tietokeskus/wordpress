if(typeof $=='undefined') {
	var $=jQuery.noConflict();
}

$(document).ready(function($) {

	$('input[placeholder]').each(function(){

		if( $(this).val() == '' ) $(this).parent().append( $('<span class="hri-ie-placeholder">'+$(this).attr('placeholder')+'</span>') );

	}).focus(function(){

		$(this).siblings('.hri-ie-placeholder').hide();

	}).blur(function(){

		if( $(this).val() == '' ) $(this).siblings('.hri-ie-placeholder').show();

	});

	$(document).on( 'click', '.hri-ie-placeholder', function() {

		$(this).siblings('input[type="text"],input[type="email"]').focus();

	});

});