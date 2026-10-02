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

const {Transpiler} = require('espo-frontend-build-tools');

let file;

let fIndex = process.argv.findIndex(item => item === '-f');

if (fIndex > 0) {
    file = process.argv.at(fIndex + 1);

    if (!file) {
        throw new Error(`No file specified.`);
    }
}

const transpiler1 = new Transpiler({
    file: file,
});

const transpiler2 = new Transpiler({
    mod: 'crm',
    path: 'client/modules/crm',
    file: file,
});

const result1 = transpiler1.process();
const result2 = transpiler2.process();

let count = result1.transpiled.length + result2.transpiled.length;
let copiedCount = result1.copied.length + result2.copied.length;

console.log(`\n  transpiled: ${count}, copied: ${copiedCount}`)
