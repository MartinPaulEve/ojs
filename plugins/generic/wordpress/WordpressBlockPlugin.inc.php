<?php

/**
 * @file WordpressBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WordpressBlockPlugin
 * @ingroup plugins_generic_wordpress
 *
 * @brief Class for block component of wordpress plugin (the wordpress sidebar)
 */

import('plugins.BlockPlugin');

class WordpressBlockPlugin extends BlockPlugin {
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'WordpressBlockPlugin';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.wordpress.block.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.generic.wordpress.block.description');
	}

	/**
	 * Get the supported contexts (e.g. BLOCK_CONTEXT_...) for this block.
	 * @return array
	 */
	function getSupportedContexts() {
		return array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR);
	}

	/**
	 * Get the web feed plugin
	 * @return object
	 */
	function &getWordpressPlugin() {
		$plugin =& PluginRegistry::getPlugin('generic', 'WordpressPlugin');
		return $plugin;
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 * @return string
	 */
	function getPluginPath() {
		$plugin =& $this->getWordpressPlugin();
		return $plugin->getPluginPath();
	}

	/**
	 * Override the builtin to get the correct template path.
	 * @return string
	 */
	function getTemplatePath() {
		$plugin =& $this->getWordpressPlugin();
		return $plugin->getTemplatePath();
	}

	/**
	 * Get the filename of the template block
	 * Returning null from this function results in an empty display.
	 * @return string
	 */
	function getBlockTemplateFilename() {
		return 'sidebar.tpl';
	}
}

?>
