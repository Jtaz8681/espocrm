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

define('crm:views/event-confirmation/confirmation', ['view'], function (Dep) {

    return Dep.extend({

        template: 'crm:event-confirmation/confirmation',

        data: function () {
            let style = this.actionData.style || 'default';

            return {
                actionData: this.actionData,
                style: style,
                dateStart: this.actionData.dateStart ?
                    this.convertDateTime(this.actionData.dateStart) : null,
                sentDateStart: this.actionData.sentDateStart ?
                    this.convertDateTime(this.actionData.sentDateStart) : null,
                dateStartChanged: this.actionData.sentDateStart &&
                    this.actionData.dateStart !== this.actionData.sentDateStart,
                actionDataList: this.getActionDataList(),
            };
        },

        setup: function () {
            this.actionData = this.options.actionData;
        },

        getActionDataList: function () {
            let actionMap = {
                'Accepted': 'accept',
                'Declined': 'decline',
                'Tentative': 'tentative',
            };

            let statusList = ['Accepted', 'Tentative', 'Declined'];

            if (!statusList.includes(this.actionData.status)) {
                return null;
            }

            let url = window.location.href.replace('action=' + actionMap[this.actionData.status], 'action={action}');

            return statusList.map(item => {
                let active = item === this.actionData.status;

                return {
                    active: active,
                    link: active ? '' : url.replace('{action}', actionMap[item]),
                    label: this.actionData.statusTranslation[item],
                };
            });
        },

        convertDateTime: function (value) {
            let timezone = this.getConfig().get('timeZone');

            let m = this.getDateTime().toMoment(value)
                .tz(timezone);

            return m.format(this.getDateTime().getDateTimeFormat()) + ' ' +
                m.format('Z z');
        },
    });
});
