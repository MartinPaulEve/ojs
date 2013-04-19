{**
 * block.tpl
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Most Popular Articles
 *
 *}
<table width="100%">
	<tr>
		<td width="50%" valign="top"`>
	<h2>{translate key="plugins.blocks.recentArticles.displayTitle"}</h2>
	{foreach name=sections from=$recentArticles item=section key=sectionId}
		{foreach from=$section.articles item=article}
			{assign var="recentArticleIssue" value=$issueDao->getIssueByArticleId($article->getArticleId())}
	<div class="feature">
<div class="articleTitle"><a class="articleLink" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">{$article->getLocalizedTitle()|strip|escape:"html"}</a></div>
			<div id="authorString"><em>{translate key="plugins.blocks.recentArticles.by"} {$article->getAuthorString()|escape:"html"}</em></div>

			{$recentArticleIssue->getIssueIdentification()}, {$article->getPages()}<br />
			{** {$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}<br /> **}

			{assign var=galleys value=$article->getGalleys()}
			{if $galleys}
				{foreach from=$article->getGalleys() item=galley name=galleyList}
					<strong>
					<a href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)|to_array:$galley->getBestGalleyId($currentJournal)}" target="_parent">{$galley->getGalleyLabel()|escape}</a>
					</strong>
				{/foreach}
				<br />
			{/if}
</div>
		{/foreach}{* articles *}
	{/foreach}{* sections *}
	</td>
	<td></td>
	<td width="50%" valign="top">
	{php}
		wp_reset_query();
		query_posts('&cat=6&posts_per_page=5');
		{/php}
		{php} if (have_posts()) : {/php}
			<h2>{translate key="plugins.blocks.recentArticles.latestVideos"}</h2>
			{php} while (have_posts()) : the_post(); {/php}
				{php} the_excerpt(); {/php}
				<br />
			{php} endwhile; {/php}
		{php} endif; {/php}
	</td>
 	</tr>
</table>