{**
 * plugins/generic/alm/output.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * ALM plugin settings
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the alm visualization handler.
		$('#alm').pkpHandler('$.pkp.plugins.generic.alm.ALMVisualizationHandler',
			{ldelim}
				altStatsJson: '{$altStatsJson|escape:"javascript"}',
				downloadStatsJson: '{$downloadJson|escape:"javascript"}',
				d3: d3,
				baseUrl: "http://pkp-alm.lib.sfu.ca",
				minItemsToShowGraph: {ldelim}
					minEventsForYearly: 2,
					minEventsForMonthly: 2,
					minEventsForDaily: 2,
					minYearsForYearly: 2,
					minMonthsForMonthly: 2,
					minDaysForDaily: 2
				{rdelim},
				categories: [{ldelim} name: "html", display_name: "HTML Views", tooltip_text: "{translate key="plugins.generic.alm.categories.html"}" },
					{ldelim} name: "pdf", display_name: "PDF Downloads", tooltip_text: "{translate key="plugins.generic.alm.categories.pdf"}" {rdelim},
					{ldelim} name: "likes", display_name: "Likes", tooltip_text: "{translate key="plugins.generic.alm.categories.likes"}" {rdelim},
					{ldelim} name: "shares", display_name: "Shares", tooltip_text: "{translate key="plugins.generic.alm.categories.shares"}" {rdelim},
					{ldelim} name: "comments", display_name: "Comments", tooltip_text: "{translate key="plugins.generic.alm.categories.comments"}" {rdelim},
					{ldelim} name: "citations", display_name: "Citations", tooltip_text: "{translate key="plugins.generic.alm.categories.citations"}?)" {rdelim}],
				jqueryImportUrl: '{$jqueryImportPath}',
				tooltipImportUrl: '{$tooltipImportPath}'
			{rdelim}
		);
	{rdelim});
</script>

<div class="separator"></div>
<a name="alm"></a>
<h4>{translate key="plugins.generic.alm.title"}</h4>
<div id="alm" class="alm"><div id="loading">{translate key="plugins.generic.alm.loading"}</div></div>

<br />
<span style="float: right"><sub>Metrics powered by <a href="http://pkp-alm.lib.sfu.ca/">PLOS ALM</a><sub></span>
<br />


