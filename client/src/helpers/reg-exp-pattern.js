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
import Metadata from 'metadata';
import Language from 'language';

/**
 * A regular expression pattern helper.
 */
class RegExpPatternHelper {

    /**
     * @private
     * @type {Metadata}
     */
    @inject(Metadata)
    metadata

    /**
     * @private
     * @type {Language}
     */
    @inject(Language)
    language

    /**
     *
     * @param {string} pattern
     * @param {string|null} value
     * @param {string} [field]
     * @param {string | null} [entityType]
     * @return {{message: string}|null}
     */
    validate(pattern, value, field, entityType) {
        if (value === '' || value === null) {
            return null;
        }

        let messageKey = 'fieldNotMatchingPattern';

        if (pattern[0] === '$') {
            const patternName = pattern.slice(1);
            const foundPattern = this.metadata.get(['app', 'regExpPatterns', patternName, 'pattern']);

            if (foundPattern) {
                messageKey += '$' + patternName;
                pattern = foundPattern;
            }
        }

        const regExp = new RegExp('^' + pattern + '$');

        if (regExp.test(value)) {
            return null;
        }

        let message = this.language.translate(messageKey, 'messages')
            .replace('{pattern}', pattern);

        if (field && entityType) {
            message = message.replace('{field}', this.language.translate(field, 'fields', entityType));
        }

        return {message: message};
    }
}

export default RegExpPatternHelper;
