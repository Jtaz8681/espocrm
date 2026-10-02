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

/**
 * Builds language files from a PO file.
 *
 * Command example: `node lang de_DE`.
 *
 * A PO file should be located in `build` directory: `build/bugzyro-lang_CODE.po`.
 * Language files will be created in `build` directory.
 *
 * You specify a module with `--module=` parameter. It will build only for the specified module.
 */

const Lang = require('./js/lang');
const path = require('path');
const fs = require('fs');

if (process.argv.length < 3) {
    throw new Error('You need to pass a language code as a second parameter.');
}

let espoPath = path.dirname(fs.realpathSync(__filename)) + '';
let language = process.argv[2];

let poPath = null;
let onlyModuleName = null;

if (process.argv.length > 2) {
    for (let i in process.argv) {
        if (~process.argv[i].indexOf('--module=')) {
            onlyModuleName = process.argv[i].substring(('--module=').length);
        }

        if (~process.argv[i].indexOf('--path=')) {
            poPath = process.argv[i].substring(('--path=').length);
        }
    }
}

if (!poPath) {
    poPath = espoPath + '/build/' + 'bugzyro-' + language;

    if (onlyModuleName) {
        poPath += '-' + onlyModuleName;
    }

    poPath += '.po';
}

new Lang(language, poPath, espoPath, onlyModuleName).run();
