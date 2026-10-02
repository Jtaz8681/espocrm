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

import BaseFieldView from 'views/fields/base';

class UserGeneratePasswordFieldView extends BaseFieldView {

    templateContent = `
        <button
            type="button"
            class="btn btn-default"
            data-action="generatePassword"
        >{{translate 'Generate' scope='User'}}</button>`

    events = {
        /** @this {UserGeneratePasswordFieldView} */
        'click [data-action="generatePassword"]': function () {
            this.actionGeneratePassword();
        },
    }

    setup() {
        super.setup();

        this.listenTo(this.model, 'change:password', (model, value, o) => {
            if (o.isGenerated) {
                return;
            }

            if (value !== undefined) {
                this.model.set('passwordPreview', null);

                return;
            }

            this.model.unset('passwordPreview');
        });

        this.strengthParams = this.options.strengthParams || {};

        this.passwordStrengthLength = this.strengthParams.passwordStrengthLength ??
            this.getConfig().get('passwordStrengthLength') ?? null;

        this.passwordStrengthLetterCount = this.strengthParams.passwordStrengthLetterCount ??
            this.getConfig().get('passwordStrengthLetterCount') ?? null;

        this.passwordStrengthNumberCount = this.strengthParams.passwordStrengthNumberCount ??
            this.getConfig().get('passwordStrengthNumberCount') ?? null;

        this.passwordStrengthSpecialCharacterCount = this.strengthParams.passwordStrengthSpecialCharacterCount ??
            this.getConfig().get('passwordStrengthSpecialCharacterCount') ?? null;

        this.passwordGenerateLength = this.strengthParams.passwordGenerateLength ??
            this.getConfig().get('passwordGenerateLength') ?? null;

        this.passwordGenerateLetterCount = this.strengthParams.passwordGenerateLetterCount ??
            this.getConfig().get('passwordGenerateLetterCount') ?? null;

        this.passwordGenerateNumberCount = this.strengthParams.passwordGenerateNumberCount ??
            this.getConfig().get('passwordGenerateNumberCount') ?? null;
    }

    fetch() {
        return {};
    }

    actionGeneratePassword() {
        let length = this.passwordStrengthLength;
        let letterCount = this.passwordStrengthLetterCount;
        let numberCount = this.passwordStrengthNumberCount;
        const specialCharacterCount = this.passwordStrengthSpecialCharacterCount;

        const generateLength = this.passwordGenerateLength || 10;
        const generateLetterCount = this.passwordGenerateLetterCount || 4;
        const generateNumberCount = this.passwordGenerateNumberCount || 2;

        length = (typeof length === 'undefined') ? generateLength : length;
        letterCount = (typeof letterCount === 'undefined') ? generateLetterCount : letterCount;
        numberCount = (typeof numberCount === 'undefined') ? generateNumberCount : numberCount;

        if (length < generateLength) {
            length = generateLength;
        }

        if (letterCount < generateLetterCount) {
            letterCount = generateLetterCount;
        }

        if (numberCount < generateNumberCount) {
            numberCount = generateNumberCount;
        }

        const password = this.generatePassword(length, letterCount, numberCount, true, specialCharacterCount);

        this.model.set({
            password: password,
            passwordConfirm: password,
            passwordPreview: password,
        }, {isGenerated: true});
    }

    /**
     * @private
     * @param {number} length
     * @param {number} letters
     * @param {number} numbers
     * @param {boolean} bothCases
     * @param {number} specialCharacters
     * @return {string}
     */
    generatePassword(length, letters, numbers, bothCases, specialCharacters) {
        const chars = [
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            '0123456789',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'abcdefghijklmnopqrstuvwxyz',
            "'-!\"#$%&()*,./:;?@[]^_`{|}~+<=>",
        ];

        let upperCase = 0;
        let lowerCase = 0;

        if (bothCases) {
            upperCase = 1;
            lowerCase = 1;

            if (letters >= 2) {
                letters = letters - 2;
            } else {
                letters = 0;
            }
        }

        let either = length - (letters + numbers + upperCase + lowerCase + specialCharacters);

        if (either < 0) {
            either = 0;
        }

        const setList = [letters, numbers, either, upperCase, lowerCase, specialCharacters];

        const shuffle = function (array) {
            let currentIndex = array.length;

            while (0 !== currentIndex) {
                const randomArray = new Uint32Array(1);
                crypto.getRandomValues(randomArray);

                const randomIndex = Math.floor((randomArray[0] / (0xFFFFFFFF + 1)) * currentIndex);

                currentIndex -= 1;

                const tempValue = array[currentIndex];

                array[currentIndex] = array[randomIndex];
                array[randomIndex] = tempValue;
            }

            return array;
        };

        const array = setList
            .map((len, i) => {
                return Array(len)
                    .fill(chars[i])
                    .map(x => {
                        const randomArray = new Uint32Array(1);
                        crypto.getRandomValues(randomArray);

                        const randomIndex = Math.floor((randomArray[0] / (0xFFFFFFFF + 1)) * x.length);

                        return x[randomIndex];
                    })
                    .join('');
            })
            .concat();

        return shuffle(array).join('');
    }
}

export default UserGeneratePasswordFieldView;
