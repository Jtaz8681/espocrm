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
const cp = require('child_process');
const buildUtils = require('../build-utils');

// @todo Introduce libs-provider.
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

const bundleConfig = require('../../frontend/bundle-config.json');

const libDir = './client/lib';
const originalLibDir = './client/lib/original';
const libCrmDir = './client/modules/crm/lib';
const originalLibCrmDir = './client/modules/crm/lib/original';

[libDir, originalLibDir, libCrmDir, originalLibCrmDir]
    .filter(path => !fs.existsSync(path))
    .forEach(path => fs.mkdirSync(path));

const bundleFiles = Object.keys(bundleConfig.chunks)
    .map(name => {
        const namePart = 'espo-' + name;

        return namePart + '.js';
    });

fs.readdirSync(originalLibDir)
    .filter(file => !bundleFiles.includes(file))
    .forEach(file => fs.unlinkSync(originalLibDir + '/' + file));

fs.readdirSync(originalLibCrmDir)
    .forEach(file => fs.unlinkSync(originalLibCrmDir + '/' + file));

libs.filter(it => it.prepareCommand)
    .forEach(it => {
        cp.execSync(it.prepareCommand, {stdio: ['ignore', 'ignore', 'pipe']});
    });

const stripSourceMappingUrl = path => {
    /** @var {string} */
    const originalContents = fs.readFileSync(path, {encoding: 'utf-8'});

    const re = /^\/\/# sourceMappingURL.*/gm;

    if (!originalContents.match(re)) {
        return;
    }

    const contents = originalContents.replaceAll(re, '');

    fs.writeFileSync(path, contents, {encoding: 'utf-8'});
}

const addLoadingSubject = (path, subject) => {
    /** @var {string} */
    let contents = fs.readFileSync(path, {encoding: 'utf-8'});

    contents =
        `Espo.loader.setContextId('${subject}');\n` +
        contents + '\n' +
        `Espo.loader.setContextId(null);\n`;

    fs.writeFileSync(path, contents, {encoding: 'utf-8'});
}

const addSuppressAmd = path => {
    /** @var {string} */
    let contents = fs.readFileSync(path, {encoding: 'utf-8'});

    contents =
        `var _previousDefineAmd = define.amd; define.amd = false;\n` +
        contents + '\n' +
        `define.amd = _previousDefineAmd;\n`;

    fs.writeFileSync(path, contents, {encoding: 'utf-8'});
}

const amdIdMap = {};
const suppressAmdMap = {};

libs.forEach(item => {
    if (!item.amdId || !item.bundle || item.files) {
        return;
    }

    if (item.suppressAmd) {
        suppressAmdMap[item.src] = true;

        return;
    }

    amdIdMap[item.src] = 'lib!' + item.amdId;
});

buildUtils.getBundleLibList(libs, true).forEach(item => {
    const src = item.src;

    const dest = originalLibDir + '/' + item.file;

    fs.copyFileSync(src, dest);
    stripSourceMappingUrl(dest);

    if (suppressAmdMap[src]) {
        addSuppressAmd(dest);
    }

    const key = amdIdMap[src];

    if (key) {
        addLoadingSubject(dest, key);
    }
});

buildUtils.getCopyLibDataList(libs)
    .filter(item => item.minify)
    .forEach(item => {
        fs.copyFileSync(item.src, item.originalDest);
        stripSourceMappingUrl(item.originalDest);

        if (suppressAmdMap[item.src]) {
            addSuppressAmd(item.originalDest);
        }

        const key = amdIdMap[item.src];

        if (key) {
            addLoadingSubject(item.originalDest, key);
        }
    });
