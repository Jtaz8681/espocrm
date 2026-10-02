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

class ClearCacheView extends View {

    template = 'clear-cache'

    el = '> body'

    events = {
        /** @this ClearCacheView */
        'click .action[data-action="clearLocalCache"]': function () {
            this.clearLocalCache();
        },
        /** @this ClearCacheView */
        'click .action[data-action="returnToApplication"]': function () {
            this.returnToApplication();
        }
    }

    data() {
        return {
            cacheIsEnabled: !!this.options.cache
        };
    }

    clearLocalCache() {
        this.options.cache.clear();

        this.$el.find('.action[data-action="clearLocalCache"]').remove();
        this.$el.find('.message-container').removeClass('hidden');
        this.$el.find('.message-container span').html(this.translate('Cache has been cleared'));
        this.$el.find('.action[data-action="returnToApplication"]').removeClass('hidden');
    }

    returnToApplication() {
        this.getRouter().navigate('', {trigger: true});
    }
}

export default ClearCacheView;
