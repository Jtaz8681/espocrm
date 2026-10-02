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

import Controller from 'controller';

class StreamController extends Controller {

    defaultAction = 'index'

    // noinspection JSUnusedGlobalSymbols
    actionIndex() {
        const key = 'index';
        const isReturn = this.getRouter().backProcessed;

        if (!isReturn) {
            this.clearStoredMainView(key);
        }

        this.main('views/stream', {displayTitle: true}, undefined, {
            key: key,
            useStored: isReturn,
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionPosts() {
        const key = 'index';
        const isReturn = this.getRouter().backProcessed;

        if (!isReturn) {
            this.clearStoredMainView(key);
        }

        this.main('views/stream', {displayTitle: true, filter: 'posts'}, undefined, {
            key: key,
            useStored: isReturn,
        });
    }

    // noinspection JSUnusedGlobalSymbols
    actionUpdates() {
        const key = 'index';
        const isReturn = this.getRouter().backProcessed;

        if (!isReturn) {
            this.clearStoredMainView(key);
        }

        this.main('views/stream', {displayTitle: true, filter: 'updates'}, undefined, {
            key: key,
            useStored: isReturn,
        });
    }
}

export default StreamController;
