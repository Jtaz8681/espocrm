/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

define('crm:views/dashlets/sales-by-month', ['crm:views/dashlets/abstract/chart'], function (Dep) {

    return Dep.extend({

        name: 'SalesByMonth',

        columnWidth: 50,

        setupDefaultOptions: function () {
            this.defaultOptions['dateFrom'] = this.defaultOptions['dateFrom'] || moment().format('YYYY') + '-01-01';
            this.defaultOptions['dateTo'] = this.defaultOptions['dateTo'] || moment().format('YYYY') + '-12-31';
        },

        url: function () {
            var url = 'Opportunity/action/reportSalesByMonth?dateFilter='+ this.getDateFilter();

            if (this.getDateFilter() === 'between') {
                url += '&dateFrom=' + this.getOption('dateFrom') + '&dateTo=' + this.getOption('dateTo');
            }

            return url;
        },

        getLegendHeight: function () {
            return 0;
        },

        isNoData: function () {
            return this.isEmpty;
        },

        prepareData: function (response) {
            var monthList = this.monthList = response.keyList;

            var dataMap = response.dataMap || {};
            var values = [];

            monthList.forEach(month => {
                values.push(dataMap[month]);
            });

            this.chartData = [];

            this.isEmpty = true;

            var mid = 0;

            if (values.length) {
                mid = values.reduce((a, b) => a + b) / values.length;
            }

            var data = [];
            var max = 0;

            values.forEach((value, i) => {
                if (value) {
                    this.isEmpty = false;
                }

                if (value && value > max) {
                    max = value;
                }

                data.push({
                    data: [[i, value]],
                    color: (value >= mid) ? this.successColor : this.colorBad,
                });
            });

            this.max = max;

            return data;
        },

        setup: function () {
            this.currency = this.getConfig().get('defaultCurrency');
            this.currencySymbol = this.getMetadata().get(['app', 'currency', 'symbolMap', this.currency]) || '';

            this.colorBad = this.successColor;
        },

        getTickNumber: function () {
            var containerWidth = this.$container.width();

            return Math.floor(containerWidth / this.columnWidth * this.fontSizeFactor);
        },

        draw: function () {
            var tickNumber = this.getTickNumber();

            this.flotr.draw(this.$container.get(0), this.chartData, {
                shadowSize: false,
                bars: {
                    show: true,
                    horizontal: false,
                    shadowSize: 0,
                    lineWidth: 1 * this.fontSizeFactor,
                    fillOpacity: 1,
                    barWidth: 0.5,
                },
                grid: {
                    horizontalLines: true,
                    verticalLines: false,
                    outline: 'sw',
                    color: this.gridColor,
                    tickColor: this.tickColor,
                },
                yaxis: {
                    min: 0,
                    showLabels: true,
                    color: this.textColor,
                    max: this.max + 0.08 * this.max,
                    tickFormatter: (value) => {
                        value =  parseFloat(value);

                        if (!value) {
                            return '';
                        }

                        if (value % 1 === 0) {
                            return this.currencySymbol +
                                '<span class="numeric-text">' +
                                this.formatNumber(Math.floor(value), false, true).toString() + '</span>';
                        }

                        return '';
                    },
                },
                xaxis: {
                    min: 0,
                    color: this.textColor,
                    noTicks: tickNumber,
                    tickFormatter: (value) => {
                        if (value % 1 === 0) {
                            let i = parseInt(value);

                            if (i in this.monthList) {
                                if (this.monthList.length - tickNumber > 5 && i === this.monthList.length - 1) {
                                    return '';
                                }

                                return moment(this.monthList[i] + '-01').format('MMM YYYY');
                            }
                        }

                        return '';
                    }
                },
                mouse: {
                    track: true,
                    relative: true,
                    lineColor: this.hoverColor,
                    position: 's',
                    autoPositionVertical: true,
                    trackFormatter: obj => {
                        let i = parseInt(obj.x);
                        let value = '';

                        if (i in this.monthList) {
                            value += moment(this.monthList[i] + '-01').format('MMM YYYY') + '<br>';
                        }

                        return value + this.currencySymbol +
                            '<span class="numeric-text">' + this.formatNumber(obj.y, true) + '</span>';
                    }
                },
            })
        },
    });
});
