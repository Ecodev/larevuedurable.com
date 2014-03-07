<script type="text/javascript">

	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '{$ganalyticsID}']);
	{if isset($ganalitics_domaine_cookie)}_gaq.push(['_setDomainName', '{$ganalitics_domaine_cookie}']);{/if}
	
	{if !isset($step) }
		_gaq.push(['_trackPageview']);
	{else}
		{if $step==1}
		//send('order/step1.html');
		{/if}
		_gaq.push(['_trackPageview','order/step{$step}.html']);
	{/if}
	
	

	
	
	{ literal}
		if(logged==true){
			_gaq.push(['_setCustomVar', 1, 'Client_connecte', 'Oui', 3]);
		}else{
			_gaq.push(['_setCustomVar', 1, 'Client_connecte', 'Non', 3]);
		}
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	{/literal}

</script>
