<?php

/**
 * @file WordpressPlugin.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.wordpress
 * @class WordpressPlugin
 *
 * WordpressPlugin class
 *
 */

import('classes.plugins.GenericPlugin');

class WordpressPlugin extends GenericPlugin {

	function getName() {
		return 'WordpressPlugin';
	}

	function getDisplayName() {
		return Locale::translate('plugins.generic.wordpress.displayName');
	}

	function getDescription() {
		return Locale::translate('plugins.generic.wordpress.description');;
	}

	/**
	 * Register the plugin, attaching to hooks as necessary.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			$this->addLocaleData();
			if ($this->getEnabled()) {
				HookRegistry::register('PluginRegistry::loadCategory', array(&$this, 'callbackLoadCategory'));
				HookRegistry::register ('TemplateManager::display', array(&$this, 'handleTemplateDisplay'));
				HookRegistry::register('LoadHandler', array(&$this, 'callbackReplaceWordpress'));
			}
			return true;
		}
		return false;
	}
	/**
	 * Declare the handler function to process the actual page PATH
	 */
	function callbackReplaceWordpress($hookName, $args) {
		$templateMgr = &TemplateManager::getManager();

		$page =& $args[0];
		$op =& $args[1];

		switch ($page) {
			case 'blog':
				define('HANDLER_CLASS', 'WordpressHandler');
				$this->import('WordpressHandler');
				return true;
		}
		return false;
	}

	/**
	 * Hook callback: Handle requests.
	 */
	function handleTemplateDisplay($hookName, $args) {
		$templateMgr =& $args[0];
		$template =& $args[1];

		switch ($template) {
			case 'article/article.tpl':
				HookRegistry::register ('TemplateManager::include', array(&$this, 'handleReaderTemplateInclude'));
				break;
		}
		return false;
	}

	function handleReaderTemplateInclude($hookName, $args) {
		global $withcomments;
		$templateMgr =& $args[0];
		$params =& $args[1];
		if (!isset($params['smarty_include_tpl_file'])) return false;
		switch ($params['smarty_include_tpl_file']) {
			case 'article/comments.tpl':

			$requestedArgs = Request::getRequestedArgs();

			//FIXME: this wont work with custom identifiers
			$articleId = (int) $requestedArgs[0];
			$posts = query_posts('meta_key=article_id&meta_value=' . $articleId . '&cat=3&numberposts=1');
			if ( count($posts) > 0 ) {
				the_post();
  				$withcomments = true;
  				comments_template();
				return true;
			}
		}
		return false;
	}


	/**
	 * Register as a block plugin, even though this is a generic plugin.
	 * This will allow the plugin to behave as a block plugin, i.e. to
	 * have layout tasks performed on it.
	 * @param $hookName string
	 * @param $args array
	 */
	function callbackLoadCategory($hookName, $args) {
		$category =& $args[0];
		$plugins =& $args[1];
		switch ($category) {
			case 'blocks':
				$this->import('WordpressBlockPlugin');
				$blockPlugin =& new WordpressBlockPlugin();
				$plugins[$blockPlugin->getSeq()][$blockPlugin->getPluginPath(). '/' . $blockPlugin->getName()] =& $blockPlugin;
				break;
		}
		return false;
	}

	/**
	 * Determine whether or not this plugin is enabled.
	 */
	function getEnabled() {
		$journal =& Request::getJournal();
		if ( !$journal ) return false;
		return $this->getSetting($journal->getJournalId(), 'enabled');
	}

	/**
	 * Set the enabled/disabled state of this plugin
	 */
	function setEnabled($enabled) {
		$journal =& Request::getJournal();
		return $this->updateSetting($journal->getJournalId(), 'enabled', $enabled ? true : false);
	}

	/**
	 * Display verbs for the management interface.
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->getEnabled()) {
			$verbs[] = array(
				'disable',
				Locale::translate('manager.plugins.disable')
			);
/*
			$verbs[] = array(
				'settings',
				Locale::translate('plugins.generic.wordpress.settings')
			);
*/
		} else {
			$verbs[] = array(
				'enable',
				Locale::translate('manager.plugins.enable')
			);
		}
		return $verbs;
	}

	function getTemplatePath($theme = 'default') {
		return parent::getTemplatePath() . 'themes' . DIRECTORY_SEPARATOR . $theme . DIRECTORY_SEPARATOR;
	}

	/**
	 * Perform management functions
	 */
	function manage($verb, $args) {
		$returner = true;

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		$templateMgr->assign('pagesPath', Request::url(null, 'pages', 'view'));

		$pageCrumbs = array(
			array(
				Request::url(null, 'user'),
				'navigation.user'
			),
			array(
				Request::url(null, 'manager'),
				'user.role.manager'
			)
		);

		switch ($verb) {
			case 'settings':
				$journal =& Request::getJournal();

				$this->import('WordpressSettingsForm');
				$form =& new WordpressSettingsForm($this, $journal->getJournalId());

				$templateMgr->assign('pageHierarchy', $pageCrumbs);
				$form->initData();
				$form->display();
				break;
			case 'execute':
				$journal =& Request::getJournal();

				$this->import('WordpressSettingsForm');
				$form =& new WordpressSettingsForm($this, $journal->getJournalId());

				$form->readInputData();
				$form->execute();
				$form->display();
				break;
			case 'enable':
				$this->setEnabled(true);
				$returner = false;
				break;
			case 'disable':
				$this->setEnabled(false);
				$returner = false;
				break;
		}

		return $returner;
	}
}

?>
