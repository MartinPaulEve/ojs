{include file="common/header.tpl"}
	<div id="blogContent" class="widecolumn">

	{if !$noPosts}

		<div class="blogNavigation">
			<div class="alignleft">{php} previous_post_link('&laquo; %link', 'previous post', false, '4') {/php}</div>
			<div class="alignright">{php} next_post_link('%link &raquo;', 'next post',  false, '4') {/php}</div>
		</div>
		<br />
		<br />
		<div {php} post_class() {/php} id="post-{php} the_ID(); {/php}">
			<div class="entry">
				{php} the_content('<p class="serif">More &raquo;</p>'); {/php}

				{php} wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); {/php}
				{php} the_tags( '<p>Tags: ', ', ', '</p>'); {/php}

				<p class="postmetadata alt">
					<small>
						This entry was posted
						{php} /* This is commented, because it requires a little adjusting sometimes.
							You'll need to download this plugin, and follow the instructions:
							http://binarybonsai.com/archives/2004/08/17/time-since-plugin/ */
							/* $entry_datetime = abs(strtotime($post->post_date) - (60*120)); echo time_since($entry_datetime); echo ' ago'; */ {/php}
						on {php} the_date('l, jS F Y') {/php} at {php} the_time() {/php}
						and is filed under {php} the_category(', ') {/php}.
						You can follow any responses to this entry through the {php} post_comments_feed_link('RSS 2.0'); {/php} feed.

						{php} edit_post_link('Edit this entry','','.'); {/php}

					</small>
				</p>

			</div>
		</div>


		{php} comments_template(); {/php}

	{else}

		<p>Sorry, no posts matched your criteria.</p>

	{/if}

	</div>

{include file="common/footer.tpl"}