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

const BuildUtils = {
    /**
     * @param {{
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
     * }[]} libs
     * @param {boolean} [skipPreparable]
     * @return {{src: string, file: string}[]}
     */
    getBundleLibList: function(libs, skipPreparable = false) {
        const list = [];

        /**
         *
         * @param {{amdId?: string, src: string}} item
         * @return {*|string}
         */
        const getFile = item => {
            if (item.amdId) {
                if (item.amdId.startsWith('@')) {
                    return item.amdId.slice(1).replace('/', '-') + '.js';
                }

                return item.amdId + '.js';
            }

            return item.src.split('/').slice(-1);
        };

        libs.filter(item => item.bundle)
            .forEach(item => {
                if (item.prepareCommand && skipPreparable) {
                    return;
                }

                if (item.files) {
                    item.files.forEach(item => list.push({
                        src: item.src,
                        file: getFile(item),
                    }));

                    return;
                }

                if (!item.src) {
                    return;
                }

                list.push({
                    src: item.src,
                    file: getFile(item),
                });
            });

        return list;
    },

    getPreparedBundleLibList: function (libs) {
        const items = BuildUtils.getBundleLibList(libs);

        return items.map(item => 'client/lib/original/' + item.file);
    },

    destToOriginalDest: function (dest) {
        const path = dest.split('/');

        return path.slice(0, -1)
            .concat('original')
            .concat(path.slice(-1))
            .join('/');
    },

    /**
     * @param {Object[]} libs
     * @return {{
     *   src: string,
     *   dest: string,
     *   originalDest: string|null,
     *   minify: boolean,
     * }[]}
     */
    getCopyLibDataList: function (libs) {
        const list = [];

        /**
         * @param {Object} item
         * @return {string}
         */
        const getItemDest = item => item.dest || 'client/lib/' + item.src.split('/').pop();

        /**
         * @param {Object} item
         * @return {string}
         */
        const getItemOriginalDest = item => {
            return BuildUtils.destToOriginalDest(
                getItemDest(item)
            );
        };

        libs.forEach(item => {
            if (item.bundle) {
                return;
            }

            const minify = !!item.minify;

            if (item.files) {
                item.files.forEach(item => {
                    list.push({
                        src: item.src,
                        dest: getItemDest(item),
                        originalDest: minify ? getItemOriginalDest(item) : null,
                        minify: minify,
                    });
                });

                return;
            }

            if (!item.src) {
                return;
            }

            list.push({
                src: item.src,
                dest: getItemDest(item),
                originalDest: minify ? getItemOriginalDest(item) : null,
                minify: minify,
            });
        });

        return list;
    },

    camelCaseToHyphen: function (string) {
        return string.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    },
}

module.exports = BuildUtils;
