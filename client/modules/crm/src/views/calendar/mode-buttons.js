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

import View from 'view';

/** @module modules/crm/views/calendar/mode-buttons */

class CalendarModeButtons extends View {

    template = 'crm:calendar/mode-buttons'

    visibleModeListCount = 3

    data() {
        const scopeFilterList = Espo.Utils.clone(this.scopeList);
        scopeFilterList.unshift('all');

        const scopeFilterDataList = [];

        this.scopeList.forEach(scope => {
            const o = {scope: scope};

            if (!this.getCalendarParentView().enabledScopeList.includes(scope)) {
                o.disabled = true;
            }

            scopeFilterDataList.push(o);
        });

        return {
            mode: this.mode,
            visibleModeDataList: this.getVisibleModeDataList(),
            hiddenModeDataList: this.getHiddenModeDataList(),
            scopeFilterDataList: scopeFilterDataList,
            isCustomViewAvailable: this.isCustomViewAvailable,
            hasMoreItems: this.isCustomViewAvailable,
            hasWorkingTimeCalendarLink: this.getAcl().checkScope('WorkingTimeCalendar'),
        };
    }

    /**
     * @return {
     *     import('modules/crm/views/calendar/calendar').default|
     *     import('modules/crm/views/calendar/timeline').default
     * }
     */
    getCalendarParentView() {
        // noinspection JSValidateTypes
        return this.getParentView();
    }

    setup() {
        this.isCustomViewAvailable = this.options.isCustomViewAvailable;
        this.modeList = this.options.modeList;
        this.scopeList = this.options.scopeList;
        this.mode = this.options.mode;
    }

    /**
     * @param {boolean} [originalOrder]
     * @return {Object.<string, *>[]}
     */
    getModeDataList(originalOrder) {
        const list = [];

        this.modeList.forEach(name => {
            const o = {
                mode: name,
                label: this.translate(name, 'modes', 'Calendar'),
                labelShort: this.translate(name, 'modes', 'Calendar').substring(0, 2),
            };

            list.push(o);
        });

        if (this.isCustomViewAvailable) {
            (this.getPreferences().get('calendarViewDataList') || []).forEach(item => {
                item = Espo.Utils.clone(item);

                item.mode = 'view-' + item.id;
                item.label = item.name;
                item.labelShort = (item.name || '').substring(0, 2);
                list.push(item);
            });
        }

        if (originalOrder) {
            return list;
        }

        let currentIndex = -1;

        list.forEach((item, i) => {
            if (item.mode === this.mode) {
                currentIndex = i;
            }
        });

        return list;
    }

    getVisibleModeDataList() {
        const fullList = this.getModeDataList();

        const current = fullList.find(it => it.mode === this.mode);

        const list = fullList.slice(0, this.visibleModeListCount);

        if (current && !list.find(it => it.mode === this.mode)) {
            list.push(current);
        }

        return list;
    }

    getHiddenModeDataList() {
        const fullList = this.getModeDataList();

        const list = [];

        fullList.forEach((o, i) => {
            if (i < this.visibleModeListCount) {
                return;
            }

            list.push(o);
        });

        return list;
    }
}

export default CalendarModeButtons;
