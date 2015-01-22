function scrollToPosition(clickedElement, options)
{
	//clickedElement, scrollingDivId, spottedAnchor, duration, easing, offset, callback

	var href = 	$(clickedElement).attr('href');

	href =  href.substring(1, href.length);

	if(!options.spottedAnchor) 	
		var cible = href;
	else 						
		var cible = options.spottedAnchor;			
				
	if(options.alias) 			
		cible = cible +""+ options.alias;
	

	if($(cible).length>0) 		
		var name=cible;
	else 						
		var name = "a[name="+cible.substr(1,cible.length-1)+ "]";
	
	console.info( cible );
	console.info( name );

	
	if($(name).length>0){ // evite les erreurs dûes à l'absence d'éléments possedant le paramètres requis
		if(!options.scrollingDivId) {
			var target = 'html,body'; 
			var top=$(name).offset().top;
			var left=$(name).offset().left;
			
		}else {
			var target = options.scrollingDivId;
			var top = $(name).position().top + $(options.scrollingDivId)[0].scrollTop;
			var left = $(name).position().left + $(options.scrollingDivId)[0].scrollLeft;
		}
		
		var duration = 	(!options.duration) ? 	1000 			: options.duration;
		var easing = 	(!options.easing)   ?	'easeOutQuint' 	: options.easing;
		var offset = 	(!options.offset)   ?	0 				: options.offset;
		var callback = 	(!options.callback) ?	null 			: options.callback;

		$(target).animate({scrollTop:top+offset, scrollLeft:left},duration,easing, callback);
	}

}