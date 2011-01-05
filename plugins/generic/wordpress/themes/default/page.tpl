{include file="common/header.tpl"}
	
	<div id="blogContent" class="narrowcolumn">
		<div class="post" id="post-{php} the_ID(); {/php}">
			<div class="entry">
				{php} the_content('<p class="serif">Read the rest of this page &raquo;</p>'); {/php}
			</div>
		</div>
	</div>
{include file="common/footer.tpl"}	