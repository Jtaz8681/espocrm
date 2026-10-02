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

const fs = require('fs');
const buildUtils = require('../build-utils');

/**
 * @type {{
 *     src?: string,
 *     dest?: string,
 *     bundle?: boolean,
 *     amdId?: string,
 *     suppressAmd?: boolean,
 *     minify?: boolean,
 *     prepareCommand?: string,
 *     name?: string,
 *     files?: {
 *         src: string,
 *         dest: string,
 *     }[],
 * }[]}
 */
const libs = require('./../../frontend/libs.json');

const stripSourceMappingUrl = path => {
    /** @var {string} */
    const originalContents = fs.readFileSync(path, {encoding: 'utf-8'});

    const re = /\/\/# sourceMappingURL.*/g;

    if (!originalContents.match(re)) {
        return;
    }

    const contents = originalContents.replaceAll(re, '');

    fs.writeFileSync(path, contents, {encoding: 'utf-8'});
};

buildUtils.getCopyLibDataList(libs)
    .filter(item => !item.minify)
    .forEach(item => {
        stripSourceMappingUrl(item.dest);
    });
