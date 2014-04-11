{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}
<!-- Module Presta Blog START PAGE -->



<div id="page" class="clearfix container_9">

	<div id="columns" class="grid_9 alpha omega clearfix">
		<!-- Left -->
		<div id="left_column" class="column grid_2 alpha">
			{$HOOK_LEFT_COLUMN_BLOG}
		</div>

		<!-- Center -->
		<div id="center_column" class="omega grid_7">
			


{*if isset($tpl_menu_cat) && $tpl_menu_cat}{$tpl_menu_cat}{/if*}
{if isset($tpl_unique) && $tpl_unique}{$tpl_unique}{/if}
{if isset($tpl_comment) && $tpl_comment}{$tpl_comment}{/if}

{if isset($tpl_slide) && $tpl_slide}{$tpl_slide}{/if}
{if isset($tpl_all) && $tpl_all}{$tpl_all}{/if}

<!-- /Module Presta Blog END PAGE -->

<ul id='usefull_link_block'>
    {$HOOK_END_PAGE_BLOG}
</ul>