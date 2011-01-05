<?php

/**
 * @file EpaaUrlPlugin.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.epaaUrl
 * @class EpaaUrlPlugin
 *
 * EpaaUrlPlugin class
 *
 */

import('classes.plugins.GenericPlugin');

class EpaaUrlPlugin extends GenericPlugin {

	function getName() {
		return 'EpaaUrlPlugin';
	}

	function getDisplayName() {
		return Locale::translate('plugins.generic.epaaUrl.displayName');
	}

	function getDescription() {
		$description = Locale::translate('plugins.generic.epaaUrl.description');
		return $description;
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
				HookRegistry::register('LoadHandler', array(&$this, 'callbackHandleContent'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Declare the handler function to process the actual page PATH
	 */
	function callbackHandleContent($hookName, $args) {
		$templateMgr =& TemplateManager::getManager();

		$page =& $args[0];
		$op =& $args[1];

		if ( $page == 'oldepaa' ) {
			define('HANDLER_CLASS', 'EpaaUrlHandler');
			$this->import('EpaaUrlHandler');
			return true;
		}
		return false;
	}

	/**
	 * Determine whether or not this plugin is enabled.
	 */
	function getEnabled() {
		$journal =& Request::getJournal();
		if (!$journal) return false;
		return $this->getSetting($journal->getJournalId(), 'enabled');
	}

	/**
	 * Set the enabled/disabled state of this plugin
	 */
	function setEnabled($enabled) {
		$journal =& Request::getJournal();
		if ($journal) {
			$this->updateSetting($journal->getJournalId(), 'enabled', $enabled ? true : false);

			return true;
		}
		return false;
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
		} else {
			$verbs[] = array(
				'enable',
				Locale::translate('manager.plugins.enable')
			);
		}
		return $verbs;
	}

	/**
	 * Perform management functions
	 */
	function manage($verb, $args) {
		$returner = true;

		switch ($verb) {
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
