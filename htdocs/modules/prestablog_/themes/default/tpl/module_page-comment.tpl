{*
*  @author HDClic
*  @copyright  2007-2013 www.hdclic.com
*  @version  Release: $Revision: 1.5 $
*}

<!-- Module Presta Blog -->
{if sizeof($comments)}
<h3><span id="toggle-comments">{l s='View all comments' mod='prestablog'}</span>&nbsp;<small>({count($comments)})</small></h3>
<div id="comments">
{foreach from=$comments item=comment name=Comment}
	<div class="comment">
		<h4>
			{if $comment.url}
				<a href="{$comment.url}" {if $link_nofollow}rel="nofollow"{/if}>{$comment.name}</a>
			{else}
				{$comment.name}
			{/if}
		</h4>
		<hr />
		<p class="date-comment">{dateFormat date=$comment.date full=1}</p>
		<p>{$comment.comment}</p>
	</div>
{/foreach}
</div>
<script type="text/javascript">{literal}$(document).ready(function() { $('#toggle-comments').click(function() { $('#comments').toggle("slow"); }); });{/literal}</script>
{/if}
{if ($comment_only_login && $logged) || !$comment_only_login}
	{if !$isSubmit}
		<form action="{$LinkReal}&id={$News->id}" method="post" class="std">
			<fieldset id="prestablog-comment">
				<h3>
					{l s='Add a comment' mod='prestablog'}
					{if $Mail_Subscription}
						<div id="abo">
						{if $Is_Subscribe}
							<a href="{$LinkReal}&d={$News->id}">{l s='Stop my subscription to comments' mod='prestablog'}</a>
						{else}
							<a href="{$LinkReal}&a={$News->id}">{l s='Subscribe to comments' mod='prestablog'}</a>
						{/if}
						</div>
					{/if}
				</h3>
				{if sizeof($errors)}
				<p id="errors">{foreach from=$errors item=Ierror name=errors}{$Ierror}<br />{/foreach}</p>
				<script type="text/javascript">{literal}$(document).ready(function() { $('html, body').animate({scrollTop: $("#errors").offset().top}, 750); });{/literal}</script>
				{/if}
				<p class="text">
					<label for="name">{l s='Name' mod='prestablog'}</label>
					<input type="text" class="text{if sizeof($errors) && array_key_exists('name', $errors)} errors{/if}" name="name" id="name" value="{$content_form.name}" />
				</p>
				<p class="text">
					<label for="url">{l s='Url' mod='prestablog'}</label>
					<input type="text" class="text{if sizeof($errors) && array_key_exists('url', $errors)} errors{/if}" name="url" id="url" value="{$content_form.url}" />&nbsp;<small id="with-http">({l s='with http://' mod='prestablog'})</small>
				</p>
				<p class="textarea">
					<label for="comment">{l s='Comment' mod='prestablog'}</label>
					<textarea name="comment" id="comment" cols="26" rows="3" {if sizeof($errors) && array_key_exists('comment', $errors)}class="errors"{/if}>{$content_form.comment}</textarea>
				</p>
				<p class="submit">
					<input type="submit" class="button" name="submitComment" id="submitComment" value="{l s='Submit comment' mod='prestablog'}" />
				</p>
			</fieldset>
		</form>
		<script type="text/javascript">
			{literal}
			$(document).ready(function() { 
				$("#with-http").hide();
				$("#url").focus(function() {
					$("#with-http").fadeIn();
				});
				$("#url").focusout(function() {
					$("#with-http").fadeOut();
				});
			});
			{/literal}
		</script>
	{else}
		<form id="submitOk" class="std">
			<fieldset>
				<h3>{l s='Your comment is successful send' mod='prestablog'}</h3>
				{if $comment_auto}
				<p>{l s='This comment is automatically published.' mod='prestablog'}</p>
				{else}
				<p>{l s='Before published, your comment must be approve by an administrator.' mod='prestablog'}</p>
				{/if}
			</fieldset>
		</form>
		<script type="text/javascript">{literal}$(document).ready(function() { $('html, body').animate({scrollTop: $("#submitOk").offset().top}, 750); });{/literal}</script>
	{/if}
{else}
	<form class="std" >
		<fieldset id="prestablog-comment-register">
			<h3>{l s='You must be register' mod='prestablog'}</h3>
			<p style="text-align:center;">
				<a href="authentication.php">{l s='Clic here to register' mod='prestablog'}</a>
			</p>
		</fieldset>
		<fieldset id="prestablog-comment">
			<h3>
				{l s='Add a comment' mod='prestablog'}
				{if $Mail_Subscription}
					<div id="abo">
						<a>{l s='Subscribe to comments' mod='prestablog'}</a>
					</div>
				{/if}
			</h3>
			<p class="text">
				<label for="name">{l s='Name' mod='prestablog'}</label>
				<input type="text" class="text" name="name" id="name" value="" />
			</p>
			<p class="text">
				<label for="url">{l s='Url' mod='prestablog'}</label>
				<input type="text" class="text" name="url" id="url" value="" />
			</p>
			<p class="textarea">
				<label for="comment">{l s='Comment' mod='prestablog'}</label>
				<textarea name="comment" id="comment" cols="26" rows="3" ></textarea>
			</p>
			<p class="submit">
				<input type="submit" class="button" value="{l s='Submit comment' mod='prestablog'}" />
			</p>
		</fieldset>
	</form>
	<script type="text/javascript">
		{literal}
		$(document).ready(function() { 
			$("form.std").submit(function() {
				return false;
			});
			$("#prestablog-comment-register").hide();
			$("#prestablog-comment").click(function() {
				$("#prestablog-comment").fadeOut();
				$("#prestablog-comment-register").fadeIn();
			});
		});
		{/literal}
	</script>
{/if}
<!-- /Module Presta Blog -->
