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

const registry = new Map();
const container = new Map();

/**
 * A DI container.
 */
export {container};

/**
 * A 'register' decorator.
 *
 * @param {*[]} argumentList Arguments.
 * @return {function(typeof Object)}
 */
export function register(argumentList = []) {
    return function(classObject) {
        registry.set(classObject, argumentList);
    };
}

/**
 * An 'inject' decorator.
 *
 * @param {any} classObject A class.
 * @return {(function(*, Object): void)}
 */
export function inject(classObject) {
    /**
     * @param {{addInitializer: function(function())}} context
     */
    return function(value, context) {
        context.addInitializer(function() {
            let instance = container.get(classObject);

            if (!instance) {
                instance = Reflect.construct(classObject, registry.get(classObject));

                container.set(classObject, instance);
            }

            this[context.name] = instance;
        });
    };
}
