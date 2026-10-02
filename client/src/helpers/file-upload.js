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

/** @module helpers/file-upload */

import {inject} from 'di';
import Settings from 'models/settings';

/**
 * A file-upload helper.
 */
class FileUploadExport {

    /**
     * @private
     * @type {Settings}
     */
    @inject(Settings)
    config

    /**
     * @typedef {Object} module:helpers/file-upload~Options
     *
     * @property {function(number):void} [afterChunkUpload] After every chunk is uploaded.
     * @property {function(module:model):void} [afterAttachmentSave] After an attachment is saved.
     * @property {{isCanceled?: boolean}} [mediator] A mediator.
     */

    /**
     * Upload.
     *
     * @param {File} file A file.
     * @param {module:model} attachment An attachment model.
     * @param {module:helpers/file-upload~Options} [options] Options.
     * @returns {Promise}
     */
    upload(file, attachment, options) {
        options = options || {};

        options.afterChunkUpload = options.afterChunkUpload || (() => {});
        options.afterAttachmentSave = options.afterAttachmentSave || (() => {});
        options.mediator = options.mediator || {};

        attachment.set('name', file.name);
        attachment.set('type', file.type || 'text/plain');
        attachment.set('size', file.size);

        if (this._useChunks(file)) {
            return this._uploadByChunks(file, attachment, options);
        }

        return new Promise((resolve, reject) => {
            const fileReader = new FileReader();

            fileReader.onload = (e) => {
                attachment.set('file', e.target.result);

                attachment.save({}, {timeout: 0})
                    .then(() => resolve())
                    .catch(() => reject());
            };

            fileReader.readAsDataURL(file);
        });
    }

    /**
     * @private
     */
    _uploadByChunks(file, attachment, options) {
        return new Promise((resolve, reject) => {
            attachment.set('isBeingUploaded', true);

            attachment
                .save()
                .then(() => {
                    options.afterAttachmentSave(attachment);

                    return this._uploadChunks(
                        file,
                        attachment,
                        resolve,
                        reject,
                        options
                    );
                })
                .catch(() => reject());
        });
    }

    /**
     * @private
     */
    _uploadChunks(file, attachment, resolve, reject, options, start) {
        start = start || 0;
        let end = start + this._getChunkSize() + 1;

        if (end > file.size) {
            end = file.size;
        }

        if (options.mediator.isCanceled) {
            reject();

            return;
        }

        const blob = file.slice(start, end);

        const fileReader = new FileReader();

        fileReader.onloadend = (e) => {
            if (e.target.readyState !== FileReader.DONE) {
                return;
            }

            Espo.Ajax
                .postRequest('Attachment/chunk/' + attachment.id, e.target.result, {
                    headers: {
                        contentType: 'multipart/form-data',
                    }
                })
                .then(() => {
                    options.afterChunkUpload(end);

                    if (end === file.size) {
                        resolve();

                        return;
                    }

                    this._uploadChunks(
                        file,
                        attachment,
                        resolve,
                        reject,
                        options,
                        end
                    );
                })
                .catch(() => reject());
        };

        fileReader.readAsDataURL(blob);
    }

    /**
     * @private
     */
    _useChunks(file) {
        const chunkSize = this._getChunkSize();

        if (!chunkSize) {
            return false;
        }

        if (file.size > chunkSize) {
            return true;
        }

        return false;
    }

    /**
     * @private
     */
    _getChunkSize() {
        return (this.config.get('attachmentUploadChunkSize') || 0) * 1024 * 1024;
    }
}

export default FileUploadExport;
