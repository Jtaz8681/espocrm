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

define('crm:views/dashlets/sales-pipeline', ['crm:views/dashlets/abstract/chart', 'lib!espo-funnel-chart'],
function (Dep) {

    return Dep.extend({

        name: 'SalesPipeline',

        setupDefaultOptions: function () {
            this.defaultOptions['dateFrom'] = this.defaultOptions['dateFrom'] || moment().format('YYYY') + '-01-01';
            this.defaultOptions['dateTo'] = this.defaultOptions['dateTo'] || moment().format('YYYY') + '-12-31';
        },

        url: function () {
            var url = 'Opportunity/action/reportSalesPipeline?dateFilter='+ this.getDateFilter();

            if (this.getDateFilter() === 'between') {
                url += '&dateFrom=' + this.getOption('dateFrom') + '&dateTo=' + this.getOption('dateTo');
            }

            if (this.getOption('useLastStage')) {
                url += '&useLastStage=true';
            }

            if (this.getOption('teamId')) {
                url += '&teamId=' + this.getOption('teamId');
            }

            return url;
        },

        isNoData: function () {
            return this.isEmpty;
        },

        prepareData: function (response) {
            let list = [];

            this.isEmpty = true;

            response.dataList.forEach(item => {
                if (item.value) {
                    this.isEmpty = false;
                }

                list.push({
                    stageTranslated: this.getLanguage().translateOption(item.stage, 'stage', 'Opportunity'),
                    value: item.value,
                    stage: item.stage,
                });
            });

            return list;
        },

        setup: function () {
            this.currency = this.getConfig().get('defaultCurrency');
            this.currencySymbol = this.getMetadata().get(['app', 'currency', 'symbolMap', this.currency]) || '';

            this.chartData = [];
        },

        draw: function () {
            let colors = Espo.Utils.clone(this.colorList);

            this.chartData.forEach((item, i) => {
                if (i + 1 > colors.length) {
                    colors.push('#164');
                }

                if (this.chartData.length === i + 1 && item.stage === 'Closed Won') {
                    colors[i] = this.successColor;
                }

                this.chartData[i].color = colors[i];
            });

            this.$container.empty();

            let tooltipStyleString =
                'opacity:0.7;background-color:#000;color:#fff;position:absolute;'+
                'padding:2px 8px;-moz-border-radius:4px;border-radius:4px;white-space:nowrap;'

            // noinspection JSUnusedGlobalSymbols
            new EspoFunnel.Funnel(
                this.$container.get(0),
                {
                    colors: colors,
                    outlineColor: this.hoverColor,
                    callbacks: {
                        tooltipHtml: (i) => {
                            let value = this.chartData[i].value;

                            return this.chartData[i].stageTranslated +
                                '<br>' + this.currencySymbol +
                                '<span class="numeric-text">' +
                                this.formatNumber(value, true) +
                                '</span>';
                        },
                    },
                    tooltipClassName: 'flotr-mouse-value',
                    tooltipStyleString: tooltipStyleString,
                },
                this.chartData
            );

            this.drawLegend();
            this.adjustLegend();
        },

        drawLegend: function () {
            let number = this.getLegendColumnNumber();
            let $container = this.$el.find('.legend-container');

            let html = '<table style="font-size: smaller; color:' + this.textColor + '">';

            this.chartData.forEach((item, i) => {
                if (i % number === 0) {
                    if (i > 0) {
                        html += '</tr>';
                    }

                    html += '<tr>';
                }

                let stageTranslated = this.getHelper().escapeString(item.stageTranslated);

                let box = '<div style="border: var(--1px) solid transparent; padding: var(--1px)">'+
                    '<div style="width: var(--13px); height: var(--9px); border: var(--1px) solid ' + item.color + '">'+
                    '<div style="width: var(--14px); height: var(--10px); background-color:' + item.color + ';"></div></div></div>';

                html += '<td class="flotr-legend-color-box">' + box + '</td>';

                html += '<td class="flotr-legend-label"><span title="' + stageTranslated + '">'+
                    stageTranslated + '</span></td>';
            });

            html += '</tr>';
            html += '</table>';

            $container.html(html);
        },
    });
});
