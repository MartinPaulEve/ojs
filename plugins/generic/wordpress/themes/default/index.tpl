{include file="common/header.tpl"}

	<div id="blogContent" class="narrowcolumn">

	{php} if (have_posts()) : {/php}

		{php} while (have_posts()) : the_post(); {/php}

			<div {php} post_class() {/php} id="post-{php} the_ID(); {/php}">
				<h2><a href="{php} the_permalink() {/php}" rel="bookmark" title="Permanent Link to {php} the_title_attribute(); {/php}">{php} the_title(); {/php}</a></h2>
				<small>{php} the_time('F jS, Y') {/php} <!-- by {php} the_author() {/php} --></small>

				<div class="entry">
					{php} the_content('Read the rest of this entry &raquo;'); {/php}
				</div>

				<p class="postmetadata">{php} the_tags('Tags: ', ', ', '<br />'); {/php} Posted in {php} the_category(', ') {/php} | {php} edit_post_link('Edit', '', ' | '); {/php}  {php} comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); {/php}</p>
			</div>

		{php} endwhile; {/php}

		<div class="blogNavigation">
			<div class="alignleft">{php} next_posts_link('&laquo; Older Entries') {/php}</div>
			<div class="alignright">{php} previous_posts_link('Newer Entries &raquo;') {/php}</div>
		</div>

	{php} else : {/php}

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>
		{php} get_search_form(); {/php}

	{php} endif; {/php}

	</div>	

{include file="common/footer.tpl"}