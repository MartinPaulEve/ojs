	<div class="block" id="wordpessSidebar">
		<ul style="list-style:none; padding-left:0px;">
			{php} 	/* Widgetized sidebar, if you have the plugin installed. */
					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : {/php}
			<li>
				{php} get_search_form(); {/php}
			</li>

			<!-- Author information is disabled per default. Uncomment and fill in your details if you want to use it.
			<li><span class="blockTitle">Author</span>
			<p>A little something about you, the author. Nothing lengthy, just an overview.</p>
			</li>
			-->

			{php} if ( is_404() || is_category() || is_day() || is_month() ||
						is_year() || is_search() || is_paged() ) {
			{/php} <li>

			{php} /* If this is a 404 page */ if (is_404()) { {/php}
			{php} /* If this is a category archive */ } elseif (is_category()) { {/php}
			<p>You are currently browsing the archives for the {php} single_cat_title(''); {/php} category.</p>

			{php} /* If this is a yearly archive */ } elseif (is_day()) { {/php}
			<p>You are currently browsing the <a href="{php} bloginfo('url'); {/php}/">{php} echo bloginfo('name'); {/php}</a> blog archives
			for the day {php} the_time('l, F jS, Y'); {/php}.</p>

			{php} /* If this is a monthly archive */ } elseif (is_month()) { {/php}
			<p>You are currently browsing the <a href="{php} bloginfo('url'); {/php}/">{php} echo bloginfo('name'); {/php}</a> blog archives
			for {php} the_time('F, Y'); {/php}.</p>

			{php} /* If this is a yearly archive */ } elseif (is_year()) { {/php}
			<p>You are currently browsing the <a href="{php} bloginfo('url'); {/php}/">{php} echo bloginfo('name'); {/php}</a> blog archives
			for the year {php} the_time('Y'); {/php}.</p>

			{php} /* If this is a monthly archive */ } elseif (is_search()) { {/php}
			<p>You have searched the <a href="{php} echo bloginfo('url'); {/php}/">{php} echo bloginfo('name'); {/php}</a> blog archives
			for <strong>'{php} the_search_query(); {/php}'</strong>. If you are unable to find anything in these search results, you can try one of these links.</p>

			{php} /* If this is a monthly archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { {/php}
			<p>You are currently browsing the <a href="{php} echo bloginfo('url'); {/php}/">{php} echo bloginfo('name'); {/php}</a> blog archives.</p>

			{php} } {/php}

			</li> {php} }{/php}

			{php} wp_list_pages('title_li=<span class="blockTitle">Pages</span>' ); {/php}

			<li><span class="blockTitle">Archives</span>
				<ul>
				{php} wp_get_archives('type=monthly'); {/php}
				</ul>
			</li>

			{php} wp_list_categories('show_count=1&title_li=<span class="blockTitle">Categories</span>'); {/php}

			{php} endif; {/php}
		</ul>
	</div>

