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

import {inject} from 'di';
import ThemeManager from 'theme-manager';

export default class WindowPanelHelper {

    /**
     * @type {import('view').default}
     * @private
     */
    view

    /**
     * @private
     * @type {boolean}
     */
    overflowWasHidden = false

    /**
     * @private
     */
    onResizeBind

    /**
     * @private
     * @type {ThemeManager}
     */
    @inject(ThemeManager)
    themeManager

    /**
     * @param {import('view').default} view
     */
    constructor(view) {
        this.view = view;

        view.listenToOnce(view, 'remove', () => {
            window.removeEventListener('resize', this.onResizeBind)

            if (this.overflowWasHidden) {
                document.body.style.overflow = 'unset';

                this.overflowWasHidden = false;
            }
        });

        this.onResizeBind = this.onResize.bind(this);

        window.addEventListener('resize', this.onResizeBind);

        this.navbarPanelHeightSpace = this.themeManager.getParam('navbarPanelHeightSpace') ?? 100;
        this.navbarPanelBodyMaxHeight = this.themeManager.getParam('navbarPanelBodyMaxHeight') ?? 600;
        this.xsWidth = this.themeManager.getParam('screenWidthXs');

        this.onResize();
    }

    /**
     * @private
     */
    onResize() {
        const windowHeight = window.innerHeight;
        const windowWidth = window.innerWidth;

        const panelBody = this.view.element?.querySelector('.panel-body');
        const heading = this.view.element?.querySelector('.panel-heading');

        if (!(panelBody instanceof HTMLElement)) {
            return;
        }

        const diffHeight = heading?.outerHeight ?? 0;

        const cssParams = {};

        if (windowWidth <= this.xsWidth) {
            cssParams.height = (windowHeight - diffHeight) + 'px';
            cssParams.overflow = 'auto';

            document.body.style.overflow = 'hidden';

            this.overflowWasHidden = true;
        } else {
            cssParams.height = 'unset';
            cssParams.overflow = 'none';

            if (this.overflowWasHidden) {
                panelBody.style.overflow = 'unset';

                this.overflowWasHidden = false;
            }

            if (windowHeight - this.navbarPanelBodyMaxHeight < this.navbarPanelHeightSpace) {
                const maxHeight = windowHeight - this.navbarPanelHeightSpace;

                cssParams.maxHeight = maxHeight + 'px';
            }
        }

        for (const [param, value] of Object.entries(cssParams)) {
            panelBody.style[param] = value;
        }
    }
}
