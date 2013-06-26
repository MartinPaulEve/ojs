<?php

/**
 * @file plugins/importexport/crossref/CrossRefExportPlugin.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefExportPlugin
 * @ingroup plugins_importexport_crossref
 *
 * @brief CrossRef export/registration plugin.
 */


if (!class_exists('DOIExportPlugin')) { // Bug #7848
	import('plugins.importexport.crossref.classes.DOIExportPlugin');
}

// CrossRef API
define('CROSSREF_API_URL', 'http://doi.crossref.org/servlet/deposit');
define('CROSSREF_API_TEST_URL', 'http://test.crossref.org/servlet/deposit');

define('CROSSREF_API_RESPONSE_OK', 201);

class CrossRefExportPlugin extends DOIExportPlugin {

	//
	// Constructor
	//
	function CrossRefExportPlugin() {
		parent::DOIExportPlugin();
	}


	//
	// Implement template methods from ImportExportPlugin
	//
	/**
	 * @see ImportExportPlugin::getName()
	 */
	function getName() {
		return 'CrossRefExportPlugin';
	}

	/**
	 * @see ImportExportPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.importexport.crossref.displayName');
	}

	/**
	 * @see ImportExportPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.importexport.crossref.description');
	}


	//
	// Implement template methods from DOIExportPlugin
	//
	/**
	 * @see DOIExportPlugin::getPluginId()
	 */
	function getPluginId() {
		return 'crossref';
	}

	/**
	 * @see DOIExportPlugin::getSettingsFormClassName()
	 */
	function getSettingsFormClassName() {
		return 'CrossRefSettingsForm';
	}

	/**
	 * @see: DOIExportPlugin::_generateExportFilesForObjects
	 */
	function _generateExportFilesForObjects(&$request, &$journal, $exportSpec, $exportPath, &$errors) {
		// Lets figure out how many export files we need and generate them
		$exportFiles = array();

		// these will hold the articles and issue id's we'll export.
		$issueIds = isset($exportSpec[DOI_EXPORT_ISSUES]) ? $exportSpec[DOI_EXPORT_ISSUES] : array();
		$articleIds = array();

		// These are articles that were asked for specifically
		$requestedArticleIds = isset($exportSpec[DOI_EXPORT_ARTICLES]) ? $exportSpec[DOI_EXPORT_ARTICLES] : array();

		// Normalize the object id(s) into an array.
		if (is_scalar($issueIds)) $issueIds = array($issueIds);
		if (is_scalar($requestedArticleIds)) $requestedArticleIds = array($requestedArticleIds);

		// Retrieve them
		$articles =& $this->_getObjectsFromIds(DOI_EXPORT_ARTICLES, $requestedArticleIds, $journal->getId(), $errors);
		if ($articles) {
			foreach ($articles as $article) {
				// but only keep the articleIds that are not in issues we're already going to export
				if (!in_array($article->getIssueId(), $issueIds)) {
					$articleIds[] = $article->getId();
				}
			}
		}

		$issues =& $this->_getObjectsFromIds(DOI_EXPORT_ISSUES, $issueIds, $journal->getId(), $errors);
		$articles =& $this->_getObjectsFromIds(DOI_EXPORT_ARTICLES, $articleIds, $journal->getId(), $errors);

		// FIXME: this is a little messy, but _getObjectsFromIds returns false
		// and need to check why it cannot return an empty array
		// which would let us simply array_merge the two results
		if (!$issues && !$articles) {
			return false;
		} elseif (!$issues) {
			$objects = $articles;
		} elseif (!$articles) {
			$objects = $issues;
		} else {
			$objects = array_merge($issues, $articles);
		}

		// NB: This is different from the parent class generateExportFiles
		// CrossRef allows us to put everything in one file so we do that for simplicity and efficiency
		// The base class has not been designed correctly to work for the single-file usecase
		// which is why we're working around it by making a new method
		$exportFile = $this->generateExportFile($request, $objects, $exportPath, $journal, $errors);
		if ($exportFile === false) {
			$this->cleanTmpfiles($exportPath, $exportFile);
			return false;
		}

		return $exportFile;
	}


	/**
	 * Generate the export data file.
	 * Note this is different than DoiExportPlugin::genereateExportFiles
	 * although it serves a similar function.
	 * CrossRef allows us to put everything in one file so we do that for simplicity and efficiency
	 * The base class has not been designed correctly to work for the single-file usecase
	 * which is why we're working around it by making a new method
	 * @param $request Request
	 * @param $objects
	 * @param $targetPath string
	 * @param $journal Journal
	 * @param $errors array Output parameter for error details when
	 *  the function returns false.
	 * @return array|boolean Either an array of generated export
	 *  files together with the contained objects or false if not successful.
	 */
	function generateExportFile($request, &$objects, $targetPath, $journal, &$errors) {
		// Additional locale file.
		AppLocale::requireComponents(array(LOCALE_COMPONENT_OJS_EDITOR));

		$exportFiles = array();

		$this->import('classes.CrossRefExportDom');
		$dom = new CrossRefExportDom($request, $this, $journal, $this->getCache());
		$doc =& $dom->generate($objects);
		if ($doc) {
			// Write the result.
			$exportFile = $this->getTargetFileName($targetPath, DOI_EXPORT_MIXED);
			file_put_contents($exportFile, XMLCustomWriter::getXML($doc));
			$fileManager = new FileManager();
			$fileManager->setMode($exportFile, FILE_MODE_MASK);
			$exportFiles[$exportFile] = $objects;
			unset($object);
		}
		return $exportFiles;
	}

	function isTestMode($request) {
		return true;
	}

	/**
	 * @see DOIExportPlugin::registerDoi()
	 */
	function registerDoi($request, $journal, &$objects, $file) {
		if ($this->isTestMode($request)) {
			$url = CROSSREF_API_TEST_URL;
		} else {
			$url = CROSSREF_API_URL;
		}

		$params = array();
		$params['login_id'] = $this->getSetting($journal->getId(), 'username');
		$params['login_passwd'] = $this->getSetting($journal->getId(), 'password');

		assert(is_readable($file));
		$payload = file_get_contents($file);
		assert($payload !== false && !empty($payload));
		$params['fname'] = $payload;

		import('lib.pkp.classes.webservice.WebService');
		$crossRefWebServiceRequest = new WebServiceRequest($url, $params, 'POST');
		$webService = new WebService();
		$result = $webService->call($crossRefWebServiceRequest);
		var_dump($result);
		exit;

//
//		// Mint a DOI.
//		if ($result === true) {
//			$payload = "doi=$doi\nurl=$url";
//
//			curl_setopt($curlCh, CURLOPT_URL, CROSSREF_API_URL . 'doi');
//			curl_setopt($curlCh, CURLOPT_HTTPHEADER, array('Content-Type: text/plain;charset=UTF-8'));
//			curl_setopt($curlCh, CURLOPT_POSTFIELDS, $payload);
//
//			$response = curl_exec($curlCh);
//			if ($response === false) {
//				$result = array(array('plugins.importexport.common.register.error.mdsError', 'No response from server.'));
//			} else {
//				$status = curl_getinfo($curlCh, CURLINFO_HTTP_CODE);
//				if ($status != CROSSREF_API_RESPONSE_OK) {
//					$result = array(array('plugins.importexport.common.register.error.mdsError', "$status - $response"));
//				}
//			}
//		}
//
//		curl_close($curlCh);
//
//		if ($result === true) {
//			// Mark the object as registered.
//			$this->markRegistered($request, $object, CROSSREF_API_TESTPREFIX);
//		}
//
//		return $result;
	}
}

?>
