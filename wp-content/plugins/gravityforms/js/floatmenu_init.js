var name = "#floatMenu";
var menuYloc = null;
var disableFloat = false;
jQuery(document).ready(function(){
	menuYloc = parseInt(jQuery(name).css("top").substring(0,jQuery(name).css("top").indexOf("px")))
	jQuery(window).scroll(function () {
        if(disableFloat)
            return;
		offset = menuYloc+jQuery(document).scrollTop()+"px";
		jQuery(name).animate({top:offset},{duration:500,queue:false});
	});
});