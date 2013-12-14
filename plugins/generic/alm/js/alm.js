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
function AlmViz(options) {
    var stats, additionalStats;

    stats = options.almStatsJson;
    if (stats[0] !== undefined && stats[0].sources !== undefined) {
        additionalStats = options.additionalStatsJson;
        if (additionalStats) {
            stats[0].sources.push(additionalStats);
        }
    }

    if (stats) {
        this.stats = stats;
    }

    this.baseUrl_ = options.baseUrl;
    this.hasSVG_ = document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1");
    this.hasIcon = options.hasIcon;
    this.minItems_ = options.minItemsToShowGraph;
    this.categories_ = options.categories;
    this.showTitle = options.showTitle;
    this.formatNumber_ = d3.format(",d");

    /**
     * Initialize the visualization.
     */
    this.initViz = function() {
        var almviz = this;

        var data = almviz.stats,
            canvas = d3.select("#alm"),
            index, limit, pub_date, category;

        d3.select("#alm > #loading").remove();

        // extract publication date
        pub_date = d3.time.format.iso.parse(data[0]["publication_date"]);

        if (almviz.showTitle) {
            canvas.append("a")
                .attr('href', 'http://dx.doi.org/' + data[0].doi)
                .attr("class", "title")
                .text(data[0].title);
        }

        // loop through categories
        for (index = 0, limit = almviz.categories_.length; index < limit; index++) {
            category = almviz.categories_[index];
            almviz.addCategory_(canvas, category, pub_date, data);
        }

        if (!almviz.metricsFound_) {
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
    this.addCategory_ = function(canvas, category, pub_date, data) {
        var almviz = this;
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
                    categoryRow = almviz.getCategoryRow_(canvas, category);
                }

                // Flag that there is at least one metric
                almviz.metricsFound_ = true;
                almviz.addSource_(source, total, category, categoryRow, pub_date);
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
    this.getCategoryRow_ = function(canvas, category) {
        var almviz = this;
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

        $(tooltip).tooltip({title: category.tooltip_text, container: 'body'});

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
    this.addSource_ = function(source, sourceTotalValue, category, $categoryRow, pub_date) {
        var almviz = this;
        var $row, $countLabel, $count, total = sourceTotalValue;

        $row = $categoryRow
            .append("div")
            .attr("class", "alm-row")
            .attr("style", "float: left")
            .attr("id", "alm-row-" + source.name + "-" + category.name);

        $countLabel = $row.append("div")
            .attr("class", "alm-count-label ui-state-default ui-corner-all");

        if (almviz.hasIcon.indexOf(source.name) >= 0) {
            $countLabel.append("img")
                .attr("src", almviz.baseUrl_ + '/assets/' + source.name + '.png')
                .attr("alt", 'a description of the source')
                .attr("class", "label-img");
        }

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
            .text(almviz.formatNumber_(total));

        $countLabel.append("br");

        if (source.name == 'pkpTimedViews') {
            $countLabel.append("span")
                .text(source.display_name);
        } else {
            // link the source name
            $countLabel.append("a")
                .attr("href", almviz.baseUrl_ + "/sources/" + source.name)
                .text(source.display_name);
        }

        if (almviz.hasSVG_) {
            var level = false;

            // check what levels we can show
            var showDaily = false;
            var showMonthly = false;
            var showYearly = false;

            if (source.by_year) {
                level_data = almviz.getData_('year', source);
                var yearTotal = level_data.reduce(function(i, d) { return i + d[category.name]; }, 0);
                var numYears = d3.time.year.utc.range(pub_date, new Date()).length;

                if (yearTotal >= almviz.minItems_.minEventsForYearly &&
                    numYears >= almviz.minItems_.minYearsForYearly) {
                    showYearly = true;
                    level = 'year';
                };
            }

            if (source.by_month) {
                level_data = almviz.getData_('month', source);
                var monthTotal = level_data.reduce(function(i, d) { return i + d[category.name]; }, 0);
                var numMonths = d3.time.month.utc.range(pub_date, new Date()).length;

                if (monthTotal >= almviz.minItems_.minEventsForMonthly &&
                    numMonths >= almviz.minItems_.minMonthsForMonthly) {
                    showMonthly = true;
                    level = 'month';
                };
            }

            if (source.by_day){
                level_data = almviz.getData_('day', source);
                var dayTotal = level_data.reduce(function(i, d) { return i + d[category.name]; }, 0);
                var numDays = d3.time.day.utc.range(pub_date, new Date()).length;

                if (dayTotal >= almviz.minItems_.minEventsForDaily && numDays >= almviz.minItems_.minDaysForDaily) {
                    showDaily = true;
                    level = 'day';
                };
            }

            // The level and level_data should be set to the finest level
            // of granularity that we can show
            timeInterval = almviz.getTimeInterval_(level);

            // check there is data for
            if (showDaily || showMonthly || showYearly) {
                var $chartDiv = $row.append("div")
                    .attr("style", "width: 70%; float:left;")
                    .attr("class", "alm-chart-area");

                var viz = almviz.getCanvas_($chartDiv, pub_date, source, category);
                almviz.loadData_(viz, level);

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
                        .on("click", function() {
                            if (showDaily && !$(this).hasClass('active')) {
                                almviz.loadData_(viz, 'day');
                                update_controls($(this));
                            }
                        }
                    );

                    $levelControlsDiv.append("text").text(" | ");
                }

                if (showMonthly) {
                    $levelControlsDiv.append("a")
                        .attr("href", "javascript:void(0)")
                        .classed("alm-control", true)
                        .classed("disabled", !showMonthly || !showYearly)
                        .classed("active", (level == 'month'))
                        .text("monthly")
                        .on("click", function() { if (showMonthly && !$(this).hasClass('active')) {
                            almviz.loadData_(viz, 'month');
                            update_controls($(this));
                        } });

                    if (showYearly) {
                        $levelControlsDiv.append("text")
                            .text(" | ");
                    }

                }

                if (showYearly) {
                    $levelControlsDiv.append("text").text(" | ");

                    $levelControlsDiv.append("a")
                        .attr("href", "javascript:void(0)")
                        .classed("alm-control", true)
                        .classed("disabled", !showYearly || !showMonthly)
                        .classed("active", (level == 'year'))
                        .text("yearly")
                        .on("click", function() {
                            if (showYearly && !$(this).hasClass('active')) {
                                almviz.loadData_(viz, 'year');
                                update_controls($(this));
                            }
                        }
                    );
                }

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
    this.getDate_ = function(level, d) {
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
    this.getFormattedDate_ = function(level, d) {
        var almviz = this;
        switch (level) {
            case 'year':
                return d3.time.format("%Y")(almviz.getDate_(level, d));
            case 'month':
                return d3.time.format("%b %y")(almviz.getDate_(level, d));
            case 'day':
                return d3.time.format("%d %b %y")(almviz.getDate_(level, d));
        }
    };


    /**
     * Extract the date from the source.
     * @param {string} level (day|month|year)
     * @param {Object} source
     * @return {Array} Metrics
     */
    this.getData_ = function(level, source) {
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
    this.getTimeInterval_ = function(level) {
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
    this.getCanvas_ = function(chartDiv, pub_date, source, category) {
        var canvas = {};

        // size parameters
        canvas.margin = {top: 10, right: 40, bottom: 0, left: 40};
        canvas.width = 400 - canvas.margin.left - canvas.margin.right;
        canvas.height = 100 - canvas.margin.top - canvas.margin.bottom;

        // div where everything goes
        canvas.chartDiv = chartDiv;

        // publication date
        canvas.pub_date = pub_date;

        // source data and which category
        canvas.category = category;
        canvas.source = source;

        // just for record keeping
        canvas.name = source.name + '-' + category.name;

        canvas.x = d3.time.scale();
        canvas.x.range([0, canvas.width]);

        canvas.y = d3.scale.linear();
        canvas.y.range([canvas.height, 0]);

        canvas.z = d3.scale.ordinal();
        canvas.z.range(['main', 'alt']);

        // the chart
        canvas.svg = canvas.chartDiv.append("svg")
            .attr("width", canvas.width + canvas.margin.left + canvas.margin.right)
            .attr("height", canvas.height + canvas.margin.top + canvas.margin.bottom)
            .append("g")
            .attr("transform", "translate(" + canvas.margin.left + "," + canvas.margin.top + ")");


        // draw the bars g first so it ends up underneath the axes
        canvas.bars = canvas.svg.append("g");

        // and the shadow bars on top for the tooltips
        canvas.barsForTooltips = canvas.svg.append("g");

        canvas.svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + (canvas.height - 1) + ")");

        canvas.svg.append("g")
            .attr("class", "y axis");

        return canvas;
    };


    /**
     * Takes in the basic set up of a graph and loads the data itself
     * @param {Object} viz AlmViz object
     * @param {string} level (day|month|year)
     */
    this.loadData_ = function(viz, level) {
        var almviz = this;

        var pub_date = viz.pub_date;
        var category = viz.category;
        var level_data = almviz.getData_(level, viz.source);
        var timeInterval = almviz.getTimeInterval_(level);

        var end_date = new Date();
        // use only first 29 days if using day view
        // close out the year otherwise
        if (level == 'day') {
            end_date = timeInterval.offset(pub_date, 29);
        } else {
            end_date = d3.time.year.utc.ceil(end_date);
        }

        //
        // Domains for x and y
        //
        // a time x axis, between pub_date and end_date
        viz.x.domain([timeInterval.floor(pub_date), end_date]);

        // a linear axis from 0 to max value found
        viz.y.domain([0, d3.max(level_data, function(d) { return d[category.name]; })]);

        //
        // Axis
        //
        // a linear axis between publication date and current date
        viz.xAxis = d3.svg.axis()
            .scale(viz.x)
            .tickSize(0)
            .ticks(0);

        // a linear y axis between 0 and max value found in data
        viz.yAxis = d3.svg.axis()
            .scale(viz.y)
            .orient("left")
            .tickSize(0)
            .tickValues([d3.max(viz.y.domain())])   // only one tick at max
            .tickFormat(d3.format(",d"));

        //
        // The chart itself
        //

        // TODO: these transitions could use a little work
        var barWidth = Math.max((viz.width/(timeInterval.range(pub_date, end_date).length + 1)) - 2, 1);

        var barsForTooltips = viz.barsForTooltips.selectAll(".barsForTooltip")
            .data(level_data, function(d) { return almviz.getDate_(level, d); });

        barsForTooltips
            .exit()
            .remove();

        var bars = viz.bars.selectAll(".bar")
            .data(level_data, function(d) { return almviz.getDate_(level, d); });

        bars
            .enter().append("rect")
            .attr("class", function(d) { return "bar " + viz.z((level == 'day' ? d3.time.weekOfYear(almviz.getDate_(level, d)) : d.year)); })
            .attr("y", viz.height)
            .attr("height", 0);

        bars
            .attr("x", function(d) { return viz.x(almviz.getDate_(level, d)) + 2; }) // padding of 2, 1 each side
            .attr("width", barWidth);

        bars.transition()
            .duration(1000)
            .attr("width", barWidth)
            .attr("y", function(d) { return viz.y(d[category.name]); })
            .attr("height", function(d) { return viz.height - viz.y(d[category.name]); });

        bars
            .exit().transition()
            .attr("y", viz.height)
            .attr("height", 0);

        bars
            .exit()
            .remove();

        viz.svg
            .select(".x.axis")
            .call(viz.xAxis);

        viz.svg
            .transition().duration(1000)
            .select(".y.axis")
            .call(viz.yAxis);

        barsForTooltips
            .enter().append("rect")
            .attr("class", function(d) { return "barsForTooltip " + viz.z((level == 'day' ? d3.time.weekOfYear(almviz.getDate_(level, d)) : d.year)); });

        barsForTooltips
            .attr("width", barWidth + 2)
            .attr("x", function(d) { return viz.x(almviz.getDate_(level, d)) + 1; })
            .attr("y", function(d) { return viz.y(d[category.name]) - 1; })
            .attr("height", function(d) { return viz.height - viz.y(d[category.name]) + 1; });


        // add in some tool tips
        viz.barsForTooltips.selectAll("rect").each(
            function(d,i){
                $(this).tooltip('destroy'); // need to destroy so all bars get updated
                $(this).tooltip({title: almviz.formatNumber_(d[category.name]) + " in " + almviz.getFormattedDate_(level, d), container: "body"});
            }
        );
    }
}