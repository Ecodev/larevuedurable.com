{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}

<!-- Module Presta Blog -->
<div class="block">
	<h4 class="title_block">{l s='Blog archives' mod='prestablog'}</h4>
	<div class="block_content" id="prestablog_dateliste">
		{if $ResultDateListe}
			<ul>
			{foreach from=$ResultDateListe key=KeyAnnee item=ValueAnnee name=loopAnnee}
				<li>
					<a href="#" class="prestablog_annee" {if count($ResultDateListe)<=1}style="display:none;"{/if}>{$KeyAnnee}&nbsp;<span>({$ValueAnnee.nombre_news})</span></a>
					<ul class="prestablog_mois {if (isset($prestablog_annee) && $prestablog_annee==$KeyAnnee)}prestablog_show{/if}">
				{foreach from=$ValueAnnee.mois key=KeyMois item=ValueMois name=loopMois}
						<li>
							<a href="{PrestaBlogUrl y=$KeyAnnee m=$KeyMois}">{$ValueMois.mois_value}&nbsp;<span>({$ValueMois.nombre_news})</span></a>
						</li>
				{/foreach}
					</ul>
				</li>
			{/foreach}
			</ul>
		{else}
			<p>{l s='No news' mod='prestablog'}</p>
		{/if}
		{if $link_datenews_showall}
		<p>
			<a href="{PrestaBlogUrl}" class="button_large">{l s='See all' mod='prestablog'}</a>
		</p>
		{/if}
	</div>
</div>
<script type="text/javascript">
	{literal}
	$(document).ready(function() {
		$('.prestablog_mois').hide();
		
		if ( $('ul.prestablog_mois').hasClass('prestablog_show') )
			$('.prestablog_show').show();
		else
			$('.prestablog_annee:first').next().show();
		
		$('.prestablog_annee').click(function(){
			if( $(this).next().is(':hidden') ) {
				$('.prestablog_annee').next().slideUp();
				$(this).next().slideDown();
			}
			return false;
		});
	});
	{/literal}
</script>
<!-- /Module Presta Blog -->
