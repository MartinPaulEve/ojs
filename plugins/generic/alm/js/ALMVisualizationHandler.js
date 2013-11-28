/**
 * @defgroup plugins_generic_alm_js
 */
/**
 * @file plugins/generic/alm/js/ALMVisualizationHandler.js
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ALMVisualizationHandler
 * @ingroup plugins_generic_alm_js
 *
 * @brief Article level metrics visualization controller.
 */
(function($) {

	// Define the namespace.
	$.pkp.plugins.generic.alm = $.pkp.plugins.generic.alm || {};

	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQueryObject} $wrapper the wrapped HTML visualization element.
	 * @param {Object} options options to configure the form handler.
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler = function($wrapper, options) {
		var stats, downloadStats;
		this.parent($wrapper, options);

		stats = $.parseJSON(options.altStatsJson);
		if (stats[0] !== undefined && stats[0].sources !== undefined) {
			downloadStats = $.parseJSON(options.downloadStatsJson);
			if (downloadStats) {
				stats[0].sources.push(downloadStats);
			}
		}

		if (stats) {
			this.stats_ = stats;
		}

		this.d3_ = options.d3;
		this.baseUrl_ = options.baseUrl;
		this.hasSVG_ = document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1");
		this.minItems_ = options.minItemsToShowGraph;
		this.categories_ = options.categories;

		this.formatNumber_ = d3.format(",d");
		this.charts_ = new Array();

		// Import JQuery 1.10 version, needed for the tooltip plugin
		// that we use below.
		$.getScript(options.jqueryImportUrl, this.callbackWrapper(
				function() {
					$.getScript(options.tooltipImportUrl, this.callbackWrapper(
							function() {
								// Assign the last inserted JQuery version to a new variable, to avoid
								// conflicts with the current version in $ variable.
								this.j110_ = jQuery.noConflict(true);
								this.initViz();
							}
					));
				}
		));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.plugins.generic.alm.ALMVisualizationHandler,
			$.pkp.classes.Handler);


	//
	// Private properties
	//
	/**
	 * Article level statistics.
	 * @private
	 * @type {object}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.stats_ = {};


	/**
	 * Data-driven documents library object.
	 * @see http://d3js.org/
	 * @private
	 * @type {Object}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.d3_ = {};


	/**
	 * Base PKP ALM service url.
	 * @private
	 * @type {string}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.baseUrl_ = null;


	/**
	 * Whether browser has Scalable Vector Graphics (SVG) support.
	 * @private
	 * @type {boolean}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.hasSVG_ = false;


	/**
	 * Minumum items value to show graphs.
	 * @private
	 * @type {array}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.minItems_ = [];


	/**
	 * Statistics categories.
	 * @private
	 * @type {array}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.categories_ = [];


	/**
	 * JQuery version 1.10 object. We need that for the tooltip
	 * to work.
	 * @private
	 * @type {object}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.j110_ = {};


	/**
	 * Flag whether metrics were found or not.
	 * @private
	 * @type {boolean}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.metricsFound_ = false;


	/**
	 * Format numbers for display.
	 * @private
	 * @type {function}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.formatNumber_ = {};


	/**
	 * Keep track of AlmViz objects.
	 * @private
	 * @type {object}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler
		.prototype.charts_ = {};


	/**
	 * Initialize the visualization.
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
			.initViz = function() {
		var data = this.stats_, canvas = this.d3_.select("#alm"),
			index, limit, pub_date, category;

		this.d3_.select("#alm > #loading").remove();

		// extract publication date
		pub_date = this.d3_.time.format.iso.parse(data[0]["publication_date"]);

		// loop through categories
		for (index = 0, limit = this.categories_.length; index < limit; index++) {
			category = this.categories_[index];
			this.addCategory_(canvas, category, pub_date, data);
		}

		if (!this.metricsFound_) {
			canvas.append("p")
				.attr("class", "muted")
				.text("No metrics found.");
		}
	};


	/**
	 * Build each article level statistics category.
	 * @param {Object} canvas d3 element
	 * @param {Array} category Information about the category.
	 * @param {Function} pub_date d3 date function.
	 * @param {Object} data Statistics.
	 * @return {JQueryObject|boolean}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
			.addCategory_ = function(canvas, category, pub_date, data) {
		var categoryRow = false, index, limit, source;

		// Loop through sources to add statistics data to the category.
		for (index = 0, limit = data[0]["sources"].length; index < limit; index++) {
			source = data[0]["sources"][index];
			var total = source.metrics[category.name];
			if (total > 0) {
				// We can only get the category row element if we have sure
				// that there is at least one source with metrics to show,
				// because as soon as we get the category element, it will be
				// added to the canvas.
				if (!categoryRow) {
					categoryRow = this.getCategoryRow_(canvas, category);
				}

				// Flag that there is at least one metric
				this.metricsFound_ = true;
				this.addSource_(source, total, category, categoryRow, pub_date);
			}
		}
	};


	/**
	 * Get category row d3 HTML element. It will automatically
	 * add the element to the passed canvas.
	 * @param {d3Object} canvas d3 HTML element
	 * @param {Array} category Category information.
	 * @param {d3Object}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
			.getCategoryRow_ = function(canvas, category) {
		var categoryRow, categoryTitle, tooltip;

		// Build category html objects.
		categoryRow = canvas.append("div")
			.attr("class", "alm-category-row")
			.attr("style", "width: 100%; overflow: hidden;")
			.attr("id", "category-" + category.name);

		categoryTitle = categoryRow.append("h5")
			.attr("class", "alm-category-row-heading")
			.attr("id", "month-" + category.name)
			.text(category.display_name);

		tooltip = categoryTitle.append("div")
			.attr("class", "alm-category-row-info ui-state-highlight").append("span")
			.attr("class", "ui-icon ui-icon-info");

		this.j110_(tooltip).tooltip({title: category.tooltip_text, container: 'body'});

		return categoryRow;
	};


	/**
	 * Add source information to the passed category row element.
	 * @param {Object} source
	 * @param {integer} sourceTotalValue
	 * @param {Object} category
	 * @param {JQueryObject} $categoryRow
	 * @param {Function} pub_date
	 * @return {JQueryObject}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
			.addSource_ = function(source, sourceTotalValue, category, $categoryRow, pub_date) {
		var $row, $countLabel, $count, total = sourceTotalValue;

		$row = $categoryRow
			.append("div")
				.attr("class", "alm-row")
				.attr("style", "float: left")
				.attr("id", "alm-row-" + source.name + "-" + category.name);

		$countLabel = $row.append("div")
			.attr("class", "alm-count-label ui-state-default ui-corner-all");

		$countLabel.append("img")
			.attr("src", this.baseUrl_ + '/assets/' + source.name + '.png')
			.attr("alt", 'a description of the source')
			.attr("class", "label-img");

		if (source.events_url) {
			// if there is an events_url, we can link to it from the count
			$count = $countLabel.append("a")
			  .attr("href", function(d) { return source.events_url; });
		} else {
			// if no events_url, we just put in the count
			$count = $countLabel.append("span");
		}

		$count
			.attr("class", "alm-count")
			.attr("id", "alm-count-" + source.name + "-" + category.name)
			.text(this.formatNumber_(total));

		$countLabel.append("br");

		if (source.name == 'pkpTimedViews') {
			$countLabel.append("span")
					.text(source.display_name);
		} else {
			// link the source name
			$countLabel.append("a")
				.attr("href", this.baseUrl_ + "/sources/" + source.name)
				.text(source.display_name);
		}

		if (this.hasSVG_) {
			var level = false;

			// check what levels we can show
			var showDaily = false;
			var showMonthly = false;
			var showYearly = false;

			if (source.by_year) {
				level_data = this.getData_('year', source);
				var yearTotal = level_data.reduce(function(i, d) { return i + d[category.name]; }, 0);
				var numYears = this.d3_.time.year.utc.range(pub_date, new Date()).length;

				if (yearTotal >= this.minItems_.minEventsForYearly &&
						numYears >= this.minItems_.minYearsForYearly) {
					showYearly = true;
					level = 'year';
				};
			}

			if (source.by_month) {
				level_data = this.getData_('month', source);
				var monthTotal = level_data.reduce(function(i, d) { return i + d[category.name]; }, 0);
				var numMonths = this.d3_.time.month.utc.range(pub_date, new Date()).length;

				if (monthTotal >= this.minItems_.minEventsForMonthly &&
						numMonths >= this.minItems_.minMonthsForMonthly) {
					showMonthly = true;
					level = 'month';
				};
			}

			if (source.by_day){
				level_data = this.getData_('day', source);
				var dayTotal = level_data.reduce(function(i, d) { return i + d[category.name]; }, 0);
				var numMonths = this.d3_.time.month.utc.range(pub_date, new Date()).length;

				if (dayTotal >= this.minItems_.minEventsForDaily && numDays >= this.minItems_.minMonthsForDaily) {
					showDaily = true;
					level = 'day';
				};
			}

			// The level level_data should be set to the finest level
			// of granularity that we can show
			timeInterval = this.getTimeInterval_(level);

			// check there is data for
			if (showDaily || showMonthly || showYearly) {
				var $chartDiv = $row.append("div")
					.attr("style", "width: 70%; float:left;")
					.attr("class", "alm-chart-area");

				var viz = this.getAlmViz_($chartDiv, pub_date, source, category);
				this.loadData_(viz, level);

				var update_controls = function(control) {
					control.siblings('.alm-control').removeClass('active');
					control.addClass('active');
				};

				var $levelControlsDiv = $chartDiv.append("div")
					.attr("style", "width: " + (viz.margin.left + viz.width) + "px;")
					.append("div")
					.attr("style", "float:right;");

				if (showDaily) {
					 $levelControlsDiv.append("a")
						 .attr("href", "javascript:void(0)")
						 .classed("alm-control", true)
						 .classed("disabled", !showDaily)
						 .classed("active", (level == 'day'))
						 .text("daily (first 30)")
						 .on("click", this.callbackWrapper(function() {
								 if (showDaily && !$(this).hasClass('active')) {
									 this.loadData_(viz, 'day');
									 update_controls($(this));
								 }
							 })
						 );

					 $levelControlsDiv.append("text").text(" | ");
				 }

				$levelControlsDiv.append("a")
					.attr("href", "javascript:void(0)")
					.classed("alm-control", true)
					.classed("disabled", !showMonthly || !showYearly)
					.classed("active", (level == 'month'))
					.text("monthly")
					.on("click", this.callbackWrapper(function() {
							if (showMonthly && !$(this).hasClass('active')) {
								this.loadData_(viz, 'month');
								update_controls($(this));
							}
						})
					);

				if (showYearly) {
					$levelControlsDiv.append("text").text(" | ");

					 $levelControlsDiv.append("a")
							 .attr("href", "javascript:void(0)")
							 .classed("alm-control", true)
							 .classed("disabled", !showYearly || !showMonthly)
							 .classed("active", (level == 'year'))
							 .text("yearly")
							 .on("click", this.callbackWrapper(function() {
								 	if (showYearly && !$(this).hasClass('active')) {
								 		this.loadData_(viz, 'year');
								 		update_controls($(this));
									}
								 })
							 );
				}

				// keep track of all instances (mostly for debugging at this point)
				this.charts_[source.name + '-' + category.name] = viz;

				// add a clearer and styles to ensure graphs on their own line
				$row.insert("div", ":first-child")
					  .attr('style', 'clear:both');
				$row.attr('style', "width: 100%");
			};
		};

		return $row;
	};


	/**
	* Extract the date from the source
	* @param level (day|month|year)
	* @param d the datum
	* @return {Date}
	*/
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype.
			getDate_ = function(level, d) {
        switch (level) {
        	case 'year':
        		return new Date(d.year, 0, 1);
        	case 'month':
        		// js Date indexes months at 0
        		return new Date(d.year, d.month - 1, 1);
        	case 'day':
        		// js Date indexes months at 0
        		return new Date(d.year, d.month - 1, d.day);
        }
	};


	/**
	* Format the date for display
	* @param level (day|month|year)
	* @param d the datum
	* @return {String}
	*/
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype.
			getFormattedDate_ = function(level, d) {
        switch (level) {
        	case 'year':
        		return d3.time.format("%Y")(this.getDate_(level, d));
        	case 'month':
        		return d3.time.format("%b %y")(this.getDate_(level, d));
        	case 'day':
        		return d3.time.format("%d %b %y")(this.getDate_(level, d));
        }
	};


	/**
	* Extract the date from the source.
	* @param {string} level (day|month|year)
	* @param {Object} source
	* @return {Array} Metrics
	*/
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
			.getData_ = function(level, source) {
        switch (level) {
        	case 'year':
        		return source.by_year;
        	case 'month':
        		return source.by_month;
        	case 'day':
        		return source.by_day;
        }
	};


	/**
	 * Returns a d3 timeInterval for date operations.
	 * @param {string} level (day|month|year
	 * @return {Object} d3 time Interval
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
			.getTimeInterval_ = function(level) {
        switch (level) {
        	case 'year':
        		return d3.time.year.utc;
        	case 'month':
        		return d3.time.month.utc;
        	case 'day':
        		return d3.time.day.utc;
        }
	};

	/**
	 * The basic general set up of the graph itself
	 * @param {JQueryElement} chartDiv The div where the chart should go
	 * @param {Function} pub_date
	 * @param {Object} source
	 * @param {Array} category The category for 86 chart
	 * @return {Object}
	 */
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
			.getAlmViz_ = function(chartDiv, pub_date, source, category) {
		var almViz = {};

		// size parameters
		almViz.margin = {top: 10, right: 40, bottom: 0, left: 40};
		almViz.width = 400 - almViz.margin.left - almViz.margin.right;
		almViz.height = 100 - almViz.margin.top - almViz.margin.bottom;

		// div where everything goes
		almViz.chartDiv = chartDiv;

        // publication date
        almViz.pub_date = pub_date;

        // source data and which category
        almViz.category = category;
        almViz.source = source;

        // just for record keeping
        almViz.name = source.name + '-' + category.name;

        almViz.x = this.d3_.time.scale();
        almViz.x.range([0, almViz.width]);

        almViz.y = this.d3_.scale.linear();
        almViz.y.range([almViz.height, 0]);

        almViz.z = this.d3_.scale.ordinal();
        almViz.z.range(['main', 'alt']);

        // the chart
        almViz.svg = almViz.chartDiv.append("svg")
                .attr("width", almViz.width + almViz.margin.left + almViz.margin.right)
                .attr("height", almViz.height + almViz.margin.top + almViz.margin.bottom)
                .append("g")
                .attr("transform", "translate(" + almViz.margin.left + "," + almViz.margin.top + ")");

        // draw the bars g first so it ends up underneath the axes
        almViz.bars = almViz.svg.append("g");

        almViz.svg.append("g")
        	.attr("class", "x axis")
        	.attr("transform", "translate(0," + (almViz.height - 1) + ")");
        almViz.svg.append("g")
        	.attr("class", "y axis");

        return almViz;
	};


	/**
	* Takes in the basic set up of a graph and loads the data itself
	* @param {Object} viz AlmViz object
	* @param {string} level (day|month|year)
	*/
	$.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype
		.loadData_ = function(viz, level) {
	        this.d3_.select("#alm > #loading").remove();

	        var pub_date = viz.pub_date;
	        var category = viz.category;
	        var level_data = this.getData_(level, viz.source);
	        var timeInterval = this.getTimeInterval_(level);

	        var end_date = new Date();
	        // use only first 29 days if using day view
	        // close out the year otherwise
	        if (level == 'day') {
	        	end_date = timeInterval.offset(pub_date, 29);
	        } else {
	        	end_date = this.d3_.time.year.utc.ceil(end_date);
	        }

	        //
	        // Domains for x and y
	        //
	        // a time x axis, between pub_date and end_date
	        viz.x.domain([timeInterval.floor(pub_date), end_date]);

	        // a linear axis from 0 to max value found
	        viz.y.domain([0, this.d3_.max(level_data, function(d) { return d[category.name]; })]);

	        //
	        // Axis
	        //
	        // a linear axis between publication date and current date
	        viz.xAxis = this.d3_.svg.axis()
	                .scale(viz.x)
	                .tickSize(0)
	                .ticks(0);

	        // a linear y axis between 0 and max value found in data
	        viz.yAxis = this.d3_.svg.axis()
	                .scale(viz.y)
	                .orient("left")
	                .tickSize(0)
	                .tickValues([this.d3_.max(viz.y.domain())]) // only one tick at max
	                .tickFormat(this.d3_.format(",d"));

	        //
	        // The chart itself
	        //

	        // TODO: these transitions could use a little work
	        // Use to call getDate_ method statically, so we don't
	        // need to use callback wrappers.
	        var staticCallPath = $.pkp.plugins.generic.alm.ALMVisualizationHandler.prototype;
	        var bars = viz.bars.selectAll(".bar")
	                .data(level_data, function(d) {
	                	return staticCallPath.getDate_(level, d);
	                });

	        bars
	         .enter().append("rect")
	                .attr("class", function(d) {
	                	return "bar " + viz.z((level == 'day' ?
	                		d3.time.weekOfYear(staticCallPath.getDate_(level, d)) : d.year)); })


	                .attr("y", viz.height)
	                .attr("height", 0);

	        bars
	         .attr("x", function(d) { return viz.x(staticCallPath.getDate_(level, d)) + 1; }) // padding of 2, 1 each side
	         .attr("width", (viz.width/(timeInterval.range(pub_date, end_date).length + 1))-2);

	        bars.transition()
	         .duration(1000)
	         .attr("width", (viz.width/(timeInterval.range(pub_date, end_date).length + 1)) - 2)
	         .attr("y", function(d) { return viz.y(d[category.name]); })
	         .attr("height", function(d) { return viz.height - viz.y(d[category.name]); });

	        bars
	         .exit().transition()
	                .attr("y", viz.height)
	                .attr("height", 0);

	        bars
	         .exit().transition().delay(1000)
	                .remove();

	        viz.svg.selectAll(".y.axis")
	                .call(viz.yAxis);

	        viz.svg.selectAll(".x.axis")
	                .call(viz.xAxis);

	        // add in some tool tips
	        viz.bars.selectAll("rect").each(this.callbackWrapper(
		        function(element, d,i){
		        	this.j110_(element).tooltip('destroy'); // need to destroy so all bars get updated
		        	this.j110_(element).tooltip({title: this.formatNumber_(d[category.name]) +
		        		" in " + this.getFormattedDate_(level, d), container: "body"});
		        })
	        );
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
