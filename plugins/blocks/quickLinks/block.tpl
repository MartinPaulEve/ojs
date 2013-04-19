{**
 * block.tpl
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- "Developed By" block.
 *
 * $Id: block.tpl,v 1.4 2009/04/08 19:54:34 asmecher Exp $
 *}

<div class="block" id="sidebarQuickLinks">
	<span class="blockTitle">{translate key="plugins.block.quickLinks.displayName"}</span>
	<ul>
		{php} wp_list_pages(array('title_li'=>'', 'child_of'=>2)); {/php}
		{if !$isUserLoggedIn}
			<li><a href="{url page="user" op="register"}">{translate key="navigation.register"}</a></li>
		{/if}
	</ul>
</div>
