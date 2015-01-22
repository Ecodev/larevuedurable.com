
/*
Easings :

easeInQuad
easeOutQuad
easeInOutQuad
easeInCubic
easeOutCubic
easeInOutCubic
easeInQuart
easeOutQuart
easeInOutQuart
easeInQuint
easeOutQuint
easeInOutQuint
easeInSine
easeOutSine
easeInOutSine
easeInExpo
easeOutExpo
easeInOutExpo
easeInCirc
easeOutCircc
easeInOutCirc
easeInElastic
easeOutElastic
easeInOutElastic
easeInBack
easeOutBack
easeInOutBack
easeInBounce
easeOutBounce
easeInOutBounce
*/



// mouvements pour les animations
var easingPage = 'easeOutQuint' ; //'easeInOutSine';//'easeOutQuint';
var changePageTime = 300;
var scrollPageTime = 2000;
var scrollTooltime = 1200;
var menuMoveTime = 700;



$(document).ready(function() 
{

	$('#menu').adaptativeMenu();

	// capture les événements venant des <a href='#...'>
	addClickEvents();

	// va a la page courante selon l'url transmise avec l'ancre #...
	goToCurrentPage();

	// ajoute la détection de l'événement pour le changement de l'url
	document.location.oldhref = document.location.href;
	$(window).focus(function(){
		if(document.location.oldhref != document.location.href) {
			document.location.oldhref = document.location.href;
			if(document.location.onchange) document.location.onchange();
		 }		
	});
	
});

document.location.onchange = function() 
{
	goToCurrentPage();
};




function goToCurrentPage()
{
	var page = document.location.hash;
	if(page != '')
	{
		page = page.substr(2, page.length-1);
		$('#contenu').load( page, addClickEvents );
	}

}






function addClickEvents()
{	


	$("a[href*='#']").unbind().on('click', function(e)
	{
        e.preventDefault();
		if( $(this).attr('href').substring(0,2) == '##' )
		{
            gotoAnchor($(this));
		}
		else 
		{
            gotoPage($(this));
		}
	});
	
	Shadowbox.init({
	    handleOversize: "drag",
	    modal: false
	});
 	Shadowbox.setup();
	
}





function gotoAnchor( $element )
{

	// options : clickedElement, scrollingDivId, spottedAnchor, duration, easing, offset, callback
	var optionsPage = {
		clickedElement :null,
		scrollingDivId: null, 
		spottedAnchor: null, 
		duration: scrollPageTime, 
		easing: easingPage
		,offset: -140
		//,callback: function(){scrollToPosition(self,optionsLittle);},
	}
	
	scrollToPosition($element, optionsPage);
}




function gotoPage(element)
{	

	var page = element.attr('href');
	document.location.hash = page;
	page = page.substr(2, page.length-1);	
	$('#contenu').load( page, addClickEvents );
	
	
	
		
	// scroll les tools
//	var optionsTools = {
//		scrollingDivId: '#tools',
//		duration : scrollTooltime,
//		easing: easingTools,
//		alias:'2'
//	}
//		
	// affiche la bonne page
//	$('.visible').each(function(){
//		$(this).removeClass('visible');
//		$(this).fadeOut(changePageTime, function(){
//			$('#'+page).addClass('visible');
//			$('#'+page).fadeIn(changePageTime, function(){
//				scrollToPosition($(element)[0], optionsTools);
//			});
//		});
//		
//	});
	
}







