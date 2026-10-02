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

import ModalView from 'views/modal';

class ImageCropModalView extends ModalView {

    template = 'modals/image-crop'

    cssName = 'image-crop'

    events = {
        /** @this ImageCropModalView */
        'click [data-action="zoomIn"]': function () {
            this.$img.cropper('zoom', 0.1);
        },
        /** @this ImageCropModalView */
        'click [data-action="zoomOut"]': function () {
            this.$img.cropper('zoom', -0.1);
        },
    }

    setup() {
        this.buttonList = [
            {
                name: 'crop',
                label: 'Submit',
                style: 'primary',
            },
            {
                name: 'cancel',
                label: 'Cancel',
            },
        ];

        this.wait(
            Espo.loader.requirePromise('lib!cropper')
        );

        this.on('remove', () => {
            if (this.$img.length) {
                this.$img.cropper('destroy');
                this.$img.parent().empty();
            }
        });
    }

    afterRender() {
        // noinspection RequiredAttributes,HtmlRequiredAltAttribute
        let $img = this.$img = $(`<img>`)
            .attr('src', this.options.contents)
            .addClass('hidden');

        this.$el.find('.image-container').append($img);

        setTimeout(() => {
            $img.cropper({
                aspectRatio: 1,
                movable: true,
                resizable: true,
                rotatable: false,
            });
        }, 50);
    }

    // noinspection JSUnusedGlobalSymbols
    actionCrop() {
        let dataUrl = this.$img.cropper('getDataURL', 'image/jpeg');

        this.trigger('crop', dataUrl);
        this.close();
    }
}

export default ImageCropModalView;
