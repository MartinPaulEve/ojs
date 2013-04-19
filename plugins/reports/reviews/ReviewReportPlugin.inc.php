<?php

/**
 * @file plugins/reports/reviews/ReviewReportPlugin.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewReportPlugin
 * @ingroup plugins_reports_review
 * @see ReviewReportDAO
 *
 * @brief Review report plugin
 */

import('classes.plugins.ReportPlugin');

class ReviewReportPlugin extends ReportPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			$this->import('ReviewReportDAO');
			$reviewReportDAO = new ReviewReportDAO();
			DAORegistry::registerDAO('ReviewReportDAO', $reviewReportDAO);
		}
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'ReviewReportPlugin';
	}

	function getDisplayName() {
		return PKPLocale::translate('plugins.reports.reviews.displayName');
	}

	function getDescription() {
		return PKPLocale::translate('plugins.reports.reviews.description');
	}

	function display(&$args) {
		$journal =& Request::getJournal();

		header('content-type: text/comma-separated-values');
		header('content-disposition: attachment; filename=reviews-' . date('Ymd') . '.csv');
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);

		$reviewReportDao =& DAORegistry::getDAO('ReviewReportDAO');
		list($commentsIterator, $reviewsIterator) = $reviewReportDao->getReviewReport($journal->getId());

		$comments = array();
		while ($row =& $commentsIterator->next()) {
			if (isset($comments[$row['article_id']][$row['author_id']])) {
				$comments[$row['article_id']][$row['author_id']] .= "; " . $row['comments'];
			} else {
				$comments[$row['article_id']][$row['author_id']] = $row['comments'];
			}
		}

		$yesnoMessages = array( 0 => PKPLocale::translate('common.no'), 1 => PKPLocale::translate('common.yes'));

		import('classes.submission.reviewAssignment.ReviewAssignment');
		$recommendations = ReviewAssignment::getReviewerRecommendationOptions();

		$columns = array(
			'round' => PKPLocale::translate('plugins.reports.reviews.round'),
			'article' => PKPLocale::translate('article.articles'),
			'articleid' => PKPLocale::translate('article.submissionId'),
			'reviewerid' => PKPLocale::translate('plugins.reports.reviews.reviewerId'),
			'reviewer' => PKPLocale::translate('plugins.reports.reviews.reviewer'),
			'firstname' => PKPLocale::translate('user.firstName'),
			'middlename' => PKPLocale::translate('user.middleName'),
			'lastname' => PKPLocale::translate('user.lastName'),
			'dateassigned' => PKPLocale::translate('plugins.reports.reviews.dateAssigned'),
			'datenotified' => PKPLocale::translate('plugins.reports.reviews.dateNotified'),
			'dateconfirmed' => PKPLocale::translate('plugins.reports.reviews.dateConfirmed'),
			'datecompleted' => PKPLocale::translate('plugins.reports.reviews.dateCompleted'),
			'datereminded' => PKPLocale::translate('plugins.reports.reviews.dateReminded'),
			'declined' => PKPLocale::translate('submissions.declined'),
			'cancelled' => PKPLocale::translate('common.cancelled'),
			'recommendation' => PKPLocale::translate('reviewer.article.recommendation'),
			'comments' => PKPLocale::translate('comments.commentsOnArticle')
		);
		$yesNoArray = array('declined', 'cancelled');

		$fp = fopen('php://output', 'wt');
		String::fputcsv($fp, array_values($columns));

		while ($row =& $reviewsIterator->next()) {
			foreach ($columns as $index => $junk) {
				if (in_array($index, $yesNoArray)) {
					$columns[$index] = $yesnoMessages[$row[$index]];
				} elseif ($index == "recommendation") {
					$columns[$index] = (!isset($row[$index])) ? PKPLocale::translate('common.none') : PKPLocale::translate($recommendations[$row[$index]]);
				} elseif ($index == "comments") {
					if (isset($comments[$row['articleid']][$row['reviewerid']])) {
						$columns[$index] = $comments[$row['articleid']][$row['reviewerid']];
					} else {
						$columns[$index] = "";
					}
				} else {
					$columns[$index] = $row[$index];
				}
			}
			String::fputcsv($fp, $columns);
			unset($row);
		}
		fclose($fp);
	}
}

?>
