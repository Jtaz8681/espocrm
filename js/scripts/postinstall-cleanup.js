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

function rmDir(path) {
    if (!fs.existsSync(path)) {
        return;
    }


    if (fs.rm) {
        fs.rmSync(path, {recursive: true});

        return;
    }


    fs.rmdirSync(path, {recursive: true});
}

function rmFile(path) {
    if (!fs.existsSync(path)) {
        return;
    }


    fs.unlinkSync(path);
}


// Can't be ignored by IntelliJ IDE, resort to removing.
rmDir('./node_modules/vis/examples');
rmDir('./node_modules/vis/lib');
rmDir('./node_modules/vis/docs');

rmFile('./node_modules/vis/gulpfile.js');
rmFile('./node_modules/vis/index.js');
rmFile('./node_modules/vis/index-graph3d.js');
rmFile('./node_modules/vis/index-network.js');
rmFile('./node_modules/vis/index-timeline-graph2d.js');
