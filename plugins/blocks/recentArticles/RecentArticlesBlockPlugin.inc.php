<?php

/**
 * @file RecentArticlesBlockPlugin.inc.php
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RecentArticlesBlockPlugin
 * @ingroup plugins_blocks_recentArticles
 *
 * @brief Class for Popular Article Block plugin
 */


import('lib.pkp.classes.plugins.BlockPlugin');

class RecentArticlesBlockPlugin extends BlockPlugin {
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			$this->addLocaleData();
			HookRegistry::register('TemplateManager::display', array(&$this, 'templateManagerCallback'));
		}
		return $success;
	}

	function getEnabled() {
        if (!Config::getVar('general', 'installed')) return true;
      		return parent::getEnabled();
	}

	function templateManagerCallback($hookName, &$args) {
		$templateMgr =& $args[0]; //TemplateManager::getManager();
		// if ( Request::getRequestedPage() == 'index' || Request::getRequestedPage() == '' )
		// 	$templateMgr->assign('alternativeTitleTranslated', ''); 	//PKPLocale::translate('plugins.blocks.recentArticles.displayTitle'));
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	function getSupportedContexts() {
		return array(BLOCK_CONTEXT_HOMEPAGE);
	}

	function getBlockContext() {
		return BLOCK_CONTEXT_HOMEPAGE;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'RecentArticlesBlockPlugin';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return PKPLocale::translate('plugins.block.recentArticles.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return PKPLocale::translate('plugins.block.recentArticles.description');
	}

	function getContents(&$templateMgr) {
		$journal =& Request::getJournal();
		if (!$journal) return '';

		$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO  */
		import('db.DBResultRange');
		$rangeInfo = new DBResultRange(8, 1);
		$publishedArticleObjects =& $publishedArticleDao->getPublishedArticlesByJournalId($journal->getId(), $rangeInfo, true);
		while ($publishedArticle =& $publishedArticleObjects->next()) {
			$recentArticles[]['articles'][] =& $publishedArticle;
			unset($publishedArticle);
		}

		$templateMgr->assign_by_ref('recentArticles', $recentArticles);

		// provide the issue DAO to pull out the volume/number information for each article
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$templateMgr->assign_by_ref('issueDao', $issueDao);
		return parent::getContents($templateMgr);
	}
}

?>
