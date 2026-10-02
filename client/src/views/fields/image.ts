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

import FileFieldView, {FileOptions, FileParams, PreviewSize} from 'views/fields/file';
import {BaseViewSchema} from 'views/fields/base';

class ImageFieldView<
    S extends BaseViewSchema = BaseViewSchema,
    O extends FileOptions = FileOptions,
    P extends FileParams = FileParams
> extends FileFieldView<S, O, P> {

    readonly type: string = 'image'

    protected showPreview: boolean = true

    protected accept = ['image/*']

    protected defaultType = 'image/jpeg'

    protected previewSize: PreviewSize = 'small'
}

export default ImageFieldView;
