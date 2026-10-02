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
 * Builds upgrade packages.
 * From a specified version to the current version or all packages needed for a release.

 * Examples:
 * * `node diff 5.9.0` - builds an upgrade from 5.9.0 to the current version;
 * * `node diff --all` - builds all upgrades needed for a release.
 *
 * Data for upgrade packages is defined in `upgrades/{x.x|x.x.x-x.x.x}/data.json`.
 *
 * Parameters:
 * * `mandatoryFiles` – {string[]} – mandatory files to include in upgrade
 *   (even files that were not changed in version control);
 * * `beforeUpgradeFiles` – {string[]} – files to copy in the beginning of the upgrade process;
 * * `manifest` – {object} – upgrade manifest parameters.
 *
 * Manifest parameters:
 * * `delete` – {string[]} – additional files to be deleted (usually those that are not in version control).
 */

const Diff = require('./js/diff');
const path = require('path');
const fs = require('fs');
const process = require('process');

const versionFrom = process.argv[2];

let acceptedVersionName = versionFrom;
let isDev = false;
let isAll = false;
let withVendor = true;
let forceScripts = false;
let isClosest = false;

if (process.argv.length > 1) {
    for (const i in process.argv) {
        if (process.argv[i] === '--dev') {
            isDev = true;
            withVendor = false;
        }

        if (process.argv[i] === '--all') {
            isAll = true;
        }

        if (process.argv[i] === '--no-vendor') {
            withVendor = false;
        }

        if (process.argv[i] === '--scripts') {
            forceScripts = true;
        }

        if (process.argv[i] === '--closest') {
            isClosest = true;
        }

        if (~process.argv[i].indexOf('--acceptedVersion=')) {
            acceptedVersionName = process.argv[i].substr(('--acceptedVersion=').length);
        }
    }
}

const espoPath = path.dirname(fs.realpathSync(__filename));

if (isAll || isClosest) {
    acceptedVersionName = null;
}

const diff = new Diff(espoPath, {
    isDev: isDev,
    withVendor: withVendor,
    forceScripts: forceScripts,
    acceptedVersionName: acceptedVersionName,
});

(() => {
    if (isAll) {
        diff.buildAllUpgradePackages();

        return;
    }

    if (isClosest) {
        diff.buildClosestUpgradePackages();

        return;
    }

    if (!versionFrom) {
        throw new Error("No 'version' specified.");
    }

    diff.buildUpgradePackage(versionFrom);
})();
