jQuery.noConflict();

/**** Cache problem LESS files ***/
/*function destroyLessCache(templates/e4jeasyhiring/css) { // e.g. '/css/' or '/stylesheets/'
 
  if (!window.localStorage || !less || less.env !== 'development') {
	return;
  }
  var host = window.location.host;
  var protocol = window.location.protocol;
  var keyPrefix = protocol + '//' + host + pathToCss;
  
  for (var key in window.localStorage) {
	if (key.indexOf(keyPrefix) === 0) {
	  delete window.localStorage[key];
	}
  }
}
/*** End Less Problem ***/

var resp_menu_on = false;
//In windows and in Opera this can cause an issue
var scroll_bounce = (window.chrome !== null && typeof window.chrome !== "undefined") || typeof window.opr !== "undefined";

function vikShowResponsiveMenu() {
	jQuery(".nav-menu-active").toggle();
	jQuery(".e4j-body-page").toggleClass("e4j-body-fixed e4j-body-shifted");
	if(jQuery(".e4j-body-page").hasClass("e4j-body-shifted")) {
		resp_menu_on = true;
	}else {
		resp_menu_on = false;
	}
}


function vikcs_adapter() {
	if (jQuery("#vikcs-slider")) {
		var vcsl_height = jQuery("#vikcs-slider").css("height");
		if (parseInt(vcsl_height) > 0) {
			jQuery("#slideadv").css("height", vcsl_height);
		}
	}
}

function getUrlVar(key){
	var result = new RegExp(key + "=([^&]*)", "i").exec(window.location.search); 
	return result && unescape(result[1]) || ""; 
}


jQuery(document).ready(function() {

	/*** Clone the Language Active for inline languages display ***/
	jQuery( ".dropalt .lang-active a" ).clone().appendTo( "#e4jcurrent_lang" );
	var lang_mmenu = jQuery( "#lmpart .moduletable").hasClass( "dropmenu" );
	if(lang_mmenu === true ) {
		jQuery( "#lmpart .dropmenu .lang-active a" ).clone().appendTo( "#lmpart #e4jcurrent_lang" );
		jQuery( "#lmpart .dropmenu .lang-active a" ).clone().appendTo( ".nav-devices-list #e4jcurrent_lang" );
	}
	
	var lang_blocked = false;
	jQuery('.dropalt').hover(function() {
		jQuery(this).addClass("parent-open");
	}, function() {
		if(!lang_blocked) {
			jQuery(this).removeClass("parent-open");
		}
	});
	jQuery('.dropmenu').hover(function() {
		jQuery(this).addClass("parent-open");
	}, function() {
		if(!lang_blocked) {
			jQuery(this).removeClass("parent-open");
		}
	});

	/*** Show on scroll **/
    jQuery(window).scroll( function(){
    
        /* Check the location of each desired element */
        jQuery('#main-inner img, #module-box2 img, #full-up img, #upcontent, #module-box1 img, #subcontent img, #module-box3 img, #fullbox img').each( function(i){
            
            var quarter_of_object = jQuery(this).offset().top + jQuery(this).outerHeight()/6;
            //var top_of_object = jQuery(this).offset().top;
            var bottom_of_window = jQuery(window).scrollTop() + jQuery(window).height();
            
            /* If the object is completely visible in the window, fade it it */
            if( bottom_of_window > quarter_of_object ){
                
               jQuery(this).animate({'opacity':'1'},300);
                    
            }
            
        }); 
    
    });

    jQuery(window).trigger('scroll');
    /*** --End-- Show on scroll **/

    /*** If slider is on Windows mode calc the height of the top header ***/
	var head_height = jQuery("#headt-part").height();
	//jQuery(".contentheader-topfix").css('margin-top', '-'+head_height+'px');
	//console.log(head_height, jQuery(".contentheader-topfix").css('margin-top'));

	var searchmod_height = jQuery(".h-search").height();

	vikcs_adapter();

	/**** Menu MAINMENU position ***/
	var screen = jQuery(window).width();
	if (screen <= 1280) {
		jQuery("#nav-menu-devices").addClass("nav-menu-active");
		//jQuery(".contentheader-topfix").css('margin-bottom', ''+searchmod_height+'px');
	}
	jQuery(window).resize(function() {
		var width = jQuery(window).width();
		if (width <= 1280) {
			jQuery("#nav-menu-devices").addClass("nav-menu-active");
		} else {
			jQuery("#nav-menu-devices").removeClass("nav-menu-active").removeAttr("style");
		}
		vikcs_adapter();
	});

	var screen = jQuery(window).width();
	if (screen <= 1480) {
		jQuery("#mainmenu .loginmenu").addClass("e4jsign-rsz");
	}
	jQuery(window).resize(function() {
		var width = jQuery(window).width();
		if (width <= 1480) {
			jQuery("#mainmenu .loginmenu").addClass("e4jsign-rsz");
		} else {
			jQuery("mainmenu .loginmenu").removeClass("e4jsign-rsz").removeAttr("style");
		}
		vikcs_adapter();
	});

	jQuery(window).resize(function() {
		var searchmod_height = jQuery(".h-search").height();
		var width = jQuery(window).width();
		if (width <= 1280) {
			jQuery(".contentheader-topfix").css('margin-bottom', ''+searchmod_height+'px');
		} else {
			jQuery(".contentheader-topfix").css('margin-bottom', '0px');
		}
	});

	/** Class fixed menu ***/

	var menu_selector = jQuery(".headfixed");
	var container_sticky = jQuery('.fixedmenu');
	var change_to_sticky = true;
	var lim_pos = 90;
	if(menu_selector.length) {
		lim_pos = menu_selector.height();
	}
	jQuery(window).scroll(function() {
		var scrollpos = jQuery(window).scrollTop();
		if (scrollpos > lim_pos) {
			if (change_to_sticky === true) {

				container_sticky.toggleClass("fx-menu-slide");
				change_to_sticky = false;
              	
              	//In windows and in Opera this can cause an issue
				/*if(scroll_bounce) {
					jQuery(window).scrollTop(jQuery(window).scrollTop() + (lim_pos + 1));
				}*/
				
			}
		} else {
			if ((change_to_sticky === false) && (scrollpos == 0) ) {
				container_sticky.toggleClass("fx-menu-slide");
				change_to_sticky = true;
               
               	//In windows and in Opera this can cause an issue
				/*if(scroll_bounce) {
					jQuery(window).scrollTop(jQuery(window).scrollTop() - (lim_pos + 1));
				}*/
			}
		}
	});

	var login_blocked = false;
	jQuery("#mainmenu li.parent, .loginmenu, .topmenu li.parent, .modopen").hover(function() {
		if(!login_blocked) {
			jQuery(this).addClass("parent-open");
		}
	}, function() {
		if(!login_blocked) {
			jQuery(this).removeClass("parent-open");
		}
	});
	jQuery(".loginmenu .modlgn-username, .loginmenu .modlgn-passwd").focus(function() {
		if( jQuery(this).is(':visible') ) {
			login_blocked = true;
		}
	});

	jQuery(document).mouseup(function (e) {
		//Responsive Menu
		var resp_menu_cont = jQuery("#nav-menu-devices");
		var resp_menu_button = jQuery("#menumob-btn-ico");
		if(resp_menu_on && !resp_menu_cont.is(e.target) && resp_menu_cont.has(e.target).length === 0  && !resp_menu_button.is(e.target) && resp_menu_button.has(e.target).length === 0) {
			resp_menu_on = false;
			jQuery(".nav-menu-active").hide();
			jQuery(".e4j-body-page").removeClass("e4j-body-fixed e4j-body-shifted");
			jQuery("#menumob-btn-ico").removeClass("open");
		}
		//Login dropdown
		var login_container = jQuery(".loginmenu");
		if (!login_container.is(e.target) && login_container.has(e.target).length === 0) {
			login_blocked = false;
			login_container.removeClass(".parent-open");
			login_container.trigger("mouseout");
		}
	});

	/**** Menu SUBMENU position ***/
	var screen = jQuery(window).width();
	if (screen <= 860) {
		jQuery("#submenu .l-inline").addClass("menumobile");
	}
	jQuery(window).resize(function() {
		var width = jQuery(window).width();
		if (width <= 860) {
			jQuery("#submenu .l-inline").addClass("menumobile");
		} else {
			jQuery("#submenu .l-inline").removeClass("menumobile").removeAttr("style");
		}
	});

	jQuery("#submenu li.parent").hover(function() {
		jQuery(this).find(".l-block:first").stop(true, true).delay(50).slideDown(400);
	}, function() {
		jQuery(this).find(".l-block:first").stop(true, true).slideUp(600);
	});


	/**** Login Tab ***/
	jQuery(".logintab h3").click(function() {
		jQuery(this).toggleClass('logintabopened');
		jQuery(".logintab .form-inline").fadeToggle();
	});
	
	jQuery(document).mouseup(function(e) {
		var logintabcontainer = jQuery(".logintab .form-inline");
		if (!logintabcontainer.is(e.target) && logintabcontainer.has(e.target).length === 0) {
			logintabcontainer.hide();
			jQuery(".logintab h3").removeClass("logintabopened");
		}
	});  

	jQuery('#menumob-btn-ico').click(function(){
		jQuery(this).toggleClass('open');
	});

	/**** SYSTEM MESSAGE DISMISS ****/
	jQuery('#system-message a.close').on('click', function(){
		jQuery('#system-message').fadeOut();
	});
});