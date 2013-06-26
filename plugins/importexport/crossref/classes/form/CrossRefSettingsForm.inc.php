<?php

/**
 * @file plugins/importexport/crossref/classes/form/CrossRefSettingsForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefSettingsForm
 * @ingroup plugins_importexport_crossref_classes_form
 *
 * @brief Form for journal managers to setup the CrossRef plug-in.
 */


if (!class_exists('DOIExportSettingsForm')) { // Bug #7848
	import('plugins.importexport.crossref.classes.form.DOIExportSettingsForm');
}

class CrossRefSettingsForm extends DOIExportSettingsForm {

	//
	// Constructor
	//
	/**
	 * Constructor
	 * @param $plugin CrossRefExportPlugin
	 * @param $journalId integer
	 */
	function CrossRefSettingsForm(&$plugin, $journalId) {
		// Configure the object.
		parent::DOIExportSettingsForm($plugin, $journalId);

		// Add form validation checks.
		// The username is used in HTTP basic authentication and according to RFC2617 it therefore may not contain a colon.
		$this->addCheck(new FormValidatorRegExp($this, 'username', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.importexport.crossref.settings.form.usernameRequired', '/^[^:]+$/'));
	}


	//
	// Implement template methods from DOIExportSettingsForm
	//
	/**
	 * @see DOIExportSettingsForm::getFormFields()
	 */
	function getFormFields() {
		return array(
			'username' => 'string',
			'password' => 'string'
		);
	}
}

?>
