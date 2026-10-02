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

import Model from 'model';
import View from 'view';

export default class extends View {

    template = 'admin/formula-sandbox/index'

    targetEntityType = null
    storageKey = 'formulaSandbox'

    setup() {
        const entityTypeList = [''].concat(
            this.getMetadata()
                .getScopeEntityList()
                .filter(item => {
                    return this.getMetadata().get(['scopes', item, 'object']);
                })
        );

        const data = {
            script: null,
            targetId: null,
            targetType: null,
            output: null,
        };

        if (this.getSessionStorage().has(this.storageKey)) {
            const storedData = this.getSessionStorage().get(this.storageKey);

            data.script = storedData.script || null;
            data.targetId = storedData.targetId || null;
            data.targetName = storedData.targetName || null;
            data.targetType = storedData.targetType || null;
        }

        const model = this.model = new Model();

        model.name = 'Formula';

        model.setDefs({
            fields: {
                targetType: {
                    type: 'enum',
                    options: entityTypeList,
                    translation: 'Global.scopeNames',
                    view: 'views/fields/entity-type',
                },
                target: {
                    type: 'link',
                    entity: data.targetType,
                },
                script: {
                    type: 'formula',
                    view: 'views/fields/formula',
                },
                output: {
                    type: 'text',
                    readOnly: true,
                    displayRawText: true,
                    tooltip: true,
                },
                errorMessage: {
                    type: 'text',
                    readOnly: true,
                    displayRawText: true,
                },
            }
        });

        model.set(data);

        this.createRecordView();

        this.listenTo(this.model, 'change:targetType', (m, v, o) => {
            if (!o.ui) {
                return;
            }

            setTimeout(() => {
                this.targetEntityType = this.model.get('targetType');

                this.model.set({
                    targetId: null,
                    targetName: null,
                }, {silent: true});

                const attributes = Espo.Utils.cloneDeep(this.model.attributes);

                this.clearView('record');

                this.model.set(attributes, {silent: true});
                this.model.defs.fields.target.entity = this.targetEntityType;

                this.createRecordView()
                    .then(view => view.render());
            }, 10);
        });

        this.listenTo(this.model, 'run', () => this.run());

        this.listenTo(this.model, 'change', (m, o) => {
            if (!o.ui) {
                return;
            }

            const dataToStore = {
                script: this.model.get('script'),
                targetType: this.model.get('targetType'),
                targetId: this.model.get('targetId'),
                targetName: this.model.get('targetName'),
            };

            this.getSessionStorage().set(this.storageKey, dataToStore);
        });
    }

    /**
     * @return {import('views/record/detail').default}
     */
    getRecordView() {
        return this.getView('record');
    }

    createRecordView() {
        return this.createView('record', 'views/admin/formula-sandbox/record/edit', {
            selector: '.record',
            model: this.model,
            targetEntityType: this.targetEntityType,
            confirmLeaveDisabled: true,
            shortcutKeysEnabled: true,
        });
    }

    updatePageTitle() {
        this.setPageTitle(this.getLanguage().translate('Formula Sandbox', 'labels', 'Admin'));
    }

    async run() {
        const script = this.model.get('script');

        this.model.set({
            output: null,
            errorMessage: null,
        });

        if (script === '' || script === null) {
            this.model.set('output', null);

            Espo.Ui.warning(
                this.translate('emptyScript', 'messages', 'Formula')
            );

            return;
        }

        this.getRecordView().disableActionItems();
        Espo.Ui.notifyWait();

        /** @var {Record} */
        let response;

        try {
            response = await Espo.Ajax.postRequest('Formula/action/run', {
                expression: script,
                targetId: this.model.get('targetId'),
                targetType: this.model.get('targetType'),
            });
        } catch (e) {
            this.getRecordView().enableActionItems();

            return;
        }

        this.getRecordView().enableActionItems();

        this.model.set('output', response.output || null);

        let errorMessage = null;

        if (!response.isSuccess) {
            errorMessage = response.message || null;
        }

        this.model.set('errorMessage', errorMessage);

        if (response.isSuccess) {
            Espo.Ui.success(
                this.translate('runSuccess', 'messages', 'Formula')
            );

            return;
        }

        if (response.isSyntaxError) {
            let msg = this.translate('checkSyntaxError', 'messages', 'Formula');

            if (response.message) {
                msg += ' ' + response.message;
            }

            Espo.Ui.error(msg);

            return;
        }

        let msg = this.translate('runError', 'messages', 'Formula');

        if (response.message) {
            msg += ' ' + response.message;
        }

        Espo.Ui.error(msg);
    }
}
