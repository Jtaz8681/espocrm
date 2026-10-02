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
const {globSync} = require('glob');

class LayoutTemplateBundler {

    /**
     * @return {string}
     */
    bundle() {
        const path = 'client/res/layout-types';

        /** @var {string[]} */
        const files = globSync(path + '/*.tpl')
            .map(file => file.replaceAll('\\', '/'));

        const map = {};

        files.forEach(file => {
            const name = file
                .substring(path.length + 1)
                .slice(0, -4);

            map[name] = fs.readFileSync(file, 'utf8');
        });

        const mapPart = JSON.stringify(map);

        return `\nEspo.layoutTemplates = ${mapPart};\n`;
    }
}

module.exports = LayoutTemplateBundler;
