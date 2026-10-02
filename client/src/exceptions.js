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

/** @module exceptions */

Espo.Exceptions = Espo.Exceptions || {};

/**
 * An access-denied exception.
 *
 * @param {string} [message] A message.
 * @class
 */
Espo.Exceptions.AccessDenied = function (message) {
    this.message = message;

    Error.apply(this, arguments);
};

Espo.Exceptions.AccessDenied.prototype = new Error();
Espo.Exceptions.AccessDenied.prototype.name = 'AccessDenied';

/**
 * A not-found exception.
 *
 * @param {string} [message] A message.
 * @class
 */
Espo.Exceptions.NotFound = function (message) {
    this.message = message;

    Error.apply(this, arguments);
};

Espo.Exceptions.NotFound.prototype = new Error();
Espo.Exceptions.NotFound.prototype.name = 'NotFound';

export default Espo.Exceptions;
