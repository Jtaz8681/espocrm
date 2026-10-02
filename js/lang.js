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
const nodePath = require('path');
const PO = require('pofile');
const os = require('os');

/**
 * Builds language files from a PO file.
 */
class Lang
{
    constructor (language, poPath, espoPath, onlyModuleName) {
        this.baseLanguage = 'en_US';
        this.language = language;
        this.poPath = poPath;

        this.ignoreList = [
            'Global.options.language',
        ];

        this.espoPath = espoPath;

        if (this.espoPath.slice(-1) !== '/') {
            this.espoPath += '/';
        }

        this.moduleList = ['Crm'];

        if (onlyModuleName) {
            this.moduleList = [onlyModuleName];
        }

        const dirNames = this.dirNames = {};

        const coreDir = this.espoPath + 'application/Espo/Resources/i18n/' + this.baseLanguage + '/';

        let dirs = [coreDir];

        dirNames[coreDir] = 'application/Espo/Resources/i18n/' + this.language + '/';

        const installDir = this.espoPath + 'install/core/i18n/' + this.baseLanguage + '/';

        dirs.push(installDir);
        dirNames[installDir] = 'install/core/i18n/' + this.language + '/';

        const templatesDir = this.espoPath + 'application/Espo/Core/Templates/i18n/' + this.baseLanguage + '/';

        dirs.push(templatesDir);
        dirNames[templatesDir] = 'application/Espo/Core/Templates/i18n/' + this.language + '/';

        if (onlyModuleName) {
            dirs = [];
        }

        this.moduleList.forEach(moduleName => {
            const path1 = this.espoPath + 'application/Espo/Modules/' + moduleName;
            const path2 = this.espoPath + 'custom/Espo/Modules/' + moduleName;

            const baseDir = fs.existsSync(path1) ? path1 : path2;

            const dir = baseDir + '/Resources/i18n/' + this.baseLanguage + '/';

            dirs.push(dir);

            dirNames[dir] = fs.existsSync(path1) ?
                'application/Espo/Modules/' + moduleName + '/Resources/i18n/' + this.language + '/' :
                'custom/Espo/Modules/' + moduleName + '/Resources/i18n/' + this.language + '/';
        });

        this.dirs = dirs;

        this.isWin = /^win/.test(os.platform());
    }

    escape (string) {
        return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
    }

    run () {
        const translationData = {};
        const dirs = this.dirs;

        const isWin = this.isWin;
        const language = this.language;

        PO.load(this.poPath, (err, po) => {
            if (err) {
                throw new Error("Could not parse " + this.poPath + ".");
            }

            po.items.forEach(item => {
                if (!item.msgctxt) {
                    return;
                }

                //let key = item.msgctxt + '__' + item.msgid;
                const file = item.msgctxt.split('.')[0];
                const path = item.msgctxt.split('.').slice(1);

                const o = {
                    stringOriginal: item.msgid,
                    stringTranslated: item.msgstr[0],
                    context: item.msgctxt,
                    file: file,
                    path: path,
                };

                translationData[file] = translationData[file] || [];
                translationData[file].push(o);
            });

            dirs.forEach(path => {
                const resDirPath = this.dirNames[path];
                const resPath = this.espoPath + 'build/' + language + '/' + resDirPath;

                if (!fs.existsSync(resPath)) {
                    let d = '';

                    resPath.split('/').forEach(f =>{
                        if (!f) {
                            return;
                        }

                        if (isWin) {
                            d = nodePath.join(d, f);
                        } else {
                            d += '/' + f;
                        }

                        if (!fs.existsSync(d)) {
                            fs.mkdirSync(d);
                        }
                    });
                }

                const list = fs.readdirSync(path);

                list.forEach(fileName => {
                    const filePath = path + fileName;
                    const resFilePath = resPath + '/' + fileName;

                    let contents = fs.readFileSync(filePath, 'utf8');

                    const fileKey = fileName.split('.')[0];

                    const fileObject = JSON.parse(contents);
                    const targetFileObject = {};

                    if (!(fileKey in translationData)) {
                        return;
                    }

                    translationData[fileKey].forEach(item => {
                        let isArray = false;
                        let isMet = true;
                        let c = fileObject;
                        const path = item.path.slice(0);

                        if (
                            this.baseLanguage !== this.language &&
                            ~this.ignoreList.indexOf(fileKey + '.' + path.join('.'))
                        ) {
                            return;
                        }

                        for (const i in item.path) {
                            const key = item.path[i];

                            if (key in c) {
                                c = c[key];

                                if (Array.isArray(c)) {
                                    isArray = true;

                                    break;
                                }
                            } else {
                                isMet = false;
                            }
                        }

                        const pathList = [];

                        if (isMet) {
                            if (!isArray) {
                                isMet = false;

                                for (const k in c) {
                                    if (c[k] === item.stringOriginal) {
                                        const p = path.slice(0);

                                        p.push(k);

                                        pathList.push(p);
                                        isMet = true;
                                    }
                                }
                            } else {
                                pathList.push(path);
                            }
                        }

                        if (!isMet) {
                            return;
                        }

                        let targetValue = item.stringTranslated;

                        if (targetValue === '') {
                            return;
                        }
                        else {
                            if (item.stringOriginal === item.stringTranslated) {
                                return;
                            }
                        }

                        if (isArray) {
                            try {
                                targetValue = JSON.parse('[' + targetValue  + ']');
                            } catch (e) {
                                targetValue = null;
                            }
                        }
                        if (targetValue == null) {
                            return;
                        }

                        pathList.forEach(path => {
                            let c = targetFileObject;

                            path.forEach((pathKey, i) => {
                                if (i < path.length - 1) {
                                    c[pathKey] = c[pathKey] || {};
                                    c = c[pathKey];
                                } else {
                                    c[pathKey] = targetValue;
                                }
                            });
                        });
                    });

                    contents = JSON.stringify(targetFileObject, null, '  ');

                    if (fs.existsSync(resFilePath)) {
                        fs.unlinkSync(resFilePath);
                    }

                    fs.writeFileSync(resFilePath, contents);
                });
            });
        });
    }
}

module.exports = Lang;
