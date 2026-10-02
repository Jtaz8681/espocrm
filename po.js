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
* Buils a PO file for a specific language or all languages.
* The built PO file will be available in `build` directory.
*
* Command example: `node po en_US`.
*
* Options:
* * --module={ModuleName} - only specific module;
* * --all - all languages.
*/

const PO = require('./js/po');
const path = require('path');
const fs = require('fs');

let language = process.argv[2] || null;

let onlyModuleName = null;

if (process.argv.length > 2) {
    for (let i in process.argv) {
        if (~process.argv[i].indexOf('--module=')) {
            onlyModuleName = process.argv[i].substr(('--module=').length);
        }

        if (~process.argv[i].indexOf('--all')) {
            language = '--all';
        }
    }
}

let espoPath = path.dirname(fs.realpathSync(__filename)) + '';

let po = new PO(espoPath, language, onlyModuleName);

language === '--all' ?
    po.runAll() :
    po.run();

