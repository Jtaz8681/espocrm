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

/**
* Builds a PO file for a specific language or all languages.
*/
class PO
{
    constructor (espoPath, language, onlyModuleName) {
        this.espoPath = espoPath;
        this.onlyModuleName = onlyModuleName;

        this.espoPath = espoPath;
        if (this.espoPath.substr(-1) !== '/') {
            this.espoPath += '/';
        }

        this.moduleList = ['Crm'];

        if (onlyModuleName) {
            this.moduleList = [onlyModuleName];
        }

        this.baseLanguage = 'en_US';
        this.language = language || this.baseLanguage;

        this.outputFileName = 'espocrm-' + this.language;

        if (onlyModuleName) {
            this.outputFileName += '-' + onlyModuleName;
        }

        this.outputFileName += '.po';

        let dirs = [
            this.espoPath + 'application/Espo/Resources/i18n/',
            this.espoPath + 'install/core/i18n/',
            this.espoPath + 'application/Espo/Core/Templates/i18n/'
        ];

        if (onlyModuleName) {
            dirs = [];
        }

        this.moduleList.forEach(moduleName => {
            let path1 = this.espoPath + 'application/Espo/Modules/' + moduleName;
            let path2 = this.espoPath + 'custom/Espo/Modules/' + moduleName;

            let dir = fs.existsSync(path1) ? path1 : path2;

            dirs.push(dir + '/Resources/i18n/');
        });

        this.dirs = dirs;

        this.poContentHeader = 'msgid ""\n' +
            'msgstr ""\n' +
            '"Project-Id-Version: \\n"\n' +
            '"POT-Creation-Date: \\n"\n' +
            '"PO-Revision-Date: \\n"\n' +
            '"Last-Translator: \\n"\n' +
            '"MIME-Version: 1.0\\n"\n' +
            '"Content-Type: text/plain; charset=UTF-8\\n"\n' +
            '"Content-Transfer-Encoding: 8bit\\n"\n' +
            '"Language: ' + this.language + '\\n"\n\n';
    }

    runAll () {
        let pathToLanguage = this.espoPath + '/application/Espo/Resources/i18n/';

        let languageList = [];

        fs.readdirSync(pathToLanguage).forEach(dir => {
            if (dir.indexOf('_') === 2) {
                languageList.push(dir);
            }
        });

        languageList.forEach(language => {
            let po = new PO(this.espoPath, language, this.onlyModuleName);

            po.run();
        });
    }

    run () {
        let dirs = this.dirs;
        let messageData = {};
        let targetMessageData = {}

        let poContents = this.poContentHeader;

        dirs.forEach(path => {
            let dirPath = this.getDirPath(path, this.baseLanguage);

            let list = fs.readdirSync(dirPath);

            list.forEach(fileName => {
                let filePath = this.getDirPath(path, this.baseLanguage) + fileName;

                this.populateMessageDataFromFile(filePath, messageData);

                if (this.language !== this.baseLanguage) {
                    let langFilePath = this.getDirPath(path, this.language) + fileName;

                    this.populateMessageDataFromFile(langFilePath, targetMessageData);
                }
            });
        });


        if (this.language === this.baseLanguage) {
            targetMessageData = messageData;
        }

        for (let key in messageData) {
            poContents += 'msgctxt "' + messageData[key].context + '"\n';
            poContents += 'msgid "' + messageData[key].value + '"\n';

            let translatedValue = (targetMessageData[key] || {}).value || "";

            poContents += 'msgstr "' + translatedValue + '"\n\n';
        }

        let resFilePath = this.espoPath + 'build/' + this.outputFileName;

        if (fs.existsSync(resFilePath)) {
            fs.unlinkSync(resFilePath);
        }

        fs.writeFileSync(resFilePath, poContents);
    }

    populateMessageDataFromFile (filePath, messageData) {
        if (!fs.existsSync(filePath)) {
            return messageData;
        }

        let data = fs.readFileSync(filePath, 'utf8');

        data = JSON.parse(data);

        let fileName = filePath.split('\/').slice(-1).pop().split('.')[0];

        this.populateMessageData(fileName, data, '', messageData);
    }

    getDirPath (path, language) {
        return path + language + '/';
    }

    populateMessageData (fileName, dataObject, prefix, messageData) {
        prefix = prefix || '';

        for (let index in dataObject) {
            if (dataObject[index] === null || dataObject[index] === "") {
                continue;
            }

            if (typeof dataObject[index] === 'object' && !Array.isArray(dataObject[index])) {
                let nextPrefix = prefix + (prefix ? '.' : '') + index;

                this.populateMessageData(fileName, dataObject[index], nextPrefix, messageData);

                continue;
            }

            let path = fileName + '.' + prefix;
            let key = path + '.' + index;
            let value = dataObject[index];

            if (Array.isArray(value)) {
                value = '"' + value.join('", "') + '"';
                path = path + '.' + index;
            }

            messageData[key] = {
                context: path,
                value: this.fixString(value)
            };
        }
    }

    replaceAll (string, find, replace) {
        let escapedRegExp = find.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");

        return string.replace(new RegExp(escapedRegExp, 'g'), replace);
    }

    fixString (savedString) {
        savedString = this.replaceAll(savedString, "\\", '\\\\');
        savedString = this.replaceAll(savedString, '"', '\\"');
        savedString = this.replaceAll(savedString, "\n", '\\n');
        savedString = this.replaceAll(savedString, "\t", '\\t');

        return savedString;
    }
}

module.exports = PO;
