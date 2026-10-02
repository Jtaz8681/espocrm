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

import EditRecordView from 'views/record/edit';
import Detail from 'views/email-account/record/detail';

export default class extends EditRecordView {

    hasModifyDetailLayout = true

    setup() {
        super.setup();

        Detail.prototype.setupFieldsBehaviour.call(this);
        Detail.prototype.initSslFieldListening.call(this);
        Detail.prototype.initSmtpFieldsControl.call(this);

        if (this.getUser().isAdmin()) {
            this.setFieldNotReadOnly('assignedUser');
        } else {
            this.setFieldReadOnly('assignedUser');
        }
    }

    modifyDetailLayout(layout) {
        Detail.prototype.modifyDetailLayout.call(this, layout);
    }

    setupFieldsBehaviour() {
        Detail.prototype.setupFieldsBehaviour.call(this);
    }

    controlStatusField() {
        Detail.prototype.controlStatusField.call(this);
    }

    controlSmtpFields() {
        Detail.prototype.controlSmtpFields.call(this);
    }

    controlSmtpAuthField() {
        Detail.prototype.controlSmtpAuthField.call(this);
    }

    wasFetched() {
        Detail.prototype.wasFetched.call(this);
    }
}
