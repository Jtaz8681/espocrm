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
import type {Button, DropdownItem} from 'views/record/detail';
import {ButtonComponent, DropdownItemComponent} from 'components/controls';
import {h, fragment, VNode} from 'bullbone';

export default class DetailRecordButtonsView extends View<{
    options: {
        dataProvider: () => {
            buttonList: Button[],
            dropdownItemList: (DropdownItem | false)[],
            allDisabled: boolean,
        },
        actionClassName: string,
        entityType: string | null,
    },
}> {

    readonly useVirtualDom = true

    content(): VNode {
        const data = this.data();

        const buttons: any[] = [];

        data.buttonList.forEach(it => {
            let className = this.options.actionClassName;

            if (it.iconClass && !(it.label || it.labelTranslation || it.text || it.html)) {
                className += ' btn-icon';
            } else {
                className += ' btn-xs-wide';
            }

            buttons.push(
                new ButtonComponent({
                    name: it.name,
                    action: it.name,
                    scope: this.options.entityType,
                    label: it.label,
                    labelTranslation: it.labelTranslation,
                    style: it.style,
                    title: it.title,
                    titleTranslation: it.titleTranslation,
                    text: it.text,
                    className: className,
                    hidden: it.hidden,
                    disabled: it.disabled || data.allDisabled,
                    iconClass: it.iconClass,
                    html: it.html,
                }).node()
            );
        });

        const elements: any[] = [...buttons];

        if (data.dropdownItemList.length) {
            const icon = h('span', {class: {'fas': true, 'fa-ellipsis-h': true}});

            elements.push(
                h('button', {
                    key: '_dropdown-button',
                    attrs: {type: 'button'},
                    class: {
                        'btn': true,
                        'btn-default': true,
                        'dropdown-toggle': true,
                        'dropdown-item-list-button': true,
                    },
                    dataset: {toggle: 'dropdown'}
                }, icon)
            );

            const dropdownItems: VNode[] = [];

            data.dropdownItemList.forEach(it => {
                if (it === false) {
                    dropdownItems.push(
                        h('li', {class: {'divider': true}})
                    );

                    return;
                }

                dropdownItems.push(
                    new DropdownItemComponent({
                        name: it.name,
                        action: it.name,
                        scope: this.options.entityType,
                        label: it.label,
                        labelTranslation: it.labelTranslation,
                        title: it.title,
                        titleTranslation: it.titleTranslation,
                        text: it.text,
                        className: data.actionClassName,
                        hidden: it.hidden,
                        disabled: it.disabled || data.allDisabled,
                        html: it.html,
                        iconClass: it.iconClass,
                    }).node()
                );
            });

            elements.push(
                h('ul', {
                    class: {'dropdown-menu': true, 'pull-left': true},
                }, dropdownItems)
            );
        }

        return fragment(elements);
    }

    protected data() {
        const data = this.options.dataProvider();

        const dropdownEmpty = data.dropdownItemList.filter(it => it && !it.hidden).length === 0;

        const dropdownItemList = data.dropdownItemList
            .filter(it => it === false || !it.hidden)
            .filter((it, i, list) => {
                if (it === false && (i === 0 || i === list.length - 1)) {
                    return false;
                }

                if (it === false && list[i - 1] === false) {
                    return false;
                }

                return true;
            });

        return {
            buttonList: data.buttonList.filter(it => !it.hidden),
            dropdownItemList: dropdownItemList,
            entityType: this.options.entityType,
            actionClassName: this.options.actionClassName,
            dropdownEmpty: dropdownEmpty,
            allDisabled: data.allDisabled,
        };
    }
}
