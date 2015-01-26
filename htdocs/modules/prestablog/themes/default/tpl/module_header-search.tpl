{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}

{if isset($smarty.get.search_query)}
<!-- Module Presta Blog -->
	<script type="text/javascript">
	{literal}
		$(document).ready(function(){
			$.ajax({
				type: "GET",
				url: "{/literal}{PrestaBlogAjaxSearchUrl}{literal}?do=search&req={/literal}{$smarty.get.search_query}{literal}",
				dataType : "html",
				error:function(msg, string){ alert( "Error !: " + string ); },
				success:function(data){
					if(data)
						$("div#center_column").append(data);
				}
			});
		});
	{/literal}
	</script>
<!-- /Module Presta Blog -->
{/if}
