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

import ArrayFieldView, {ArrayOptions, ArrayParams} from 'views/fields/array';
import {BaseViewSchema} from 'views/fields/base';

export interface UrlMultipleParams extends ArrayParams {
    /**
     * Strip.
     */
    strip?: boolean
}

export interface UrlMultipleOptions extends ArrayOptions {}

/**
 * A Url-Multiple field.
 */
class UrlMultipleFieldView<
    S extends BaseViewSchema = BaseViewSchema,
    O extends UrlMultipleOptions = UrlMultipleOptions,
    P extends UrlMultipleParams = UrlMultipleParams,
> extends ArrayFieldView<S, O, P> {

    readonly type: string = 'urlMultiple'

    protected maxItemLength = 255
    protected displayAsList = true
    protected defaultProtocol = 'https:'

    protected setup() {
        super.setup();

        this.noEmptyString = true;
        this.params.pattern = '$uriOptionalProtocol';
    }

    protected addValueFromUi(value: string) {
        value = value.trim();

        if (this.params.strip) {
            value = this.strip(value);
        }

        try {
            if (decodeURIComponent(value) !== value) {
                return value;
            }

            value = encodeURI(value);
        } catch (e) {
            console.warn(`Malformed URI ${value}.`);
        }

        super.addValueFromUi(value);
    }

    private decodeURI(value: string): string {
        try {
            return decodeURI(value);
        } catch (e) {
            console.warn(`Malformed URI ${value}.`);

            return value;
        }
    }

    private strip(value: string): string {
        if (value.indexOf('//') !== -1) {
            value = value.substring(value.indexOf('//') + 2);
        }

        value = value.replace(/\/+$/, '');

        return value;
    }

    private prepareUrl(url: string): string {
        if (url.indexOf('//') === -1) {
            url = this.defaultProtocol + '//' + url;
        }

        return url;
    }

    protected getValueForDisplay(): string {
        const $list = this.selected.map(value => {
            return $('<a>')
                .attr('href', this.prepareUrl(value))
                .attr('target', '_blank')
                .text(this.decodeURI(value));
        });

        return $list
            .map($item =>
                $('<div>')
                    .addClass('multi-enum-item-container')
                    .append($item)
                    .get(0)?.outerHTML as any
            )
            .join('');
    }

    protected getItemHtml(value: string): string {
        const html = super.getItemHtml(value);

        const $item = $(html);

        $item.find('span.text').html(
            $('<a>')
                .attr('href', this.prepareUrl(value))
                .css('user-drag', 'none')
                .attr('target', '_blank')
                .text(this.decodeURI(value)) as any
        );

        return $item.get(0)?.outerHTML as string;
    }
}

export default UrlMultipleFieldView;
