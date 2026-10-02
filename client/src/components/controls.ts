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

import Language from 'language';
import {inject} from 'di';
import {h, VNode, toVNode} from 'bullbone';

interface ButtonOptions {
    name: string | null;
    action?: string | null;
    style?: 'default' | 'success' | 'danger'| 'warning' | 'info' | 'primary' | 'text' | null;
    className?: string | null;
    scope?: string | null;
    title?: string | null;
    titleTranslation?: string | null;
    label?: string | null;
    labelTranslation?: string | null;
    link?: string | null;
    iconClass?: string | null;
    text?: string | null;
    disabled?: boolean;
    hidden?: boolean;
    html?: string | null;
    iconHtml?: string | null;
    data?: Record<string, unknown>;
    onClick?: (event: MouseEvent) => void;
}

interface DropdownItemOptions {
    name: string | null;
    action?: string | null;
    className?: string | null;
    scope?: string | null;
    title?: string | null;
    titleTranslation?: string | null;
    label?: string | null;
    labelTranslation?: string | null;
    link?: string | null;
    iconClass?: string | null;
    text?: string | null;
    disabled?: boolean;
    hidden?: boolean;
    html?: string | null;
    iconHtml?: string | null;
    data?: Record<string, unknown>;
    onClick?: (event: MouseEvent) => void;
}

/**
 * @internal
 * @experimental
 * @since 10.0.0
 */
export class ButtonComponent {

    @inject(Language)
    private language: Language

    constructor(private options: ButtonOptions) {}

    node(): VNode {
        const classes: any = {
            'btn': true,
            'disabled': this.options.disabled === true,
            'hidden': this.options.hidden === true,
            'action': this.options.action != null,
        };

        const style = this.options.style ?? 'default';
        classes['btn-' + style] = true;

        if (this.options.className) {
            this.options.className.split(' ').forEach(it => classes[it.trim()] = true);
        }

        const tag: 'button' | 'a' = this.options.link ? 'a' : 'button';

        const attrs: Record<string, any> = {};

        if (this.options.link) {
            attrs.href = this.options.link;
        } else {
            attrs.type = 'button';
        }

        if (this.options.disabled) {
            attrs.disabled = 'disabled';
        }

        if (this.options.title) {
            attrs.title = this.options.title;
        }

        if (!this.options.title && this.options.titleTranslation) {
            attrs.title = this.language.translatePath(this.options.titleTranslation);
        }

        const {content, props} = prepareItemContent(this.options, this.language);

        const on: any = {};

        if (this.options.onClick) {
            on.click = (event: MouseEvent) => this.options.onClick!(event)
        }

        return h(tag, {
            key: this.options.name ?? undefined,
            class: classes,
            attrs: attrs,
            dataset: {
                ...this.options.data,
                name: (this.options.name ?? undefined)!,
                action: (this.options.action ?? undefined)!,
            },
            props,
            on: on,
        }, content);
    }
}

/**
 * @internal
 * @experimental
 * @since 10.0.0
 */
export class DropdownItemComponent {

    @inject(Language)
    private language: Language

    constructor(private options: DropdownItemOptions) {}

    node(): VNode {
        const classes: any = {
            'disabled': this.options.disabled === true,
            'hidden': this.options.hidden === true,
            'action': this.options.action != null,
        };

        if (this.options.className) {
            this.options.className.split(' ').forEach(it => classes[it.trim()] = true);
        }

        const attrs: Record<string, any> = {
            tabindex: 0,
        };

        if (this.options.link) {
            attrs.href = this.options.link;
        }

        if (this.options.disabled) {
            attrs.disabled = 'disabled';
        }

        if (this.options.title) {
            attrs.title = this.options.title;
        }

        if (!this.options.title && this.options.titleTranslation) {
            attrs.title = this.language.translatePath(this.options.titleTranslation);
        }

        const {content, props} = prepareItemContent(this.options, this.language, true);

        if (!this.options.link) {
            attrs.role = 'button';
        }

        const on: any = {};

        if (this.options.onClick) {
            on.click = (event: MouseEvent) => this.options.onClick!(event)
        }

        const a = h('a', {
            class: classes,
            attrs: attrs,
            dataset: {
                ...this.options.data,
                name: (this.options.name ?? undefined)!,
                action: (this.options.action ?? undefined)!,
            },
            props: props,
            on: on,
        }, content);

        return h('li', {
            key: this.options.name ?? undefined,
            class: {
                'disabled': this.options.disabled === true,
                'hidden': this.options.hidden === true,
            },
        }, a);
    }
}

function prepareItemContent(
    options: ButtonOptions | DropdownItemOptions,
    language: Language,
    isDropdownItem: boolean = false,
): {props: Record<string, unknown>, content: any} {

    let content = null;
    const props: Record<string, unknown> = {};

    if (options.html) {
        props.innerHTML = options.html;

        if (options.iconHtml) {
            props.innerHTML = options.iconHtml + props.innerHTML;
        }
    } else {
        const label = options.label;

        let text = options.text ??
            (
                options.labelTranslation ?
                    language.translatePath(options.labelTranslation) :
                    language.translate(label ?? '', 'labels', options.scope)
            );

        let icon = null;

        if (options.iconHtml) {
            const div = document.createElement('div');
            div.innerHTML = options.iconHtml;

            icon = div.firstElementChild ? toVNode(div.firstElementChild) : null;
        } else if (options.iconClass) {
            icon = h('span', {props: {className: options.iconClass + ' item-icon'}});
        }

        if (isDropdownItem) {
            text = h('span', {props: {className: 'item-text'}}, text);
        } else {
            if (icon && text) {
                text = h('span', text);
            }
        }

        content = icon ?
            [
                icon,
                text,
            ] :
            text;
    }

    return {content, props};
}
