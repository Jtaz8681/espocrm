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

import BaseDashletView from 'views/dashlets/abstract/base';

class CalendarDashletView extends BaseDashletView {

    name = 'Calendar'
    noPadding = true

    templateContent =`<div class="calendar-container">{{{calendar}}}</div>`

    afterRender() {
        const mode = this.getOption('mode');

        if (mode === 'timeline') {
            const userList = [];
            const userIdList = this.getOption('usersIds') || [];
            const userNames = this.getOption('usersNames') || {};

            userIdList.forEach(id => {
                userList.push({
                    id: id,
                    name: userNames[id] || id,
                });
            });

            const viewName = this.getMetadata().get(['clientDefs', 'Calendar', 'timelineView']) ||
                'crm:views/calendar/timeline';

            this.createView('calendar', viewName, {
                selector: '> .calendar-container',
                header: false,
                calendarType: 'shared',
                userList: userList,
                enabledScopeList: this.getOption('enabledScopeList'),
                suppressLoadingAlert: true,
            }, view => {
                view.render();
            });

            return;
        }

        let teamIdList = null;

        if (['basicWeek', 'month', 'basicDay'].includes(mode)) {
            teamIdList = this.getOption('teamsIds');
        }

        const viewName = this.getMetadata().get(['clientDefs', 'Calendar', 'calendarView']) ||
            'crm:views/calendar/calendar';

        this.createView('calendar', viewName, {
            mode: mode,
            selector: '> .calendar-container',
            header: false,
            enabledScopeList: this.getOption('enabledScopeList'),
            containerSelector: this.getSelector(),
            teamIdList: teamIdList,
            scrollToNowSlots: 3,
            suppressLoadingAlert: true,
        }, view => {
            this.listenTo(view, 'view', () => {
                if (this.getOption('mode') === 'month') {
                    const title = this.getOption('title');

                    const $container = $('<span>')
                        .append(
                            $('<span>').text(title),
                            ' <span class="chevron-right"></span> ',
                            $('<span>').text(view.getTitle())
                        );

                    const $headerSpan = this.$el.closest('.panel').find('.panel-heading > .panel-title > span');

                    $headerSpan.html($container.get(0).innerHTML);
                }
            });

            view.render();

            this.on('resize', () => {
                setTimeout(() => view.adjustSize(), 50);
            });
        });
    }

    setupActionList() {
        this.actionList.unshift({
            name: 'viewCalendar',
            text: this.translate('View Calendar', 'labels', 'Calendar'),
            url: '#Calendar',
            iconClass: 'far fa-calendar-alt',
            onClick: () => this.actionViewCalendar(),
        });
    }

    setupButtonList() {
        if (this.getOption('mode') !== 'timeline') {
            this.buttonList.push({
                name: 'previous',
                html: '<span class="fas fa-chevron-left"></span>',
                onClick: () => this.actionPrevious(),
            });

            this.buttonList.push({
                name: 'next',
                html: '<span class="fas fa-chevron-right"></span>',
                onClick: () => this.actionNext(),
            });
        }
    }

    /**
     * @return {
     *     import('modules/crm/views/calendar/calendar').default |
     *     import('modules/crm/views/calendar/timeline').default
     * }
     */
    getCalendarView() {
        return this.getView('calendar');
    }

    actionRefresh() {
        const view = this.getCalendarView();

        if (!view) {
            return;
        }

        view.actionRefresh();
    }

    autoRefresh() {
        const view = this.getCalendarView();

        if (!view) {
            return;
        }

        view.actionRefresh({suppressLoadingAlert: true});
    }

    actionNext() {
        const view = this.getCalendarView();

        if (!view) {
            return;
        }

        view.actionNext();
    }

    actionPrevious() {
        const view = this.getCalendarView();

        if (!view) {
            return;
        }

        view.actionPrevious();
    }

    actionViewCalendar() {
        this.getRouter().navigate('#Calendar', {trigger: true});
    }
}

export default CalendarDashletView;
