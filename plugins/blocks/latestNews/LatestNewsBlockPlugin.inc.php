<?php

/**
 * @file LatestNewsBlockPlugin.inc.php
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LatestNewsBlockPlugin
 * @ingroup plugins_blocks_latestNews
 *
 * @brief Class for Popular Article Block plugin
 */


import('lib.pkp.classes.plugins.BlockPlugin');

class LatestNewsBlockPlugin extends BlockPlugin {
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
		if ( Request::getRequestedPage() == 'index' || Request::getRequestedPage() == '' )
			$templateMgr->assign('alternativeTitleTranslated', ''); //Locale::translate('plugins.blocks.latestNews.displayTitle'));
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
		return 'LatestNewsBlockPlugin';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.block.latestNews.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.block.latestNews.description');
	}

	function getContents(&$templateMgr) {
		$page = Request::getRequestedPage();
		$op = Request::getRequestedOp();

		if ( $page != 'index' && $page != '' ) return '';

		echo parent::getContents($templateMgr);
	}
}

?>
