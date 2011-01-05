{**
 * block.tpl
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Most Popular Articles
 *
 *}
	<h2>{translate key="plugins.blocks.recentArticles.displayTitle"}</h2>
	{foreach name=sections from=$recentArticles item=section key=sectionId}
		{foreach from=$section.articles item=article}
			{assign var="recentArticleIssue" value=$issueDao->getIssueByArticleId($article->getArticleId())}
	<div class="feature">		
<h3><div class="articleTitle"><a class="articleLink" href="{url page="article" op="view" path=$article->getBestArticleId($currentJournal)}">{$article->getLocalizedTitle()|strip|escape:"html"}</a></div></h3>
			<div id="authorString"><em>{translate key="plugins.blocks.recentArticles.by"} {$article->getAuthorString()|escape:"html"}</em></div>

			{$recentArticleIssue->getIssueIdentification()}, {$article->getPages()}<br />
			{$article->getLocalizedAbstract()|strip_unsafe_html|nl2br}<br />

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
