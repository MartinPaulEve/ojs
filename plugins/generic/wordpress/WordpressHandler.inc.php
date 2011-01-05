<?php

/**
 * @file WordpressHandler.inc.php
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.wordpress
 * @class WordpressHandler
 *
 * Find the content and display the appropriate page
 *
 */

import('handler.Handler');



class WordpressHandler extends Handler {
	function index( $args ) {
		
		$wordpressPlugin = &PluginRegistry::getPlugin('generic', 'WordpressPlugin');
		$templateMgr =& TemplateManager::getManager();
		$wordpressPlugin->addLocaleData();
		
		$templateMgr->addStyleSheet(Request::getBaseUrl().'/plugins/generic/wordpress/themes/default/style.css');
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON));

		//
		// PORT OF wordpress/wp-includes/template-loader.php
		//

		$templateMgr->assign('pageTitleTranslated', 'Blog');
		if ( is_robots() ) {
			do_action('do_robots');
		} elseif ( is_feed() ) {			
			do_feed();
		} elseif ( is_single() || $isPage = is_page()) {
			if ( have_posts() ) {
				the_post();
				
				$templateMgr->assign('pageTitleTranslated', get_the_title());
			} else { 
				$templateMgr->assign('noPost', true);
			}
			$pageHierarchy = $templateMgr->get_template_vars('pageHierarchy');
			$pageHierarchy[] = array(Request::url(null, 'blog'), 'plugins.generic.wordpress.blog');
			$templateMgr->assign('pageHierarchy', $pageHierarchy);

			if ( $isPage )
				$templateMgr->display($wordpressPlugin->getTemplatePath() . 'page.tpl');	
			else
				$templateMgr->display($wordpressPlugin->getTemplatePath() . 'single.tpl');
		} else if ( is_category() ) {
			$catId = (int) Request::getUserVar('cat');
			if ( $categoryName = get_cat_name($catId) ) {
				$templateMgr->assign("pageTitleTranslated", $categoryName);
				$pageHierarchy = $templateMgr->get_template_vars('pageHierarchy');
				$pageHierarchy[] = array(Request::url(null, 'blog'), 'plugins.generic.wordpress.blog');
				$templateMgr->assign('pageHierarchy', $pageHierarchy);
			}
			$templateMgr->display($wordpressPlugin->getTemplatePath() . 'index.tpl');			
		} elseif ( is_trackback() ) {
			include(ABSPATH . 'wp-trackback.php');
			return;
		} elseif ( is_404() ) { 			
			Request::redirect(null, 'index');
		}else {
		    $templateMgr->assign("pagetitle", "Blog");	
			$templateMgr->display($wordpressPlugin->getTemplatePath() . 'index.tpl');		
		}

		/*

	} else if ( is_search() && $template = get_search_template() ) {
		include($template);
		return;
	} else if ( is_tax() && $template = get_taxonomy_template()) {
		include($template);
		return;
	} else if ( is_home() && $template = get_home_template() ) {
		include($template);
		return;
	} else if ( is_attachment() && $template = get_attachment_template() ) {
		remove_filter('the_content', 'prepend_attachment');
		include($template);
		return;
	} else if ( is_single() && $template = get_single_template() ) {
		include($template);
		return;
	} else if ( is_page() && $template = get_page_template() ) {
		include($template);
		return;
	} else if ( is_category() && $template = get_category_template()) {
		include($template);
		return;
	} else if ( is_tag() && $template = get_tag_template()) {
		include($template);
		return;
	} else if ( is_author() && $template = get_author_template() ) {
		include($template);
		return;
	} else if ( is_date() && $template = get_date_template() ) {
		include($template);
		return;
	} else if ( is_archive() && $template = get_archive_template() ) {
		include($template);
		return;
	} else if ( is_comments_popup() && $template = get_comments_popup_template() ) {
		include($template);
		return;
	} else if ( is_paged() && $template = get_paged_template() ) {
		include($template);
		return;
	} else if ( file_exists(TEMPLATEPATH . "/index.php") ) {
		include(TEMPLATEPATH . "/index.php");
		return;		
	*/
	
	}	
}

?>
