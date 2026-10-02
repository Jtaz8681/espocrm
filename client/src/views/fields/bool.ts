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

/** @module views/fields/bool */

import BaseFieldView from 'views/fields/base';
import Select from 'ui/select';
import {BaseOptions as BaseOptions} from 'views/fields/base';
import {BaseParams as BaseParams} from 'views/fields/base';
import {BaseViewSchema} from 'views/fields/base';

export interface BoolParams extends BaseParams {}

export interface BoolOptions extends BaseOptions {}

/**
 * A boolean field (checkbox).
 */
export default class BoolFieldView<
    S extends BaseViewSchema = BaseViewSchema,
    O extends BoolOptions = BoolOptions,
    P extends BoolParams = BoolParams,
> extends BaseFieldView<S, O, P> {

    readonly type = 'bool'

    protected listTemplate = 'fields/bool/list'
    protected detailTemplate = 'fields/bool/detail'
    protected editTemplate = 'fields/bool/edit'
    protected searchTemplate = 'fields/bool/search'

    protected validations: BaseFieldView['validations'] = []
    initialSearchIsNotIdle = true

    data() {
        const data = super.data();

        data.valueIsSet = this.model.has(this.name);

        return data;
    }

    protected afterRender() {
        super.afterRender();

        if (this.mode === this.MODE_SEARCH && this.$element) {
            this.$element.on('change', () => {
                this.trigger('change');
            });

            Select.init(this.$element);
        }
    }

    fetch() {
        const element = this.mainInputElement as HTMLInputElement | null

        const value = element?.checked

        const data = {} as Record<string, any>;

        data[this.name] = value;

        return data;
    }

    fetchSearch() {
        const type = this.$element?.val();

        if (!type) {
            return null;
        }

        if (type === 'any') {
            return {
                type: 'or',
                value: [
                    {
                        type: 'isTrue',
                        attribute: this.name,
                    },
                    {
                        type: 'isFalse',
                        attribute: this.name,
                    },
                ],
                data: {
                    type: type,
                },
            };
        }

        return {
            type: type,
            data: {
                type: type,
            },
        };
    }

    protected getSearchType(): string {
        return this.getSearchParamsData().type ?? this.searchParams?.type ?? 'isTrue';
    }
}
