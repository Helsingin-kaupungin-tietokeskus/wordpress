var $=jQuery.noConflict();
$(document).ready(function() {
	$("a[href*='#']").click(function() {
		var $=jQuery.noConflict();
		if($(this).attr("href") != "#") {
			//alert($(this).attr("href").substr($(this).attr("href").indexOf("#")));
			$.scrollTo($(this).attr("href").substr($(this).attr("href").indexOf("#")), 400);
		}
	});
});
