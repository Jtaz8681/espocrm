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
import $ from 'jquery';

/**
 * To be extended with an own template.
 *
 * @abstract
 */
class PopupNotificationView extends View {

    type = 'default'
    style = 'default'

    /**
     * @protected
     * @type {boolean}
     */
    closeButton = true


    /**
     * @protected
     * @type {boolean}
     * @since 10.0
     */
    collapseButton = true

    /**
     * @type {boolean}
     * @internal
     */
    isCollapsed = false

    soundPath = 'client/sounds/cloud.ogg'

    /**
     * @param {{
     *     id: string,
     *     notificationData: Record,
     *     notificationId: string|null,
     *     isFirstCheck: boolean,
     *     onCollapse: function(),
     *     onExpand: function(),
     * }} options
     */
    constructor(options) {
        super(options);

        this.options = options;
    }

    init() {
        super.init();

        const id = this.options.id;
        const containerSelector = this.containerSelector = `#${id}`;

        this.setSelector(containerSelector);

        this.soundPath = this.getBasePath() +
            (this.getConfig().get('popupNotificationSound') || this.soundPath);

        this.on('render', () => {
            this.hide();

            if (this.isCollapsed) {
                return;
            }

            const className = 'popup-notification-' + Espo.Utils.toDom(this.type);

            $('<div>')
                .attr('id', id)
                .addClass('popup-notification')
                .addClass(className)
                .addClass('popup-notification-' + this.style)
                .appendTo('#popup-notifications-container');

            this.setElement(containerSelector);
        });

        this.on('after:render', () => {
            this.$el.find('[data-action="close"]').on('click', () =>{
                this.resolveCancel();
            });
        });

        this.on('after:render', () => {
            this.onShow();
        });

        this.on('remove', function () {
            $(containerSelector).remove();
        });

        this.notificationData = this.options.notificationData;
        this.notificationId = this.options.notificationId;
        this.id = this.options.id;

        if (!this.notificationId) {
            this.collapseButton = false;
        }

        this.addActionHandler('collapse', () => this.collapse());
    }

    data() {
        return {
            closeButton: this.closeButton,
            notificationData: this.notificationData,
            notificationId: this.notificationId,
            collapseButton: true,
        };
    }

    /**
     * @internal
     * @since 10.0
     */
    hide() {
        this.element = undefined;

        $(this.containerSelector).remove();
    }

    playSound() {
        if (!this.getPreferences().get('notificationSound')) {
            return;
        }

        const audio = new Audio(this.soundPath);
        audio.volume = 0.3;

        audio.play();
    }

    /**
     * @protected
     */
    onShow() {
        if (!this.options.isFirstCheck) {
            this.playSound();
        }
    }

    /**
     * An on-confirm action. To be extended.
     *
     * @protected
     */
    onConfirm() {}

    /**
     * An on-cancel action. To be extended.
     *
     * @protected
     */
    onCancel() {}

    resolveConfirm() {
        this.onConfirm();
        this.trigger('confirm');
        this.remove();
    }

    resolveCancel() {
        this.onCancel();
        this.trigger('cancel');
        this.remove();
    }

    // noinspection JSCheckFunctionSignatures
    /**
     * @deprecated Use `resolveConfirm`.
     */
    confirm() {
        console.warn(`Method 'confirm' in views/popup-notification is deprecated. Use 'resolveConfirm' instead.`);

        this.resolveConfirm();
    }

    /**
     * @deprecated Use `resolveCancel`.
     */
    cancel() {
        console.warn(`Method 'cancel' in views/popup-notification is deprecated. Use 'resolveCancel' instead.`);

        this.resolveCancel();
    }

    /**
     * Collapse.
     *
     * @since 10.0
     * @private
     */
    collapse() {
        this.isCollapsed = true;

        this.options.onCollapse();

        this.hide();
    }

    /**
     * Expand.
     *
     * @since 10.0
     * @internal
     */
    expand() {
        this.isCollapsed = false;

        this.options.onExpand();

        this.reRender(true);
    }

    /**
     * Collapse silently.
     *
     * @since 10.0
     * @internal
     */
    makeCollapsed() {
        this.isCollapsed = true;

        this.hide();
    }

    /**
     * Expand silently.
     *
     * @since 10.0
     * @internal
     */
    makeExpanded() {
        this.isCollapsed = false;

        this.reRender(true);
    }

    /**
     * Get title.
     *
     * @return string|null
     * @since 10.0
     */
    getTitle() {
        return null;
    }
}

export default PopupNotificationView;
