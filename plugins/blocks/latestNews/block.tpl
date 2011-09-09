{**
 * block.tpl
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Most Popular Articles
 *
 *}
	{php}
	wp_reset_query();
	query_posts('&cat=5&year=2011&posts_per_page=1');
	{/php}
	{php} if (have_posts()) : {/php}
		<h2>{translate key="plugins.blocks.latestNews.displayTitle"}</h2>
		{php} while (have_posts()) : the_post(); {/php}

			<h3>{php} the_title(); {/php}</h3>
			{php} the_content(); {/php}

		{php} endwhile; {/php}

	{php} endif; {/php}

	{php}
	//Reset Query
	//wp_reset_query();
	{/php}
