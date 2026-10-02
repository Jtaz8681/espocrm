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

import EnumFieldView from 'views/fields/enum';

export default class extends EnumFieldView {

    setup() {
        super.setup();

        if (this.isEditMode() || this.isDetailMode()) {
            this.wait(true);

            Espo.Ajax.getRequest('Admin/jobs')
                .then(data => {
                    this.params.options = data.filter(item => {
                        return !this.getMetadata().get(['entityDefs', 'ScheduledJob', 'jobs', item, 'isSystem']);
                    });

                    this.params.options.unshift('');

                    this.wait(false);
                });
        }

        if (this.model.isNew()) {
            this.on('change', () => {
                const job = this.model.get('job');

                if (job) {
                    const label = this.getLanguage().translateOption(job, 'job', 'ScheduledJob');
                    const scheduling =
                        this.getMetadata().get(`entityDefs.ScheduledJob.jobSchedulingMap.${job}`) ||
                        '*/10 * * * *';

                    this.model.set('name', label);
                    this.model.set('scheduling', scheduling);

                    return;
                }

                this.model.set('name', '');
                this.model.set('scheduling', '');
            });
        }
    }
}
