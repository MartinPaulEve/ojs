<?php

/**
 * @file RelatedArticlesBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RelatedArticlesBlockPlugin
 * @ingroup plugins_blocks_relatedArticles
 *
 * @brief Class for "developed by" block plugin
 */

// $Id$


import('plugins.BlockPlugin');

class RelatedArticlesBlockPlugin extends BlockPlugin {
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			$this->addLocaleData();
		}
		return $success;
	}

	/**
	 * Determine whether the plugin is enabled. Overrides parent so that
	 * the plugin will be displayed during install.
	 */
	function getEnabled() {
		if (!Config::getVar('general', 'installed')) return true;
		return parent::getEnabled();
	}

	/**
	 * Install default settings on system install.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Install default settings on journal creation.
	 * @return string
	 */
	function getNewJournalPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the block context. Overrides parent so that the plugin will be
	 * displayed during install.
	 * @return int
	 */
	function getBlockContext() {
		if (!Config::getVar('general', 'installed')) return BLOCK_CONTEXT_RIGHT_SIDEBAR;
		return parent::getBlockContext();
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	function getSupportedContexts() {
		return array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR);
	}

	/**
	 * Determine the plugin sequence. Overrides parent so that
	 * the plugin will be displayed during install.
	 */
	function getSeq() {
		if (!Config::getVar('general', 'installed')) return 1;
		return parent::getSeq();
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'RelatedArticlesBlockPlugin';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return PKPLocale::translate('plugins.block.relatedArticles.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return PKPLocale::translate('plugins.block.relatedArticles.description');
	}

	function getContents() {
		$page = Request::getRequestedPage();
		$op = Request::getRequestedOp();

		if ( $page != 'article' || $op != 'view' ) return '';
		$args = Request::getRequestedArgs();

		//FIXME: this wont work with custom identifiers
		$articleId = (int) $args[0];
		$posts = get_posts('meta_key=article_id&meta_value=' . $articleId . '&category=4&numberposts=1');
		if ( count($posts) != 1 ) return '';
		$post = $posts[0];

		$output = '<div class="block" id="sidebarRelatedArticles">' . "\n" . '<span class="blockTitle">';
		$output .= PKPLocale::translate('plugins.block.relatedArticles.displayName');
		$output .= "\n</span>";
		$output .= nl2br($post->post_content);
		$output .= "\n</div>";
		return $output;
	}
}

?>
