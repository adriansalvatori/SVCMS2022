/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/add-image-to-multi-gallery.js":
/*!*******************************************!*\
  !*** ./src/add-image-to-multi-gallery.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ AddImageToMultiGallery)
/* harmony export */ });
/* harmony import */ var _attributes__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./attributes */ "./src/attributes.js");


class AddImageToMultiGallery {
    constructor(obj, cells, x, y, e) {
        this.cells = cells;
        this.obj = obj;
        this.x = parseInt(x);
        this.y = parseInt(y);

        this.run();
    }

    run() {
        let $this = this;
        const mediaMultiple = wp.media({multiple: true});
        mediaMultiple.open().off('select close')
            .on('select', function (e) {
                var selection = mediaMultiple.state().get('selection');
                selection.each(function (attachment) {
                    attachment = attachment.toJSON();
                    if (attachment.type === 'image') {
                        // galleryPopup.find('.vi-wbe-gallery-images').append(tmpl.galleryImage(attachment.url, attachment.id));
                        let imgId = attachment.id;
                        _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.imgStorage[imgId] = attachment.url;
                        $this.addImage(imgId);
                    }
                });
            });
    }

    addImage(imgId) {

        let excelObj = this.obj;
        let breakControl = false, records = [];
        let h = this.cells;
        let start = h[1], end = h[3], x = h[0];

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                let value = excelObj.options.data[y][x];
                if (!value) value = [];

                let newValue = [...new Set(value)];
                newValue.push(imgId);

                records.push(excelObj.updateCell(x, y, newValue));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }
}

/***/ }),

/***/ "./src/attributes.js":
/*!***************************!*\
  !*** ./src/attributes.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Attributes": () => (/* binding */ Attributes),
/* harmony export */   "I18n": () => (/* binding */ I18n)
/* harmony export */ });
/* harmony import */ var _custom_column__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./custom-column */ "./src/custom-column.js");


const Attributes = {
    ...wbeParams,
    productTypes: {},
    filterKey: Date.now(),
    selectPage: 1,
    ajaxData: {action: 'vi_wbe_ajax', vi_wbe_nonce: wbeParams.nonce},
    tinyMceOptions: {
        tinymce: {
            theme: "modern",
            skin: "lightgray",
            language: "en",
            formats: {
                alignleft: [
                    {selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign: "left"}},
                    {selector: "img,table,dl.wp-caption", classes: "alignleft"}
                ],
                aligncenter: [
                    {selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign: "center"}},
                    {selector: "img,table,dl.wp-caption", classes: "aligncenter"}
                ],
                alignright: [
                    {selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign: "right"}},
                    {selector: "img,table,dl.wp-caption", classes: "alignright"}
                ],
                strikethrough: {inline: "del"}
            },
            relative_urls: false,
            remove_script_host: false,
            convert_urls: false,
            browser_spellcheck: true,
            fix_list_elements: true,
            entities: "38,amp,60,lt,62,gt",
            entity_encoding: "raw",
            keep_styles: false,
            cache_suffix: "wp-mce-49110-20201110",
            resize: "vertical",
            menubar: false,
            branding: false,
            preview_styles: "font-family font-size font-weight font-style text-decoration text-transform",
            end_container_on_empty_block: true,
            wpeditimage_html5_captions: true,
            wp_lang_attr: "en-US",
            wp_keep_scroll_position: false,
            wp_shortcut_labels: {
                "Heading 1": "access1",
                "Heading 2": "access2",
                "Heading 3": "access3",
                "Heading 4": "access4",
                "Heading 5": "access5",
                "Heading 6": "access6",
                "Paragraph": "access7",
                "Blockquote": "accessQ",
                "Underline": "metaU",
                "Strikethrough": "accessD",
                "Bold": "metaB",
                "Italic": "metaI",
                "Code": "accessX",
                "Align center": "accessC",
                "Align right": "accessR",
                "Align left": "accessL",
                "Justify": "accessJ",
                "Cut": "metaX",
                "Copy": "metaC",
                "Paste": "metaV",
                "Select all": "metaA",
                "Undo": "metaZ",
                "Redo": "metaY",
                "Bullet list": "accessU",
                "Numbered list": "accessO",
                "Insert\/edit image": "accessM",
                "Insert\/edit link": "metaK",
                "Remove link": "accessS",
                "Toolbar Toggle": "accessZ",
                "Insert Read More tag": "accessT",
                "Insert Page Break tag": "accessP",
                "Distraction-free writing mode": "accessW",
                "Add Media": "accessM",
                "Keyboard Shortcuts": "accessH"
            },
            // content_css: "http://localhost:8000/wp-includes/css/dashicons.min.css?ver=5.6.2,http://localhost:8000/wp-includes/js/tinymce/skins/wordpress/wp-content.css?ver=5.6.2,https://fonts.googleapis.com/css?family=Source+Sans+Pro:400%2C300%2C300italic%2C400italic%2C600%2C700%2C900&subset=latin%2Clatin-ext,http://localhost:8000/wp-content/themes/storefront/assets/css/base/gutenberg-editor.css",
            plugins: "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
            selector: "#vi-wbe-text-editor",
            wpautop: true,
            indent: false,
            toolbar1: "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv",
            toolbar2: "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
            tabfocus_elements: ":prev,:next",
            body_class: "excerpt post-type-product post-status-publish page-template-default locale-en-us",
        },
        mediaButtons: true,
        quicktags: true
    },
    setColumns(raw) {
        try {
            let columns = JSON.parse(raw);
            Attributes.columns = columns.map((col) => {
                if (col && col.editor && _custom_column__WEBPACK_IMPORTED_MODULE_0__.customColumn[col.editor]) col.editor = _custom_column__WEBPACK_IMPORTED_MODULE_0__.customColumn[col.editor];
                if (col && col.filter && _custom_column__WEBPACK_IMPORTED_MODULE_0__.columnFilter[col.filter]) col.filter = _custom_column__WEBPACK_IMPORTED_MODULE_0__.columnFilter[col.filter];
                return col;
            });

        } catch (e) {
            console.log(e);
        }
    }
};


window.Attributes = Attributes;
const I18n = wbeI18n.i18n;


/***/ }),

/***/ "./src/calculator.js":
/*!***************************!*\
  !*** ./src/calculator.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Calculator": () => (/* binding */ Calculator),
/* harmony export */   "CalculatorBaseOnRegularPrice": () => (/* binding */ CalculatorBaseOnRegularPrice)
/* harmony export */ });
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./functions */ "./src/functions.js");
/* harmony import */ var _modal_popup__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal-popup */ "./src/modal-popup.js");



const $ = jQuery;

class Calculator {
    constructor(obj, x, y, e) {
        this._data = {};
        this._data.jexcel = obj;
        this._data.x = parseInt(x);
        this._data.y = parseInt(y);
        this.run();
    }

    get(id) {
        return this._data[id] || ''
    }

    run() {
        let formulaHtml = this.content();
        let cell = $(`td[data-x=${this.get('x') || 0}][data-y=${this.get('y') || 0}]`);
        new _modal_popup__WEBPACK_IMPORTED_MODULE_1__.Popup(formulaHtml, cell);
        formulaHtml.on('click', '.vi-wbe-apply-formula', this.applyFormula.bind(this));
        formulaHtml.on('change', '.vi-wbe-rounded', this.toggleDecimalValue);
    }

    content() {
        return $(`<div class="vi-wbe-formula-container" style="display: flex;">
                    <select class="vi-wbe-operator">
                        <option value="+">+</option>
                        <option value="-">-</option>
                    </select>
                    <input type="number" min="0" class="vi-wbe-value">
                    <select class="vi-wbe-unit">
                        <option value="fixed">n</option>
                        <option value="percentage">%</option>
                    </select>
                    <select class="vi-wbe-rounded">
                        <option value="no_round">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('No round')}</option>
                        <option value="round">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Round with decimal')}</option>
                        <option value="round_up">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Round up')}</option>
                        <option value="round_down">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Round down')}</option>
                    </select>
                    <input type="number" min="0" max="10" class="vi-wbe-decimal" value="0">
                    <button type="button" class="vi-ui button mini vi-wbe-apply-formula">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('OK')}</button>
                </div>`);
    }

    applyFormula(e) {
        let form = $(e.target).closest('.vi-wbe-formula-container'),
            operator = form.find('.vi-wbe-operator').val(),
            fValue = parseFloat(form.find('.vi-wbe-value').val()),
            unit = form.find('.vi-wbe-unit').val(),
            rounded = form.find('.vi-wbe-rounded').val(),
            decimal = parseInt(form.find('.vi-wbe-decimal').val()),
            excelObj = this.get('jexcel');

        if (!fValue) return;

        let breakControl = false, records = [];
        let h = excelObj.selectedContainer;
        let start = h[1], end = h[3], x = h[0];

        function formula(oldValue) {
            oldValue = parseFloat(oldValue);
            let extraValue = unit === 'percentage' ? oldValue * fValue / 100 : fValue;
            let newValue = operator === '-' ? oldValue - extraValue : oldValue + extraValue;
            switch (rounded) {
                case 'round':
                    newValue = newValue.toFixed(decimal);
                    break;
                case 'round_up':
                    newValue = Math.ceil(newValue);
                    break;
                case 'round_down':
                    newValue = Math.floor(newValue);
                    break;
            }
            return newValue;
        }

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                let value = excelObj.options.data[y][x] || 0;
                records.push(excelObj.updateCell(x, y, formula(value)));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }

    toggleDecimalValue() {
        let form = $(this).closest('.vi-wbe-formula-container');
        form.find('.vi-wbe-decimal').hide();
        if ($(this).val() === 'round') form.find('.vi-wbe-decimal').show();
    }
}

class CalculatorBaseOnRegularPrice {
    constructor(obj, x, y, e) {
        this._data = {};
        this._data.jexcel = obj;
        this._data.x = parseInt(x);
        this._data.y = parseInt(y);
        this.run();
    }

    get(id) {
        return this._data[id] || ''
    }

    run() {
        let formulaHtml = this.content();
        let cell = $(`td[data-x=${this.get('x') || 0}][data-y=${this.get('y') || 0}]`);
        new _modal_popup__WEBPACK_IMPORTED_MODULE_1__.Popup(formulaHtml, cell);
        formulaHtml.on('click', '.vi-wbe-apply-formula', this.applyFormula.bind(this));
        formulaHtml.on('change', '.vi-wbe-rounded', this.toggleDecimalValue);
    }

    content() {
        return $(`<div class="vi-wbe-formula-container" style="display: flex;">
                    <span class="vi-wbe-operator vi-ui button basic small icon"><i class="icon minus"> </i></span>
                    <input type="number" min="0" class="vi-wbe-value">
                    <select class="vi-wbe-unit">
                        <option value="percentage">%</option>
                        <option value="fixed">n</option>
                    </select>
                    <select class="vi-wbe-rounded">
                        <option value="no_round">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('No round')}</option>
                        <option value="round">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Round with decimal')}</option>
                        <option value="round_up">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Round up')}</option>
                        <option value="round_down">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Round down')}</option>
                    </select>
                    <input type="number" min="0" max="10" class="vi-wbe-decimal" value="0">
                    <button type="button" class="vi-ui button mini vi-wbe-apply-formula">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('OK')}</button>
                </div>`);
    }

    applyFormula(e) {
        let form = $(e.target).closest('.vi-wbe-formula-container'),
            fValue = parseFloat(form.find('.vi-wbe-value').val()),
            unit = form.find('.vi-wbe-unit').val(),
            rounded = form.find('.vi-wbe-rounded').val(),
            decimal = parseInt(form.find('.vi-wbe-decimal').val()),
            excelObj = this.get('jexcel');

        if (!fValue) return;

        let breakControl = false, records = [];
        let h = excelObj.selectedContainer;
        let start = h[1], end = h[3], x = h[0];

        function formula(regularPrice) {
            regularPrice = parseFloat(regularPrice);
            let extraValue = unit === 'percentage' ? regularPrice * fValue / 100 : fValue;
            let newValue = regularPrice - extraValue;
            newValue = newValue > 0 ? newValue : 0;

            switch (rounded) {
                case 'round':
                    newValue = newValue.toFixed(decimal);
                    break;
                case 'round_up':
                    newValue = Math.ceil(newValue);
                    break;
                case 'round_down':
                    newValue = Math.floor(newValue);
                    break;
            }
            return newValue;
        }

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                let value = excelObj.options.data[y][x - 1] || 0;
                records.push(excelObj.updateCell(x, y, formula(value)));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }

    toggleDecimalValue() {
        let form = $(this).closest('.vi-wbe-formula-container');
        form.find('.vi-wbe-decimal').hide();
        if ($(this).val() === 'round') form.find('.vi-wbe-decimal').show();
    }
}

// export default Calculator;

/***/ }),

/***/ "./src/custom-column.js":
/*!******************************!*\
  !*** ./src/custom-column.js ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "customColumn": () => (/* binding */ customColumn),
/* harmony export */   "columnFilter": () => (/* binding */ columnFilter)
/* harmony export */ });
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./functions */ "./src/functions.js");
/* harmony import */ var _attributes__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./attributes */ "./src/attributes.js");
/* harmony import */ var _templates__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./templates */ "./src/templates.js");




const customColumn = {};
const columnFilter = {};

jQuery(document).ready(function ($) {
    window.viIsEditing = false;
    const mediaMultiple = wp.media({multiple: true});
    const mediaSingle = wp.media({multiple: false});

    const tmpl = {
        galleryImage(src, id) {
            return `<li class="vi-wbe-gallery-image" data-id="${id}"><i class="vi-wbe-remove-image dashicons dashicons-no-alt"> </i><img src="${src}"></li>`;
        },

        fileDownload($_file = {}) {
            let {id, name, file} = $_file;
            let row = $(`<tr>
                        <td><i class="bars icon"></i><input type="text" class="vi-wbe-file-name" value="${name || ''}"></td>
                        <td>
                            <input type="text" class="vi-wbe-file-url" value="${file || ''}">
                            <input type="hidden" class="vi-wbe-file-hash" value="${id || ''}">
                            <span class="vi-ui button mini vi-wbe-choose-file">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Choose file')}</span>
                            <i class="vi-wbe-remove-file dashicons dashicons-no-alt"> </i>
                        </td>
                    </tr>`);

            row.on('click', '.vi-wbe-remove-file', function () {
                row.remove();
            });

            return row;
        }
    };

    customColumn.textEditor = {
        type: 'textEditor',

        createCell(cell, i, value, obj) {
            cell.innerHTML = _functions__WEBPACK_IMPORTED_MODULE_0__.default.stripHtml(value).slice(0, 50);
            return cell;
        },

        closeEditor(cell, save) {
            window.viIsEditing = false;
            let content = '';
            if (save === true) {
                content = wp.editor.getContent('vi-wbe-text-editor');

                if (!this.isEditing) {
                    wp.editor.remove('vi-wbe-text-editor');
                }
                this.isEditing = false;
            }
            return content;
        },

        openEditor(cell, el, obj) {
            window.viIsEditing = true;
            let y = cell.getAttribute('data-y'),
                x = cell.getAttribute('data-x'),
                content = obj.options.data[y][x],
                $this = this,
                modalClose = $('.vi-ui.modal .close.icon');

            $('.vi-ui.modal').modal('show');
            this.tinymceInit(content);

            modalClose.off('click');

            $('.vi-wbe-text-editor-save').off('click').on('click', function () {
                $(this).removeClass('primary');
                if ($(this).hasClass('vi-wbe-close')) {
                    $('.vi-ui.modal').modal('hide');
                } else {
                    $this.isEditing = true;
                }
                obj.closeEditor(cell, true);
            });

            modalClose.on('click', function () {
                obj.closeEditor(cell, false);
            });

            let modal = $('.vi-ui.modal').parent();
            modal.on('click', function (e) {
                if (e.target === e.delegateTarget) {
                    obj.closeEditor(cell, false);
                }
            })
        },

        updateCell(cell, value, force) {
            cell.innerHTML = _functions__WEBPACK_IMPORTED_MODULE_0__.default.stripHtml(value).slice(0, 50);
            return value;
        },

        tinymceInit(content = '') {
            content = wp.editor.autop(content);
            if (tinymce.get('vi-wbe-text-editor') === null) {
                $('#vi-wbe-text-editor').val(content);

                _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.tinyMceOptions.tinymce.setup = function (editor) {
                    editor.on('keyup', function (e) {
                        $('.vi-wbe-text-editor-save:not(.vi-wbe-close)').addClass('primary');
                    });
                };

                wp.editor.initialize('vi-wbe-text-editor', _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.tinyMceOptions);

            }

            tinymce.get('vi-wbe-text-editor').setContent(content)
        },
    };

    customColumn.image = {
        createCell(cell, i, value, obj) {
            if (value) {
                let url = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage[value];
                _functions__WEBPACK_IMPORTED_MODULE_0__.default.isUrl(url) ? $(cell).html(`<img width="40" src="${url}" data-id="${value}">`) : $(cell).html('');
            }
            return cell;
        },

        closeEditor(cell, save) {
            return $(cell).find('img').attr('data-id') || '';
        },

        openEditor(cell, el, obj) {
            function openMedia() {
                mediaSingle.open().off('select').on('select', function (e) {
                    let uploadedImages = mediaSingle.state().get('selection').first();
                    let selectedImages = uploadedImages.toJSON();
                    if (_functions__WEBPACK_IMPORTED_MODULE_0__.default.isUrl(selectedImages.url)) {
                        $(cell).html(`<img width="40" src="${selectedImages.url}" data-id="${selectedImages.id}">`);
                        _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage[selectedImages.id] = selectedImages.url;
                        obj.closeEditor(cell, true);
                    }
                });
            }

            $(cell).on('dblclick', openMedia);

            openMedia();
        },

        updateCell(cell, value, force) {
            value = parseInt(value) || '';
            let url = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage[value];
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.isUrl(url) ? $(cell).html(`<img width="40" src="${url}" data-id="${value}">`) : $(cell).html('');
            return value;
        },
    };

    customColumn.gallery = {
        type: 'gallery',

        saveData(cell) {
            let newIds = [];
            $(cell).find('.vi-wbe-gallery-image').each(function () {
                newIds.push($(this).data('id'));
            });
            $(cell).find('.vi-wbe-ids-list').val(newIds.join(','));
        },

        createCell(cell, i, value) {
            let hasItem = value.length ? 'vi-wbe-gallery-has-item' : '';
            $(cell).addClass('vi-wbe-gallery');
            $(cell).html(`<div class="vi-wbe-gallery ${hasItem}"><i class="images outline icon"> </i></div>`);
            return cell;
        },

        closeEditor(cell, save) {
            window.viIsEditing = false;

            let selected = [];
            if (save) {
                let child = $(cell).children();
                child.find('.vi-wbe-gallery-image').each(function () {
                    selected.push($(this).data('id'));
                });
            }
            $(cell).find('.vi-wbe-cell-popup').remove();
            return selected;
        },

        openEditor(cell, el, obj) {
            window.viIsEditing = true;

            let y = cell.getAttribute('data-y'),
                x = cell.getAttribute('data-x');

            let ids = obj.options.data[y][x],
                images = '', cacheEdition;

            if (ids.length) {
                for (let id of ids) {
                    let src = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage[id];
                    images += tmpl.galleryImage(src, id);
                }
            }

            let galleryPopup = $(`<div class="vi-wbe-cell-popup-inner">
                                    <ul class="vi-wbe-gallery-images">${images}</ul>
                                    <span class="vi-ui button tiny vi-wbe-add-image">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add image')}</span>
                                    <span class="vi-ui button tiny vi-wbe-remove-gallery">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Remove all')}</span>
                                </div>`);

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.createEditor(cell, 'div', galleryPopup);

            galleryPopup.find('.vi-wbe-gallery-images').sortable({
                items: 'li.vi-wbe-gallery-image',
                cursor: 'move',
                scrollSensitivity: 40,
                forcePlaceholderSize: true,
                forceHelperSize: false,
                helper: 'clone',
                placeholder: 'vi-wbe-sortable-placeholder',
                tolerance: "pointer",
            });

            galleryPopup.on('click', '.vi-wbe-remove-image', function () {
                $(this).parent().remove();
            });

            galleryPopup.on('click', '.vi-wbe-add-image', function () {
                mediaMultiple.open().off('select close')
                    .on('select', function (e) {
                        var selection = mediaMultiple.state().get('selection');
                        selection.each(function (attachment) {
                            attachment = attachment.toJSON();
                            if (attachment.type === 'image') {
                                _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage[attachment.id] = attachment.url;
                                galleryPopup.find('.vi-wbe-gallery-images').append(tmpl.galleryImage(attachment.url, attachment.id));
                            }
                        });
                    });
            });

            galleryPopup.on('click', '.vi-wbe-remove-gallery', function () {
                galleryPopup.find('.vi-wbe-gallery-images').empty();
            });

            if (ids.length === 0) {
                galleryPopup.find('.vi-wbe-add-image').trigger('click');
            }
        },

        updateCell(cell, value, force) {
            let icon = $(cell).find('.vi-wbe-gallery');
            value.length ? icon.addClass('vi-wbe-gallery-has-item') : icon.removeClass('vi-wbe-gallery-has-item');
            return value;
        },
    };

    customColumn.download = {
        createCell(cell, i, value) {
            $(cell).html(`<div><i class="download icon"> </i></div>`);
            return cell;
        },

        closeEditor(cell, save) {
            let data = [];
            if (save) {
                let child = $(cell).children();
                child.find('table.vi-wbe-files-download tbody tr').each(function () {
                    let row = $(this);
                    data.push({
                        id: row.find('.vi-wbe-file-hash').val(),
                        file: row.find('.vi-wbe-file-url').val(),
                        name: row.find('.vi-wbe-file-name').val()
                    });
                });

                child.remove();
            }
            return data;
        },

        openEditor(cell, el, obj) {

            let y = cell.getAttribute('data-y'),
                x = cell.getAttribute('data-x');

            let files = obj.options.data[y][x],
                cacheEdition, tbody = $('<tbody></tbody>');

            if (Array.isArray(files)) {
                for (let file of files) {
                    tbody.append(tmpl.fileDownload(file));
                }
            }

            let fileDownloadPopup = $(`<div class="">
                                        <table class="vi-wbe-files-download vi-ui celled table">
                                            <thead>
                                            <tr>
                                                <th>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Name')}</th>
                                                <th>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('File URL')}</th>
                                            </tr>
                                            </thead>
                                        </table>
                                        <span class="vi-ui button tiny vi-wbe-add-file">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add file')}</span>
                                    </div>`);

            fileDownloadPopup.find('.vi-wbe-files-download').append(tbody);

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.createEditor(cell, 'div', fileDownloadPopup);

            tbody.sortable();

            fileDownloadPopup.on('click', '.vi-wbe-add-file', () => fileDownloadPopup.find('.vi-wbe-files-download tbody').append(tmpl.fileDownload()));

            fileDownloadPopup.on('click', '.vi-wbe-choose-file', function () {
                cacheEdition = obj.edition;
                obj.edition = null;
                let row = $(this).closest('tr');

                mediaSingle.open().off('select close')
                    .on('select', function (e) {
                        let selected = mediaSingle.state().get('selection').first().toJSON();
                        if (selected.url) row.find('.vi-wbe-file-url').val(selected.url).trigger('change');
                    })
                    .on('close', () => obj.edition = cacheEdition);
            });

            if (!files.length) {
                fileDownloadPopup.find('.vi-wbe-add-file').trigger('click');
            }
        },

        updateCell(cell, value, force) {
            $(cell).html(`<div><i class="download icon"> </i></div>`);
            return value;
        },
    };

    customColumn.tags = {
        type: 'tags',
        createCell(cell, i, value, obj) {
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.formatText(cell, value);
            return cell;
        },

        openEditor(cell, el, obj) {
            let y = cell.getAttribute('data-y'),
                x = cell.getAttribute('data-x');

            let value = obj.options.data[y][x],
                select = $('<select/>'),
                editor = _functions__WEBPACK_IMPORTED_MODULE_0__.default.createEditor(cell, 'div', select);

            select.select2({
                data: value,
                multiple: true,
                minimumInputLength: 3,
                placeholder: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Search tags...'),
                ajax: {
                    url: _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.ajaxUrl,
                    type: 'post',
                    data: function (params) {
                        return {
                            ..._attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.ajaxData,
                            sub_action: 'search_tags',
                            search: params.term,
                            type: 'public'
                        };
                    },
                    processResults: function (data) {
                        return {results: data};
                    }
                }
            });

            select.find('option').attr('selected', true).parent().trigger('change');

            $(editor).find('.select2-search__field').trigger('click');
        },

        closeEditor(cell, save) {
            let child = $(cell).children(),
                data = child.find('select').select2('data'),
                selected = [];

            if (data.length) {
                for (let item of data) {
                    selected.push({id: item.id, text: item.text})
                }
            }
            child.remove();
            $('.select2-container').remove();
            return selected;
        },

        updateCell(cell, value, force, obj, x) {
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.formatText(cell, value);
            return value;
        }
    };

    customColumn.link_products = {
        createCell(cell, i, value, obj) {
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.formatText(cell, value);
            return cell;
        },

        closeEditor(cell, save) {
            let child = $(cell).children(), selected = [];

            if (save) {
                let data = child.find('select').select2('data');

                if (data.length) {
                    for (let item of data) {
                        selected.push({id: item.id, text: item.text})
                    }
                }
            }

            child.remove();
            $('.select2-container').remove();
            return selected;
        },

        openEditor(cell, el, obj) {
            let y = cell.getAttribute('data-y'),
                x = cell.getAttribute('data-x');

            let value = obj.options.data[y][x],
                select = $('<select/>');

            let editor = _functions__WEBPACK_IMPORTED_MODULE_0__.default.createEditor(cell, 'div', select);

            select.select2({
                data: value,
                multiple: true,
                minimumInputLength: 3,
                placeholder: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Search products...'),
                ajax: {
                    url: _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.ajaxUrl,
                    type: 'post',
                    delay: 250,
                    dataType: 'json',
                    data: function (params) {
                        return {
                            ..._attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.ajaxData,
                            sub_action: 'search_products',
                            search: params.term,
                            type: 'public'
                        };
                    },
                    processResults: function (data) {
                        var terms = [];
                        if (data) {
                            $.each(data, function (id, text) {
                                terms.push({id: id, text: text});
                            });
                        }
                        return {
                            results: terms
                        };
                    }
                }
            });

            select.find('option').attr('selected', true).parent().trigger('change');
            $(editor).find('.select2-search__field').trigger('click');
        },

        updateCell(cell, value, force, obj, x) {
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.formatText(cell, value);
            return value;
        }
    };

    customColumn.product_attributes = {
        type: 'product_attributes',

        createCell(cell, i, value, obj) {
            $(cell).html('<i class="icon edit"/>');
            return cell;
        },

        updateCell(cell, value, force, obj, x) {
            return value;
        },

        openEditor(cell, el, obj) {
            let data = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getDataFromCell(obj, cell),
                productType = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductTypeFromCell(cell),
                $this = this, html = '';

            this.productType = productType;

            let modal = _functions__WEBPACK_IMPORTED_MODULE_0__.default.createModal({
                header: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Edit attributes'),
                content: '',
                actions: [{class: 'save-attributes', text: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Save')}],
            });

            $(cell).append(modal);

            if (productType !== 'variation') {
                let {attributes} = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes;
                let addAttribute = `<option value="">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Custom product attribute')}</option>`;

                for (let attr in attributes) {
                    addAttribute += `<option value="${attr}">${attributes[attr].data.attribute_label}</option>`;
                }

                addAttribute = `<div class="vi-wbe-taxonomy-header">
                                    <select class="vi-wbe-select-taxonomy">${addAttribute}</select>
                                    <span class="vi-ui button tiny vi-wbe-add-taxonomy">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add')}</span>
                                </div>`;

                if (Array.isArray(data) && data.length) {
                    for (let item of data) {
                        html += $this.createRowTable(item);
                    }
                }

                html = `${addAttribute}
                        <table class="vi-ui celled table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Attributes</th>
                                <th width="1">Actions</th>
                            </tr>
                            </thead>
                            <tbody>${html}</tbody>
                        </table>`;

                modal.find('.content').append(html);
                modal.find('table select').select2({multiple: true});
                modal.find('tbody').sortable({
                    items: 'tr',
                    cursor: 'move',
                    axis: 'y',
                    scrollSensitivity: 40,
                    forcePlaceholderSize: true,
                    helper: 'clone',
                    handle: '.icon.move',
                });

                const setOptionDisable = () => {
                    modal.find('select.vi-wbe-select-taxonomy option').removeAttr('disabled');
                    modal.find('input[type=hidden]').each(function (i, el) {
                        let tax = $(el).val();
                        modal.find(`select.vi-wbe-select-taxonomy option[value='${tax}']`).attr('disabled', 'disabled');
                    });
                };

                setOptionDisable();

                modal.on('click', function (e) {
                    let $thisTarget = $(e.target);
                    if ($thisTarget.hasClass('trash')) {
                        $thisTarget.closest('tr').remove();
                        setOptionDisable();
                    }

                    if ($thisTarget.hasClass('vi-wbe-add-taxonomy')) {
                        let taxSelect = $('.vi-wbe-select-taxonomy'), tax = taxSelect.val(),
                            item = {name: tax, options: []};
                        if (tax) item.is_taxonomy = 1;

                        let row = $($this.createRowTable(item));
                        modal.find('table tbody').append(row);
                        row.find('select').select2({multiple: true});
                        setOptionDisable();
                        taxSelect.val('').trigger('change');
                    }

                    if ($thisTarget.hasClass('vi-wbe-select-all-attributes')) {
                        let td = $thisTarget.closest('td');
                        let select = td.find('select');
                        select.find('option').attr('selected', true);
                        select.trigger('change');
                    }

                    if ($thisTarget.hasClass('vi-wbe-select-no-attributes')) {
                        let td = $thisTarget.closest('td');
                        let select = td.find('select');
                        select.find('option').attr('selected', false);
                        select.trigger('change');
                    }

                    if ($thisTarget.hasClass('vi-wbe-add-new-attribute')) {
                        let newAttr = prompt(_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Enter a name for the new attribute term:'));

                        if (!newAttr) return;

                        let tr = $thisTarget.closest('tr.vi-wbe-attribute-row'),
                            taxAttr = tr.attr('data-attr');

                        if (taxAttr) {
                            taxAttr = JSON.parse(taxAttr);
                            _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                                data: {
                                    sub_action: 'add_new_attribute',
                                    taxonomy: taxAttr.name,
                                    term: newAttr
                                },
                                beforeSend() {
                                    $thisTarget.addClass('loading')
                                },
                                success(res) {
                                    $thisTarget.removeClass('loading');
                                    if (res.success) {
                                        let select = tr.find('select');
                                        select.append(`<option value="${res.data.term_id}" selected>${res.data.name}</option>`);
                                        select.trigger('change');
                                        _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.attributes[taxAttr.name].terms[res.data.term_id] = {slug: res.data.slug, text: res.data.name}
                                    } else {
                                        alert(res.data.message)
                                    }
                                }
                            });
                        }
                    }
                });

            } else {
                //Variation attributes
                let y = cell.getAttribute('data-y');
                let parentId = obj.options.data[y][1],
                    allProducts = obj.getData(), parentAttributes;

                for (let _y in allProducts) {
                    let productId = allProducts[_y][0];
                    if (parentId == productId) {
                        let x = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.idMappingFlip.attributes;
                        parentAttributes = obj.options.data[_y][x];
                        break;
                    }
                }

                if (parentAttributes) {
                    for (let attr of parentAttributes) {
                        let options = `<option value="">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Any...')}</option>`, name = attr.name, label;
                        if (attr.is_taxonomy) {
                            let attrData = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.attributes[name];
                            for (let id of attr.options) {
                                let term = attrData.terms[id];
                                let selected = term.slug === data[name] ? 'selected' : '';
                                options += `<option value="${term.slug}" ${selected}>${term.text}</option>`;
                            }
                            label = attrData.data.attribute_label
                        } else {
                            for (let value of attr.options) {
                                let selected = value === data[name] ? 'selected' : '';
                                options += `<option value="${value}" ${selected}>${value}</option>`;
                            }
                            label = name;
                        }
                        html += `<tr><td>${label}</td><td><select name="${name}">${options}</select></td></tr>`;
                    }
                }

                html = `<table class="vi-ui celled table">
                            <thead>
                            <tr>
                                <th>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Attribute')}</th>
                                <th>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Option')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            ${html}
                            </tbody>
                        </table>`;

                modal.find('.content').append(html);
            }

            modal.on('click', function (e) {
                let thisTarget = $(e.target);
                if (thisTarget.hasClass('close') || thisTarget.hasClass('vi-wbe-modal-container')) obj.closeEditor(cell, false);
                if (thisTarget.hasClass('save-attributes')) obj.closeEditor(cell, true);
            });
        },

        closeEditor(cell, save) {
            let data = [];
            if (save === true) {
                if (this.productType !== 'variation') {
                    $(cell).find('.vi-wbe-attribute-row').each(function (i, row) {
                        let pAttr = $(row).data('attr');
                        if (pAttr.is_taxonomy) {
                            pAttr.options = $(row).find('select').val().map(Number);
                        } else {
                            pAttr.name = $(row).find('input.custom-attr-name').val();
                            let value = $(row).find('textarea.custom-attr-val').val();
                            pAttr.value = value.trim().replace(/\s+/g, ' ');
                            pAttr.options = value.split('|').map(item => item.trim().replace(/\s+/g, ' '));
                        }
                        pAttr.visible = !!$(row).find('.attr-visibility:checked').length;
                        pAttr.variation = !!$(row).find('.attr-variation:checked').length;
                        pAttr.position = i;
                        data.push(pAttr)
                    })
                } else {
                    data = {};
                    $(cell).find('select').each(function (i, row) {
                        data[$(row).attr('name')] = $(row).val();
                    });
                }
            }
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeModal(cell);
            return data;
        },

        createRowTable(item) {
            let attrName = '', value = '';

            if (item.is_taxonomy) {
                let attribute = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.attributes[item.name],
                    terms = attribute.terms || [], options = '';

                attrName = `${attribute.data.attribute_label}<input type="hidden" value="${item.name}"/>`;

                if (Object.keys(terms).length) {
                    for (let id in terms) {
                        let selected = item.options.includes(parseInt(id)) ? 'selected' : '';
                        options += `<option value="${id}" ${selected}>${terms[id].text}</option>`;
                    }
                }
                value = `<select multiple>${options}</select>
                        <div class="vi-wbe-attributes-button-group">
                            <span class="vi-ui button mini vi-wbe-select-all-attributes">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Select all')}</span>
                            <span class="vi-ui button mini vi-wbe-select-no-attributes">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Select none')}</span>
                            <span class="vi-ui button mini vi-wbe-add-new-attribute">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add new')}</span>
                        </div>`;
            } else {
                attrName = `<input type="text" class="custom-attr-name" value="${item.name}" placeholder="${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Custom attribute name')}"/>`;
                value = `<textarea class="custom-attr-val" placeholder="${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Enter some text, or some attributes by "|" separating values.')}">${item.value || ''}</textarea>`;
            }

            attrName = `<div class="vi-wbe-attribute-name-label">${attrName}</div>`;

            attrName += `<div>
                            <input type="checkbox" class="attr-visibility" ${item.visible ? 'checked' : ''} value="1">
                            <label>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Visible on the product page')}</label>
                        </div>`;

            if (this.productType === 'variable') {
                attrName += `<div>
                                <input type="checkbox" class="attr-variation" ${item.variation ? 'checked' : ''} value="1">
                                <label>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Used for variations')}</label>
                            </div>`;
            }

            return `<tr class="vi-wbe-attribute-row" data-attr='${JSON.stringify(item)}'>
                        <td class="vi-wbe-left">${attrName}</td>
                        <td>${value}</td>
                        <td class="vi-wbe-right"><i class="icon trash"> </i> <i class="icon move"> </i></td>
                    </tr>`;
        }

    };

    customColumn.default_attributes = {
        createCell(cell, i, value, obj) {
            if (value) $(cell).text(Object.values(value).filter(Boolean).join('; '));
            return cell;
        },

        updateCell(cell, value, force, obj, x) {
            if (value) {
                $(cell).text(Object.values(value).filter(Boolean).join('; '));
            } else {
                $(cell).text('');
            }
            return value;
        },

        openEditor(cell, el, obj) {
            let data = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getDataFromCell(obj, cell),
                productType = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductTypeFromCell(cell),
                html = '';

            this.productType = productType;
            if (productType === 'variable') {
                let modal = _functions__WEBPACK_IMPORTED_MODULE_0__.default.createModal({header: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Set default attributes'), content: '', actions: [{class: 'save-attributes', text: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Save')}]});
                $(cell).append(modal);

                let y = cell.getAttribute('data-y'),
                    x = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.idMappingFlip.attributes,
                    pAttributes = obj.options.data[y][x];

                if (Array.isArray(pAttributes) && pAttributes.length) {
                    for (let attr of pAttributes) {
                        if (attr.options.length === 0) continue;

                        let attrName = '', selectHtml = '';

                        if (attr.is_taxonomy) {
                            let attrData = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.attributes[attr.name];

                            attrName = attrData.data.attribute_label;
                            for (let termId of attr.options) {
                                let term = attrData.terms[termId],
                                    selected = term.slug === data[attr.name] ? 'selected' : '';
                                selectHtml += `<option value="${term.slug}" ${selected}>${term.text}</option>`;
                            }

                        } else {
                            attrName = attr.name;
                            for (let term of attr.options) {
                                let selected = term === data[attr.name] ? 'selected' : '';
                                selectHtml += `<option value="${term}" ${selected}>${term}</option>`;
                            }
                        }
                        selectHtml = `<option value="">No default ${attrName}</option> ${selectHtml}`;

                        html += `<tr><td>${attrName}</td><td><select name="${attr.name}" class="vi-wbe-default-attribute">${selectHtml}</select></td></tr>`;
                    }
                }

                modal.find('.content').append(_templates__WEBPACK_IMPORTED_MODULE_2__.default.defaultAttributes({html}));

                modal.on('click', function (e) {
                    let thisTarget = $(e.target);
                    if (thisTarget.hasClass('close') || thisTarget.hasClass('vi-wbe-modal-container')) obj.closeEditor(cell, false);
                    if (thisTarget.hasClass('save-attributes')) obj.closeEditor(cell, true);
                });
            }
        },

        closeEditor(cell, save) {
            let data = {};
            if (save === true) $(cell).find('.vi-wbe-default-attribute').each((i, el) => data[$(el).attr('name')] = $(el).val());
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeModal(cell);
            return data;
        },

    };

    customColumn.array = {
        createCell(cell, i, value, obj) {
            $(cell).html('<i class="icon edit"/>');
            return cell;
        },

        closeEditor(cell, save) {
            let metadata = [];
            if (save === true) {
                metadata = this.editor.get();
            }

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeModal(cell);

            return metadata;
        },

        openEditor(cell, el, obj) {
            let data = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getDataFromCell(obj, cell);
            let modal = _functions__WEBPACK_IMPORTED_MODULE_0__.default.createModal({
                header: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Edit metadata'),
                content: '',
                actions: [{class: 'save-metadata', text: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Save')}],
            });

            $(cell).append(modal);
            modal.find('.content').html('<div id="vi-wbe-jsoneditor"></div>');
            let container = modal.find('#vi-wbe-jsoneditor').get(0);
            this.editor = new JSONEditor(container, {enableSort: false, search: false, enableTransform: false});
            this.editor.set(data);

            modal.on('click', function (e) {
                let thisTarget = $(e.target);
                if (thisTarget.hasClass('close') || thisTarget.hasClass('vi-wbe-modal-container')) obj.closeEditor(cell, false);
                if (thisTarget.hasClass('save-metadata')) obj.closeEditor(cell, true);
            });
        },

        updateCell(cell, value, force) {
            return value;
        },
    };

    customColumn.order_notes = {

        createCell(cell, i, value, obj) {
            let hasItem = value.length ? 'vi-wbe-gallery-has-item' : '';

            $(cell).html(`<div class="${hasItem}"><i class="icon eye"/></div>`);
            this.obj = obj;

            return cell;
        },

        closeEditor(cell, save) {
            $(cell).find('.vi-wbe-cell-popup').remove();
            return this.notes;
        },

        openEditor(cell, el, obj) {
            let y = cell.getAttribute('data-y'),
                x = cell.getAttribute('data-x');

            let notes = obj.options.data[y][x],
                _note = '';

            this.notes = notes;

            if (notes.length) {
                for (let note of notes) {
                    let content = note.content.replace(/(?:\r\n|\r|\n)/g, '<br>'),
                        classColor = note.customer_note ? 'customer' : (note.added_by === 'system' ? 'system' : 'private');

                    _note += `<div class="vi-wbe-note-row">
                                <div class="vi-wbe-note-row-content ${classColor}">${content}</div>
                                <span class="vi-wbe-note-row-meta">
                                    ${note.date}
                                    <a href="#" data-comment_id="${note.id}" class="vi-wbe-note-row-delete">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Delete')}</a>
                                </span>
                            </div>`;
                }
            }

            let galleryPopup = $(`<div class="vi-wbe-cell-popup-inner">${_note}</div>`);

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.createEditor(cell, 'div', galleryPopup);

            galleryPopup.on('click', '.vi-wbe-note-row-delete', function () {
                let $thisBtn = $(this),
                    id = $thisBtn.data('comment_id');

                if (!id) return;

                _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                    data: {sub_action: 'delete_order_note', id},
                    beforeSend() {
                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading()
                    },
                    success(res) {
                        if (res.success) {
                            let index = notes.findIndex(note => note.id === id);
                            notes.splice(index, 1);
                            $thisBtn.closest('.vi-wbe-note-row').remove();
                        }
                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading()
                    }
                })
            })
        },

        updateCell(cell, value, force) {
            return value;
        },
    };

    customColumn.select2 = {
        type: 'select2',

        createCell(cell, i, value, obj) {
            let {source} = obj.options.columns[i], newValue = [];
            if (Array.isArray(source) && source.length) newValue = source.filter(item => value.includes(item.id));

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.formatText(cell, newValue);
            return cell;
        },

        openEditor(cell, el, obj) {
            let y = cell.getAttribute('data-y'),
                x = cell.getAttribute('data-x');

            let value = obj.options.data[y][x],
                select = $('<select/>'),
                {source, multiple, placeholder} = obj.options.columns[x],
                editor = _functions__WEBPACK_IMPORTED_MODULE_0__.default.createEditor(cell, 'div', select);

            select.select2({
                data: source || [],
                multiple: multiple,
                placeholder: placeholder,
            });

            select.val(value).trigger('change');
            $(editor).find('.select2-search__field').trigger('click');
        },

        closeEditor(cell, save) {
            let child = $(cell).children(),
                data = child.find('select').val();

            data = data.map(item => !isNaN(item) ? +item : item);

            child.remove();
            $('.select2-container').remove();

            return data;
        },

        updateCell(cell, value, force, obj, x) {
            let {source} = obj.options.columns[x], newValue = [];

            if (Array.isArray(source) && source.length) newValue = source.filter(item => value.includes(item.id));

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.formatText(cell, newValue);

            return value;
        }
    };

//--------------------------------------------------------------------//
    columnFilter.sourceForVariation = (el, cell, x, y, obj) => {
        let source = obj.options.columns[x].source;
        let productType = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductTypeFromCell(cell);
        if (productType === 'variation') {
            source = obj.options.columns[x].subSource;
        }
        return source;
    };

});



/***/ }),

/***/ "./src/find-and-replace-options.js":
/*!*****************************************!*\
  !*** ./src/find-and-replace-options.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FindAndReplaceOptions)
/* harmony export */ });
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./functions */ "./src/functions.js");
/* harmony import */ var _modal_popup__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal-popup */ "./src/modal-popup.js");



const $ = jQuery;
class FindAndReplaceOptions {
    constructor(obj, cells, x, y, e) {
        this.cells = cells;
        this.obj = obj;
        this.x = parseInt(x);
        this.y = parseInt(y);
        this.searchData = [];
        this.source = obj.options.columns[x].source || [];

        this.run();
    }

    run() {
        let $this = this;
        let formulaHtml = this.content();

        let cell = $(`td[data-x=${this.x || 0}][data-y=${this.y || 0}]`);
        new _modal_popup__WEBPACK_IMPORTED_MODULE_1__.Popup(formulaHtml, cell);

        formulaHtml.find('.vi-wbe-find-string').select2({
            data: [{id: '', text: ''}, ...$this.source]
        });

        formulaHtml.find('.vi-wbe-replace-string').select2({
            data: [{id: '', text: ''}, ...$this.source]
        });

        formulaHtml.on('click', '.vi-wbe-apply-formula', this.applyFormula.bind(this));
    }

    content() {
        return $(`<div class="vi-wbe-formula-container">
                    <div class="field">
                        <div>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Find')}</div>
                        <select placeholder="" class="vi-wbe-find-string"> </select>
                    </div>
                    <div class="field">
                        <div>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Replace')}</div>
                        <select placeholder="" class="vi-wbe-replace-string"> </select>
                    </div>
                    <button type="button" class="vi-ui button mini vi-wbe-apply-formula">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Replace')}</button>
                    <p>If 'Find' value is empty, add to selected cells with 'Replace' value.</p>
                    <p>If 'Replace' value is empty, remove from selected cells with 'Find' value.</p>
                </div>`);
    }

    applyFormula(e) {
        let form = $(e.target).closest('.vi-wbe-formula-container'),
            findValue = form.find('.vi-wbe-find-string').val(),
            replaceValue = form.find('.vi-wbe-replace-string').val(),
            excelObj = this.obj;

        if (!findValue && !replaceValue) return;

        findValue = !isNaN(findValue) ? +findValue : findValue;
        replaceValue = !isNaN(replaceValue) ? +replaceValue : replaceValue;

        let breakControl = false, records = [];
        let h = this.cells;
        let start = h[1], end = h[3], x = h[0];

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                let value = excelObj.options.data[y][x];

                if (!value) value = [];

                let newValue = value.filter((item) => item !== findValue);

                if (value.length !== newValue.length || !findValue) {
                    newValue.push(replaceValue);
                }

                newValue = [...new Set(newValue)];

                records.push(excelObj.updateCell(x, y, newValue));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }

}

/***/ }),

/***/ "./src/find-and-replace-tags.js":
/*!**************************************!*\
  !*** ./src/find-and-replace-tags.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FindAndReplaceTags)
/* harmony export */ });
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./functions */ "./src/functions.js");
/* harmony import */ var _modal_popup__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal-popup */ "./src/modal-popup.js");
/* harmony import */ var _attributes__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./attributes */ "./src/attributes.js");




const $ = jQuery;
class FindAndReplaceTags {
    constructor(obj, cells, x, y, e) {
        this.cells = cells;
        this.obj = obj;
        this.x = parseInt(x);
        this.y = parseInt(y);
        this.searchData = [];

        this.run();
    }

    run() {
        let $this = this;
        let formulaHtml = this.content();
        let y1 = this.cells[1], y2 = this.cells[3];
        let selectData = [{id: '', text: ''}];
        for (let i = y1; i <= y2; i++) {
            let value = this.obj.options.data[i][this.x];
            selectData.push(...value);
        }

        selectData = selectData.filter((item, index, self) =>
            index === self.findIndex((t) => (
                t.id === item.id && t.text === item.text
            ))
        );

        let cell = $(`td[data-x=${this.x || 0}][data-y=${this.y || 0}]`);
        new _modal_popup__WEBPACK_IMPORTED_MODULE_1__.Popup(formulaHtml, cell);

        formulaHtml.find('.vi-wbe-find-string').select2({
            data: selectData
        });

        formulaHtml.find('.vi-wbe-replace-string').select2({
            multiple: false,
            minimumInputLength: 3,
            ajax: {
                url: _attributes__WEBPACK_IMPORTED_MODULE_2__.Attributes.ajaxUrl,
                type: 'post',
                data: function (params) {
                    return {
                        ..._attributes__WEBPACK_IMPORTED_MODULE_2__.Attributes.ajaxData,
                        sub_action: 'search_tags',
                        search: params.term,
                        type: 'public'
                    };
                },
                processResults: function (data) {
                    $this.searchData = data;
                    return {results: data};
                }
            }
        });

        formulaHtml.on('click', '.vi-wbe-apply-formula', this.applyFormula.bind(this));
    }

    content() {
        return $(`<div class="vi-wbe-formula-container">
                    <div class="field">
                        <div>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Find')}</div>
                        <select placeholder="" class="vi-wbe-find-string"> </select>
                    </div>
                    <div class="field">
                        <div>${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Replace')}</div>
                        <select placeholder="" class="vi-wbe-replace-string"> </select>
                    </div>
                    <button type="button" class="vi-ui button mini vi-wbe-apply-formula">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Replace')}</button>
                    <p>If 'Find' value is empty, add to selected cells with 'Replace' value.</p>
                    <p>If 'Replace' value is empty, remove from selected cells with 'Find' value.</p>
                </div>`);
    }

    applyFormula(e) {
        let form = $(e.target).closest('.vi-wbe-formula-container'),
            findString = form.find('.vi-wbe-find-string').val(),
            replaceString = form.find('.vi-wbe-replace-string').val(),
            excelObj = this.obj;

        if (!findString && !replaceString) return;

        let replace = this.searchData.filter((item) => item.id === +replaceString);

        let breakControl = false, records = [];
        let h = this.cells;
        let start = h[1], end = h[3], x = h[0];

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                let value = excelObj.options.data[y][x];
                if (!value) value = [];
                let newValue = value.filter((item) => item.id !== +findString);

                if (value.length !== newValue.length || !findString) {
                    newValue.push(...replace);
                }

                newValue = newValue.filter((item, index, self) =>
                    index === self.findIndex((t) => (t.id === item.id && t.text === item.text))
                );

                records.push(excelObj.updateCell(x, y, newValue));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }

}

/***/ }),

/***/ "./src/find-and-replace.js":
/*!*********************************!*\
  !*** ./src/find-and-replace.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FindAndReplace)
/* harmony export */ });
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./functions */ "./src/functions.js");
/* harmony import */ var _modal_popup__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal-popup */ "./src/modal-popup.js");



const $ = jQuery;
class FindAndReplace {
    constructor(obj, x, y, e) {
        this._data = {};
        this._data.jexcel = obj;
        this._data.x = parseInt(x);
        this._data.y = parseInt(y);
        this.run();
    }

    get(id) {
        return this._data[id] || '';
    }

    run() {
        let formulaHtml = this.content();
        let cell = $(`td[data-x=${this.get('x') || 0}][data-y=${this.get('y') || 0}]`);
        new _modal_popup__WEBPACK_IMPORTED_MODULE_1__.Popup(formulaHtml, cell);
        formulaHtml.on('click', '.vi-wbe-apply-formula', this.applyFormula.bind(this));
    }

    content() {
        return $(`<div class="vi-wbe-formula-container">
                    <div class="field">
                        <input type="text" placeholder="${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Find')}" class="vi-wbe-find-string">
                    </div>
                    <div class="field">
                        <input type="text" placeholder="${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Replace')}" class="vi-wbe-replace-string">
                    </div>
                    <button type="button" class="vi-ui button mini vi-wbe-apply-formula">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Replace')}</button>
                </div>`);
    }

    applyFormula(e) {
        let form = $(e.target).closest('.vi-wbe-formula-container'),
            findString = form.find('.vi-wbe-find-string').val(),
            replaceString = form.find('.vi-wbe-replace-string').val(),
            excelObj = this.get('jexcel');

        if (!findString) return;

        let breakControl = false, records = [];
        let h = excelObj.selectedContainer;
        let start = h[1], end = h[3], x = h[0];

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                let value = excelObj.options.data[y][x];
                let newValue = value.replaceAll(findString, replaceString);
                records.push(excelObj.updateCell(x, y, newValue));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }

}

/***/ }),

/***/ "./src/functions.js":
/*!**************************!*\
  !*** ./src/functions.js ***!
  \**************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _attributes__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./attributes */ "./src/attributes.js");
/* harmony import */ var _templates__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./templates */ "./src/templates.js");



const $ = jQuery;
const _f = {
    setJexcel(obj) {
        this.jexcel = obj;
    },

    text(key) {
        return _attributes__WEBPACK_IMPORTED_MODULE_0__.I18n[key] || key;
    },

    isUrl: (url) => {
        return /^(http(s?):)\/\/.*\.(?:jpg|jpeg|gif|png|webp)$/i.test(url);
    },

    formatText(cell, value) {
        let text = '';
        if (value.length) {
            for (let k = 0; k < value.length; k++) {
                if (value[k]) text += value[k].text + '; ';
            }
        }
        cell.innerText = text;
    },

    createEditor(cell, type, content = '', display = true) {
        let editor = document.createElement(type);

        if (type === 'div') {
            $(editor).append(content);
        }

        editor.style.minWidth = '300px';

        let popupHeight = $(editor).innerHeight(),
            stage = $(cell).offset(),
            x = stage.left,
            y = stage.top,
            cellWidth = $(cell).innerWidth(),
            info = cell.getBoundingClientRect();

        if (display) {
            editor.style.minHeight = (info.height - 2) + 'px';
            editor.style.maxHeight = (window.innerHeight - y - 50) + 'px';
        } else {
            editor.style.opacity = 0;
            editor.style.fontSize = 0;
        }

        editor.classList.add('vi-ui', 'segment', 'vi-wbe-cell-popup', 'vi-wbe-editing');
        cell.classList.add('editor');
        cell.appendChild(editor);

        let popupWidth = $(editor).innerWidth();

        if ($(this.jexcel.el).innerWidth() < x + popupWidth + cellWidth) {
            let left = x - popupWidth > 0 ? x - popupWidth : 10;
            $(editor).css('left', left + 'px');
        } else {
            $(editor).css('left', (x + cellWidth) + 'px');
        }

        if (window.innerHeight < y + popupHeight) {
            let h = y - popupHeight < 0 ? 0 : y - popupHeight;
            $(editor).css('top', h + 'px');
        } else {
            $(editor).css('top', y + 'px');
        }

        return editor;
    },

    createModal(data = {}) {
        let {actions} = data;
        let actionsHtml = '';

        if (Array.isArray(actions)) {
            for (let item of actions) {
                actionsHtml += `<span class="${item.class} vi-ui button tiny">${item.text}</span>`;
            }
        }

        return $(_templates__WEBPACK_IMPORTED_MODULE_1__.default.modal({...data, actionsHtml}));
    },

    removeModal(cell) {
        $(cell).find('.vi-wbe-modal-container').remove();
        $('.select2-container--open').remove();
    },

    getColFromColumnType(colType) {
        return _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.idMappingFlip[colType] || '';
    },

    getProductTypeFromCell(cell) {
        let y = cell.getAttribute('data-y');
        let x = this.getColFromColumnType('product_type');
        return this.jexcel.options.data[y][x];
    },

    getProductTypeFromY(y) {
        let x = this.getColFromColumnType('product_type');
        // console.log(this.jexcel.options.data)
        return this.jexcel.options.data[y][x];
    },

    getColumnType(x) {
        return _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.idMapping[x]
    },

    stripHtml(content) {
        return $(`<div>${content}</div>`).text();
    },

    getDataFromCell(obj, cell) {
        let y = cell.getAttribute('data-y'),
            x = cell.getAttribute('data-x');
        return obj.options.data[y][x];
    },

    getProductIdOfCell(obj, target) {
        if (typeof target === 'object') {
            let y = target.getAttribute('data-y');
            return obj.options.data[y][0];
        } else {
            return obj.options.data[target][0];
        }
    },

    ajax(args = {}) {
        let options = Object.assign({
            url: wbeParams.ajaxUrl,
            type: 'post',
            dataType: 'json',
        }, args);

        options.data.action = 'vi_wbe_ajax';
        options.data.vi_wbe_nonce = wbeParams.nonce;
        options.data.type = wbeParams.editType;

        $.ajax(options);
    },

    pagination(maxPage, currentPage) {
        currentPage = parseInt(currentPage);
        maxPage = parseInt(maxPage);
        let pagination = '',
            previousArrow = `<a class="item ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}"><i class="icon angle left"> </i></a>`,
            nextArrow = `<a class="item ${currentPage === maxPage ? 'disabled' : ''}" data-page="${currentPage + 1}"><i class="icon angle right"> </i></a>`,
            goToPage = `<input type="number" class="vi-wbe-go-to-page" value="${currentPage}" min="1" max="${maxPage}"/>`;

        for (let i = 1; i <= maxPage; i++) {
            if ([1, currentPage - 1, currentPage, currentPage + 1, maxPage].includes(i)) {
                pagination += `<a class="item ${currentPage === i ? 'active' : ''}" data-page="${i}">${i}</a>`;
            }
            if (i === currentPage - 2 && currentPage - 2 > 1) pagination += `<a class="item disabled">...</a>`;
            if (i === currentPage + 2 && currentPage + 2 < maxPage) pagination += `<a class="item disabled">...</a>`;
        }

        return `<div class="vi-ui pagination menu">${previousArrow} ${pagination} ${nextArrow} </div> ${goToPage}`;
    },

    spinner() {
        return $('<span class="vi-wbe-spinner"><span class="vi-wbe-spinner-inner"> </span></span>')
    },

    is_loading() {
        return !!this._spinner;
    },

    loading() {
        this._spinner = this.spinner();
        $('.vi-wbe-menu-bar-center').html(this._spinner);
    },

    removeLoading() {
        this._spinner = null;
        $('.vi-wbe-menu-bar-center').html('');
    },

    notice(text, color = 'black') {
        let content = $(`<div class="vi-wbe-notice" style="color:${color}">${text}</div>`);
        $('.vi-wbe-menu-bar-center').html(content);
        setTimeout(function () {
            content.remove();
        }, 5000);
    },

    generateCouponCode() {
        let $result = '';
        for (var i = 0; i < _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.couponGenerate.char_length; i++) {
            $result += _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.couponGenerate.characters.charAt(
                Math.floor(Math.random() * _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.couponGenerate.characters.length)
            );
        }
        $result = _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.couponGenerate.prefix + $result + _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.couponGenerate.suffix;
        return $result;
    }
};

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_f);

/***/ }),

/***/ "./src/modal-popup.js":
/*!****************************!*\
  !*** ./src/modal-popup.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Modal": () => (/* binding */ Modal),
/* harmony export */   "Popup": () => (/* binding */ Popup)
/* harmony export */ });
const $ = jQuery;

class Modal {
    constructor() {

    }
}

let popupInstance = null;

class Popup {
    constructor(content, cell) {
        if (!popupInstance) {
            $('body').on('mousedown keydown', this.mousedown);
        }

        popupInstance = this;

        this.popup = $('.vi-wbe-context-popup');

        this.render(content, $(cell));
    }

    mousedown(e) {
        let thisTarget = $(e.target),
            popup = $('.vi-wbe-context-popup');

        if (e.which === 27
            || !thisTarget.hasClass('vi-wbe-context-popup')
            && thisTarget.closest('.vi-wbe-context-popup').length === 0
            && popup.hasClass('vi-wbe-popup-active')
            && !thisTarget.hasClass('select2-search__field')
        ) {
            popup.empty().removeClass('vi-wbe-popup-active');
            $('.select2-container.select2-container--default.select2-container--open').remove();
        }
    }

    render(content, cell) {
        let {popup} = this,
            stage = cell.offset(),
            x = stage.left,
            y = stage.top,
            cellWidth = cell.innerWidth();

        popup.empty();
        popup.addClass('vi-wbe-popup-active').html(content);

        let popupWidth = popup.innerWidth(),
            popupHeight = popup.innerHeight();

        if (window.innerWidth < x + popupWidth + cellWidth) {
            let left = x - popupWidth > 0 ? x - popupWidth : 10;
            popup.css('left', left + 'px');
        } else {
            popup.css('left', (x + cellWidth) + 'px');
        }

        let windowInnerHeight = $('#vi-wbe-editor').innerHeight();
        if (windowInnerHeight < y + popupHeight) {
            let h = y - popupHeight < 0 ? 0 : y - popupHeight;
            popup.css('top', h + 'px');
        } else {
            popup.css('top', y + 'px');
        }
    }

    hide() {
        this.popup.removeClass('vi-wbe-popup-active');
    }
}



/***/ }),

/***/ "./src/multiple-product-attributes.js":
/*!********************************************!*\
  !*** ./src/multiple-product-attributes.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ MultipleProductAttributes)
/* harmony export */ });
/* harmony import */ var _attributes__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./attributes */ "./src/attributes.js");
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./functions */ "./src/functions.js");



const $ = jQuery;

class MultipleProductAttributes {
    constructor(obj, cells, x, y, e) {
        this.cells = cells;
        this.obj = obj;
        this.x = parseInt(x);
        this.y = parseInt(y);

        this.run();
    }

    run() {
        let cell = $(`td[data-x=${this.x || 0}][data-y=${this.y || 0}]`);

        let $this = this, html = '';

        let modal = _functions__WEBPACK_IMPORTED_MODULE_1__.default.createModal({
            header: _functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Attributes'),
            content: '',
            actions: [{class: 'save-attributes', text: _functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Apply')}],
        });

        this.content(modal);
        $(cell).append(modal);

        modal.on('click', function (e) {
            let thisTarget = $(e.target);
            if (thisTarget.hasClass('close') || thisTarget.hasClass('vi-wbe-modal-container')) modal.remove();
            if (thisTarget.hasClass('save-attributes')) {
                $this.addAttributes(modal);
            }
        });
    }

    addImage(imgId) {

        let excelObj = this.obj;
        let breakControl = false, records = [];
        let h = this.cells;
        let start = h[1], end = h[3], x = h[0];

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                let value = excelObj.options.data[y][x];
                if (!value) value = [];

                let newValue = [...new Set(value)];
                newValue.push(imgId);

                records.push(excelObj.updateCell(x, y, newValue));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }

    addAttributes(modal) {
        let newAttributes = [],
            addAttrOpt = modal.find('.vi-wbe-add-attributes-option').val();

        modal.find('.vi-wbe-attribute-row').each(function (i, row) {
            let pAttr = $(row).data('attr');
            if (pAttr.is_taxonomy) {
                pAttr.options = $(row).find('select').val().map(Number);
            } else {
                pAttr.name = $(row).find('input.custom-attr-name').val();
                let value = $(row).find('textarea.custom-attr-val').val();
                pAttr.value = value.trim().replace(/\s+/g, ' ');
                pAttr.options = value.split('|').map(item => item.trim().replace(/\s+/g, ' '));
            }
            pAttr.visible = !!$(row).find('.attr-visibility:checked').length;
            pAttr.variation = !!$(row).find('.attr-variation:checked').length;
            pAttr.position = i;
            newAttributes.push(pAttr)
        });

        console.log(newAttributes)
        if (newAttributes.length) {
            let excelObj = this.obj;
            let breakControl = false, records = [];
            let h = this.cells;
            let start = h[1], end = h[3], x = h[0];

            const findExist = (productAttrs = [], attrName) => {
                if (productAttrs.length) {
                    for (let index in productAttrs) {
                        let attr = productAttrs[index];
                        if (attr.name === attrName) {
                            return index;
                        }
                    }
                }
                return false;
            };

            for (let y = start; y <= end; y++) {
                if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                    let value = excelObj.options.data[y][x];
                    if (!value) value = [];
                    let newValue = [...new Set(value)];
                    let positionIndex = 0;

                    for (let attr of newAttributes) {
                        let attrName = attr.name;
                        let key = findExist(newValue, attrName);

                        if (key === false) {
                            attr.position = newValue.length + positionIndex++;
                            // positionIndex++;
                            newValue.push(attr);
                        } else {
                            switch (addAttrOpt) {
                                case 'replace':
                                    attr.position = newValue[key].position;
                                    newValue[key] = attr;
                                    break;

                                case 'merge_terms':
                                    let currentTerms = newValue[key].options || [];
                                    let newTerms = attr.options || [];
                                    let terms = [...currentTerms, ...newTerms];
                                    newValue[key].options = [...new Set(terms)];
                                    break;
                            }
                        }
                    }

                    records.push(excelObj.updateCell(x, y, newValue));
                    excelObj.updateFormulaChain(x, y, records);
                }
            }

            // Update history
            excelObj.setHistory({
                action: 'setValue',
                records: records,
                selection: excelObj.selectedCell,
            });

            // Update table with custom configuration if applicable
            excelObj.updateTable();
        }
        modal.remove();

    }

    content(modal) {
        let $this = this, html = '';

        let {attributes} = _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes;
        let addAttribute = `<option value="">${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Custom product attribute')}</option>`;

        for (let attr in attributes) {
            addAttribute += `<option value="${attr}">${attributes[attr].data.attribute_label}</option>`;
        }

        addAttribute = `<div class="vi-wbe-taxonomy-header">
                            <select class="vi-wbe-select-taxonomy">${addAttribute}</select>
                            <span class="vi-ui button tiny vi-wbe-add-taxonomy">${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Add')}</span>
                        </div>`;

        html = `${addAttribute}
                <table class="vi-ui celled table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Attributes</th>
                        <th width="1">Actions</th>
                    </tr>
                    </thead>
                    <tbody>${html}</tbody>
                </table>`;

        let addAttributeOptions = `<div>
                                        <div class="vi-wbe-add-attributes-option-label">
                                            Select action if exist attribute in product
                                        </div>
                                        <select class="vi-wbe-add-attributes-option">
                                            <option value="none">Don't add</option>
                                            <option value="replace">Replace existed attribute</option>
                                            <option value="merge_terms">Merge terms</option>
                                        </select>
                                    </div>`;

        modal.find('.content').append(html);
        modal.find('.actions').append(addAttributeOptions);
        modal.find('table select').select2({multiple: true});
        modal.find('tbody').sortable({
            items: 'tr',
            cursor: 'move',
            axis: 'y',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            helper: 'clone',
            handle: '.icon.move',
        });

        const setOptionDisable = () => {
            modal.find('select.vi-wbe-select-taxonomy option').removeAttr('disabled');
            modal.find('input[type=hidden]').each(function (i, el) {
                let tax = $(el).val();
                modal.find(`select.vi-wbe-select-taxonomy option[value='${tax}']`).attr('disabled', 'disabled');
            });
        };

        setOptionDisable();

        modal.on('click', function (e) {
            let $thisTarget = $(e.target);
            if ($thisTarget.hasClass('trash')) {
                $thisTarget.closest('tr').remove();
                setOptionDisable();
            }

            if ($thisTarget.hasClass('vi-wbe-add-taxonomy')) {
                let taxSelect = $('.vi-wbe-select-taxonomy'), tax = taxSelect.val(),
                    item = {name: tax, options: []};
                if (tax) item.is_taxonomy = 1;

                let row = $($this.createRowTable(item));
                modal.find('table tbody').append(row);
                row.find('select').select2({multiple: true});
                setOptionDisable();
                taxSelect.val('').trigger('change');
            }

            if ($thisTarget.hasClass('vi-wbe-select-all-attributes')) {
                let td = $thisTarget.closest('td');
                let select = td.find('select');
                select.find('option').attr('selected', true);
                select.trigger('change');
            }

            if ($thisTarget.hasClass('vi-wbe-select-no-attributes')) {
                let td = $thisTarget.closest('td');
                let select = td.find('select');
                select.find('option').attr('selected', false);
                select.trigger('change');
            }

            if ($thisTarget.hasClass('vi-wbe-add-new-attribute')) {
                let newAttr = prompt(_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Enter a name for the new attribute term:'));

                if (!newAttr) return;

                let tr = $thisTarget.closest('tr.vi-wbe-attribute-row'),
                    taxAttr = tr.attr('data-attr');

                if (taxAttr) {
                    taxAttr = JSON.parse(taxAttr);
                    _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
                        data: {
                            sub_action: 'add_new_attribute',
                            taxonomy: taxAttr.name,
                            term: newAttr
                        },
                        beforeSend() {
                            $thisTarget.addClass('loading')
                        },
                        success(res) {
                            $thisTarget.removeClass('loading');
                            if (res.success) {
                                let select = tr.find('select');
                                select.append(`<option value="${res.data.term_id}" selected>${res.data.name}</option>`);
                                select.trigger('change');
                                _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.attributes[taxAttr.name].terms[res.data.term_id] = {slug: res.data.slug, text: res.data.name}
                            } else {
                                alert(res.data.message)
                            }
                        }
                    });
                }
            }
        });
    }

    createRowTable(item) {
        let attrName = '', value = '';

        if (item.is_taxonomy) {
            let attribute = _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.attributes[item.name],
                terms = attribute.terms || [], options = '';

            attrName = `${attribute.data.attribute_label}<input type="hidden" value="${item.name}"/>`;

            if (Object.keys(terms).length) {
                for (let id in terms) {
                    let selected = item.options.includes(parseInt(id)) ? 'selected' : '';
                    options += `<option value="${id}" ${selected}>${terms[id].text}</option>`;
                }
            }

            value = `<select multiple>${options}</select>
                    <div class="vi-wbe-attributes-button-group">
                        <span class="vi-ui button mini vi-wbe-select-all-attributes">${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Select all')}</span>
                        <span class="vi-ui button mini vi-wbe-select-no-attributes">${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Select none')}</span>
                        <span class="vi-ui button mini vi-wbe-add-new-attribute">${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Add new')}</span>
                    </div>`;
        } else {
            attrName = `<input type="text" class="custom-attr-name" value="${item.name}" placeholder="${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Custom attribute name')}"/>`;
            value = `<textarea class="custom-attr-val" placeholder="${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Enter some text, or some attributes by "|" separating values.')}">${item.value || ''}</textarea>`;
        }

        attrName = `<div class="vi-wbe-attribute-name-label">${attrName}</div>`;

        attrName += `<div>
                        <input type="checkbox" class="attr-visibility" ${item.visible ? 'checked' : ''} value="1">
                        <label>${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Visible on the product page')}</label>
                    </div>`;

        attrName += `<div>
                        <input type="checkbox" class="attr-variation" ${item.variation ? 'checked' : ''} value="1">
                        <label>${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Used for variations (apply for variable)')}</label>
                    </div>`;

        return `<tr class="vi-wbe-attribute-row" data-attr='${JSON.stringify(item)}'>
                    <td class="vi-wbe-left">${attrName}</td>
                    <td>${value}</td>
                    <td class="vi-wbe-right"><i class="icon trash"> </i> <i class="icon move"> </i></td>
                </tr>`;
    }


}

/***/ }),

/***/ "./src/sidebar.js":
/*!************************!*\
  !*** ./src/sidebar.js ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "Sidebar": () => (/* binding */ Sidebar)
/* harmony export */ });
/* harmony import */ var _attributes__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./attributes */ "./src/attributes.js");
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./functions */ "./src/functions.js");



const $ = jQuery;

const Sidebar = {
    init() {
        $('.vi-ui.menu .item').vi_tab();
        this.revision = {};
        this.sidebar = $('#vi-wbe-sidebar');
        this.historyBodyTable = $('#vi-wbe-history-points-list tbody');

        this.sidebar.on('click', '.vi-wbe-apply-filter', this.applyFilter.bind(this));
        this.sidebar.on('click', '.vi-wbe-filter-label', this.filterInputLabelFocus);
        this.sidebar.on('focus', '.vi-wbe-filter-input', this.filterInputFocus);
        this.sidebar.on('blur', '.vi-wbe-filter-input', this.filterInputBlur);
        this.sidebar.on('click', '.vi-wbe-get-meta-fields', this.getMetaFields.bind(this));
        this.sidebar.on('click', '.vi-wbe-save-meta-fields:not(.loading)', this.saveMetaFields.bind(this));
        this.sidebar.on('click', '.vi-wbe-add-new-meta-field', this.addNewMetaField.bind(this));
        this.sidebar.find('table.vi-wbe-meta-fields-container tbody').sortable({axis: 'y',});
        this.sidebar.find('table.vi-wbe-meta-fields-container').on('click', '.vi-wbe-remove-meta-row', this.removeMetaRow);

        this.sidebar.on('click', '.vi-wbe-save-taxonomy-fields:not(.loading)', this.saveTaxonomyFields);

        this.sidebar.on('click', '.vi-wbe-save-settings', this.saveSettings.bind(this));

        this.sidebar.on('click', '.vi-wbe-view-history-point', this.viewHistoryPoint.bind(this));
        this.sidebar.on('click', '.vi-wbe-recover', this.recover.bind(this));
        this.sidebar.on('click', '.vi-wbe-revert-this-point', this.revertAllProducts.bind(this));
        this.sidebar.on('click', '.vi-wbe-revert-this-key', this.revertProductAttribute.bind(this));
        this.sidebar.on('click', '.vi-wbe-pagination a.item', this.changePage.bind(this));
        this.sidebar.on('change', '.vi-wbe-go-to-page', this.changePageByInput.bind(this));
        this.sidebar.on('click', '.vi-wbe-multi-select-clear', this.clearMultiSelect);

        this.sidebar.on('change', '.vi-wbe-meta-column-type', this.metaFieldChangeType);
        this.sidebar.on('keyup', '.vi-wbe-search-metakey', this.searchMetaKey);

        this.filter();
        this.settings();
        this.metafields();
        this.history();

        return this.sidebar;
    },

    filter() {
        let filterForm = $('#vi-wbe-products-filter'),
            filterInput = $('.vi-wbe-filter-input'),
            cssTop = {top: -2},
            cssMiddle = {top: '50%'};

        filterInput.each((i, el) => {
            if ($(el).val()) $(el).parent().prev().css(cssTop);
        });

        filterInput.on('focus', function () {
            let label = $(this).prev();
            label.css(cssTop);
            $(this).on('blur', function () {
                if (!$(this).val()) label.css(cssMiddle);
            })
        });

        this.sidebar.on('click', '.vi-wbe-filter-label', function () {
            $(this).next().trigger('focus');
        });

        let clearableFilter = filterForm.find('.vi-wbe.vi-ui.dropdown').dropdown({clearable: true}),
            compactFilter = filterForm.find('.vi-ui.compact.dropdown').dropdown();

        this.sidebar.on('click', '.vi-wbe-clear-filter', function () {
            $('.vi-wbe-filter-label').css(cssMiddle);
            filterInput.val('');
            clearableFilter.dropdown('clear');
            compactFilter.find('.menu .item:first').trigger('click');
        });

        this.sidebar.on('change', '#vi-wbe-has_expire_date', function () {
            let expireDateGroup = $('.vi-wbe-expire-date-group');
            $(this).val() === 'yes' ? expireDateGroup.show() : expireDateGroup.hide();
        });

        this.sidebar.find('#vi-wbe-has_expire_date').trigger('change')
    },

    settings() {
        let settingsForm = $('.vi-wbe-settings-tab');
        settingsForm.find('select.dropdown').dropdown();
    },

    metafields() {
        this.renderMetaFieldsTable(_attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.metaFields);
    },

    history() {
        this.pagination(1);
        // this.saveRevision();
    },

    pagination(currentPage, maxPage = _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.historyPages) {
        this.sidebar.find('.vi-wbe-pagination').html(_functions__WEBPACK_IMPORTED_MODULE_1__.default.pagination(maxPage, currentPage));
    },

    applyFilter(e) {
        let $this = this, thisBtn = $(e.target);

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {
                sub_action: 'add_filter_data',
                filter_data: $('#vi-wbe-products-filter').serialize(),
                filter_key: _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.filterKey
            },
            beforeSend() {
                thisBtn.addClass('loading');
            },
            success(res) {
                thisBtn.removeClass('loading');
                $this.sidebar.trigger('afterAddFilter', [res.data]);
            }
        });
    },

    limitProductPerPage() {
        let value = $(this).val();
        if (value > 50) $(this).val(50);
        if (value < 0) $(this).val(0);
    },

    saveSettings(e) {
        let $this = this, thisBtn = $(e.target);

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {
                sub_action: 'save_settings',
                fields: $('form.vi-wbe-settings-tab').serialize()
            },
            beforeSend() {
                thisBtn.addClass('loading')
            },
            success(res) {
                if (res.success) {
                    _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.settings = res.data.settings;
                    // clearInterval($this.autoSaveRevision);
                    // $this.saveRevision();
                    $this.sidebar.trigger('afterSaveSettings', [res.data]);
                }
                thisBtn.removeClass('loading')
            }
        });
    },

    filterInputLabelFocus() {
        $(this).next().find('input').trigger('focus');
    },

    filterInputFocus() {
        $(this).parent().prev().css({top: -2});
    },

    filterInputBlur() {
        if (!$(this).val()) $(this).parent().prev().css({top: '50%'});
    },

    getMetaFields(e) {
        let $this = this, thisBtn = $(e.target);

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {sub_action: 'get_meta_fields', current_meta_fields: $this.getCurrentMetaFields()},
            beforeSend() {
                thisBtn.addClass('loading');
            },
            success(res) {
                $this.renderMetaFieldsTable(res.data);
                _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.metaFields = res.data;
                thisBtn.removeClass('loading');
            }
        });
    },

    renderMetaFieldsTable(data) {
        let html = '';

        for (let metaKey in data) {
            html += this.renderRow(metaKey, data);
        }

        $('.vi-wbe-meta-fields-container tbody').html(html);
    },

    renderRow(metaKey, data) {
        let meta = data[metaKey] || {},
            optionHtml = '',
            inputType = meta.input_type || '',
            options = {
                textinput: 'Text input',
                texteditor: 'Text editor',
                numberinput: 'Number input',
                array: 'Array',
                json: 'JSON',
                checkbox: 'Checkbox',
                calendar: 'Calendar',
                image: 'Image',
                select: 'Select',
                multiselect: 'Multiselect',
            },
            metaValue = meta.meta_value || '',
            shortValue = metaValue.slice(0, 15),
            fullValueHtml = metaValue.length > 16 ? `<div class="vi-wbe-full-meta-value">${metaValue}</div>` : '',
            selectSource = '';

        for (let optionValue in options) {
            optionHtml += `<option value="${optionValue}" ${optionValue === inputType ? 'selected' : ''}>${options[optionValue]}</option>`;
        }

        shortValue += shortValue.length < metaValue.length ? '...' : '';

        if (inputType === 'select' || inputType === 'multiselect') {
            selectSource += `<textarea class="vi-wbe-select-options">${meta.select_options}</textarea>`
        }

        return `<tr>
                    <td class="vi-wbe-meta-key">${metaKey}</td>
                    <td><input type="text" class="vi-wbe-meta-column-name" value="${meta.column_name || ''}"></td>
                    <td>
                        <div class="vi-wbe-display-meta-value">
                            <div class="vi-wbe-short-meta-value">${shortValue}</div>
                            ${fullValueHtml}
                        </div>
                    </td>
                    <td>
                        <select class="vi-wbe-meta-column-type">${optionHtml}</select>
                        ${selectSource}
                    </td>
                    <td class="vi-wbe-meta-field-active-column">
                        <div class="vi-ui toggle checkbox">
                          <input type="checkbox" class="vi-wbe-meta-column-active" ${parseInt(meta.active) ? 'checked' : ''}>
                          <label> </label>
                        </div>  
                    </td>
                    <td>
                        <div class="vi-wbe-meta-field-actions">
                            <span class="vi-ui button basic mini vi-wbe-remove-meta-row"><i class="icon trash"> </i></span>
                            <span class="vi-ui button basic mini"><i class="icon move"> </i></span>
                        </div>
                    </td>
                </tr>`;
    },

    metaFieldChangeType() {
        let selectTypeOptions = $('<textarea class="vi-wbe-select-options"></textarea>');
        let val = $(this).val();
        let siblings = $(this).siblings();
        if (val === 'select' || val === 'multiselect') {
            if (!siblings.length) $(this).after(selectTypeOptions);
        } else {
            siblings.remove();
        }
    },

    searchMetaKey() {
        let filter = $(this).val().toLowerCase();
        $('.vi-wbe-meta-fields-container tbody tr').each(function (i, tr) {
            let metaKey = $(tr).find('.vi-wbe-meta-key').text().trim().toLowerCase();
            if (metaKey.indexOf(filter) > -1) {
                $(tr).show();
            } else {
                $(tr).hide();
            }
        });
    },

    saveMetaFields(e) {
        let thisBtn = $(e.target);

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {sub_action: 'save_meta_fields', meta_fields: this.getCurrentMetaFields()},
            beforeSend() {
                thisBtn.addClass('loading');
            },
            success(res) {
                thisBtn.removeClass('loading');
                location.reload();
            },
            error(res) {
                console.log(res)
            }
        });
    },

    getCurrentMetaFields() {
        let meta_fields = {};
        let metaArr = _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.metaFields;
        $('table.vi-wbe-meta-fields-container tbody tr').each(function (i, row) {
            let metaKey = $(row).find('.vi-wbe-meta-key').text();
            meta_fields[metaKey] = {
                column_name: $(row).find('.vi-wbe-meta-column-name').val(),
                input_type: $(row).find('.vi-wbe-meta-column-type').val(),
                active: $(row).find('.vi-wbe-meta-column-active:checked').length,
                meta_value: metaArr[metaKey] ? metaArr[metaKey].meta_value : '',
                select_options: $(row).find('.vi-wbe-select-options').val(),
            };
        });

        return meta_fields;
    },

    addNewMetaField(e) {
        let input = $(e.currentTarget).prev(),
            metaKey = input.val(),
            validate = metaKey.match(/^[\w\d_-]*$/g);

        if (!metaKey || !validate || _attributes__WEBPACK_IMPORTED_MODULE_0__.Attributes.metaFields[metaKey]) return;

        let newRow = this.renderRow(metaKey, {});
        if (newRow) {
            input.val('');
            $('table.vi-wbe-meta-fields-container tbody').append(newRow);
        }
    },

    removeMetaRow() {
        $(this).closest('tr').remove();
    },

    saveTaxonomyFields(e) {
        let thisBtn = $(e.target);
        let taxonomyFields = [];

        $('table.vi-wbe-taxonomy-fields .vi-wbe-taxonomy-active:checked').each(function (i, row) {
            let taxKey = $(this).closest('tr').find('.vi-wbe-taxonomy-key').text();
            taxonomyFields.push(taxKey);
        });

        if (taxonomyFields.length) {
            _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
                data: {sub_action: 'save_taxonomy_fields', taxonomy_fields: taxonomyFields},
                beforeSend() {
                    thisBtn.addClass('loading');
                },
                success(res) {
                    thisBtn.removeClass('loading');
                    location.reload();
                },
                error(res) {
                    console.log(res)
                }
            });
        }

    },

    viewHistoryPoint(e) {
        let thisBtn = $(e.currentTarget),
            historyiD = thisBtn.data('id'),
            $this = this;

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {sub_action: 'view_history_point', id: historyiD},
            beforeSend() {
                thisBtn.addClass('loading');
            },
            complete() {
            },
            success(res) {
                thisBtn.removeClass('loading');

                if (res.success && res.data) {
                    let products = res.data.compare;
                    let html = '';
                    for (let id in products) {
                        let item = products[id];
                        html += `<div class="vi-wbe-history-product" data-product_id="${id}">
                                        <div class="title">
                                            <i class="dropdown icon"></i>
                                            ${item.name}
                                            <span class="vi-ui button mini basic vi-wbe-revert-this-product">
                                                <i class="icon undo"> </i>
                                            </span>
                                            
                                        </div>`;

                        let table = '';
                        for (let key in item.fields) {
                            let currentVal = typeof item.current[key] === 'string' ? item.current[key] : JSON.stringify(item.current[key]);
                            let historyVal = typeof item.history[key] === 'string' ? item.history[key] : JSON.stringify(item.history[key]);
                            table += `<tr>
                                            <td>${item.fields[key]}</td>
                                            <td>${currentVal}</td>
                                            <td>${historyVal}</td>
                                            <td class="">
                                                <span class="vi-ui button basic mini vi-wbe-revert-this-key" data-product_id="${id}" data-product_key="${key}">
                                                    <i class="icon undo"> </i>
                                                </span>
                                            </td>
                                        </tr>`;
                        }

                        table = `<table id="vi-wbe-history-point-detail" class="vi-ui celled table">
                                    <thead>
                                    <tr>
                                        <th>Attribute</th>
                                        <th>Current</th>
                                        <th>History</th>
                                        <th class="">Revert</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    ${table}
                                    </tbody>
                                </table>`;

                        html += `<div class="content">${table}</div></div>`
                    }

                    html = $(`<div class="vi-ui styled fluid accordion">${html}</div>`);

                    $('.vi-wbe-history-review')
                        .html(html).attr('data-history_id', historyiD)
                        .prepend(`<h4>History point: ${res.data.date}</h4>`)
                        .append(`<div class="vi-ui button tiny vi-wbe-revert-this-point">
                                    ${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('Revert all product in this point')}
                                </div>
                                <p> ${_functions__WEBPACK_IMPORTED_MODULE_1__.default.text('The current value is the value of the records in database')}</p>`);

                    html.find('.title').on('click', (e) => $this.revertSingleProduct(e));

                    html.vi_accordion();
                    html.find('.title:first').trigger('click');
                }
            }
        })
    },

    recover(e) {
        let thisBtn = $(e.currentTarget),
            historyID = thisBtn.data('id');

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {sub_action: 'revert_history_all_products', history_id: historyID},
            beforeSend() {
                thisBtn.addClass('loading')
            },
            complete() {
                thisBtn.removeClass('loading')
            },
            success(res) {
                console.log(res)
            }
        });
    },

    revertSingleProduct(e) {
        let thisBtn;
        if ($(e.target).hasClass('vi-wbe-revert-this-product')) thisBtn = $(e.target);
        if ($(e.target).parent().hasClass('vi-wbe-revert-this-product')) thisBtn = $(e.target).parent();

        if (thisBtn) {
            e.stopImmediatePropagation();

            let pid = thisBtn.closest('.vi-wbe-history-product').data('product_id'),
                historyID = thisBtn.closest('.vi-wbe-history-review').data('history_id');

            if (thisBtn.hasClass('loading')) return;

            _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
                data: {sub_action: 'revert_history_single_product', history_id: historyID, pid: pid},
                beforeSend() {
                    thisBtn.addClass('loading')
                },
                complete() {
                    thisBtn.removeClass('loading')
                },
                success(res) {
                    console.log(res)
                }
            });
        }
    },

    revertAllProducts(e) {
        let thisBtn = $(e.target);
        let historyID = thisBtn.closest('.vi-wbe-history-review').data('history_id');

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {sub_action: 'revert_history_all_products', history_id: historyID},
            beforeSend() {
                thisBtn.addClass('loading')
            },
            complete() {
                thisBtn.removeClass('loading')
            },
            success(res) {
                console.log(res)
            }
        });
    },

    revertProductAttribute(e) {
        let thisBtn = $(e.currentTarget),
            attribute = thisBtn.data('product_key'),
            pid = thisBtn.closest('.vi-wbe-history-product').data('product_id'),
            historyID = thisBtn.closest('.vi-wbe-history-review').data('history_id');

        if (thisBtn.hasClass('loading')) return;

        _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
            data: {sub_action: 'revert_history_product_attribute', attribute: attribute, history_id: historyID, pid: pid},
            beforeSend() {
                thisBtn.addClass('loading')
            },
            complete() {
                thisBtn.removeClass('loading')
            },
            success(res) {
                console.log(res)
            }
        });
    },

    changePage(e) {
        let page = parseInt($(e.currentTarget).attr('data-page'));
        if ($(e.currentTarget).hasClass('active') || $(e.currentTarget).hasClass('disabled') || !page) return;
        this.loadHistoryPage(page);
    },

    changePageByInput(e) {
        let page = parseInt($(e.target).val());
        let max = parseInt($(e.target).attr('max'));

        if (page <= max && page > 0) this.loadHistoryPage(page);
    },

    clearMultiSelect() {
        $(this).parent().find('.vi-ui.dropdown').dropdown('clear');
    },

    loadHistoryPage(page) {
        let loading = _functions__WEBPACK_IMPORTED_MODULE_1__.default.spinner(),
            $this = this;

        if (page) {
            _functions__WEBPACK_IMPORTED_MODULE_1__.default.ajax({
                dataType: 'text',
                data: {sub_action: 'load_history_page', page: page},
                beforeSend() {
                    $this.sidebar.find('.vi-wbe-pagination').prepend(loading);
                },
                complete() {
                    loading.remove();
                },
                success(res) {
                    $this.pagination(page);
                    $('#vi-wbe-history-points-list tbody').html(res);
                }
            });
        }
    },

    // saveRevision() {
    //     let autoSaveTime = parseInt(Attributes.settings.auto_save_revision);
    //     if (autoSaveTime === 0) return;
    //     let $this = this;
    //
    //     this.autoSaveRevision = setInterval(function () {
    //         if (Object.keys($this.revision).length) {
    //             let currentPage = $this.sidebar.find('.vi-wbe-pagination a.item.active').data('page') || 1;
    //             _f.ajax({
    //                 data: {sub_action: 'auto_save_revision', data: $this.revision, page: currentPage || 1},
    //                 success(res) {
    //                     if (res.success) {
    //                         if (res.data.pages) Attributes.historyPages = res.data.pages;
    //                         if (res.data.updatePage) $this.historyBodyTable.html(res.data.updatePage);
    //                         $this.revision = {};
    //                         $this.pagination(currentPage);
    //                     }
    //                 }
    //             });
    //         }
    //
    //     }, autoSaveTime * 1000)
    // }
};


/***/ }),

/***/ "./src/templates.js":
/*!**************************!*\
  !*** ./src/templates.js ***!
  \**************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
const Templates = {
    modal(data = {}) {
        let {header = '', content = '', actionsHtml = ''} = data;
        return `<div class="vi-wbe-modal-container">
                    <div class="vi-wbe-modal-main vi-ui form small">
                        <i class="close icon"></i>
                        <div class="vi-wbe-modal-wrapper">
                            <h3 class="header">${header}</h3>
                            <div class="content">${content}</div>
                            <div class="actions">${actionsHtml}</div>
                        </div>
                    </div>
                </div>`;
    },

    defaultAttributes(data = {}) {
        let {html} = data;
        return `<table class="vi-ui celled table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Attribute</th>
                    </tr>
                    </thead>
                    <tbody>
                    ${html}
                    </tbody>
                </table>`;
    },

};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Templates);

/***/ }),

/***/ "./src/text-multi-cells-edit.js":
/*!**************************************!*\
  !*** ./src/text-multi-cells-edit.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ TextMultiCellsEdit)
/* harmony export */ });
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./functions */ "./src/functions.js");
/* harmony import */ var _modal_popup__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modal-popup */ "./src/modal-popup.js");



const $ = jQuery;

class TextMultiCellsEdit {
    constructor(obj, x, y, e, wordWrap) {
        this._data = {};
        this._data.jexcel = obj;
        this._data.x = parseInt(x);
        this._data.y = parseInt(y);
        this._wordWrap = wordWrap;
        this.run();
    }

    get(id) {
        return this._data[id] || '';
    }

    run() {
        let formulaHtml = this.content();
        let cell = $(`td[data-x=${this.get('x') || 0}][data-y=${this.get('y') || 0}]`);
        new _modal_popup__WEBPACK_IMPORTED_MODULE_1__.Popup(formulaHtml, cell);
        formulaHtml.on('click', '.vi-wbe-apply-formula', this.applyFormula.bind(this));
        // formulaHtml.on('change', '.vi-wbe-text-input', this.applyFormula.bind(this));
    }

    content() {
        let input = this._wordWrap ? `<textarea class="vi-wbe-text-input" rows="3"></textarea>` : `<input type="text" placeholder="${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Content')}" class="vi-wbe-text-input">`;
        return $(`<div class="vi-wbe-formula-container">
                    <div class="field">
                        ${input}
                    </div>
                    <button type="button" class="vi-ui button mini vi-wbe-apply-formula">${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Save')}</button>
                </div>`);
    }

    applyFormula(e) {
        let form = $(e.target).closest('.vi-wbe-formula-container'),
            value = form.find('.vi-wbe-text-input').val(),
            excelObj = this.get('jexcel');

        let breakControl = false, records = [];
        let h = excelObj.selectedContainer;
        let start = h[1], end = h[3], x = h[0];

        for (let y = start; y <= end; y++) {
            if (excelObj.records[y][x] && !excelObj.records[y][x].classList.contains('readonly') && excelObj.records[y][x].style.display !== 'none' && breakControl === false) {
                records.push(excelObj.updateCell(x, y, value));
                excelObj.updateFormulaChain(x, y, records);
            }
        }

        // Update history
        excelObj.setHistory({
            action: 'setValue',
            records: records,
            selection: excelObj.selectedCell,
        });

        // Update table with custom configuration if applicable
        excelObj.updateTable();
    }

}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!***********************!*\
  !*** ./src/editor.js ***!
  \***********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _functions__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./functions */ "./src/functions.js");
/* harmony import */ var _attributes__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./attributes */ "./src/attributes.js");
/* harmony import */ var _calculator__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./calculator */ "./src/calculator.js");
/* harmony import */ var _sidebar__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./sidebar */ "./src/sidebar.js");
/* harmony import */ var _find_and_replace__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./find-and-replace */ "./src/find-and-replace.js");
/* harmony import */ var _text_multi_cells_edit__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./text-multi-cells-edit */ "./src/text-multi-cells-edit.js");
/* harmony import */ var _modal_popup__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./modal-popup */ "./src/modal-popup.js");
/* harmony import */ var _find_and_replace_tags__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./find-and-replace-tags */ "./src/find-and-replace-tags.js");
/* harmony import */ var _find_and_replace_options__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./find-and-replace-options */ "./src/find-and-replace-options.js");
/* harmony import */ var _add_image_to_multi_gallery__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./add-image-to-multi-gallery */ "./src/add-image-to-multi-gallery.js");
/* harmony import */ var _multiple_product_attributes__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./multiple-product-attributes */ "./src/multiple-product-attributes.js");












jQuery(document).ready(function ($) {

    class BulkEdit {
        constructor() {
            this.sidebar = _sidebar__WEBPACK_IMPORTED_MODULE_3__.Sidebar.init();
            this.compare = [];
            this.trash = [];
            this.unTrash = [];
            this.revision = {};
            this.isAdding = false;

            this.editor = $('#vi-wbe-container');
            this.menubar = $('#vi-wbe-menu-bar');

            this.menubar.on('click', '.vi-wbe-open-sidebar', this.openMenu.bind(this));
            this.menubar.on('click', 'a.item:not(.vi-wbe-open-sidebar)', this.closeMenu.bind(this));

            this.menubar.on('click', '.vi-wbe-new-products', this.addNewProduct.bind(this));
            this.menubar.on('click', '.vi-wbe-new-coupons', this.addNewCoupon.bind(this));
            this.menubar.on('click', '.vi-wbe-new-orders', this.addNewOrder.bind(this));

            this.menubar.on('click', '.vi-wbe-full-screen-btn', this.toggleFullScreen.bind(this));
            this.menubar.on('click', '.vi-wbe-save-button', this.save.bind(this));
            this.menubar.on('click', '.vi-wbe-pagination a.item', this.changePage.bind(this));
            this.menubar.on('click', '.vi-wbe-get-product', this.reloadCurrentPage.bind(this));
            this.menubar.on('change', '.vi-wbe-go-to-page', this.changePageByInput.bind(this));

            this.editor.on('cellonchange', 'tr', this.cellOnChange.bind(this));
            this.editor.on('click', '.jexcel_content', this.removeExistingEditor.bind(this));
            this.editor.on('dblclick', this.removeContextPopup);

            this.sidebar.on('afterAddFilter', this.afterAddFilter.bind(this));
            this.sidebar.on('afterSaveSettings', this.afterSaveSettings.bind(this));
            this.sidebar.on('click', '.vi-wbe-close-sidebar', this.closeMenu.bind(this));

            this.init();

            $(document).on('keydown', this.keyDownControl.bind(this));
            $(document).on('keyup', this.keyUpControl.bind(this));
        }

        removeExistingEditor(e) {
            if (e.target === e.currentTarget) {
                if (this.WorkBook && this.WorkBook.edition) {
                    this.WorkBook.closeEditor(this.WorkBook.edition[0], true);
                }
            }
        }

        keyDownControl(e) {
            if ((e.ctrlKey || e.metaKey) && !e.shiftKey) {
                if (e.which === 83) {
                    e.preventDefault();
                    this.save();
                }
            }

            switch (e.which) {
                case 27:
                    this.sidebar.removeClass('vi-wbe-open');
                    break;
            }
        }

        keyUpControl(e) {
            if (e.target && !e.target.getAttribute('readonly')) {
                let decimal = e.target.getAttribute('data-currency');
                if (decimal) {
                    let currentValue = e.target.value;
                    if (currentValue) {
                        let decimalExist = currentValue.indexOf(decimal);

                        if (decimalExist < 1) {
                            let value = currentValue.match(/\d/g);
                            e.target.value = value ? value.join('') : '';
                        } else {
                            let split = currentValue.split(decimal);
                            let integer, fraction = '';
                            integer = split[0].match(/[\d]/g).join('');

                            if (split[1]) {
                                fraction = split[1].match(/[\d]/g);
                                fraction = fraction ? fraction.join('') : '';
                            }

                            e.target.value = fraction ? `${integer}${decimal}${fraction}` : `${integer}${decimal}`;
                        }
                    }
                }
            }
        }

        removeContextPopup() {
            $('.vi-wbe-context-popup').removeClass('vi-wbe-popup-active')
        }

        init() {
            if (wbeParams.columns) _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.setColumns(wbeParams.columns);
            this.pagination(1, 1);
            this.workBookInit();
            this.loadProducts();
            _functions__WEBPACK_IMPORTED_MODULE_0__.default.setJexcel(this.WorkBook);
        }

        cellOnChange(e, data) {
            let {col = ''} = data;

            if (!col) return;

            let type = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.idMapping[col];
            let thisRow = $(e.target);

            switch (type) {
                case 'product_type':
                    thisRow.find('td').each(function (i, el) {
                        let x = $(el).data('x');
                        if (x && x !== 0 && x !== 1) {
                            $(el).removeClass('readonly');
                        }
                    });

                    let dependArr = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.cellDependType[data.value];
                    if (Array.isArray(dependArr)) {
                        dependArr.forEach(function (el) {
                            let pos = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.idMappingFlip[el];
                            thisRow.find(`td[data-x='${pos}']`).addClass('readonly');
                        });
                    }

                    break;

                case 'post_date':
                    let value = data.value,
                        x = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getColFromColumnType('status'),
                        cell = thisRow.find(`td[data-x='${x}']`).get(0),
                        time = (new Date(value)).getTime(),
                        now = Date.now(),
                        status = time > now ? 'future' : 'publish';

                    this.WorkBook.setValue(cell, status);

                    break;
            }
        }

        workBookInit() {
            let $this = this,
                countCol = 0,
                deleteSelectedRows = _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Delete rows with selected cells'),
                oncreaterow = null,
                contextMenuItems,
                onselection = null;

            function setValueToCell(obj, value) {
                let breakControl = false, records = [], h = obj.selectedContainer, start = h[1], end = h[3], x = h[0];

                for (let y = start; y <= end; y++) {
                    if (obj.records[y][x] && !obj.records[y][x].classList.contains('readonly') && obj.records[y][x].style.display !== 'none' && breakControl === false) {
                        records.push(obj.updateCell(x, y, value));
                        obj.updateFormulaChain(x, y, records);
                    }
                }

                obj.setHistory({action: 'setValue', records: records, selection: obj.selectedCell});
                obj.updateTable();
            }

            switch (_attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.editType) {
                case 'products':
                    deleteSelectedRows = `${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Delete rows with selected cells')} 
                                            <span class="vi-wbe-context-menu-note">
                                                (${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Variations cannot revert after save')})
                                            </span>`;

                    oncreaterow = function (row, j) {
                        let productType = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductTypeFromY(j);
                        let dependArr = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.cellDependType[productType];

                        if (Array.isArray(dependArr)) {
                            dependArr.forEach(function (el) {
                                let pos = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.idMappingFlip[el];
                                $(row).find(`td[data-x='${pos}']`).addClass('readonly');
                            });
                        }
                    };

                    onselection = function (el, x1, y1, x2, y2, origin) {
                        if (x1 === x2 && y1 === y2) {
                            let cell = this.getCellFromCoords(x1, y1),
                                child = $(cell).children();

                            if (child.length && child.hasClass('vi-wbe-gallery-has-item')) {
                                let ids = this.options.data[y1][x1],
                                    images = '';

                                if (ids.length) {
                                    for (let id of ids) {
                                        let src = _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage[id];
                                        images += `<li class="vi-wbe-gallery-image"><img src="${src}"></li>`;
                                    }
                                }

                                new _modal_popup__WEBPACK_IMPORTED_MODULE_6__.Popup(`<ul class="vi-wbe-gallery-images">${images}</ul>`, $(cell));
                            }
                        }
                    };

                    contextMenuItems = function (items, obj, x, y, e) {
                        $this.removeContextPopup();

                        let cells = obj.selectedContainer;
                        x = parseInt(x);
                        y = parseInt(y);

                        if (cells[0] === cells[2] && x !== null) {
                            switch (obj.options.columns[x].type) {
                                case 'checkbox':
                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Check'),
                                        onclick(e) {
                                            setValueToCell(obj, true);
                                        }
                                    });

                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Uncheck'),
                                        onclick(e) {
                                            setValueToCell(obj, false);
                                        }
                                    });
                                    break;

                                case 'number':
                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Calculator'),
                                        onclick(e) {
                                            new _calculator__WEBPACK_IMPORTED_MODULE_2__.Calculator(obj, x, y, e);
                                        }
                                    });

                                    if (x > 1 && obj.options.columns[x].id === 'sale_price' && obj.options.columns[x - 1].id === 'regular_price') {
                                        items.push({
                                            title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Calculator base on Regular price'),
                                            onclick(e) {
                                                new _calculator__WEBPACK_IMPORTED_MODULE_2__.CalculatorBaseOnRegularPrice(obj, x, y, e);
                                            }
                                        });
                                    }

                                    break;

                                case 'text':
                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Edit multiple cells'),
                                        onclick(e) {
                                            new _text_multi_cells_edit__WEBPACK_IMPORTED_MODULE_5__.default(obj, x, y, e, obj.options.columns[x].wordWrap);
                                        }
                                    });

                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Find and Replace'),
                                        onclick(e) {
                                            new _find_and_replace__WEBPACK_IMPORTED_MODULE_4__.default(obj, x, y, e);
                                        }
                                    });
                                    break;

                                case 'calendar':
                                    let cell = $(`td[data-x=${x}][data-y=${y}]`).get(0);
                                    if (!$(cell).hasClass('readonly')) {
                                        items.push({
                                            title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Open date picker'),
                                            onclick() {
                                                let value = obj.options.data[y][x];

                                                var editor = _functions__WEBPACK_IMPORTED_MODULE_0__.default.createEditor(cell, 'input', '', false);
                                                editor.value = value;
                                                editor.style.left = 'unset';

                                                let h = obj.selectedContainer;
                                                let start = h[1], end = h[3];

                                                if (obj.options.tableOverflow == true || obj.options.fullscreen == true) {
                                                    obj.options.columns[x].options.position = true;
                                                }
                                                obj.options.columns[x].options.value = obj.options.data[y][x];
                                                obj.options.columns[x].options.opened = true;
                                                obj.options.columns[x].options.onclose = function (el, value) {
                                                    let records = [];
                                                    value = el.value;

                                                    for (let y = start; y <= end; y++) {
                                                        if (obj.records[y][x] && !obj.records[y][x].classList.contains('readonly') && obj.records[y][x].style.display !== 'none') {
                                                            records.push(obj.updateCell(x, y, value));
                                                            obj.updateFormulaChain(x, y, records);
                                                        }
                                                    }
                                                    // obj.closeEditor(cell, true);

                                                    // Update history
                                                    obj.setHistory({
                                                        action: 'setValue',
                                                        records: records,
                                                        selection: obj.selectedCell,
                                                    });

                                                    // Update table with custom configuration if applicable
                                                    obj.updateTable();
                                                };
                                                // Current value
                                                jSuites.calendar(editor, obj.options.columns[x].options);
                                                // Focus on editor
                                                editor.focus();
                                            }
                                        });
                                    }

                                    break;

                                case 'custom':

                                    switch (obj.options.columns[x].editor.type) {
                                        case 'textEditor':
                                            items.push({
                                                title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Edit multiple cells'),
                                                onclick() {
                                                    $('.vi-ui.modal').modal('show');
                                                    $('.vi-ui.modal .close.icon').off('click');

                                                    if (tinymce.get('vi-wbe-text-editor') === null) {
                                                        $('#vi-wbe-text-editor').val('');
                                                        wp.editor.initialize('vi-wbe-text-editor', _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.tinyMceOptions);
                                                    } else {
                                                        tinymce.get('vi-wbe-text-editor').setContent('')
                                                    }

                                                    $('.vi-wbe-text-editor-save').off('click').on('click', function () {
                                                        let content = wp.editor.getContent('vi-wbe-text-editor');
                                                        setValueToCell(obj, content);
                                                        if ($(this).hasClass('vi-wbe-close')) $('.vi-ui.modal').modal('hide');
                                                    });
                                                }
                                            });
                                            break;

                                        case 'tags':
                                            items.push({
                                                title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Find and replace tags'),
                                                onclick(e) {
                                                    new _find_and_replace_tags__WEBPACK_IMPORTED_MODULE_7__.default(obj, cells, x, y, e);
                                                }
                                            });
                                            break;

                                        case 'select2':
                                            items.push({
                                                title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Find and replace options'),
                                                onclick(e) {
                                                    new _find_and_replace_options__WEBPACK_IMPORTED_MODULE_8__.default(obj, cells, x, y, e);
                                                }
                                            });
                                            break;

                                        case 'gallery':
                                            items.push({
                                                title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add image to selected cells'),
                                                onclick(e) {
                                                    new _add_image_to_multi_gallery__WEBPACK_IMPORTED_MODULE_9__.default(obj, cells, x, y, e);
                                                }
                                            });

                                            break;

                                        case 'product_attributes':
                                            items.push({
                                                title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add attributes to products'),
                                                onclick(e) {
                                                    new _multiple_product_attributes__WEBPACK_IMPORTED_MODULE_10__.default(obj, cells, x, y, e);
                                                }
                                            });
                                            break;
                                    }

                                    break;

                            }
                        }

                        if (items.length) items.push({type: 'line'});

                        if (cells[1] === cells[3] && y !== null) {
                            let productType = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductTypeFromY(y);
                            if (productType === 'variable') {
                                items.push({
                                    title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add variation'),
                                    onclick() {
                                        if (_functions__WEBPACK_IMPORTED_MODULE_0__.default.is_loading()) return;

                                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                                                data: {
                                                    sub_action: 'add_variation',
                                                    pid: _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductIdOfCell(obj, y)
                                                },
                                                beforeSend() {
                                                    _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                                                },
                                                success(res) {
                                                    if (res.success) {
                                                        obj.insertRow(0, y, false, true);
                                                        obj.setRowData(y + 1, res.data, true);
                                                    }
                                                    _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                                                }
                                            }
                                        );
                                    }
                                });

                                items.push({
                                    title: `${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Create variations from all attributes')} <span class="vi-wbe-context-menu-note">(${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Save new attributes before')})</span>`,
                                    onclick() {
                                        if (_functions__WEBPACK_IMPORTED_MODULE_0__.default.is_loading()) return;

                                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                                            data: {
                                                sub_action: 'link_all_variations',
                                                pid: _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductIdOfCell(obj, y)
                                            },
                                            beforeSend() {
                                                _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                                            },
                                            success(res) {
                                                if (!res.success) return;
                                                if (res.data.length) {
                                                    res.data.forEach(function (item, i) {
                                                        obj.insertRow(0, y + i, false, true);
                                                        obj.setRowData(y + i + 1, item, true);
                                                    })
                                                }

                                                _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                                                _functions__WEBPACK_IMPORTED_MODULE_0__.default.notice(`${res.data.length} ${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('variations are added')}`)
                                            }
                                        });
                                    }
                                });

                                items.push({type: 'line'});
                            }

                            if (productType !== 'variation') {
                                let pid = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductIdOfCell(obj, y);

                                items.push({
                                    title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Duplicate'),
                                    onclick() {
                                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                                            data: {sub_action: 'duplicate_product', product_id: pid},
                                            beforeSend() {
                                                _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                                            },
                                            success(res) {
                                                if (res.data.length) {
                                                    res.data.forEach(function (item, i) {
                                                        obj.insertRow(0, y + i, true, true);
                                                        obj.setRowData(y + i, item, true);
                                                    })
                                                }
                                                _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                                            }
                                        });
                                    }
                                });

                                items.push({
                                    title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Go to edit product page'),
                                    onclick() {
                                        window.open(`${_attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.adminUrl}post.php?post=${pid}&action=edit`, '_blank');
                                    }
                                });

                                items.push({
                                    title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('View on Single product page'),
                                    onclick() {
                                        window.open(`${_attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.frontendUrl}?p=${pid}&post_type=product&preview=true`, '_blank');
                                    }
                                });

                            }

                        }

                        return items;
                    };

                    break;

                case 'orders':
                    contextMenuItems = function (items, obj, x, y, e) {
                        let cells = obj.selectedContainer;
                        x = parseInt(x);
                        y = parseInt(y);

                        if (x !== null && y !== null) {

                            for (let action in _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.orderActions) {
                                items.push({
                                    title: _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.orderActions[action],
                                    onclick() {
                                        let order_ids = [];

                                        for (let i = cells[1]; i <= cells[3]; i++) {
                                            order_ids.push(_functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductIdOfCell(obj, i))
                                        }

                                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                                            data: {sub_action: action, order_ids},
                                            beforeSend() {
                                                _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                                            },
                                            success(res) {
                                                _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                                            }
                                        });
                                    }
                                });
                            }

                            if (items.length) items.push({type: 'line'});

                            const addNote = function (is_customer_note = 0) {
                                let cell = obj.getCellFromCoords(cells[0], cells[1]),
                                    control = $(`<div>
                                                    <div class="field"> 
                                                        <textarea rows="3"></textarea>
                                                    </div>
                                                    <div class="field"> 
                                                        <span class="vi-wbe-add-note vi-ui button tiny">
                                                            ${_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add')}
                                                        </span>
                                                    </div>
                                                </div>`);

                                let popup = new _modal_popup__WEBPACK_IMPORTED_MODULE_6__.Popup(control, $(cell));

                                control.on('click', '.vi-wbe-add-note', function () {
                                    let note = control.find('textarea').val();

                                    if (!note) return;

                                    let h = obj.selectedContainer;
                                    let start = h[1], end = h[3], x = h[0];
                                    let ids = [];

                                    for (let y = start; y <= end; y++) {
                                        ids.push(obj.options.data[y][0])
                                    }

                                    popup.hide();

                                    _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                                        data: {sub_action: 'add_order_note', ids, note, is_customer_note},
                                        beforeSend() {
                                            _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                                        },
                                        success(res) {
                                            _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                                        }
                                    })
                                });
                            };

                            items.push({
                                title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add private note'),
                                onclick() {
                                    addNote(0);
                                }
                            });

                            items.push({
                                title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Add note to customer'),
                                onclick() {
                                    addNote(1);
                                }
                            });

                            if (items.length) items.push({type: 'line'});

                            if (cells[1] === cells[3]) {
                                let order_id = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getProductIdOfCell(obj, y);

                                items.push({
                                    title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Go to edit order page'),
                                    onclick() {
                                        window.open(`${_attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.adminUrl}post.php?post=${order_id}&action=edit`, '_blank');
                                    }
                                });
                                if (items.length) items.push({type: 'line'});
                            }

                        }
                        return items;
                    };
                    break;

                case 'coupons':
                    contextMenuItems = function (items, obj, x, y, e) {
                        let cells = obj.selectedContainer;
                        x = parseInt(x);
                        y = parseInt(y);

                        if (x !== null && y !== null) {

                            if (cells[0] === cells[2]) {
                                let colType = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getColumnType(x);
                                if (colType === 'code') {
                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Generate coupon code'),
                                        onclick() {
                                            let breakControl = false, records = [],
                                                h = obj.selectedContainer, start = h[1], end = h[3], x = h[0];

                                            for (let y = start; y <= end; y++) {
                                                if (obj.records[y][x] && !obj.records[y][x].classList.contains('readonly') && obj.records[y][x].style.display !== 'none' && breakControl === false) {
                                                    let value = _functions__WEBPACK_IMPORTED_MODULE_0__.default.generateCouponCode();
                                                    records.push(obj.updateCell(x, y, value));
                                                    obj.updateFormulaChain(x, y, records);
                                                }
                                            }
                                            obj.setHistory({action: 'setValue', records: records, selection: obj.selectedCell});
                                            obj.updateTable();
                                        }
                                    });
                                }

                                if (obj.options.columns[x].type === 'text') {
                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Edit multiple cells'),
                                        onclick(e) {
                                            new _text_multi_cells_edit__WEBPACK_IMPORTED_MODULE_5__.default(obj, x, y, e, obj.options.columns[x].wordWrap);
                                        }
                                    });

                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Find and Replace'),
                                        onclick(e) {
                                            new _find_and_replace__WEBPACK_IMPORTED_MODULE_4__.default(obj, x, y, e);
                                        }
                                    });
                                }

                                if (obj.options.columns[x].type === 'checkbox') {

                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Check'),
                                        onclick(e) {
                                            setValueToCell(obj, true);
                                        }
                                    });

                                    items.push({
                                        title: _functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Uncheck'),
                                        onclick(e) {
                                            setValueToCell(obj, false);
                                        }
                                    });
                                }

                                if (items.length) items.push({type: 'line'});
                            }
                        }
                        return items;
                    };

                    break;
            }

            this.WorkBook = $('#vi-wbe-spreadsheet').jexcel({
                allowInsertRow: false,
                allowInsertColumn: false,
                about: false,
                freezeColumns: 3,
                tableOverflow: true,
                tableWidth: '100%',
                tableHeight: '100%',
                columns: _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.columns,
                stripHTML: false,
                allowExport: false,
                allowDeleteColumn: false,
                allowRenameColumn: false,
                autoIncrement: false,
                allowXCopy: false,
                text: {deleteSelectedRows},
                oncreaterow,
                contextMenuItems,
                onselection,

                onchange(instance, cell, col, row, value, oldValue) {
                    if (JSON.stringify(value) !== JSON.stringify(oldValue)) {
                        // if (c == 0) {
                        //     var columnName = jexcel.getColumnNameFromId([c + 1, r]);
                        //     instance.jexcel.setValue(columnName, '');
                        // }
                        $(cell).parent().trigger('cellonchange', {cell, col, row, value});

                        let pid = this.options.data[row][0];
                        $this.compare.push(pid);
                        $this.compare = [...new Set($this.compare)];
                        $this.menubar.find('.vi-wbe-save-button').addClass('vi-wbe-saveable');

                        if (!$this.isAdding) {
                            if (!$this.revision[pid]) $this.revision[pid] = {};
                            let columnType = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getColumnType(col);
                            $this.revision[pid][columnType] = oldValue;
                        }
                    }
                },

                ondeleterow(el, rowNumber, numOfRows, rowRecords) {
                    for (let row of rowRecords) {
                        $this.trash.push(row[0].innerText);
                    }

                    if ($this.trash.length) $this.menubar.find('.vi-wbe-save-button').addClass('vi-wbe-saveable');
                },

                onundo(el, historyRecord) {
                    if (historyRecord && historyRecord.action === 'deleteRow') {
                        for (let row of historyRecord.rowData) {
                            $this.unTrash.push(row[0]);
                        }
                    }
                },

                onbeforecopy() {
                    countCol = 0;
                    $this.firstCellCopy = null;
                },

                oncopying(value, x, y) {
                    if (!$this.firstCellCopy) $this.firstCellCopy = [x, y];
                    if ($this.firstCellCopy[0] !== x) countCol++;
                },

                onbeforepaste(data, selectedCell) {
                    if (typeof data !== 'string') return data;
                    data = data.trim();

                    let x = selectedCell[0];
                    let cellType = this.columns[x].type;

                    if (typeof $this.firstCellCopy === 'undefined') {
                        if (['text', 'number', 'custom'].includes(cellType)) {
                            if (cellType === 'custom') {
                                let editorType = this.columns[x].editor.type;
                                return editorType === 'textEditor' ? data : '';
                            } else {
                                return data;
                            }
                        }
                        return '';
                    }

                    let sX = +$this.firstCellCopy[0],
                        tX = +selectedCell[0],
                        sXType = this.columns[sX].type,
                        tXType = this.columns[tX].type;

                    if (+$this.firstCellCopy[0] !== +selectedCell[0]) {

                        if (countCol > 0) {
                            alert('Copy single column each time.');
                            return '';
                        }

                        if (sXType !== tXType) {
                            alert('Can not paste data with different column type.');
                            return '';
                        }
                    }

                    return data;
                },

                onscroll(el) {
                    let selectOpening = $(el).find('select.select2-hidden-accessible');
                    if (selectOpening.length) selectOpening.select2('close')
                },

                oncreateeditor(el, cell, x, y, editor) {
                    if (this.options.columns[x].currency) {
                        editor.setAttribute('data-currency', this.options.columns[x].currency);
                    }
                }
            });
        }

        closeMenu(e) {
            this.sidebar.removeClass('vi-wbe-open')
        }

        openMenu(e) {
            let tab = $(e.currentTarget).data('menu_tab');
            let currentTab = this.sidebar.find(`a.item[data-tab='${tab}']`);
            if (currentTab.hasClass('active') && this.sidebar.hasClass('vi-wbe-open')) {
                this.sidebar.removeClass('vi-wbe-open');
            } else {
                this.sidebar.addClass('vi-wbe-open');
                currentTab.trigger('click');
            }
        }

        addNewProduct() {
            if (_functions__WEBPACK_IMPORTED_MODULE_0__.default.is_loading()) return;
            let productName = prompt(_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Please enter new product name'));

            if (productName) {
                let $this = this;
                _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                    data: {sub_action: 'add_new_product', product_name: productName},
                    beforeSend() {
                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                    },
                    success(res) {
                        $this.isAdding = true;
                        $this.WorkBook.insertRow(0, 0, true, true);
                        $this.WorkBook.setRowData(0, res.data, true);
                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                    },
                    complete() {
                        $this.isAdding = false;
                    }
                })
            }
        }

        addNewCoupon() {
            if (_functions__WEBPACK_IMPORTED_MODULE_0__.default.is_loading()) return;

            let $this = this;

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                data: {sub_action: 'add_new_coupon'},
                beforeSend() {
                    _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                },
                success(res) {
                    $this.isAdding = true;
                    $this.WorkBook.insertRow(0, 0, true, true);
                    $this.WorkBook.setRowData(0, res.data, true);
                    _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                },
                complete() {
                    $this.isAdding = false;
                }
            })
        }

        addNewOrder() {
            window.open('post-new.php?post_type=shop_order');
        }

        toggleFullScreen(e) {
            let body = $('.wp-admin'), screenBtn = $(e.currentTarget);
            body.toggleClass('vi-wbe-full-screen');

            if (body.hasClass('vi-wbe-full-screen')) {
                screenBtn.find('i.icon').removeClass('external alternate').addClass('window close outline');
                screenBtn.attr('title', 'Exit full screen');
            } else {
                screenBtn.find('i.icon').removeClass('window close outline').addClass('external alternate');
                screenBtn.attr('title', 'Full screen');
            }

            $.ajax({
                url: _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.ajaxUrl,
                type: 'post',
                dataType: 'json',
                data: {
                    ..._attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.ajaxData,
                    sub_action: 'set_full_screen_option',
                    status: body.hasClass('vi-wbe-full-screen')
                }
            });
        }

        getAllRows() {
            return this.WorkBook.getData(false, true);
        }

        save() {
            $('td.error').removeClass('error');

            let $this = this,
                products = this.getAllRows(),
                productsForSave = [], skuErrors = [];

            for (let pid of this.compare) {
                for (let product of products) {
                    if (product[0] === parseInt(pid)) {
                        productsForSave.push(product);
                    }
                }
            }

            if (_functions__WEBPACK_IMPORTED_MODULE_0__.default.is_loading()) return;

            function saveStep(step = 0) {
                let range = 30,
                    start = step * range,
                    end = start + range,
                    products = productsForSave.slice(start, end),
                    lastStep = (step + 1) * range > productsForSave.length;

                if (!(products.length || $this.trash.length || $this.unTrash.length)) {
                    if (step === 0) _functions__WEBPACK_IMPORTED_MODULE_0__.default.notice(_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Nothing change to save'));

                    if (lastStep) {
                        if (skuErrors.length) {
                            _functions__WEBPACK_IMPORTED_MODULE_0__.default.notice(_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('Invalid or duplicated SKU'));

                            let x = _functions__WEBPACK_IMPORTED_MODULE_0__.default.getColFromColumnType('sku');
                            let allRows = $this.WorkBook.getData();
                            if (allRows.length) {
                                for (let y in allRows) {
                                    let row = allRows[y];
                                    if (skuErrors.includes(row[0])) {
                                        let cell = $this.WorkBook.getCellFromCoords(x, y);
                                        $(cell).addClass('error');
                                    }
                                }
                            }
                        }

                        let histories = $this.WorkBook.history;
                        if (histories.length) {
                            for (let history of histories) {
                                if (history.action !== 'deleteRow') continue;
                                let iForDel = [];
                                for (let i in history.rowData) {
                                    if (history.rowData[i][1] > 0) {
                                        iForDel.push(parseInt(i));
                                    }
                                }

                                if (iForDel.length) {
                                    history.rowData = history.rowData.filter((item, i) => !iForDel.includes(i));
                                    history.rowNode = history.rowNode.filter((item, i) => !iForDel.includes(i));
                                    history.rowRecords = history.rowRecords.filter((item, i) => !iForDel.includes(i));
                                    history.numOfRows = history.numOfRows - iForDel.length;
                                }
                            }
                        }

                        $this.saveRevision();

                    }

                    return;
                }

                _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                    data: {
                        sub_action: 'save_products',
                        products: JSON.stringify(products),
                        trash: $this.trash,
                        untrash: $this.unTrash,
                    },
                    beforeSend() {
                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                    },
                    success(res) {
                        $this.trash = [];
                        $this.unTrash = [];
                        $this.compare = [];
                        $this.menubar.find('.vi-wbe-save-button').removeClass('vi-wbe-saveable');

                        if (res.data.skuErrors) {
                            skuErrors = [...new Set([...skuErrors, ...res.data.skuErrors])];
                        }

                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();
                        saveStep(step + 1);
                    },
                    error(res) {
                        console.log(res)
                    }
                });
            }

            saveStep();
        }

        loadProducts(page = 1, reCreate = false) {
            let $this = this;

            if (_functions__WEBPACK_IMPORTED_MODULE_0__.default.is_loading()) return;

            _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                data: {
                    sub_action: 'load_products',
                    page: page,
                    re_create: reCreate
                },
                beforeSend() {
                    _functions__WEBPACK_IMPORTED_MODULE_0__.default.loading();
                },
                success(res) {
                    if (res.success) {
                        _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage = res.data.img_storage;

                        if (reCreate) {
                            $this.WorkBook.destroy();

                            if (res.data.columns) _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.setColumns(res.data.columns);
                            if (res.data.idMapping) _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.idMapping = res.data.idMapping;
                            if (res.data.idMappingFlip) _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.idMappingFlip = res.data.idMappingFlip;

                            $this.workBookInit();
                        }

                        $this.WorkBook.options.data = res.data.products;
                        $this.WorkBook.setData();
                        $this.pagination(res.data.max_num_pages, page);

                        _functions__WEBPACK_IMPORTED_MODULE_0__.default.removeLoading();

                        if (!res.data.products.length) {
                            _functions__WEBPACK_IMPORTED_MODULE_0__.default.notice(_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('No item was found'));
                        }
                    }
                },
                error(res) {
                    console.log(res)
                }
            });
        }

        pagination(maxPage, currentPage) {
            this.menubar.find('.vi-wbe-pagination').html(_functions__WEBPACK_IMPORTED_MODULE_0__.default.pagination(maxPage, currentPage));
        }

        changePage(e) {
            let page = parseInt($(e.currentTarget).attr('data-page'));
            if ($(e.currentTarget).hasClass('active') || $(e.currentTarget).hasClass('disabled') || !page) return;
            this.loadProducts(page);
        }

        changePageByInput(e) {
            let page = parseInt($(e.target).val());
            let max = parseInt($(e.target).attr('max'));

            if (page <= max && page > 0) this.loadProducts(page);
        }

        reloadCurrentPage() {
            this.loadProducts(this.getCurrentPage())
        }

        getCurrentPage() {
            return this.menubar.find('.vi-wbe-pagination .item.active').data('page') || 1;
        }

        afterAddFilter(ev, data) {
            _attributes__WEBPACK_IMPORTED_MODULE_1__.Attributes.imgStorage = data.img_storage;
            this.WorkBook.options.data = data.products;
            this.WorkBook.setData();
            this.pagination(data.max_num_pages, 1);
            if (!data.products.length) _functions__WEBPACK_IMPORTED_MODULE_0__.default.notice(_functions__WEBPACK_IMPORTED_MODULE_0__.default.text('No item was found'))
        }

        afterSaveSettings(ev, data) {
            if (data.fieldsChange) {
                this.loadProducts(this.getCurrentPage(), true);
            }
        }

        saveRevision() {
            let $this = this;
            if (Object.keys($this.revision).length) {
                let currentPage = $this.sidebar.find('.vi-wbe-pagination a.item.active').data('page') || 1;
                _functions__WEBPACK_IMPORTED_MODULE_0__.default.ajax({
                    data: {sub_action: 'auto_save_revision', data: $this.revision, page: currentPage || 1},
                    success(res) {
                        if (res.success) {
                            if (res.data.updatePage) $('#vi-wbe-history-points-list tbody').html(res.data.updatePage);
                            $this.revision = {};
                            $this.sidebar.find('.vi-wbe-pagination').html(_functions__WEBPACK_IMPORTED_MODULE_0__.default.pagination(res.data.pages, currentPage));
                        }
                    }
                });
            }
        }

    }

    new BulkEdit();

});

})();

/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9hc3NldHMvLi9zcmMvYWRkLWltYWdlLXRvLW11bHRpLWdhbGxlcnkuanMiLCJ3ZWJwYWNrOi8vYXNzZXRzLy4vc3JjL2F0dHJpYnV0ZXMuanMiLCJ3ZWJwYWNrOi8vYXNzZXRzLy4vc3JjL2NhbGN1bGF0b3IuanMiLCJ3ZWJwYWNrOi8vYXNzZXRzLy4vc3JjL2N1c3RvbS1jb2x1bW4uanMiLCJ3ZWJwYWNrOi8vYXNzZXRzLy4vc3JjL2ZpbmQtYW5kLXJlcGxhY2Utb3B0aW9ucy5qcyIsIndlYnBhY2s6Ly9hc3NldHMvLi9zcmMvZmluZC1hbmQtcmVwbGFjZS10YWdzLmpzIiwid2VicGFjazovL2Fzc2V0cy8uL3NyYy9maW5kLWFuZC1yZXBsYWNlLmpzIiwid2VicGFjazovL2Fzc2V0cy8uL3NyYy9mdW5jdGlvbnMuanMiLCJ3ZWJwYWNrOi8vYXNzZXRzLy4vc3JjL21vZGFsLXBvcHVwLmpzIiwid2VicGFjazovL2Fzc2V0cy8uL3NyYy9tdWx0aXBsZS1wcm9kdWN0LWF0dHJpYnV0ZXMuanMiLCJ3ZWJwYWNrOi8vYXNzZXRzLy4vc3JjL3NpZGViYXIuanMiLCJ3ZWJwYWNrOi8vYXNzZXRzLy4vc3JjL3RlbXBsYXRlcy5qcyIsIndlYnBhY2s6Ly9hc3NldHMvLi9zcmMvdGV4dC1tdWx0aS1jZWxscy1lZGl0LmpzIiwid2VicGFjazovL2Fzc2V0cy93ZWJwYWNrL2Jvb3RzdHJhcCIsIndlYnBhY2s6Ly9hc3NldHMvd2VicGFjay9ydW50aW1lL2RlZmluZSBwcm9wZXJ0eSBnZXR0ZXJzIiwid2VicGFjazovL2Fzc2V0cy93ZWJwYWNrL3J1bnRpbWUvaGFzT3duUHJvcGVydHkgc2hvcnRoYW5kIiwid2VicGFjazovL2Fzc2V0cy93ZWJwYWNrL3J1bnRpbWUvbWFrZSBuYW1lc3BhY2Ugb2JqZWN0Iiwid2VicGFjazovL2Fzc2V0cy8uL3NyYy9lZGl0b3IuanMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7Ozs7Ozs7Ozs7O0FBQXdDOztB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MEUsMERBQWE7QUFDdkY7QUFDQTtBQUNBLGlCQUFpQjtBQUNqQjtBQUNBOztBQUVBOztBQUVBOztBQUVBLENBQUMiLCJmaWxlIjoiZWRpdG9yLmpzIiwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IHtBdHRyaWJ1dGVzfSBmcm9tIFwiLi9hdHRyaWJ1dGVzXCI7XHJcblxyXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBBZGRJbWFnZVRvTXVsdGlHYWxsZXJ5IHtcclxuICAgIGNvbnN0cnVjdG9yKG9iaiwgY2VsbHMsIHgsIHksIGUpIHtcclxuICAgICAgICB0aGlzLmNlbGxzID0gY2VsbHM7XHJcbiAgICAgICAgdGhpcy5vYmogPSBvYmo7XHJcbiAgICAgICAgdGhpcy54ID0gcGFyc2VJbnQoeCk7XHJcbiAgICAgICAgdGhpcy55ID0gcGFyc2VJbnQoeSk7XHJcblxyXG4gICAgICAgIHRoaXMucnVuKCk7XHJcbiAgICB9XHJcblxyXG4gICAgcnVuKCkge1xyXG4gICAgICAgIGxldCAkdGhpcyA9IHRoaXM7XHJcbiAgICAgICAgY29uc3QgbWVkaWFNdWx0aXBsZSA9IHdwLm1lZGlhKHttdWx0aXBsZTogdHJ1ZX0pO1xyXG4gICAgICAgIG1lZGlhTXVsdGlwbGUub3BlbigpLm9mZignc2VsZWN0IGNsb3NlJylcclxuICAgICAgICAgICAgLm9uKCdzZWxlY3QnLCBmdW5jdGlvbiAoZSkge1xyXG4gICAgICAgICAgICAgICAgdmFyIHNlbGVjdGlvbiA9IG1lZGlhTXVsdGlwbGUuc3RhdGUoKS5nZXQoJ3NlbGVjdGlvbicpO1xyXG4gICAgICAgICAgICAgICAgc2VsZWN0aW9uLmVhY2goZnVuY3Rpb24gKGF0dGFjaG1lbnQpIHtcclxuICAgICAgICAgICAgICAgICAgICBhdHRhY2htZW50ID0gYXR0YWNobWVudC50b0pTT04oKTtcclxuICAgICAgICAgICAgICAgICAgICBpZiAoYXR0YWNobWVudC50eXBlID09PSAnaW1hZ2UnKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIGdhbGxlcnlQb3B1cC5maW5kKCcudmktd2JlLWdhbGxlcnktaW1hZ2VzJykuYXBwZW5kKHRtcGwuZ2FsbGVyeUltYWdlKGF0dGFjaG1lbnQudXJsLCBhdHRhY2htZW50LmlkKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCBpbWdJZCA9IGF0dGFjaG1lbnQuaWQ7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIEF0dHJpYnV0ZXMuaW1nU3RvcmFnZVtpbWdJZF0gPSBhdHRhY2htZW50LnVybDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMuYWRkSW1hZ2UoaW1nSWQpO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgIH1cclxuXHJcbiAgICBhZGRJbWFnZShpbWdJZCkge1xyXG5cclxuICAgICAgICBsZXQgZXhjZWxPYmogPSB0aGlzLm9iajtcclxuICAgICAgICBsZXQgYnJlYWtDb250cm9sID0gZmFsc2UsIHJlY29yZHMgPSBbXTtcclxuICAgICAgICBsZXQgaCA9IHRoaXMuY2VsbHM7XHJcbiAgICAgICAgbGV0IHN0YXJ0ID0gaFsxXSwgZW5kID0gaFszXSwgeCA9IGhbMF07XHJcblxyXG4gICAgICAgIGZvciAobGV0IHkgPSBzdGFydDsgeSA8PSBlbmQ7IHkrKykge1xyXG4gICAgICAgICAgICBpZiAoZXhjZWxPYmoucmVjb3Jkc1t5XVt4XSAmJiAhZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5jbGFzc0xpc3QuY29udGFpbnMoJ3JlYWRvbmx5JykgJiYgZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5zdHlsZS5kaXNwbGF5ICE9PSAnbm9uZScgJiYgYnJlYWtDb250cm9sID09PSBmYWxzZSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IHZhbHVlID0gZXhjZWxPYmoub3B0aW9ucy5kYXRhW3ldW3hdO1xyXG4gICAgICAgICAgICAgICAgaWYgKCF2YWx1ZSkgdmFsdWUgPSBbXTtcclxuXHJcbiAgICAgICAgICAgICAgICBsZXQgbmV3VmFsdWUgPSBbLi4ubmV3IFNldCh2YWx1ZSldO1xyXG4gICAgICAgICAgICAgICAgbmV3VmFsdWUucHVzaChpbWdJZCk7XHJcblxyXG4gICAgICAgICAgICAgICAgcmVjb3Jkcy5wdXNoKGV4Y2VsT2JqLnVwZGF0ZUNlbGwoeCwgeSwgbmV3VmFsdWUpKTtcclxuICAgICAgICAgICAgICAgIGV4Y2VsT2JqLnVwZGF0ZUZvcm11bGFDaGFpbih4LCB5LCByZWNvcmRzKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgLy8gVXBkYXRlIGhpc3RvcnlcclxuICAgICAgICBleGNlbE9iai5zZXRIaXN0b3J5KHtcclxuICAgICAgICAgICAgYWN0aW9uOiAnc2V0VmFsdWUnLFxyXG4gICAgICAgICAgICByZWNvcmRzOiByZWNvcmRzLFxyXG4gICAgICAgICAgICBzZWxlY3Rpb246IGV4Y2VsT2JqLnNlbGVjdGVkQ2VsbCxcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgLy8gVXBkYXRlIHRhYmxlIHdpdGggY3VzdG9tIGNvbmZpZ3VyYXRpb24gaWYgYXBwbGljYWJsZVxyXG4gICAgICAgIGV4Y2VsT2JqLnVwZGF0ZVRhYmxlKCk7XHJcbiAgICB9XHJcbn0iLCJpbXBvcnQge2NvbHVtbkZpbHRlciwgY3VzdG9tQ29sdW1ufSBmcm9tIFwiLi9jdXN0b20tY29sdW1uXCI7XHJcblxyXG5jb25zdCBBdHRyaWJ1dGVzID0ge1xyXG4gICAgLi4ud2JlUGFyYW1zLFxyXG4gICAgcHJvZHVjdFR5cGVzOiB7fSxcclxuICAgIGZpbHRlcktleTogRGF0ZS5ub3coKSxcclxuICAgIHNlbGVjdFBhZ2U6IDEsXHJcbiAgICBhamF4RGF0YToge2FjdGlvbjogJ3ZpX3diZV9hamF4Jywgdmlfd2JlX25vbmNlOiB3YmVQYXJhbXMubm9uY2V9LFxyXG4gICAgdGlueU1jZU9wdGlvbnM6IHtcclxuICAgICAgICB0aW55bWNlOiB7XHJcbiAgICAgICAgICAgIHRoZW1lOiBcIm1vZGVyblwiLFxyXG4gICAgICAgICAgICBza2luOiBcImxpZ2h0Z3JheVwiLFxyXG4gICAgICAgICAgICBsYW5ndWFnZTogXCJlblwiLFxyXG4gICAgICAgICAgICBmb3JtYXRzOiB7XHJcbiAgICAgICAgICAgICAgICBhbGlnbmxlZnQ6IFtcclxuICAgICAgICAgICAgICAgICAgICB7c2VsZWN0b3I6IFwicCxoMSxoMixoMyxoNCxoNSxoNix0ZCx0aCxkaXYsdWwsb2wsbGlcIiwgc3R5bGVzOiB7dGV4dEFsaWduOiBcImxlZnRcIn19LFxyXG4gICAgICAgICAgICAgICAgICAgIHtzZWxlY3RvcjogXCJpbWcsdGFibGUsZGwud3AtY2FwdGlvblwiLCBjbGFzc2VzOiBcImFsaWdubGVmdFwifVxyXG4gICAgICAgICAgICAgICAgXSxcclxuICAgICAgICAgICAgICAgIGFsaWduY2VudGVyOiBbXHJcbiAgICAgICAgICAgICAgICAgICAge3NlbGVjdG9yOiBcInAsaDEsaDIsaDMsaDQsaDUsaDYsdGQsdGgsZGl2LHVsLG9sLGxpXCIsIHN0eWxlczoge3RleHRBbGlnbjogXCJjZW50ZXJcIn19LFxyXG4gICAgICAgICAgICAgICAgICAgIHtzZWxlY3RvcjogXCJpbWcsdGFibGUsZGwud3AtY2FwdGlvblwiLCBjbGFzc2VzOiBcImFsaWduY2VudGVyXCJ9XHJcbiAgICAgICAgICAgICAgICBdLFxyXG4gICAgICAgICAgICAgICAgYWxpZ25yaWdodDogW1xyXG4gICAgICAgICAgICAgICAgICAgIHtzZWxlY3RvcjogXCJwLGgxLGgyLGgzLGg0LGg1LGg2LHRkLHRoLGRpdix1bCxvbCxsaVwiLCBzdHlsZXM6IHt0ZXh0QWxpZ246IFwicmlnaHRcIn19LFxyXG4gICAgICAgICAgICAgICAgICAgIHtzZWxlY3RvcjogXCJpbWcsdGFibGUsZGwud3AtY2FwdGlvblwiLCBjbGFzc2VzOiBcImFsaWducmlnaHRcIn1cclxuICAgICAgICAgICAgICAgIF0sXHJcbiAgICAgICAgICAgICAgICBzdHJpa2V0aHJvdWdoOiB7aW5saW5lOiBcImRlbFwifVxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICByZWxhdGl2ZV91cmxzOiBmYWxzZSxcclxuICAgICAgICAgICAgcmVtb3ZlX3NjcmlwdF9ob3N0OiBmYWxzZSxcclxuICAgICAgICAgICAgY29udmVydF91cmxzOiBmYWxzZSxcclxuICAgICAgICAgICAgYnJvd3Nlcl9zcGVsbGNoZWNrOiB0cnVlLFxyXG4gICAgICAgICAgICBmaXhfbGlzdF9lbGVtZW50czogdHJ1ZSxcclxuICAgICAgICAgICAgZW50aXRpZXM6IFwiMzgsYW1wLDYwLGx0LDYyLGd0XCIsXHJcbiAgICAgICAgICAgIGVudGl0eV9lbmNvZGluZzogXCJyYXdcIixcclxuICAgICAgICAgICAga2VlcF9zdHlsZXM6IGZhbHNlLFxyXG4gICAgICAgICAgICBjYWNoZV9zdWZmaXg6IFwid3AtbWNlLTQ5MTEwLTIwMjAxMTEwXCIsXHJcbiAgICAgICAgICAgIHJlc2l6ZTogXCJ2ZXJ0aWNhbFwiLFxyXG4gICAgICAgICAgICBtZW51YmFyOiBmYWxzZSxcclxuICAgICAgICAgICAgYnJhbmRpbmc6IGZhbHNlLFxyXG4gICAgICAgICAgICBwcmV2aWV3X3N0eWxlczogXCJmb250LWZhbWlseSBmb250LXNpemUgZm9udC13ZWlnaHQgZm9udC1zdHlsZSB0ZXh0LWRlY29yYXRpb24gdGV4dC10cmFuc2Zvcm1cIixcclxuICAgICAgICAgICAgZW5kX2NvbnRhaW5lcl9vbl9lbXB0eV9ibG9jazogdHJ1ZSxcclxuICAgICAgICAgICAgd3BlZGl0aW1hZ2VfaHRtbDVfY2FwdGlvbnM6IHRydWUsXHJcbiAgICAgICAgICAgIHdwX2xhbmdfYXR0cjogXCJlbi1VU1wiLFxyXG4gICAgICAgICAgICB3cF9rZWVwX3Njcm9sbF9wb3NpdGlvbjogZmFsc2UsXHJcbiAgICAgICAgICAgIHdwX3Nob3J0Y3V0X2xhYmVsczoge1xyXG4gICAgICAgICAgICAgICAgXCJIZWFkaW5nIDFcIjogXCJhY2Nlc3MxXCIsXHJcbiAgICAgICAgICAgICAgICBcIkhlYWRpbmcgMlwiOiBcImFjY2VzczJcIixcclxuICAgICAgICAgICAgICAgIFwiSGVhZGluZyAzXCI6IFwiYWNjZXNzM1wiLFxyXG4gICAgICAgICAgICAgICAgXCJIZWFkaW5nIDRcIjogXCJhY2Nlc3M0XCIsXHJcbiAgICAgICAgICAgICAgICBcIkhlYWRpbmcgNVwiOiBcImFjY2VzczVcIixcclxuICAgICAgICAgICAgICAgIFwiSGVhZGluZyA2XCI6IFwiYWNjZXNzNlwiLFxyXG4gICAgICAgICAgICAgICAgXCJQYXJhZ3JhcGhcIjogXCJhY2Nlc3M3XCIsXHJcbiAgICAgICAgICAgICAgICBcIkJsb2NrcXVvdGVcIjogXCJhY2Nlc3NRXCIsXHJcbiAgICAgICAgICAgICAgICBcIlVuZGVybGluZVwiOiBcIm1ldGFVXCIsXHJcbiAgICAgICAgICAgICAgICBcIlN0cmlrZXRocm91Z2hcIjogXCJhY2Nlc3NEXCIsXHJcbiAgICAgICAgICAgICAgICBcIkJvbGRcIjogXCJtZXRhQlwiLFxyXG4gICAgICAgICAgICAgICAgXCJJdGFsaWNcIjogXCJtZXRhSVwiLFxyXG4gICAgICAgICAgICAgICAgXCJDb2RlXCI6IFwiYWNjZXNzWFwiLFxyXG4gICAgICAgICAgICAgICAgXCJBbGlnbiBjZW50ZXJcIjogXCJhY2Nlc3NDXCIsXHJcbiAgICAgICAgICAgICAgICBcIkFsaWduIHJpZ2h0XCI6IFwiYWNjZXNzUlwiLFxyXG4gICAgICAgICAgICAgICAgXCJBbGlnbiBsZWZ0XCI6IFwiYWNjZXNzTFwiLFxyXG4gICAgICAgICAgICAgICAgXCJKdXN0aWZ5XCI6IFwiYWNjZXNzSlwiLFxyXG4gICAgICAgICAgICAgICAgXCJDdXRcIjogXCJtZXRhWFwiLFxyXG4gICAgICAgICAgICAgICAgXCJDb3B5XCI6IFwibWV0YUNcIixcclxuICAgICAgICAgICAgICAgIFwiUGFzdGVcIjogXCJtZXRhVlwiLFxyXG4gICAgICAgICAgICAgICAgXCJTZWxlY3QgYWxsXCI6IFwibWV0YUFcIixcclxuICAgICAgICAgICAgICAgIFwiVW5kb1wiOiBcIm1ldGFaXCIsXHJcbiAgICAgICAgICAgICAgICBcIlJlZG9cIjogXCJtZXRhWVwiLFxyXG4gICAgICAgICAgICAgICAgXCJCdWxsZXQgbGlzdFwiOiBcImFjY2Vzc1VcIixcclxuICAgICAgICAgICAgICAgIFwiTnVtYmVyZWQgbGlzdFwiOiBcImFjY2Vzc09cIixcclxuICAgICAgICAgICAgICAgIFwiSW5zZXJ0XFwvZWRpdCBpbWFnZVwiOiBcImFjY2Vzc01cIixcclxuICAgICAgICAgICAgICAgIFwiSW5zZXJ0XFwvZWRpdCBsaW5rXCI6IFwibWV0YUtcIixcclxuICAgICAgICAgICAgICAgIFwiUmVtb3ZlIGxpbmtcIjogXCJhY2Nlc3NTXCIsXHJcbiAgICAgICAgICAgICAgICBcIlRvb2xiYXIgVG9nZ2xlXCI6IFwiYWNjZXNzWlwiLFxyXG4gICAgICAgICAgICAgICAgXCJJbnNlcnQgUmVhZCBNb3JlIHRhZ1wiOiBcImFjY2Vzc1RcIixcclxuICAgICAgICAgICAgICAgIFwiSW5zZXJ0IFBhZ2UgQnJlYWsgdGFnXCI6IFwiYWNjZXNzUFwiLFxyXG4gICAgICAgICAgICAgICAgXCJEaXN0cmFjdGlvbi1mcmVlIHdyaXRpbmcgbW9kZVwiOiBcImFjY2Vzc1dcIixcclxuICAgICAgICAgICAgICAgIFwiQWRkIE1lZGlhXCI6IFwiYWNjZXNzTVwiLFxyXG4gICAgICAgICAgICAgICAgXCJLZXlib2FyZCBTaG9ydGN1dHNcIjogXCJhY2Nlc3NIXCJcclxuICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgLy8gY29udGVudF9jc3M6IFwiaHR0cDovL2xvY2FsaG9zdDo4MDAwL3dwLWluY2x1ZGVzL2Nzcy9kYXNoaWNvbnMubWluLmNzcz92ZXI9NS42LjIsaHR0cDovL2xvY2FsaG9zdDo4MDAwL3dwLWluY2x1ZGVzL2pzL3RpbnltY2Uvc2tpbnMvd29yZHByZXNzL3dwLWNvbnRlbnQuY3NzP3Zlcj01LjYuMixodHRwczovL2ZvbnRzLmdvb2dsZWFwaXMuY29tL2Nzcz9mYW1pbHk9U291cmNlK1NhbnMrUHJvOjQwMCUyQzMwMCUyQzMwMGl0YWxpYyUyQzQwMGl0YWxpYyUyQzYwMCUyQzcwMCUyQzkwMCZzdWJzZXQ9bGF0aW4lMkNsYXRpbi1leHQsaHR0cDovL2xvY2FsaG9zdDo4MDAwL3dwLWNvbnRlbnQvdGhlbWVzL3N0b3JlZnJvbnQvYXNzZXRzL2Nzcy9iYXNlL2d1dGVuYmVyZy1lZGl0b3IuY3NzXCIsXHJcbiAgICAgICAgICAgIHBsdWdpbnM6IFwiY2hhcm1hcCxjb2xvcnBpY2tlcixocixsaXN0cyxtZWRpYSxwYXN0ZSx0YWJmb2N1cyx0ZXh0Y29sb3IsZnVsbHNjcmVlbix3b3JkcHJlc3Msd3BhdXRvcmVzaXplLHdwZWRpdGltYWdlLHdwZW1vamksd3BnYWxsZXJ5LHdwbGluayx3cGRpYWxvZ3Msd3B0ZXh0cGF0dGVybix3cHZpZXdcIixcclxuICAgICAgICAgICAgc2VsZWN0b3I6IFwiI3ZpLXdiZS10ZXh0LWVkaXRvclwiLFxyXG4gICAgICAgICAgICB3cGF1dG9wOiB0cnVlLFxyXG4gICAgICAgICAgICBpbmRlbnQ6IGZhbHNlLFxyXG4gICAgICAgICAgICB0b29sYmFyMTogXCJmb3JtYXRzZWxlY3QsYm9sZCxpdGFsaWMsYnVsbGlzdCxudW1saXN0LGJsb2NrcXVvdGUsYWxpZ25sZWZ0LGFsaWduY2VudGVyLGFsaWducmlnaHQsbGluayx3cF9tb3JlLHNwZWxsY2hlY2tlcixmdWxsc2NyZWVuLHdwX2FkdlwiLFxyXG4gICAgICAgICAgICB0b29sYmFyMjogXCJzdHJpa2V0aHJvdWdoLGhyLGZvcmVjb2xvcixwYXN0ZXRleHQscmVtb3ZlZm9ybWF0LGNoYXJtYXAsb3V0ZGVudCxpbmRlbnQsdW5kbyxyZWRvLHdwX2hlbHBcIixcclxuICAgICAgICAgICAgdGFiZm9jdXNfZWxlbWVudHM6IFwiOnByZXYsOm5leHRcIixcclxuICAgICAgICAgICAgYm9keV9jbGFzczogXCJleGNlcnB0IHBvc3QtdHlwZS1wcm9kdWN0IHBvc3Qtc3RhdHVzLXB1Ymxpc2ggcGFnZS10ZW1wbGF0ZS1kZWZhdWx0IGxvY2FsZS1lbi11c1wiLFxyXG4gICAgICAgIH0sXHJcbiAgICAgICAgbWVkaWFCdXR0b25zOiB0cnVlLFxyXG4gICAgICAgIHF1aWNrdGFnczogdHJ1ZVxyXG4gICAgfSxcclxuICAgIHNldENvbHVtbnMocmF3KSB7XHJcbiAgICAgICAgdHJ5IHtcclxuICAgICAgICAgICAgbGV0IGNvbHVtbnMgPSBKU09OLnBhcnNlKHJhdyk7XHJcbiAgICAgICAgICAgIEF0dHJpYnV0ZXMuY29sdW1ucyA9IGNvbHVtbnMubWFwKChjb2wpID0+IHtcclxuICAgICAgICAgICAgICAgIGlmIChjb2wgJiYgY29sLmVkaXRvciAmJiBjdXN0b21Db2x1bW5bY29sLmVkaXRvcl0pIGNvbC5lZGl0b3IgPSBjdXN0b21Db2x1bW5bY29sLmVkaXRvcl07XHJcbiAgICAgICAgICAgICAgICBpZiAoY29sICYmIGNvbC5maWx0ZXIgJiYgY29sdW1uRmlsdGVyW2NvbC5maWx0ZXJdKSBjb2wuZmlsdGVyID0gY29sdW1uRmlsdGVyW2NvbC5maWx0ZXJdO1xyXG4gICAgICAgICAgICAgICAgcmV0dXJuIGNvbDtcclxuICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIH0gY2F0Y2ggKGUpIHtcclxuICAgICAgICAgICAgY29uc29sZS5sb2coZSk7XHJcbiAgICAgICAgfVxyXG4gICAgfVxyXG59O1xyXG5cclxuXHJcbndpbmRvdy5BdHRyaWJ1dGVzID0gQXR0cmlidXRlcztcclxuY29uc3QgSTE4biA9IHdiZUkxOG4uaTE4bjtcclxuZXhwb3J0IHtBdHRyaWJ1dGVzLCBJMThufSA7IiwiaW1wb3J0IF9mIGZyb20gJy4vZnVuY3Rpb25zJztcclxuaW1wb3J0IHtQb3B1cH0gZnJvbSBcIi4vbW9kYWwtcG9wdXBcIjtcclxuXHJcbmNvbnN0ICQgPSBqUXVlcnk7XHJcblxyXG5leHBvcnQgY2xhc3MgQ2FsY3VsYXRvciB7XHJcbiAgICBjb25zdHJ1Y3RvcihvYmosIHgsIHksIGUpIHtcclxuICAgICAgICB0aGlzLl9kYXRhID0ge307XHJcbiAgICAgICAgdGhpcy5fZGF0YS5qZXhjZWwgPSBvYmo7XHJcbiAgICAgICAgdGhpcy5fZGF0YS54ID0gcGFyc2VJbnQoeCk7XHJcbiAgICAgICAgdGhpcy5fZGF0YS55ID0gcGFyc2VJbnQoeSk7XHJcbiAgICAgICAgdGhpcy5ydW4oKTtcclxuICAgIH1cclxuXHJcbiAgICBnZXQoaWQpIHtcclxuICAgICAgICByZXR1cm4gdGhpcy5fZGF0YVtpZF0gfHwgJydcclxuICAgIH1cclxuXHJcbiAgICBydW4oKSB7XHJcbiAgICAgICAgbGV0IGZvcm11bGFIdG1sID0gdGhpcy5jb250ZW50KCk7XHJcbiAgICAgICAgbGV0IGNlbGwgPSAkKGB0ZFtkYXRhLXg9JHt0aGlzLmdldCgneCcpIHx8IDB9XVtkYXRhLXk9JHt0aGlzLmdldCgneScpIHx8IDB9XWApO1xyXG4gICAgICAgIG5ldyBQb3B1cChmb3JtdWxhSHRtbCwgY2VsbCk7XHJcbiAgICAgICAgZm9ybXVsYUh0bWwub24oJ2NsaWNrJywgJy52aS13YmUtYXBwbHktZm9ybXVsYScsIHRoaXMuYXBwbHlGb3JtdWxhLmJpbmQodGhpcykpO1xyXG4gICAgICAgIGZvcm11bGFIdG1sLm9uKCdjaGFuZ2UnLCAnLnZpLXdiZS1yb3VuZGVkJywgdGhpcy50b2dnbGVEZWNpbWFsVmFsdWUpO1xyXG4gICAgfVxyXG5cclxuICAgIGNvbnRlbnQoKSB7XHJcbiAgICAgICAgcmV0dXJuICQoYDxkaXYgY2xhc3M9XCJ2aS13YmUtZm9ybXVsYS1jb250YWluZXJcIiBzdHlsZT1cImRpc3BsYXk6IGZsZXg7XCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPHNlbGVjdCBjbGFzcz1cInZpLXdiZS1vcGVyYXRvclwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPVwiK1wiPis8L29wdGlvbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT1cIi1cIj4tPC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgPC9zZWxlY3Q+XHJcbiAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJudW1iZXJcIiBtaW49XCIwXCIgY2xhc3M9XCJ2aS13YmUtdmFsdWVcIj5cclxuICAgICAgICAgICAgICAgICAgICA8c2VsZWN0IGNsYXNzPVwidmktd2JlLXVuaXRcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT1cImZpeGVkXCI+bjwvb3B0aW9uPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPVwicGVyY2VudGFnZVwiPiU8L29wdGlvbj5cclxuICAgICAgICAgICAgICAgICAgICA8L3NlbGVjdD5cclxuICAgICAgICAgICAgICAgICAgICA8c2VsZWN0IGNsYXNzPVwidmktd2JlLXJvdW5kZWRcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT1cIm5vX3JvdW5kXCI+JHtfZi50ZXh0KCdObyByb3VuZCcpfTwvb3B0aW9uPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPVwicm91bmRcIj4ke19mLnRleHQoJ1JvdW5kIHdpdGggZGVjaW1hbCcpfTwvb3B0aW9uPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPVwicm91bmRfdXBcIj4ke19mLnRleHQoJ1JvdW5kIHVwJyl9PC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxvcHRpb24gdmFsdWU9XCJyb3VuZF9kb3duXCI+JHtfZi50ZXh0KCdSb3VuZCBkb3duJyl9PC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgPC9zZWxlY3Q+XHJcbiAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJudW1iZXJcIiBtaW49XCIwXCIgbWF4PVwiMTBcIiBjbGFzcz1cInZpLXdiZS1kZWNpbWFsXCIgdmFsdWU9XCIwXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJ2aS11aSBidXR0b24gbWluaSB2aS13YmUtYXBwbHktZm9ybXVsYVwiPiR7X2YudGV4dCgnT0snKX08L2J1dHRvbj5cclxuICAgICAgICAgICAgICAgIDwvZGl2PmApO1xyXG4gICAgfVxyXG5cclxuICAgIGFwcGx5Rm9ybXVsYShlKSB7XHJcbiAgICAgICAgbGV0IGZvcm0gPSAkKGUudGFyZ2V0KS5jbG9zZXN0KCcudmktd2JlLWZvcm11bGEtY29udGFpbmVyJyksXHJcbiAgICAgICAgICAgIG9wZXJhdG9yID0gZm9ybS5maW5kKCcudmktd2JlLW9wZXJhdG9yJykudmFsKCksXHJcbiAgICAgICAgICAgIGZWYWx1ZSA9IHBhcnNlRmxvYXQoZm9ybS5maW5kKCcudmktd2JlLXZhbHVlJykudmFsKCkpLFxyXG4gICAgICAgICAgICB1bml0ID0gZm9ybS5maW5kKCcudmktd2JlLXVuaXQnKS52YWwoKSxcclxuICAgICAgICAgICAgcm91bmRlZCA9IGZvcm0uZmluZCgnLnZpLXdiZS1yb3VuZGVkJykudmFsKCksXHJcbiAgICAgICAgICAgIGRlY2ltYWwgPSBwYXJzZUludChmb3JtLmZpbmQoJy52aS13YmUtZGVjaW1hbCcpLnZhbCgpKSxcclxuICAgICAgICAgICAgZXhjZWxPYmogPSB0aGlzLmdldCgnamV4Y2VsJyk7XHJcblxyXG4gICAgICAgIGlmICghZlZhbHVlKSByZXR1cm47XHJcblxyXG4gICAgICAgIGxldCBicmVha0NvbnRyb2wgPSBmYWxzZSwgcmVjb3JkcyA9IFtdO1xyXG4gICAgICAgIGxldCBoID0gZXhjZWxPYmouc2VsZWN0ZWRDb250YWluZXI7XHJcbiAgICAgICAgbGV0IHN0YXJ0ID0gaFsxXSwgZW5kID0gaFszXSwgeCA9IGhbMF07XHJcblxyXG4gICAgICAgIGZ1bmN0aW9uIGZvcm11bGEob2xkVmFsdWUpIHtcclxuICAgICAgICAgICAgb2xkVmFsdWUgPSBwYXJzZUZsb2F0KG9sZFZhbHVlKTtcclxuICAgICAgICAgICAgbGV0IGV4dHJhVmFsdWUgPSB1bml0ID09PSAncGVyY2VudGFnZScgPyBvbGRWYWx1ZSAqIGZWYWx1ZSAvIDEwMCA6IGZWYWx1ZTtcclxuICAgICAgICAgICAgbGV0IG5ld1ZhbHVlID0gb3BlcmF0b3IgPT09ICctJyA/IG9sZFZhbHVlIC0gZXh0cmFWYWx1ZSA6IG9sZFZhbHVlICsgZXh0cmFWYWx1ZTtcclxuICAgICAgICAgICAgc3dpdGNoIChyb3VuZGVkKSB7XHJcbiAgICAgICAgICAgICAgICBjYXNlICdyb3VuZCc6XHJcbiAgICAgICAgICAgICAgICAgICAgbmV3VmFsdWUgPSBuZXdWYWx1ZS50b0ZpeGVkKGRlY2ltYWwpO1xyXG4gICAgICAgICAgICAgICAgICAgIGJyZWFrO1xyXG4gICAgICAgICAgICAgICAgY2FzZSAncm91bmRfdXAnOlxyXG4gICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlID0gTWF0aC5jZWlsKG5ld1ZhbHVlKTtcclxuICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuICAgICAgICAgICAgICAgIGNhc2UgJ3JvdW5kX2Rvd24nOlxyXG4gICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlID0gTWF0aC5mbG9vcihuZXdWYWx1ZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgcmV0dXJuIG5ld1ZhbHVlO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgZm9yIChsZXQgeSA9IHN0YXJ0OyB5IDw9IGVuZDsgeSsrKSB7XHJcbiAgICAgICAgICAgIGlmIChleGNlbE9iai5yZWNvcmRzW3ldW3hdICYmICFleGNlbE9iai5yZWNvcmRzW3ldW3hdLmNsYXNzTGlzdC5jb250YWlucygncmVhZG9ubHknKSAmJiBleGNlbE9iai5yZWNvcmRzW3ldW3hdLnN0eWxlLmRpc3BsYXkgIT09ICdub25lJyAmJiBicmVha0NvbnRyb2wgPT09IGZhbHNlKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgdmFsdWUgPSBleGNlbE9iai5vcHRpb25zLmRhdGFbeV1beF0gfHwgMDtcclxuICAgICAgICAgICAgICAgIHJlY29yZHMucHVzaChleGNlbE9iai51cGRhdGVDZWxsKHgsIHksIGZvcm11bGEodmFsdWUpKSk7XHJcbiAgICAgICAgICAgICAgICBleGNlbE9iai51cGRhdGVGb3JtdWxhQ2hhaW4oeCwgeSwgcmVjb3Jkcyk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIC8vIFVwZGF0ZSBoaXN0b3J5XHJcbiAgICAgICAgZXhjZWxPYmouc2V0SGlzdG9yeSh7XHJcbiAgICAgICAgICAgIGFjdGlvbjogJ3NldFZhbHVlJyxcclxuICAgICAgICAgICAgcmVjb3JkczogcmVjb3JkcyxcclxuICAgICAgICAgICAgc2VsZWN0aW9uOiBleGNlbE9iai5zZWxlY3RlZENlbGwsXHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIC8vIFVwZGF0ZSB0YWJsZSB3aXRoIGN1c3RvbSBjb25maWd1cmF0aW9uIGlmIGFwcGxpY2FibGVcclxuICAgICAgICBleGNlbE9iai51cGRhdGVUYWJsZSgpO1xyXG4gICAgfVxyXG5cclxuICAgIHRvZ2dsZURlY2ltYWxWYWx1ZSgpIHtcclxuICAgICAgICBsZXQgZm9ybSA9ICQodGhpcykuY2xvc2VzdCgnLnZpLXdiZS1mb3JtdWxhLWNvbnRhaW5lcicpO1xyXG4gICAgICAgIGZvcm0uZmluZCgnLnZpLXdiZS1kZWNpbWFsJykuaGlkZSgpO1xyXG4gICAgICAgIGlmICgkKHRoaXMpLnZhbCgpID09PSAncm91bmQnKSBmb3JtLmZpbmQoJy52aS13YmUtZGVjaW1hbCcpLnNob3coKTtcclxuICAgIH1cclxufVxyXG5cclxuZXhwb3J0IGNsYXNzIENhbGN1bGF0b3JCYXNlT25SZWd1bGFyUHJpY2Uge1xyXG4gICAgY29uc3RydWN0b3Iob2JqLCB4LCB5LCBlKSB7XHJcbiAgICAgICAgdGhpcy5fZGF0YSA9IHt9O1xyXG4gICAgICAgIHRoaXMuX2RhdGEuamV4Y2VsID0gb2JqO1xyXG4gICAgICAgIHRoaXMuX2RhdGEueCA9IHBhcnNlSW50KHgpO1xyXG4gICAgICAgIHRoaXMuX2RhdGEueSA9IHBhcnNlSW50KHkpO1xyXG4gICAgICAgIHRoaXMucnVuKCk7XHJcbiAgICB9XHJcblxyXG4gICAgZ2V0KGlkKSB7XHJcbiAgICAgICAgcmV0dXJuIHRoaXMuX2RhdGFbaWRdIHx8ICcnXHJcbiAgICB9XHJcblxyXG4gICAgcnVuKCkge1xyXG4gICAgICAgIGxldCBmb3JtdWxhSHRtbCA9IHRoaXMuY29udGVudCgpO1xyXG4gICAgICAgIGxldCBjZWxsID0gJChgdGRbZGF0YS14PSR7dGhpcy5nZXQoJ3gnKSB8fCAwfV1bZGF0YS15PSR7dGhpcy5nZXQoJ3knKSB8fCAwfV1gKTtcclxuICAgICAgICBuZXcgUG9wdXAoZm9ybXVsYUh0bWwsIGNlbGwpO1xyXG4gICAgICAgIGZvcm11bGFIdG1sLm9uKCdjbGljaycsICcudmktd2JlLWFwcGx5LWZvcm11bGEnLCB0aGlzLmFwcGx5Rm9ybXVsYS5iaW5kKHRoaXMpKTtcclxuICAgICAgICBmb3JtdWxhSHRtbC5vbignY2hhbmdlJywgJy52aS13YmUtcm91bmRlZCcsIHRoaXMudG9nZ2xlRGVjaW1hbFZhbHVlKTtcclxuICAgIH1cclxuXHJcbiAgICBjb250ZW50KCkge1xyXG4gICAgICAgIHJldHVybiAkKGA8ZGl2IGNsYXNzPVwidmktd2JlLWZvcm11bGEtY29udGFpbmVyXCIgc3R5bGU9XCJkaXNwbGF5OiBmbGV4O1wiPlxyXG4gICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktd2JlLW9wZXJhdG9yIHZpLXVpIGJ1dHRvbiBiYXNpYyBzbWFsbCBpY29uXCI+PGkgY2xhc3M9XCJpY29uIG1pbnVzXCI+IDwvaT48L3NwYW4+XHJcbiAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJudW1iZXJcIiBtaW49XCIwXCIgY2xhc3M9XCJ2aS13YmUtdmFsdWVcIj5cclxuICAgICAgICAgICAgICAgICAgICA8c2VsZWN0IGNsYXNzPVwidmktd2JlLXVuaXRcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT1cInBlcmNlbnRhZ2VcIj4lPC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxvcHRpb24gdmFsdWU9XCJmaXhlZFwiPm48L29wdGlvbj5cclxuICAgICAgICAgICAgICAgICAgICA8L3NlbGVjdD5cclxuICAgICAgICAgICAgICAgICAgICA8c2VsZWN0IGNsYXNzPVwidmktd2JlLXJvdW5kZWRcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT1cIm5vX3JvdW5kXCI+JHtfZi50ZXh0KCdObyByb3VuZCcpfTwvb3B0aW9uPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPVwicm91bmRcIj4ke19mLnRleHQoJ1JvdW5kIHdpdGggZGVjaW1hbCcpfTwvb3B0aW9uPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPVwicm91bmRfdXBcIj4ke19mLnRleHQoJ1JvdW5kIHVwJyl9PC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxvcHRpb24gdmFsdWU9XCJyb3VuZF9kb3duXCI+JHtfZi50ZXh0KCdSb3VuZCBkb3duJyl9PC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgPC9zZWxlY3Q+XHJcbiAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJudW1iZXJcIiBtaW49XCIwXCIgbWF4PVwiMTBcIiBjbGFzcz1cInZpLXdiZS1kZWNpbWFsXCIgdmFsdWU9XCIwXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJ2aS11aSBidXR0b24gbWluaSB2aS13YmUtYXBwbHktZm9ybXVsYVwiPiR7X2YudGV4dCgnT0snKX08L2J1dHRvbj5cclxuICAgICAgICAgICAgICAgIDwvZGl2PmApO1xyXG4gICAgfVxyXG5cclxuICAgIGFwcGx5Rm9ybXVsYShlKSB7XHJcbiAgICAgICAgbGV0IGZvcm0gPSAkKGUudGFyZ2V0KS5jbG9zZXN0KCcudmktd2JlLWZvcm11bGEtY29udGFpbmVyJyksXHJcbiAgICAgICAgICAgIGZWYWx1ZSA9IHBhcnNlRmxvYXQoZm9ybS5maW5kKCcudmktd2JlLXZhbHVlJykudmFsKCkpLFxyXG4gICAgICAgICAgICB1bml0ID0gZm9ybS5maW5kKCcudmktd2JlLXVuaXQnKS52YWwoKSxcclxuICAgICAgICAgICAgcm91bmRlZCA9IGZvcm0uZmluZCgnLnZpLXdiZS1yb3VuZGVkJykudmFsKCksXHJcbiAgICAgICAgICAgIGRlY2ltYWwgPSBwYXJzZUludChmb3JtLmZpbmQoJy52aS13YmUtZGVjaW1hbCcpLnZhbCgpKSxcclxuICAgICAgICAgICAgZXhjZWxPYmogPSB0aGlzLmdldCgnamV4Y2VsJyk7XHJcblxyXG4gICAgICAgIGlmICghZlZhbHVlKSByZXR1cm47XHJcblxyXG4gICAgICAgIGxldCBicmVha0NvbnRyb2wgPSBmYWxzZSwgcmVjb3JkcyA9IFtdO1xyXG4gICAgICAgIGxldCBoID0gZXhjZWxPYmouc2VsZWN0ZWRDb250YWluZXI7XHJcbiAgICAgICAgbGV0IHN0YXJ0ID0gaFsxXSwgZW5kID0gaFszXSwgeCA9IGhbMF07XHJcblxyXG4gICAgICAgIGZ1bmN0aW9uIGZvcm11bGEocmVndWxhclByaWNlKSB7XHJcbiAgICAgICAgICAgIHJlZ3VsYXJQcmljZSA9IHBhcnNlRmxvYXQocmVndWxhclByaWNlKTtcclxuICAgICAgICAgICAgbGV0IGV4dHJhVmFsdWUgPSB1bml0ID09PSAncGVyY2VudGFnZScgPyByZWd1bGFyUHJpY2UgKiBmVmFsdWUgLyAxMDAgOiBmVmFsdWU7XHJcbiAgICAgICAgICAgIGxldCBuZXdWYWx1ZSA9IHJlZ3VsYXJQcmljZSAtIGV4dHJhVmFsdWU7XHJcbiAgICAgICAgICAgIG5ld1ZhbHVlID0gbmV3VmFsdWUgPiAwID8gbmV3VmFsdWUgOiAwO1xyXG5cclxuICAgICAgICAgICAgc3dpdGNoIChyb3VuZGVkKSB7XHJcbiAgICAgICAgICAgICAgICBjYXNlICdyb3VuZCc6XHJcbiAgICAgICAgICAgICAgICAgICAgbmV3VmFsdWUgPSBuZXdWYWx1ZS50b0ZpeGVkKGRlY2ltYWwpO1xyXG4gICAgICAgICAgICAgICAgICAgIGJyZWFrO1xyXG4gICAgICAgICAgICAgICAgY2FzZSAncm91bmRfdXAnOlxyXG4gICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlID0gTWF0aC5jZWlsKG5ld1ZhbHVlKTtcclxuICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuICAgICAgICAgICAgICAgIGNhc2UgJ3JvdW5kX2Rvd24nOlxyXG4gICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlID0gTWF0aC5mbG9vcihuZXdWYWx1ZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgcmV0dXJuIG5ld1ZhbHVlO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgZm9yIChsZXQgeSA9IHN0YXJ0OyB5IDw9IGVuZDsgeSsrKSB7XHJcbiAgICAgICAgICAgIGlmIChleGNlbE9iai5yZWNvcmRzW3ldW3hdICYmICFleGNlbE9iai5yZWNvcmRzW3ldW3hdLmNsYXNzTGlzdC5jb250YWlucygncmVhZG9ubHknKSAmJiBleGNlbE9iai5yZWNvcmRzW3ldW3hdLnN0eWxlLmRpc3BsYXkgIT09ICdub25lJyAmJiBicmVha0NvbnRyb2wgPT09IGZhbHNlKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgdmFsdWUgPSBleGNlbE9iai5vcHRpb25zLmRhdGFbeV1beCAtIDFdIHx8IDA7XHJcbiAgICAgICAgICAgICAgICByZWNvcmRzLnB1c2goZXhjZWxPYmoudXBkYXRlQ2VsbCh4LCB5LCBmb3JtdWxhKHZhbHVlKSkpO1xyXG4gICAgICAgICAgICAgICAgZXhjZWxPYmoudXBkYXRlRm9ybXVsYUNoYWluKHgsIHksIHJlY29yZHMpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAvLyBVcGRhdGUgaGlzdG9yeVxyXG4gICAgICAgIGV4Y2VsT2JqLnNldEhpc3Rvcnkoe1xyXG4gICAgICAgICAgICBhY3Rpb246ICdzZXRWYWx1ZScsXHJcbiAgICAgICAgICAgIHJlY29yZHM6IHJlY29yZHMsXHJcbiAgICAgICAgICAgIHNlbGVjdGlvbjogZXhjZWxPYmouc2VsZWN0ZWRDZWxsLFxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBVcGRhdGUgdGFibGUgd2l0aCBjdXN0b20gY29uZmlndXJhdGlvbiBpZiBhcHBsaWNhYmxlXHJcbiAgICAgICAgZXhjZWxPYmoudXBkYXRlVGFibGUoKTtcclxuICAgIH1cclxuXHJcbiAgICB0b2dnbGVEZWNpbWFsVmFsdWUoKSB7XHJcbiAgICAgICAgbGV0IGZvcm0gPSAkKHRoaXMpLmNsb3Nlc3QoJy52aS13YmUtZm9ybXVsYS1jb250YWluZXInKTtcclxuICAgICAgICBmb3JtLmZpbmQoJy52aS13YmUtZGVjaW1hbCcpLmhpZGUoKTtcclxuICAgICAgICBpZiAoJCh0aGlzKS52YWwoKSA9PT0gJ3JvdW5kJykgZm9ybS5maW5kKCcudmktd2JlLWRlY2ltYWwnKS5zaG93KCk7XHJcbiAgICB9XHJcbn1cclxuXHJcbi8vIGV4cG9ydCBkZWZhdWx0IENhbGN1bGF0b3I7IiwiaW1wb3J0IF9mIGZyb20gXCIuL2Z1bmN0aW9uc1wiO1xyXG5pbXBvcnQge0F0dHJpYnV0ZXN9IGZyb20gXCIuL2F0dHJpYnV0ZXNcIjtcclxuaW1wb3J0IFRlbXBsYXRlcyBmcm9tIFwiLi90ZW1wbGF0ZXNcIjtcclxuXHJcbmNvbnN0IGN1c3RvbUNvbHVtbiA9IHt9O1xyXG5jb25zdCBjb2x1bW5GaWx0ZXIgPSB7fTtcclxuXHJcbmpRdWVyeShkb2N1bWVudCkucmVhZHkoZnVuY3Rpb24gKCQpIHtcclxuICAgIHdpbmRvdy52aUlzRWRpdGluZyA9IGZhbHNlO1xyXG4gICAgY29uc3QgbWVkaWFNdWx0aXBsZSA9IHdwLm1lZGlhKHttdWx0aXBsZTogdHJ1ZX0pO1xyXG4gICAgY29uc3QgbWVkaWFTaW5nbGUgPSB3cC5tZWRpYSh7bXVsdGlwbGU6IGZhbHNlfSk7XHJcblxyXG4gICAgY29uc3QgdG1wbCA9IHtcclxuICAgICAgICBnYWxsZXJ5SW1hZ2Uoc3JjLCBpZCkge1xyXG4gICAgICAgICAgICByZXR1cm4gYDxsaSBjbGFzcz1cInZpLXdiZS1nYWxsZXJ5LWltYWdlXCIgZGF0YS1pZD1cIiR7aWR9XCI+PGkgY2xhc3M9XCJ2aS13YmUtcmVtb3ZlLWltYWdlIGRhc2hpY29ucyBkYXNoaWNvbnMtbm8tYWx0XCI+IDwvaT48aW1nIHNyYz1cIiR7c3JjfVwiPjwvbGk+YDtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBmaWxlRG93bmxvYWQoJF9maWxlID0ge30pIHtcclxuICAgICAgICAgICAgbGV0IHtpZCwgbmFtZSwgZmlsZX0gPSAkX2ZpbGU7XHJcbiAgICAgICAgICAgIGxldCByb3cgPSAkKGA8dHI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDx0ZD48aSBjbGFzcz1cImJhcnMgaWNvblwiPjwvaT48aW5wdXQgdHlwZT1cInRleHRcIiBjbGFzcz1cInZpLXdiZS1maWxlLW5hbWVcIiB2YWx1ZT1cIiR7bmFtZSB8fCAnJ31cIj48L3RkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8dGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZT1cInRleHRcIiBjbGFzcz1cInZpLXdiZS1maWxlLXVybFwiIHZhbHVlPVwiJHtmaWxlIHx8ICcnfVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJoaWRkZW5cIiBjbGFzcz1cInZpLXdiZS1maWxlLWhhc2hcIiB2YWx1ZT1cIiR7aWQgfHwgJyd9XCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpLXVpIGJ1dHRvbiBtaW5pIHZpLXdiZS1jaG9vc2UtZmlsZVwiPiR7X2YudGV4dCgnQ2hvb3NlIGZpbGUnKX08L3NwYW4+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzcz1cInZpLXdiZS1yZW1vdmUtZmlsZSBkYXNoaWNvbnMgZGFzaGljb25zLW5vLWFsdFwiPiA8L2k+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDwvdGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgPC90cj5gKTtcclxuXHJcbiAgICAgICAgICAgIHJvdy5vbignY2xpY2snLCAnLnZpLXdiZS1yZW1vdmUtZmlsZScsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIHJvdy5yZW1vdmUoKTtcclxuICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICByZXR1cm4gcm93O1xyXG4gICAgICAgIH1cclxuICAgIH07XHJcblxyXG4gICAgY3VzdG9tQ29sdW1uLnRleHRFZGl0b3IgPSB7XHJcbiAgICAgICAgdHlwZTogJ3RleHRFZGl0b3InLFxyXG5cclxuICAgICAgICBjcmVhdGVDZWxsKGNlbGwsIGksIHZhbHVlLCBvYmopIHtcclxuICAgICAgICAgICAgY2VsbC5pbm5lckhUTUwgPSBfZi5zdHJpcEh0bWwodmFsdWUpLnNsaWNlKDAsIDUwKTtcclxuICAgICAgICAgICAgcmV0dXJuIGNlbGw7XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgY2xvc2VFZGl0b3IoY2VsbCwgc2F2ZSkge1xyXG4gICAgICAgICAgICB3aW5kb3cudmlJc0VkaXRpbmcgPSBmYWxzZTtcclxuICAgICAgICAgICAgbGV0IGNvbnRlbnQgPSAnJztcclxuICAgICAgICAgICAgaWYgKHNhdmUgPT09IHRydWUpIHtcclxuICAgICAgICAgICAgICAgIGNvbnRlbnQgPSB3cC5lZGl0b3IuZ2V0Q29udGVudCgndmktd2JlLXRleHQtZWRpdG9yJyk7XHJcblxyXG4gICAgICAgICAgICAgICAgaWYgKCF0aGlzLmlzRWRpdGluZykge1xyXG4gICAgICAgICAgICAgICAgICAgIHdwLmVkaXRvci5yZW1vdmUoJ3ZpLXdiZS10ZXh0LWVkaXRvcicpO1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgdGhpcy5pc0VkaXRpbmcgPSBmYWxzZTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICByZXR1cm4gY29udGVudDtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBvcGVuRWRpdG9yKGNlbGwsIGVsLCBvYmopIHtcclxuICAgICAgICAgICAgd2luZG93LnZpSXNFZGl0aW5nID0gdHJ1ZTtcclxuICAgICAgICAgICAgbGV0IHkgPSBjZWxsLmdldEF0dHJpYnV0ZSgnZGF0YS15JyksXHJcbiAgICAgICAgICAgICAgICB4ID0gY2VsbC5nZXRBdHRyaWJ1dGUoJ2RhdGEteCcpLFxyXG4gICAgICAgICAgICAgICAgY29udGVudCA9IG9iai5vcHRpb25zLmRhdGFbeV1beF0sXHJcbiAgICAgICAgICAgICAgICAkdGhpcyA9IHRoaXMsXHJcbiAgICAgICAgICAgICAgICBtb2RhbENsb3NlID0gJCgnLnZpLXVpLm1vZGFsIC5jbG9zZS5pY29uJyk7XHJcblxyXG4gICAgICAgICAgICAkKCcudmktdWkubW9kYWwnKS5tb2RhbCgnc2hvdycpO1xyXG4gICAgICAgICAgICB0aGlzLnRpbnltY2VJbml0KGNvbnRlbnQpO1xyXG5cclxuICAgICAgICAgICAgbW9kYWxDbG9zZS5vZmYoJ2NsaWNrJyk7XHJcblxyXG4gICAgICAgICAgICAkKCcudmktd2JlLXRleHQtZWRpdG9yLXNhdmUnKS5vZmYoJ2NsaWNrJykub24oJ2NsaWNrJywgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAgICAgJCh0aGlzKS5yZW1vdmVDbGFzcygncHJpbWFyeScpO1xyXG4gICAgICAgICAgICAgICAgaWYgKCQodGhpcykuaGFzQ2xhc3MoJ3ZpLXdiZS1jbG9zZScpKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgJCgnLnZpLXVpLm1vZGFsJykubW9kYWwoJ2hpZGUnKTtcclxuICAgICAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgJHRoaXMuaXNFZGl0aW5nID0gdHJ1ZTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIG9iai5jbG9zZUVkaXRvcihjZWxsLCB0cnVlKTtcclxuICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICBtb2RhbENsb3NlLm9uKCdjbGljaycsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIG9iai5jbG9zZUVkaXRvcihjZWxsLCBmYWxzZSk7XHJcbiAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgbGV0IG1vZGFsID0gJCgnLnZpLXVpLm1vZGFsJykucGFyZW50KCk7XHJcbiAgICAgICAgICAgIG1vZGFsLm9uKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgICAgICBpZiAoZS50YXJnZXQgPT09IGUuZGVsZWdhdGVUYXJnZXQpIHtcclxuICAgICAgICAgICAgICAgICAgICBvYmouY2xvc2VFZGl0b3IoY2VsbCwgZmFsc2UpO1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KVxyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIHVwZGF0ZUNlbGwoY2VsbCwgdmFsdWUsIGZvcmNlKSB7XHJcbiAgICAgICAgICAgIGNlbGwuaW5uZXJIVE1MID0gX2Yuc3RyaXBIdG1sKHZhbHVlKS5zbGljZSgwLCA1MCk7XHJcbiAgICAgICAgICAgIHJldHVybiB2YWx1ZTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICB0aW55bWNlSW5pdChjb250ZW50ID0gJycpIHtcclxuICAgICAgICAgICAgY29udGVudCA9IHdwLmVkaXRvci5hdXRvcChjb250ZW50KTtcclxuICAgICAgICAgICAgaWYgKHRpbnltY2UuZ2V0KCd2aS13YmUtdGV4dC1lZGl0b3InKSA9PT0gbnVsbCkge1xyXG4gICAgICAgICAgICAgICAgJCgnI3ZpLXdiZS10ZXh0LWVkaXRvcicpLnZhbChjb250ZW50KTtcclxuXHJcbiAgICAgICAgICAgICAgICBBdHRyaWJ1dGVzLnRpbnlNY2VPcHRpb25zLnRpbnltY2Uuc2V0dXAgPSBmdW5jdGlvbiAoZWRpdG9yKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgZWRpdG9yLm9uKCdrZXl1cCcsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICQoJy52aS13YmUtdGV4dC1lZGl0b3Itc2F2ZTpub3QoLnZpLXdiZS1jbG9zZSknKS5hZGRDbGFzcygncHJpbWFyeScpO1xyXG4gICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgfTtcclxuXHJcbiAgICAgICAgICAgICAgICB3cC5lZGl0b3IuaW5pdGlhbGl6ZSgndmktd2JlLXRleHQtZWRpdG9yJywgQXR0cmlidXRlcy50aW55TWNlT3B0aW9ucyk7XHJcblxyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICB0aW55bWNlLmdldCgndmktd2JlLXRleHQtZWRpdG9yJykuc2V0Q29udGVudChjb250ZW50KVxyXG4gICAgICAgIH0sXHJcbiAgICB9O1xyXG5cclxuICAgIGN1c3RvbUNvbHVtbi5pbWFnZSA9IHtcclxuICAgICAgICBjcmVhdGVDZWxsKGNlbGwsIGksIHZhbHVlLCBvYmopIHtcclxuICAgICAgICAgICAgaWYgKHZhbHVlKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgdXJsID0gQXR0cmlidXRlcy5pbWdTdG9yYWdlW3ZhbHVlXTtcclxuICAgICAgICAgICAgICAgIF9mLmlzVXJsKHVybCkgPyAkKGNlbGwpLmh0bWwoYDxpbWcgd2lkdGg9XCI0MFwiIHNyYz1cIiR7dXJsfVwiIGRhdGEtaWQ9XCIke3ZhbHVlfVwiPmApIDogJChjZWxsKS5odG1sKCcnKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICByZXR1cm4gY2VsbDtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBjbG9zZUVkaXRvcihjZWxsLCBzYXZlKSB7XHJcbiAgICAgICAgICAgIHJldHVybiAkKGNlbGwpLmZpbmQoJ2ltZycpLmF0dHIoJ2RhdGEtaWQnKSB8fCAnJztcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBvcGVuRWRpdG9yKGNlbGwsIGVsLCBvYmopIHtcclxuICAgICAgICAgICAgZnVuY3Rpb24gb3Blbk1lZGlhKCkge1xyXG4gICAgICAgICAgICAgICAgbWVkaWFTaW5nbGUub3BlbigpLm9mZignc2VsZWN0Jykub24oJ3NlbGVjdCcsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IHVwbG9hZGVkSW1hZ2VzID0gbWVkaWFTaW5nbGUuc3RhdGUoKS5nZXQoJ3NlbGVjdGlvbicpLmZpcnN0KCk7XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IHNlbGVjdGVkSW1hZ2VzID0gdXBsb2FkZWRJbWFnZXMudG9KU09OKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKF9mLmlzVXJsKHNlbGVjdGVkSW1hZ2VzLnVybCkpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgJChjZWxsKS5odG1sKGA8aW1nIHdpZHRoPVwiNDBcIiBzcmM9XCIke3NlbGVjdGVkSW1hZ2VzLnVybH1cIiBkYXRhLWlkPVwiJHtzZWxlY3RlZEltYWdlcy5pZH1cIj5gKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgQXR0cmlidXRlcy5pbWdTdG9yYWdlW3NlbGVjdGVkSW1hZ2VzLmlkXSA9IHNlbGVjdGVkSW1hZ2VzLnVybDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgb2JqLmNsb3NlRWRpdG9yKGNlbGwsIHRydWUpO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAkKGNlbGwpLm9uKCdkYmxjbGljaycsIG9wZW5NZWRpYSk7XHJcblxyXG4gICAgICAgICAgICBvcGVuTWVkaWEoKTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICB1cGRhdGVDZWxsKGNlbGwsIHZhbHVlLCBmb3JjZSkge1xyXG4gICAgICAgICAgICB2YWx1ZSA9IHBhcnNlSW50KHZhbHVlKSB8fCAnJztcclxuICAgICAgICAgICAgbGV0IHVybCA9IEF0dHJpYnV0ZXMuaW1nU3RvcmFnZVt2YWx1ZV07XHJcbiAgICAgICAgICAgIF9mLmlzVXJsKHVybCkgPyAkKGNlbGwpLmh0bWwoYDxpbWcgd2lkdGg9XCI0MFwiIHNyYz1cIiR7dXJsfVwiIGRhdGEtaWQ9XCIke3ZhbHVlfVwiPmApIDogJChjZWxsKS5odG1sKCcnKTtcclxuICAgICAgICAgICAgcmV0dXJuIHZhbHVlO1xyXG4gICAgICAgIH0sXHJcbiAgICB9O1xyXG5cclxuICAgIGN1c3RvbUNvbHVtbi5nYWxsZXJ5ID0ge1xyXG4gICAgICAgIHR5cGU6ICdnYWxsZXJ5JyxcclxuXHJcbiAgICAgICAgc2F2ZURhdGEoY2VsbCkge1xyXG4gICAgICAgICAgICBsZXQgbmV3SWRzID0gW107XHJcbiAgICAgICAgICAgICQoY2VsbCkuZmluZCgnLnZpLXdiZS1nYWxsZXJ5LWltYWdlJykuZWFjaChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICAgICBuZXdJZHMucHVzaCgkKHRoaXMpLmRhdGEoJ2lkJykpO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgJChjZWxsKS5maW5kKCcudmktd2JlLWlkcy1saXN0JykudmFsKG5ld0lkcy5qb2luKCcsJykpO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIGNyZWF0ZUNlbGwoY2VsbCwgaSwgdmFsdWUpIHtcclxuICAgICAgICAgICAgbGV0IGhhc0l0ZW0gPSB2YWx1ZS5sZW5ndGggPyAndmktd2JlLWdhbGxlcnktaGFzLWl0ZW0nIDogJyc7XHJcbiAgICAgICAgICAgICQoY2VsbCkuYWRkQ2xhc3MoJ3ZpLXdiZS1nYWxsZXJ5Jyk7XHJcbiAgICAgICAgICAgICQoY2VsbCkuaHRtbChgPGRpdiBjbGFzcz1cInZpLXdiZS1nYWxsZXJ5ICR7aGFzSXRlbX1cIj48aSBjbGFzcz1cImltYWdlcyBvdXRsaW5lIGljb25cIj4gPC9pPjwvZGl2PmApO1xyXG4gICAgICAgICAgICByZXR1cm4gY2VsbDtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBjbG9zZUVkaXRvcihjZWxsLCBzYXZlKSB7XHJcbiAgICAgICAgICAgIHdpbmRvdy52aUlzRWRpdGluZyA9IGZhbHNlO1xyXG5cclxuICAgICAgICAgICAgbGV0IHNlbGVjdGVkID0gW107XHJcbiAgICAgICAgICAgIGlmIChzYXZlKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgY2hpbGQgPSAkKGNlbGwpLmNoaWxkcmVuKCk7XHJcbiAgICAgICAgICAgICAgICBjaGlsZC5maW5kKCcudmktd2JlLWdhbGxlcnktaW1hZ2UnKS5lYWNoKGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgICAgICBzZWxlY3RlZC5wdXNoKCQodGhpcykuZGF0YSgnaWQnKSk7XHJcbiAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAkKGNlbGwpLmZpbmQoJy52aS13YmUtY2VsbC1wb3B1cCcpLnJlbW92ZSgpO1xyXG4gICAgICAgICAgICByZXR1cm4gc2VsZWN0ZWQ7XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgb3BlbkVkaXRvcihjZWxsLCBlbCwgb2JqKSB7XHJcbiAgICAgICAgICAgIHdpbmRvdy52aUlzRWRpdGluZyA9IHRydWU7XHJcblxyXG4gICAgICAgICAgICBsZXQgeSA9IGNlbGwuZ2V0QXR0cmlidXRlKCdkYXRhLXknKSxcclxuICAgICAgICAgICAgICAgIHggPSBjZWxsLmdldEF0dHJpYnV0ZSgnZGF0YS14Jyk7XHJcblxyXG4gICAgICAgICAgICBsZXQgaWRzID0gb2JqLm9wdGlvbnMuZGF0YVt5XVt4XSxcclxuICAgICAgICAgICAgICAgIGltYWdlcyA9ICcnLCBjYWNoZUVkaXRpb247XHJcblxyXG4gICAgICAgICAgICBpZiAoaWRzLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgZm9yIChsZXQgaWQgb2YgaWRzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IHNyYyA9IEF0dHJpYnV0ZXMuaW1nU3RvcmFnZVtpZF07XHJcbiAgICAgICAgICAgICAgICAgICAgaW1hZ2VzICs9IHRtcGwuZ2FsbGVyeUltYWdlKHNyYywgaWQpO1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICBsZXQgZ2FsbGVyeVBvcHVwID0gJChgPGRpdiBjbGFzcz1cInZpLXdiZS1jZWxsLXBvcHVwLWlubmVyXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx1bCBjbGFzcz1cInZpLXdiZS1nYWxsZXJ5LWltYWdlc1wiPiR7aW1hZ2VzfTwvdWw+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIHRpbnkgdmktd2JlLWFkZC1pbWFnZVwiPiR7X2YudGV4dCgnQWRkIGltYWdlJyl9PC9zcGFuPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpLXVpIGJ1dHRvbiB0aW55IHZpLXdiZS1yZW1vdmUtZ2FsbGVyeVwiPiR7X2YudGV4dCgnUmVtb3ZlIGFsbCcpfTwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5gKTtcclxuXHJcbiAgICAgICAgICAgIF9mLmNyZWF0ZUVkaXRvcihjZWxsLCAnZGl2JywgZ2FsbGVyeVBvcHVwKTtcclxuXHJcbiAgICAgICAgICAgIGdhbGxlcnlQb3B1cC5maW5kKCcudmktd2JlLWdhbGxlcnktaW1hZ2VzJykuc29ydGFibGUoe1xyXG4gICAgICAgICAgICAgICAgaXRlbXM6ICdsaS52aS13YmUtZ2FsbGVyeS1pbWFnZScsXHJcbiAgICAgICAgICAgICAgICBjdXJzb3I6ICdtb3ZlJyxcclxuICAgICAgICAgICAgICAgIHNjcm9sbFNlbnNpdGl2aXR5OiA0MCxcclxuICAgICAgICAgICAgICAgIGZvcmNlUGxhY2Vob2xkZXJTaXplOiB0cnVlLFxyXG4gICAgICAgICAgICAgICAgZm9yY2VIZWxwZXJTaXplOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgIGhlbHBlcjogJ2Nsb25lJyxcclxuICAgICAgICAgICAgICAgIHBsYWNlaG9sZGVyOiAndmktd2JlLXNvcnRhYmxlLXBsYWNlaG9sZGVyJyxcclxuICAgICAgICAgICAgICAgIHRvbGVyYW5jZTogXCJwb2ludGVyXCIsXHJcbiAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgZ2FsbGVyeVBvcHVwLm9uKCdjbGljaycsICcudmktd2JlLXJlbW92ZS1pbWFnZScsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgICQodGhpcykucGFyZW50KCkucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgZ2FsbGVyeVBvcHVwLm9uKCdjbGljaycsICcudmktd2JlLWFkZC1pbWFnZScsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIG1lZGlhTXVsdGlwbGUub3BlbigpLm9mZignc2VsZWN0IGNsb3NlJylcclxuICAgICAgICAgICAgICAgICAgICAub24oJ3NlbGVjdCcsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhciBzZWxlY3Rpb24gPSBtZWRpYU11bHRpcGxlLnN0YXRlKCkuZ2V0KCdzZWxlY3Rpb24nKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZWN0aW9uLmVhY2goZnVuY3Rpb24gKGF0dGFjaG1lbnQpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGF0dGFjaG1lbnQgPSBhdHRhY2htZW50LnRvSlNPTigpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGF0dGFjaG1lbnQudHlwZSA9PT0gJ2ltYWdlJykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIEF0dHJpYnV0ZXMuaW1nU3RvcmFnZVthdHRhY2htZW50LmlkXSA9IGF0dGFjaG1lbnQudXJsO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGdhbGxlcnlQb3B1cC5maW5kKCcudmktd2JlLWdhbGxlcnktaW1hZ2VzJykuYXBwZW5kKHRtcGwuZ2FsbGVyeUltYWdlKGF0dGFjaG1lbnQudXJsLCBhdHRhY2htZW50LmlkKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgIGdhbGxlcnlQb3B1cC5vbignY2xpY2snLCAnLnZpLXdiZS1yZW1vdmUtZ2FsbGVyeScsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIGdhbGxlcnlQb3B1cC5maW5kKCcudmktd2JlLWdhbGxlcnktaW1hZ2VzJykuZW1wdHkoKTtcclxuICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICBpZiAoaWRzLmxlbmd0aCA9PT0gMCkge1xyXG4gICAgICAgICAgICAgICAgZ2FsbGVyeVBvcHVwLmZpbmQoJy52aS13YmUtYWRkLWltYWdlJykudHJpZ2dlcignY2xpY2snKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIHVwZGF0ZUNlbGwoY2VsbCwgdmFsdWUsIGZvcmNlKSB7XHJcbiAgICAgICAgICAgIGxldCBpY29uID0gJChjZWxsKS5maW5kKCcudmktd2JlLWdhbGxlcnknKTtcclxuICAgICAgICAgICAgdmFsdWUubGVuZ3RoID8gaWNvbi5hZGRDbGFzcygndmktd2JlLWdhbGxlcnktaGFzLWl0ZW0nKSA6IGljb24ucmVtb3ZlQ2xhc3MoJ3ZpLXdiZS1nYWxsZXJ5LWhhcy1pdGVtJyk7XHJcbiAgICAgICAgICAgIHJldHVybiB2YWx1ZTtcclxuICAgICAgICB9LFxyXG4gICAgfTtcclxuXHJcbiAgICBjdXN0b21Db2x1bW4uZG93bmxvYWQgPSB7XHJcbiAgICAgICAgY3JlYXRlQ2VsbChjZWxsLCBpLCB2YWx1ZSkge1xyXG4gICAgICAgICAgICAkKGNlbGwpLmh0bWwoYDxkaXY+PGkgY2xhc3M9XCJkb3dubG9hZCBpY29uXCI+IDwvaT48L2Rpdj5gKTtcclxuICAgICAgICAgICAgcmV0dXJuIGNlbGw7XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgY2xvc2VFZGl0b3IoY2VsbCwgc2F2ZSkge1xyXG4gICAgICAgICAgICBsZXQgZGF0YSA9IFtdO1xyXG4gICAgICAgICAgICBpZiAoc2F2ZSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IGNoaWxkID0gJChjZWxsKS5jaGlsZHJlbigpO1xyXG4gICAgICAgICAgICAgICAgY2hpbGQuZmluZCgndGFibGUudmktd2JlLWZpbGVzLWRvd25sb2FkIHRib2R5IHRyJykuZWFjaChmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IHJvdyA9ICQodGhpcyk7XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YS5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWQ6IHJvdy5maW5kKCcudmktd2JlLWZpbGUtaGFzaCcpLnZhbCgpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBmaWxlOiByb3cuZmluZCgnLnZpLXdiZS1maWxlLXVybCcpLnZhbCgpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBuYW1lOiByb3cuZmluZCgnLnZpLXdiZS1maWxlLW5hbWUnKS52YWwoKVxyXG4gICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgY2hpbGQucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgcmV0dXJuIGRhdGE7XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgb3BlbkVkaXRvcihjZWxsLCBlbCwgb2JqKSB7XHJcblxyXG4gICAgICAgICAgICBsZXQgeSA9IGNlbGwuZ2V0QXR0cmlidXRlKCdkYXRhLXknKSxcclxuICAgICAgICAgICAgICAgIHggPSBjZWxsLmdldEF0dHJpYnV0ZSgnZGF0YS14Jyk7XHJcblxyXG4gICAgICAgICAgICBsZXQgZmlsZXMgPSBvYmoub3B0aW9ucy5kYXRhW3ldW3hdLFxyXG4gICAgICAgICAgICAgICAgY2FjaGVFZGl0aW9uLCB0Ym9keSA9ICQoJzx0Ym9keT48L3Rib2R5PicpO1xyXG5cclxuICAgICAgICAgICAgaWYgKEFycmF5LmlzQXJyYXkoZmlsZXMpKSB7XHJcbiAgICAgICAgICAgICAgICBmb3IgKGxldCBmaWxlIG9mIGZpbGVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgdGJvZHkuYXBwZW5kKHRtcGwuZmlsZURvd25sb2FkKGZpbGUpKTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgbGV0IGZpbGVEb3dubG9hZFBvcHVwID0gJChgPGRpdiBjbGFzcz1cIlwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRhYmxlIGNsYXNzPVwidmktd2JlLWZpbGVzLWRvd25sb2FkIHZpLXVpIGNlbGxlZCB0YWJsZVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0aGVhZD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dHI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0aD4ke19mLnRleHQoJ05hbWUnKX08L3RoPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGg+JHtfZi50ZXh0KCdGaWxlIFVSTCcpfTwvdGg+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RoZWFkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIHRpbnkgdmktd2JlLWFkZC1maWxlXCI+JHtfZi50ZXh0KCdBZGQgZmlsZScpfTwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+YCk7XHJcblxyXG4gICAgICAgICAgICBmaWxlRG93bmxvYWRQb3B1cC5maW5kKCcudmktd2JlLWZpbGVzLWRvd25sb2FkJykuYXBwZW5kKHRib2R5KTtcclxuXHJcbiAgICAgICAgICAgIF9mLmNyZWF0ZUVkaXRvcihjZWxsLCAnZGl2JywgZmlsZURvd25sb2FkUG9wdXApO1xyXG5cclxuICAgICAgICAgICAgdGJvZHkuc29ydGFibGUoKTtcclxuXHJcbiAgICAgICAgICAgIGZpbGVEb3dubG9hZFBvcHVwLm9uKCdjbGljaycsICcudmktd2JlLWFkZC1maWxlJywgKCkgPT4gZmlsZURvd25sb2FkUG9wdXAuZmluZCgnLnZpLXdiZS1maWxlcy1kb3dubG9hZCB0Ym9keScpLmFwcGVuZCh0bXBsLmZpbGVEb3dubG9hZCgpKSk7XHJcblxyXG4gICAgICAgICAgICBmaWxlRG93bmxvYWRQb3B1cC5vbignY2xpY2snLCAnLnZpLXdiZS1jaG9vc2UtZmlsZScsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIGNhY2hlRWRpdGlvbiA9IG9iai5lZGl0aW9uO1xyXG4gICAgICAgICAgICAgICAgb2JqLmVkaXRpb24gPSBudWxsO1xyXG4gICAgICAgICAgICAgICAgbGV0IHJvdyA9ICQodGhpcykuY2xvc2VzdCgndHInKTtcclxuXHJcbiAgICAgICAgICAgICAgICBtZWRpYVNpbmdsZS5vcGVuKCkub2ZmKCdzZWxlY3QgY2xvc2UnKVxyXG4gICAgICAgICAgICAgICAgICAgIC5vbignc2VsZWN0JywgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHNlbGVjdGVkID0gbWVkaWFTaW5nbGUuc3RhdGUoKS5nZXQoJ3NlbGVjdGlvbicpLmZpcnN0KCkudG9KU09OKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChzZWxlY3RlZC51cmwpIHJvdy5maW5kKCcudmktd2JlLWZpbGUtdXJsJykudmFsKHNlbGVjdGVkLnVybCkudHJpZ2dlcignY2hhbmdlJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgfSlcclxuICAgICAgICAgICAgICAgICAgICAub24oJ2Nsb3NlJywgKCkgPT4gb2JqLmVkaXRpb24gPSBjYWNoZUVkaXRpb24pO1xyXG4gICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgIGlmICghZmlsZXMubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgICAgICBmaWxlRG93bmxvYWRQb3B1cC5maW5kKCcudmktd2JlLWFkZC1maWxlJykudHJpZ2dlcignY2xpY2snKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIHVwZGF0ZUNlbGwoY2VsbCwgdmFsdWUsIGZvcmNlKSB7XHJcbiAgICAgICAgICAgICQoY2VsbCkuaHRtbChgPGRpdj48aSBjbGFzcz1cImRvd25sb2FkIGljb25cIj4gPC9pPjwvZGl2PmApO1xyXG4gICAgICAgICAgICByZXR1cm4gdmFsdWU7XHJcbiAgICAgICAgfSxcclxuICAgIH07XHJcblxyXG4gICAgY3VzdG9tQ29sdW1uLnRhZ3MgPSB7XHJcbiAgICAgICAgdHlwZTogJ3RhZ3MnLFxyXG4gICAgICAgIGNyZWF0ZUNlbGwoY2VsbCwgaSwgdmFsdWUsIG9iaikge1xyXG4gICAgICAgICAgICBfZi5mb3JtYXRUZXh0KGNlbGwsIHZhbHVlKTtcclxuICAgICAgICAgICAgcmV0dXJuIGNlbGw7XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgb3BlbkVkaXRvcihjZWxsLCBlbCwgb2JqKSB7XHJcbiAgICAgICAgICAgIGxldCB5ID0gY2VsbC5nZXRBdHRyaWJ1dGUoJ2RhdGEteScpLFxyXG4gICAgICAgICAgICAgICAgeCA9IGNlbGwuZ2V0QXR0cmlidXRlKCdkYXRhLXgnKTtcclxuXHJcbiAgICAgICAgICAgIGxldCB2YWx1ZSA9IG9iai5vcHRpb25zLmRhdGFbeV1beF0sXHJcbiAgICAgICAgICAgICAgICBzZWxlY3QgPSAkKCc8c2VsZWN0Lz4nKSxcclxuICAgICAgICAgICAgICAgIGVkaXRvciA9IF9mLmNyZWF0ZUVkaXRvcihjZWxsLCAnZGl2Jywgc2VsZWN0KTtcclxuXHJcbiAgICAgICAgICAgIHNlbGVjdC5zZWxlY3QyKHtcclxuICAgICAgICAgICAgICAgIGRhdGE6IHZhbHVlLFxyXG4gICAgICAgICAgICAgICAgbXVsdGlwbGU6IHRydWUsXHJcbiAgICAgICAgICAgICAgICBtaW5pbXVtSW5wdXRMZW5ndGg6IDMsXHJcbiAgICAgICAgICAgICAgICBwbGFjZWhvbGRlcjogX2YudGV4dCgnU2VhcmNoIHRhZ3MuLi4nKSxcclxuICAgICAgICAgICAgICAgIGFqYXg6IHtcclxuICAgICAgICAgICAgICAgICAgICB1cmw6IEF0dHJpYnV0ZXMuYWpheFVybCxcclxuICAgICAgICAgICAgICAgICAgICB0eXBlOiAncG9zdCcsXHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YTogZnVuY3Rpb24gKHBhcmFtcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4ge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgLi4uQXR0cmlidXRlcy5hamF4RGF0YSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Yl9hY3Rpb246ICdzZWFyY2hfdGFncycsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWFyY2g6IHBhcmFtcy50ZXJtLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ3B1YmxpYydcclxuICAgICAgICAgICAgICAgICAgICAgICAgfTtcclxuICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgIHByb2Nlc3NSZXN1bHRzOiBmdW5jdGlvbiAoZGF0YSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4ge3Jlc3VsdHM6IGRhdGF9O1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICBzZWxlY3QuZmluZCgnb3B0aW9uJykuYXR0cignc2VsZWN0ZWQnLCB0cnVlKS5wYXJlbnQoKS50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuXHJcbiAgICAgICAgICAgICQoZWRpdG9yKS5maW5kKCcuc2VsZWN0Mi1zZWFyY2hfX2ZpZWxkJykudHJpZ2dlcignY2xpY2snKTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBjbG9zZUVkaXRvcihjZWxsLCBzYXZlKSB7XHJcbiAgICAgICAgICAgIGxldCBjaGlsZCA9ICQoY2VsbCkuY2hpbGRyZW4oKSxcclxuICAgICAgICAgICAgICAgIGRhdGEgPSBjaGlsZC5maW5kKCdzZWxlY3QnKS5zZWxlY3QyKCdkYXRhJyksXHJcbiAgICAgICAgICAgICAgICBzZWxlY3RlZCA9IFtdO1xyXG5cclxuICAgICAgICAgICAgaWYgKGRhdGEubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgICAgICBmb3IgKGxldCBpdGVtIG9mIGRhdGEpIHtcclxuICAgICAgICAgICAgICAgICAgICBzZWxlY3RlZC5wdXNoKHtpZDogaXRlbS5pZCwgdGV4dDogaXRlbS50ZXh0fSlcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICBjaGlsZC5yZW1vdmUoKTtcclxuICAgICAgICAgICAgJCgnLnNlbGVjdDItY29udGFpbmVyJykucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgIHJldHVybiBzZWxlY3RlZDtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICB1cGRhdGVDZWxsKGNlbGwsIHZhbHVlLCBmb3JjZSwgb2JqLCB4KSB7XHJcbiAgICAgICAgICAgIF9mLmZvcm1hdFRleHQoY2VsbCwgdmFsdWUpO1xyXG4gICAgICAgICAgICByZXR1cm4gdmFsdWU7XHJcbiAgICAgICAgfVxyXG4gICAgfTtcclxuXHJcbiAgICBjdXN0b21Db2x1bW4ubGlua19wcm9kdWN0cyA9IHtcclxuICAgICAgICBjcmVhdGVDZWxsKGNlbGwsIGksIHZhbHVlLCBvYmopIHtcclxuICAgICAgICAgICAgX2YuZm9ybWF0VGV4dChjZWxsLCB2YWx1ZSk7XHJcbiAgICAgICAgICAgIHJldHVybiBjZWxsO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIGNsb3NlRWRpdG9yKGNlbGwsIHNhdmUpIHtcclxuICAgICAgICAgICAgbGV0IGNoaWxkID0gJChjZWxsKS5jaGlsZHJlbigpLCBzZWxlY3RlZCA9IFtdO1xyXG5cclxuICAgICAgICAgICAgaWYgKHNhdmUpIHtcclxuICAgICAgICAgICAgICAgIGxldCBkYXRhID0gY2hpbGQuZmluZCgnc2VsZWN0Jykuc2VsZWN0MignZGF0YScpO1xyXG5cclxuICAgICAgICAgICAgICAgIGlmIChkYXRhLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgICAgIGZvciAobGV0IGl0ZW0gb2YgZGF0YSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxlY3RlZC5wdXNoKHtpZDogaXRlbS5pZCwgdGV4dDogaXRlbS50ZXh0fSlcclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGNoaWxkLnJlbW92ZSgpO1xyXG4gICAgICAgICAgICAkKCcuc2VsZWN0Mi1jb250YWluZXInKS5yZW1vdmUoKTtcclxuICAgICAgICAgICAgcmV0dXJuIHNlbGVjdGVkO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIG9wZW5FZGl0b3IoY2VsbCwgZWwsIG9iaikge1xyXG4gICAgICAgICAgICBsZXQgeSA9IGNlbGwuZ2V0QXR0cmlidXRlKCdkYXRhLXknKSxcclxuICAgICAgICAgICAgICAgIHggPSBjZWxsLmdldEF0dHJpYnV0ZSgnZGF0YS14Jyk7XHJcblxyXG4gICAgICAgICAgICBsZXQgdmFsdWUgPSBvYmoub3B0aW9ucy5kYXRhW3ldW3hdLFxyXG4gICAgICAgICAgICAgICAgc2VsZWN0ID0gJCgnPHNlbGVjdC8+Jyk7XHJcblxyXG4gICAgICAgICAgICBsZXQgZWRpdG9yID0gX2YuY3JlYXRlRWRpdG9yKGNlbGwsICdkaXYnLCBzZWxlY3QpO1xyXG5cclxuICAgICAgICAgICAgc2VsZWN0LnNlbGVjdDIoe1xyXG4gICAgICAgICAgICAgICAgZGF0YTogdmFsdWUsXHJcbiAgICAgICAgICAgICAgICBtdWx0aXBsZTogdHJ1ZSxcclxuICAgICAgICAgICAgICAgIG1pbmltdW1JbnB1dExlbmd0aDogMyxcclxuICAgICAgICAgICAgICAgIHBsYWNlaG9sZGVyOiBfZi50ZXh0KCdTZWFyY2ggcHJvZHVjdHMuLi4nKSxcclxuICAgICAgICAgICAgICAgIGFqYXg6IHtcclxuICAgICAgICAgICAgICAgICAgICB1cmw6IEF0dHJpYnV0ZXMuYWpheFVybCxcclxuICAgICAgICAgICAgICAgICAgICB0eXBlOiAncG9zdCcsXHJcbiAgICAgICAgICAgICAgICAgICAgZGVsYXk6IDI1MCxcclxuICAgICAgICAgICAgICAgICAgICBkYXRhVHlwZTogJ2pzb24nLFxyXG4gICAgICAgICAgICAgICAgICAgIGRhdGE6IGZ1bmN0aW9uIChwYXJhbXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIC4uLkF0dHJpYnV0ZXMuYWpheERhdGEsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJfYWN0aW9uOiAnc2VhcmNoX3Byb2R1Y3RzJyxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlYXJjaDogcGFyYW1zLnRlcm0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0eXBlOiAncHVibGljJ1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9O1xyXG4gICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgcHJvY2Vzc1Jlc3VsdHM6IGZ1bmN0aW9uIChkYXRhKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHZhciB0ZXJtcyA9IFtdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZGF0YSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJC5lYWNoKGRhdGEsIGZ1bmN0aW9uIChpZCwgdGV4dCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRlcm1zLnB1c2goe2lkOiBpZCwgdGV4dDogdGV4dH0pO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlc3VsdHM6IHRlcm1zXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH07XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgIHNlbGVjdC5maW5kKCdvcHRpb24nKS5hdHRyKCdzZWxlY3RlZCcsIHRydWUpLnBhcmVudCgpLnRyaWdnZXIoJ2NoYW5nZScpO1xyXG4gICAgICAgICAgICAkKGVkaXRvcikuZmluZCgnLnNlbGVjdDItc2VhcmNoX19maWVsZCcpLnRyaWdnZXIoJ2NsaWNrJyk7XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgdXBkYXRlQ2VsbChjZWxsLCB2YWx1ZSwgZm9yY2UsIG9iaiwgeCkge1xyXG4gICAgICAgICAgICBfZi5mb3JtYXRUZXh0KGNlbGwsIHZhbHVlKTtcclxuICAgICAgICAgICAgcmV0dXJuIHZhbHVlO1xyXG4gICAgICAgIH1cclxuICAgIH07XHJcblxyXG4gICAgY3VzdG9tQ29sdW1uLnByb2R1Y3RfYXR0cmlidXRlcyA9IHtcclxuICAgICAgICB0eXBlOiAncHJvZHVjdF9hdHRyaWJ1dGVzJyxcclxuXHJcbiAgICAgICAgY3JlYXRlQ2VsbChjZWxsLCBpLCB2YWx1ZSwgb2JqKSB7XHJcbiAgICAgICAgICAgICQoY2VsbCkuaHRtbCgnPGkgY2xhc3M9XCJpY29uIGVkaXRcIi8+Jyk7XHJcbiAgICAgICAgICAgIHJldHVybiBjZWxsO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIHVwZGF0ZUNlbGwoY2VsbCwgdmFsdWUsIGZvcmNlLCBvYmosIHgpIHtcclxuICAgICAgICAgICAgcmV0dXJuIHZhbHVlO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIG9wZW5FZGl0b3IoY2VsbCwgZWwsIG9iaikge1xyXG4gICAgICAgICAgICBsZXQgZGF0YSA9IF9mLmdldERhdGFGcm9tQ2VsbChvYmosIGNlbGwpLFxyXG4gICAgICAgICAgICAgICAgcHJvZHVjdFR5cGUgPSBfZi5nZXRQcm9kdWN0VHlwZUZyb21DZWxsKGNlbGwpLFxyXG4gICAgICAgICAgICAgICAgJHRoaXMgPSB0aGlzLCBodG1sID0gJyc7XHJcblxyXG4gICAgICAgICAgICB0aGlzLnByb2R1Y3RUeXBlID0gcHJvZHVjdFR5cGU7XHJcblxyXG4gICAgICAgICAgICBsZXQgbW9kYWwgPSBfZi5jcmVhdGVNb2RhbCh7XHJcbiAgICAgICAgICAgICAgICBoZWFkZXI6IF9mLnRleHQoJ0VkaXQgYXR0cmlidXRlcycpLFxyXG4gICAgICAgICAgICAgICAgY29udGVudDogJycsXHJcbiAgICAgICAgICAgICAgICBhY3Rpb25zOiBbe2NsYXNzOiAnc2F2ZS1hdHRyaWJ1dGVzJywgdGV4dDogX2YudGV4dCgnU2F2ZScpfV0sXHJcbiAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgJChjZWxsKS5hcHBlbmQobW9kYWwpO1xyXG5cclxuICAgICAgICAgICAgaWYgKHByb2R1Y3RUeXBlICE9PSAndmFyaWF0aW9uJykge1xyXG4gICAgICAgICAgICAgICAgbGV0IHthdHRyaWJ1dGVzfSA9IEF0dHJpYnV0ZXM7XHJcbiAgICAgICAgICAgICAgICBsZXQgYWRkQXR0cmlidXRlID0gYDxvcHRpb24gdmFsdWU9XCJcIj4ke19mLnRleHQoJ0N1c3RvbSBwcm9kdWN0IGF0dHJpYnV0ZScpfTwvb3B0aW9uPmA7XHJcblxyXG4gICAgICAgICAgICAgICAgZm9yIChsZXQgYXR0ciBpbiBhdHRyaWJ1dGVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgYWRkQXR0cmlidXRlICs9IGA8b3B0aW9uIHZhbHVlPVwiJHthdHRyfVwiPiR7YXR0cmlidXRlc1thdHRyXS5kYXRhLmF0dHJpYnV0ZV9sYWJlbH08L29wdGlvbj5gO1xyXG4gICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgIGFkZEF0dHJpYnV0ZSA9IGA8ZGl2IGNsYXNzPVwidmktd2JlLXRheG9ub215LWhlYWRlclwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c2VsZWN0IGNsYXNzPVwidmktd2JlLXNlbGVjdC10YXhvbm9teVwiPiR7YWRkQXR0cmlidXRlfTwvc2VsZWN0PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpLXVpIGJ1dHRvbiB0aW55IHZpLXdiZS1hZGQtdGF4b25vbXlcIj4ke19mLnRleHQoJ0FkZCcpfTwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5gO1xyXG5cclxuICAgICAgICAgICAgICAgIGlmIChBcnJheS5pc0FycmF5KGRhdGEpICYmIGRhdGEubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgaXRlbSBvZiBkYXRhKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGh0bWwgKz0gJHRoaXMuY3JlYXRlUm93VGFibGUoaXRlbSk7XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgIGh0bWwgPSBgJHthZGRBdHRyaWJ1dGV9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDx0YWJsZSBjbGFzcz1cInZpLXVpIGNlbGxlZCB0YWJsZVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRoZWFkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0aD5OYW1lPC90aD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGg+QXR0cmlidXRlczwvdGg+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRoIHdpZHRoPVwiMVwiPkFjdGlvbnM8L3RoPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGhlYWQ+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGJvZHk+JHtodG1sfTwvdGJvZHk+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDwvdGFibGU+YDtcclxuXHJcbiAgICAgICAgICAgICAgICBtb2RhbC5maW5kKCcuY29udGVudCcpLmFwcGVuZChodG1sKTtcclxuICAgICAgICAgICAgICAgIG1vZGFsLmZpbmQoJ3RhYmxlIHNlbGVjdCcpLnNlbGVjdDIoe211bHRpcGxlOiB0cnVlfSk7XHJcbiAgICAgICAgICAgICAgICBtb2RhbC5maW5kKCd0Ym9keScpLnNvcnRhYmxlKHtcclxuICAgICAgICAgICAgICAgICAgICBpdGVtczogJ3RyJyxcclxuICAgICAgICAgICAgICAgICAgICBjdXJzb3I6ICdtb3ZlJyxcclxuICAgICAgICAgICAgICAgICAgICBheGlzOiAneScsXHJcbiAgICAgICAgICAgICAgICAgICAgc2Nyb2xsU2Vuc2l0aXZpdHk6IDQwLFxyXG4gICAgICAgICAgICAgICAgICAgIGZvcmNlUGxhY2Vob2xkZXJTaXplOiB0cnVlLFxyXG4gICAgICAgICAgICAgICAgICAgIGhlbHBlcjogJ2Nsb25lJyxcclxuICAgICAgICAgICAgICAgICAgICBoYW5kbGU6ICcuaWNvbi5tb3ZlJyxcclxuICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgICAgIGNvbnN0IHNldE9wdGlvbkRpc2FibGUgPSAoKSA9PiB7XHJcbiAgICAgICAgICAgICAgICAgICAgbW9kYWwuZmluZCgnc2VsZWN0LnZpLXdiZS1zZWxlY3QtdGF4b25vbXkgb3B0aW9uJykucmVtb3ZlQXR0cignZGlzYWJsZWQnKTtcclxuICAgICAgICAgICAgICAgICAgICBtb2RhbC5maW5kKCdpbnB1dFt0eXBlPWhpZGRlbl0nKS5lYWNoKGZ1bmN0aW9uIChpLCBlbCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgdGF4ID0gJChlbCkudmFsKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIG1vZGFsLmZpbmQoYHNlbGVjdC52aS13YmUtc2VsZWN0LXRheG9ub215IG9wdGlvblt2YWx1ZT0nJHt0YXh9J11gKS5hdHRyKCdkaXNhYmxlZCcsICdkaXNhYmxlZCcpO1xyXG4gICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgfTtcclxuXHJcbiAgICAgICAgICAgICAgICBzZXRPcHRpb25EaXNhYmxlKCk7XHJcblxyXG4gICAgICAgICAgICAgICAgbW9kYWwub24oJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICBsZXQgJHRoaXNUYXJnZXQgPSAkKGUudGFyZ2V0KTtcclxuICAgICAgICAgICAgICAgICAgICBpZiAoJHRoaXNUYXJnZXQuaGFzQ2xhc3MoJ3RyYXNoJykpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXNUYXJnZXQuY2xvc2VzdCgndHInKS5yZW1vdmUoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgc2V0T3B0aW9uRGlzYWJsZSgpO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKCR0aGlzVGFyZ2V0Lmhhc0NsYXNzKCd2aS13YmUtYWRkLXRheG9ub215JykpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHRheFNlbGVjdCA9ICQoJy52aS13YmUtc2VsZWN0LXRheG9ub215JyksIHRheCA9IHRheFNlbGVjdC52YWwoKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW0gPSB7bmFtZTogdGF4LCBvcHRpb25zOiBbXX07XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICh0YXgpIGl0ZW0uaXNfdGF4b25vbXkgPSAxO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHJvdyA9ICQoJHRoaXMuY3JlYXRlUm93VGFibGUoaXRlbSkpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBtb2RhbC5maW5kKCd0YWJsZSB0Ym9keScpLmFwcGVuZChyb3cpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICByb3cuZmluZCgnc2VsZWN0Jykuc2VsZWN0Mih7bXVsdGlwbGU6IHRydWV9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgc2V0T3B0aW9uRGlzYWJsZSgpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB0YXhTZWxlY3QudmFsKCcnKS50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGlmICgkdGhpc1RhcmdldC5oYXNDbGFzcygndmktd2JlLXNlbGVjdC1hbGwtYXR0cmlidXRlcycpKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCB0ZCA9ICR0aGlzVGFyZ2V0LmNsb3Nlc3QoJ3RkJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCBzZWxlY3QgPSB0ZC5maW5kKCdzZWxlY3QnKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgc2VsZWN0LmZpbmQoJ29wdGlvbicpLmF0dHIoJ3NlbGVjdGVkJywgdHJ1ZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlbGVjdC50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGlmICgkdGhpc1RhcmdldC5oYXNDbGFzcygndmktd2JlLXNlbGVjdC1uby1hdHRyaWJ1dGVzJykpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHRkID0gJHRoaXNUYXJnZXQuY2xvc2VzdCgndGQnKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHNlbGVjdCA9IHRkLmZpbmQoJ3NlbGVjdCcpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxlY3QuZmluZCgnb3B0aW9uJykuYXR0cignc2VsZWN0ZWQnLCBmYWxzZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlbGVjdC50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGlmICgkdGhpc1RhcmdldC5oYXNDbGFzcygndmktd2JlLWFkZC1uZXctYXR0cmlidXRlJykpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IG5ld0F0dHIgPSBwcm9tcHQoX2YudGV4dCgnRW50ZXIgYSBuYW1lIGZvciB0aGUgbmV3IGF0dHJpYnV0ZSB0ZXJtOicpKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICghbmV3QXR0cikgcmV0dXJuO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHRyID0gJHRoaXNUYXJnZXQuY2xvc2VzdCgndHIudmktd2JlLWF0dHJpYnV0ZS1yb3cnKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRheEF0dHIgPSB0ci5hdHRyKCdkYXRhLWF0dHInKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICh0YXhBdHRyKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0YXhBdHRyID0gSlNPTi5wYXJzZSh0YXhBdHRyKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGRhdGE6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3ViX2FjdGlvbjogJ2FkZF9uZXdfYXR0cmlidXRlJyxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGF4b25vbXk6IHRheEF0dHIubmFtZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGVybTogbmV3QXR0clxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXNUYXJnZXQuYWRkQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXNUYXJnZXQucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcnKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5zdWNjZXNzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgc2VsZWN0ID0gdHIuZmluZCgnc2VsZWN0Jyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxlY3QuYXBwZW5kKGA8b3B0aW9uIHZhbHVlPVwiJHtyZXMuZGF0YS50ZXJtX2lkfVwiIHNlbGVjdGVkPiR7cmVzLmRhdGEubmFtZX08L29wdGlvbj5gKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGVjdC50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIEF0dHJpYnV0ZXMuYXR0cmlidXRlc1t0YXhBdHRyLm5hbWVdLnRlcm1zW3Jlcy5kYXRhLnRlcm1faWRdID0ge3NsdWc6IHJlcy5kYXRhLnNsdWcsIHRleHQ6IHJlcy5kYXRhLm5hbWV9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBhbGVydChyZXMuZGF0YS5tZXNzYWdlKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAvL1ZhcmlhdGlvbiBhdHRyaWJ1dGVzXHJcbiAgICAgICAgICAgICAgICBsZXQgeSA9IGNlbGwuZ2V0QXR0cmlidXRlKCdkYXRhLXknKTtcclxuICAgICAgICAgICAgICAgIGxldCBwYXJlbnRJZCA9IG9iai5vcHRpb25zLmRhdGFbeV1bMV0sXHJcbiAgICAgICAgICAgICAgICAgICAgYWxsUHJvZHVjdHMgPSBvYmouZ2V0RGF0YSgpLCBwYXJlbnRBdHRyaWJ1dGVzO1xyXG5cclxuICAgICAgICAgICAgICAgIGZvciAobGV0IF95IGluIGFsbFByb2R1Y3RzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IHByb2R1Y3RJZCA9IGFsbFByb2R1Y3RzW195XVswXTtcclxuICAgICAgICAgICAgICAgICAgICBpZiAocGFyZW50SWQgPT0gcHJvZHVjdElkKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCB4ID0gQXR0cmlidXRlcy5pZE1hcHBpbmdGbGlwLmF0dHJpYnV0ZXM7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHBhcmVudEF0dHJpYnV0ZXMgPSBvYmoub3B0aW9ucy5kYXRhW195XVt4XTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgIGlmIChwYXJlbnRBdHRyaWJ1dGVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgYXR0ciBvZiBwYXJlbnRBdHRyaWJ1dGVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCBvcHRpb25zID0gYDxvcHRpb24gdmFsdWU9XCJcIj4ke19mLnRleHQoJ0FueS4uLicpfTwvb3B0aW9uPmAsIG5hbWUgPSBhdHRyLm5hbWUsIGxhYmVsO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoYXR0ci5pc190YXhvbm9teSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGF0dHJEYXRhID0gQXR0cmlidXRlcy5hdHRyaWJ1dGVzW25hbWVdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgaWQgb2YgYXR0ci5vcHRpb25zKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHRlcm0gPSBhdHRyRGF0YS50ZXJtc1tpZF07XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHNlbGVjdGVkID0gdGVybS5zbHVnID09PSBkYXRhW25hbWVdID8gJ3NlbGVjdGVkJyA6ICcnO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9wdGlvbnMgKz0gYDxvcHRpb24gdmFsdWU9XCIke3Rlcm0uc2x1Z31cIiAke3NlbGVjdGVkfT4ke3Rlcm0udGV4dH08L29wdGlvbj5gO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWwgPSBhdHRyRGF0YS5kYXRhLmF0dHJpYnV0ZV9sYWJlbFxyXG4gICAgICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgdmFsdWUgb2YgYXR0ci5vcHRpb25zKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHNlbGVjdGVkID0gdmFsdWUgPT09IGRhdGFbbmFtZV0gPyAnc2VsZWN0ZWQnIDogJyc7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb3B0aW9ucyArPSBgPG9wdGlvbiB2YWx1ZT1cIiR7dmFsdWV9XCIgJHtzZWxlY3RlZH0+JHt2YWx1ZX08L29wdGlvbj5gO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGFiZWwgPSBuYW1lO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGh0bWwgKz0gYDx0cj48dGQ+JHtsYWJlbH08L3RkPjx0ZD48c2VsZWN0IG5hbWU9XCIke25hbWV9XCI+JHtvcHRpb25zfTwvc2VsZWN0PjwvdGQ+PC90cj5gO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICBodG1sID0gYDx0YWJsZSBjbGFzcz1cInZpLXVpIGNlbGxlZCB0YWJsZVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRoZWFkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRyPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0aD4ke19mLnRleHQoJ0F0dHJpYnV0ZScpfTwvdGg+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRoPiR7X2YudGV4dCgnT3B0aW9uJyl9PC90aD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdHI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RoZWFkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJHtodG1sfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90Ym9keT5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPC90YWJsZT5gO1xyXG5cclxuICAgICAgICAgICAgICAgIG1vZGFsLmZpbmQoJy5jb250ZW50JykuYXBwZW5kKGh0bWwpO1xyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICBtb2RhbC5vbignY2xpY2snLCBmdW5jdGlvbiAoZSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IHRoaXNUYXJnZXQgPSAkKGUudGFyZ2V0KTtcclxuICAgICAgICAgICAgICAgIGlmICh0aGlzVGFyZ2V0Lmhhc0NsYXNzKCdjbG9zZScpIHx8IHRoaXNUYXJnZXQuaGFzQ2xhc3MoJ3ZpLXdiZS1tb2RhbC1jb250YWluZXInKSkgb2JqLmNsb3NlRWRpdG9yKGNlbGwsIGZhbHNlKTtcclxuICAgICAgICAgICAgICAgIGlmICh0aGlzVGFyZ2V0Lmhhc0NsYXNzKCdzYXZlLWF0dHJpYnV0ZXMnKSkgb2JqLmNsb3NlRWRpdG9yKGNlbGwsIHRydWUpO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBjbG9zZUVkaXRvcihjZWxsLCBzYXZlKSB7XHJcbiAgICAgICAgICAgIGxldCBkYXRhID0gW107XHJcbiAgICAgICAgICAgIGlmIChzYXZlID09PSB0cnVlKSB7XHJcbiAgICAgICAgICAgICAgICBpZiAodGhpcy5wcm9kdWN0VHlwZSAhPT0gJ3ZhcmlhdGlvbicpIHtcclxuICAgICAgICAgICAgICAgICAgICAkKGNlbGwpLmZpbmQoJy52aS13YmUtYXR0cmlidXRlLXJvdycpLmVhY2goZnVuY3Rpb24gKGksIHJvdykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgcEF0dHIgPSAkKHJvdykuZGF0YSgnYXR0cicpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAocEF0dHIuaXNfdGF4b25vbXkpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBBdHRyLm9wdGlvbnMgPSAkKHJvdykuZmluZCgnc2VsZWN0JykudmFsKCkubWFwKE51bWJlcik7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBwQXR0ci5uYW1lID0gJChyb3cpLmZpbmQoJ2lucHV0LmN1c3RvbS1hdHRyLW5hbWUnKS52YWwoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCB2YWx1ZSA9ICQocm93KS5maW5kKCd0ZXh0YXJlYS5jdXN0b20tYXR0ci12YWwnKS52YWwoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBBdHRyLnZhbHVlID0gdmFsdWUudHJpbSgpLnJlcGxhY2UoL1xccysvZywgJyAnKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBBdHRyLm9wdGlvbnMgPSB2YWx1ZS5zcGxpdCgnfCcpLm1hcChpdGVtID0+IGl0ZW0udHJpbSgpLnJlcGxhY2UoL1xccysvZywgJyAnKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgcEF0dHIudmlzaWJsZSA9ICEhJChyb3cpLmZpbmQoJy5hdHRyLXZpc2liaWxpdHk6Y2hlY2tlZCcpLmxlbmd0aDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgcEF0dHIudmFyaWF0aW9uID0gISEkKHJvdykuZmluZCgnLmF0dHItdmFyaWF0aW9uOmNoZWNrZWQnKS5sZW5ndGg7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHBBdHRyLnBvc2l0aW9uID0gaTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgZGF0YS5wdXNoKHBBdHRyKVxyXG4gICAgICAgICAgICAgICAgICAgIH0pXHJcbiAgICAgICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgICAgIGRhdGEgPSB7fTtcclxuICAgICAgICAgICAgICAgICAgICAkKGNlbGwpLmZpbmQoJ3NlbGVjdCcpLmVhY2goZnVuY3Rpb24gKGksIHJvdykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBkYXRhWyQocm93KS5hdHRyKCduYW1lJyldID0gJChyb3cpLnZhbCgpO1xyXG4gICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIF9mLnJlbW92ZU1vZGFsKGNlbGwpO1xyXG4gICAgICAgICAgICByZXR1cm4gZGF0YTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBjcmVhdGVSb3dUYWJsZShpdGVtKSB7XHJcbiAgICAgICAgICAgIGxldCBhdHRyTmFtZSA9ICcnLCB2YWx1ZSA9ICcnO1xyXG5cclxuICAgICAgICAgICAgaWYgKGl0ZW0uaXNfdGF4b25vbXkpIHtcclxuICAgICAgICAgICAgICAgIGxldCBhdHRyaWJ1dGUgPSBBdHRyaWJ1dGVzLmF0dHJpYnV0ZXNbaXRlbS5uYW1lXSxcclxuICAgICAgICAgICAgICAgICAgICB0ZXJtcyA9IGF0dHJpYnV0ZS50ZXJtcyB8fCBbXSwgb3B0aW9ucyA9ICcnO1xyXG5cclxuICAgICAgICAgICAgICAgIGF0dHJOYW1lID0gYCR7YXR0cmlidXRlLmRhdGEuYXR0cmlidXRlX2xhYmVsfTxpbnB1dCB0eXBlPVwiaGlkZGVuXCIgdmFsdWU9XCIke2l0ZW0ubmFtZX1cIi8+YDtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAoT2JqZWN0LmtleXModGVybXMpLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgICAgIGZvciAobGV0IGlkIGluIHRlcm1zKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCBzZWxlY3RlZCA9IGl0ZW0ub3B0aW9ucy5pbmNsdWRlcyhwYXJzZUludChpZCkpID8gJ3NlbGVjdGVkJyA6ICcnO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBvcHRpb25zICs9IGA8b3B0aW9uIHZhbHVlPVwiJHtpZH1cIiAke3NlbGVjdGVkfT4ke3Rlcm1zW2lkXS50ZXh0fTwvb3B0aW9uPmA7XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgdmFsdWUgPSBgPHNlbGVjdCBtdWx0aXBsZT4ke29wdGlvbnN9PC9zZWxlY3Q+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJ2aS13YmUtYXR0cmlidXRlcy1idXR0b24tZ3JvdXBcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIG1pbmkgdmktd2JlLXNlbGVjdC1hbGwtYXR0cmlidXRlc1wiPiR7X2YudGV4dCgnU2VsZWN0IGFsbCcpfTwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIG1pbmkgdmktd2JlLXNlbGVjdC1uby1hdHRyaWJ1dGVzXCI+JHtfZi50ZXh0KCdTZWxlY3Qgbm9uZScpfTwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIG1pbmkgdmktd2JlLWFkZC1uZXctYXR0cmlidXRlXCI+JHtfZi50ZXh0KCdBZGQgbmV3Jyl9PC9zcGFuPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5gO1xyXG4gICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgYXR0ck5hbWUgPSBgPGlucHV0IHR5cGU9XCJ0ZXh0XCIgY2xhc3M9XCJjdXN0b20tYXR0ci1uYW1lXCIgdmFsdWU9XCIke2l0ZW0ubmFtZX1cIiBwbGFjZWhvbGRlcj1cIiR7X2YudGV4dCgnQ3VzdG9tIGF0dHJpYnV0ZSBuYW1lJyl9XCIvPmA7XHJcbiAgICAgICAgICAgICAgICB2YWx1ZSA9IGA8dGV4dGFyZWEgY2xhc3M9XCJjdXN0b20tYXR0ci12YWxcIiBwbGFjZWhvbGRlcj1cIiR7X2YudGV4dCgnRW50ZXIgc29tZSB0ZXh0LCBvciBzb21lIGF0dHJpYnV0ZXMgYnkgXCJ8XCIgc2VwYXJhdGluZyB2YWx1ZXMuJyl9XCI+JHtpdGVtLnZhbHVlIHx8ICcnfTwvdGV4dGFyZWE+YDtcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgYXR0ck5hbWUgPSBgPGRpdiBjbGFzcz1cInZpLXdiZS1hdHRyaWJ1dGUtbmFtZS1sYWJlbFwiPiR7YXR0ck5hbWV9PC9kaXY+YDtcclxuXHJcbiAgICAgICAgICAgIGF0dHJOYW1lICs9IGA8ZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJjaGVja2JveFwiIGNsYXNzPVwiYXR0ci12aXNpYmlsaXR5XCIgJHtpdGVtLnZpc2libGUgPyAnY2hlY2tlZCcgOiAnJ30gdmFsdWU9XCIxXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8bGFiZWw+JHtfZi50ZXh0KCdWaXNpYmxlIG9uIHRoZSBwcm9kdWN0IHBhZ2UnKX08L2xhYmVsPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5gO1xyXG5cclxuICAgICAgICAgICAgaWYgKHRoaXMucHJvZHVjdFR5cGUgPT09ICd2YXJpYWJsZScpIHtcclxuICAgICAgICAgICAgICAgIGF0dHJOYW1lICs9IGA8ZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxpbnB1dCB0eXBlPVwiY2hlY2tib3hcIiBjbGFzcz1cImF0dHItdmFyaWF0aW9uXCIgJHtpdGVtLnZhcmlhdGlvbiA/ICdjaGVja2VkJyA6ICcnfSB2YWx1ZT1cIjFcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8bGFiZWw+JHtfZi50ZXh0KCdVc2VkIGZvciB2YXJpYXRpb25zJyl9PC9sYWJlbD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZGl2PmA7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIHJldHVybiBgPHRyIGNsYXNzPVwidmktd2JlLWF0dHJpYnV0ZS1yb3dcIiBkYXRhLWF0dHI9JyR7SlNPTi5zdHJpbmdpZnkoaXRlbSl9Jz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPHRkIGNsYXNzPVwidmktd2JlLWxlZnRcIj4ke2F0dHJOYW1lfTwvdGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDx0ZD4ke3ZhbHVlfTwvdGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDx0ZCBjbGFzcz1cInZpLXdiZS1yaWdodFwiPjxpIGNsYXNzPVwiaWNvbiB0cmFzaFwiPiA8L2k+IDxpIGNsYXNzPVwiaWNvbiBtb3ZlXCI+IDwvaT48L3RkPlxyXG4gICAgICAgICAgICAgICAgICAgIDwvdHI+YDtcclxuICAgICAgICB9XHJcblxyXG4gICAgfTtcclxuXHJcbiAgICBjdXN0b21Db2x1bW4uZGVmYXVsdF9hdHRyaWJ1dGVzID0ge1xyXG4gICAgICAgIGNyZWF0ZUNlbGwoY2VsbCwgaSwgdmFsdWUsIG9iaikge1xyXG4gICAgICAgICAgICBpZiAodmFsdWUpICQoY2VsbCkudGV4dChPYmplY3QudmFsdWVzKHZhbHVlKS5maWx0ZXIoQm9vbGVhbikuam9pbignOyAnKSk7XHJcbiAgICAgICAgICAgIHJldHVybiBjZWxsO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIHVwZGF0ZUNlbGwoY2VsbCwgdmFsdWUsIGZvcmNlLCBvYmosIHgpIHtcclxuICAgICAgICAgICAgaWYgKHZhbHVlKSB7XHJcbiAgICAgICAgICAgICAgICAkKGNlbGwpLnRleHQoT2JqZWN0LnZhbHVlcyh2YWx1ZSkuZmlsdGVyKEJvb2xlYW4pLmpvaW4oJzsgJykpO1xyXG4gICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgJChjZWxsKS50ZXh0KCcnKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICByZXR1cm4gdmFsdWU7XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgb3BlbkVkaXRvcihjZWxsLCBlbCwgb2JqKSB7XHJcbiAgICAgICAgICAgIGxldCBkYXRhID0gX2YuZ2V0RGF0YUZyb21DZWxsKG9iaiwgY2VsbCksXHJcbiAgICAgICAgICAgICAgICBwcm9kdWN0VHlwZSA9IF9mLmdldFByb2R1Y3RUeXBlRnJvbUNlbGwoY2VsbCksXHJcbiAgICAgICAgICAgICAgICBodG1sID0gJyc7XHJcblxyXG4gICAgICAgICAgICB0aGlzLnByb2R1Y3RUeXBlID0gcHJvZHVjdFR5cGU7XHJcbiAgICAgICAgICAgIGlmIChwcm9kdWN0VHlwZSA9PT0gJ3ZhcmlhYmxlJykge1xyXG4gICAgICAgICAgICAgICAgbGV0IG1vZGFsID0gX2YuY3JlYXRlTW9kYWwoe2hlYWRlcjogX2YudGV4dCgnU2V0IGRlZmF1bHQgYXR0cmlidXRlcycpLCBjb250ZW50OiAnJywgYWN0aW9uczogW3tjbGFzczogJ3NhdmUtYXR0cmlidXRlcycsIHRleHQ6IF9mLnRleHQoJ1NhdmUnKX1dfSk7XHJcbiAgICAgICAgICAgICAgICAkKGNlbGwpLmFwcGVuZChtb2RhbCk7XHJcblxyXG4gICAgICAgICAgICAgICAgbGV0IHkgPSBjZWxsLmdldEF0dHJpYnV0ZSgnZGF0YS15JyksXHJcbiAgICAgICAgICAgICAgICAgICAgeCA9IEF0dHJpYnV0ZXMuaWRNYXBwaW5nRmxpcC5hdHRyaWJ1dGVzLFxyXG4gICAgICAgICAgICAgICAgICAgIHBBdHRyaWJ1dGVzID0gb2JqLm9wdGlvbnMuZGF0YVt5XVt4XTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAoQXJyYXkuaXNBcnJheShwQXR0cmlidXRlcykgJiYgcEF0dHJpYnV0ZXMubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgYXR0ciBvZiBwQXR0cmlidXRlcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoYXR0ci5vcHRpb25zLmxlbmd0aCA9PT0gMCkgY29udGludWU7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgYXR0ck5hbWUgPSAnJywgc2VsZWN0SHRtbCA9ICcnO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGF0dHIuaXNfdGF4b25vbXkpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBhdHRyRGF0YSA9IEF0dHJpYnV0ZXMuYXR0cmlidXRlc1thdHRyLm5hbWVdO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGF0dHJOYW1lID0gYXR0ckRhdGEuZGF0YS5hdHRyaWJ1dGVfbGFiZWw7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCB0ZXJtSWQgb2YgYXR0ci5vcHRpb25zKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHRlcm0gPSBhdHRyRGF0YS50ZXJtc1t0ZXJtSWRdLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZWxlY3RlZCA9IHRlcm0uc2x1ZyA9PT0gZGF0YVthdHRyLm5hbWVdID8gJ3NlbGVjdGVkJyA6ICcnO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNlbGVjdEh0bWwgKz0gYDxvcHRpb24gdmFsdWU9XCIke3Rlcm0uc2x1Z31cIiAke3NlbGVjdGVkfT4ke3Rlcm0udGV4dH08L29wdGlvbj5gO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGF0dHJOYW1lID0gYXR0ci5uYW1lO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgdGVybSBvZiBhdHRyLm9wdGlvbnMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgc2VsZWN0ZWQgPSB0ZXJtID09PSBkYXRhW2F0dHIubmFtZV0gPyAnc2VsZWN0ZWQnIDogJyc7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZWN0SHRtbCArPSBgPG9wdGlvbiB2YWx1ZT1cIiR7dGVybX1cIiAke3NlbGVjdGVkfT4ke3Rlcm19PC9vcHRpb24+YDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICBzZWxlY3RIdG1sID0gYDxvcHRpb24gdmFsdWU9XCJcIj5ObyBkZWZhdWx0ICR7YXR0ck5hbWV9PC9vcHRpb24+ICR7c2VsZWN0SHRtbH1gO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaHRtbCArPSBgPHRyPjx0ZD4ke2F0dHJOYW1lfTwvdGQ+PHRkPjxzZWxlY3QgbmFtZT1cIiR7YXR0ci5uYW1lfVwiIGNsYXNzPVwidmktd2JlLWRlZmF1bHQtYXR0cmlidXRlXCI+JHtzZWxlY3RIdG1sfTwvc2VsZWN0PjwvdGQ+PC90cj5gO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICBtb2RhbC5maW5kKCcuY29udGVudCcpLmFwcGVuZChUZW1wbGF0ZXMuZGVmYXVsdEF0dHJpYnV0ZXMoe2h0bWx9KSk7XHJcblxyXG4gICAgICAgICAgICAgICAgbW9kYWwub24oJ2NsaWNrJywgZnVuY3Rpb24gKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICBsZXQgdGhpc1RhcmdldCA9ICQoZS50YXJnZXQpO1xyXG4gICAgICAgICAgICAgICAgICAgIGlmICh0aGlzVGFyZ2V0Lmhhc0NsYXNzKCdjbG9zZScpIHx8IHRoaXNUYXJnZXQuaGFzQ2xhc3MoJ3ZpLXdiZS1tb2RhbC1jb250YWluZXInKSkgb2JqLmNsb3NlRWRpdG9yKGNlbGwsIGZhbHNlKTtcclxuICAgICAgICAgICAgICAgICAgICBpZiAodGhpc1RhcmdldC5oYXNDbGFzcygnc2F2ZS1hdHRyaWJ1dGVzJykpIG9iai5jbG9zZUVkaXRvcihjZWxsLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSxcclxuXHJcbiAgICAgICAgY2xvc2VFZGl0b3IoY2VsbCwgc2F2ZSkge1xyXG4gICAgICAgICAgICBsZXQgZGF0YSA9IHt9O1xyXG4gICAgICAgICAgICBpZiAoc2F2ZSA9PT0gdHJ1ZSkgJChjZWxsKS5maW5kKCcudmktd2JlLWRlZmF1bHQtYXR0cmlidXRlJykuZWFjaCgoaSwgZWwpID0+IGRhdGFbJChlbCkuYXR0cignbmFtZScpXSA9ICQoZWwpLnZhbCgpKTtcclxuICAgICAgICAgICAgX2YucmVtb3ZlTW9kYWwoY2VsbCk7XHJcbiAgICAgICAgICAgIHJldHVybiBkYXRhO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgfTtcclxuXHJcbiAgICBjdXN0b21Db2x1bW4uYXJyYXkgPSB7XHJcbiAgICAgICAgY3JlYXRlQ2VsbChjZWxsLCBpLCB2YWx1ZSwgb2JqKSB7XHJcbiAgICAgICAgICAgICQoY2VsbCkuaHRtbCgnPGkgY2xhc3M9XCJpY29uIGVkaXRcIi8+Jyk7XHJcbiAgICAgICAgICAgIHJldHVybiBjZWxsO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIGNsb3NlRWRpdG9yKGNlbGwsIHNhdmUpIHtcclxuICAgICAgICAgICAgbGV0IG1ldGFkYXRhID0gW107XHJcbiAgICAgICAgICAgIGlmIChzYXZlID09PSB0cnVlKSB7XHJcbiAgICAgICAgICAgICAgICBtZXRhZGF0YSA9IHRoaXMuZWRpdG9yLmdldCgpO1xyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICBfZi5yZW1vdmVNb2RhbChjZWxsKTtcclxuXHJcbiAgICAgICAgICAgIHJldHVybiBtZXRhZGF0YTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBvcGVuRWRpdG9yKGNlbGwsIGVsLCBvYmopIHtcclxuICAgICAgICAgICAgbGV0IGRhdGEgPSBfZi5nZXREYXRhRnJvbUNlbGwob2JqLCBjZWxsKTtcclxuICAgICAgICAgICAgbGV0IG1vZGFsID0gX2YuY3JlYXRlTW9kYWwoe1xyXG4gICAgICAgICAgICAgICAgaGVhZGVyOiBfZi50ZXh0KCdFZGl0IG1ldGFkYXRhJyksXHJcbiAgICAgICAgICAgICAgICBjb250ZW50OiAnJyxcclxuICAgICAgICAgICAgICAgIGFjdGlvbnM6IFt7Y2xhc3M6ICdzYXZlLW1ldGFkYXRhJywgdGV4dDogX2YudGV4dCgnU2F2ZScpfV0sXHJcbiAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgJChjZWxsKS5hcHBlbmQobW9kYWwpO1xyXG4gICAgICAgICAgICBtb2RhbC5maW5kKCcuY29udGVudCcpLmh0bWwoJzxkaXYgaWQ9XCJ2aS13YmUtanNvbmVkaXRvclwiPjwvZGl2PicpO1xyXG4gICAgICAgICAgICBsZXQgY29udGFpbmVyID0gbW9kYWwuZmluZCgnI3ZpLXdiZS1qc29uZWRpdG9yJykuZ2V0KDApO1xyXG4gICAgICAgICAgICB0aGlzLmVkaXRvciA9IG5ldyBKU09ORWRpdG9yKGNvbnRhaW5lciwge2VuYWJsZVNvcnQ6IGZhbHNlLCBzZWFyY2g6IGZhbHNlLCBlbmFibGVUcmFuc2Zvcm06IGZhbHNlfSk7XHJcbiAgICAgICAgICAgIHRoaXMuZWRpdG9yLnNldChkYXRhKTtcclxuXHJcbiAgICAgICAgICAgIG1vZGFsLm9uKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgdGhpc1RhcmdldCA9ICQoZS50YXJnZXQpO1xyXG4gICAgICAgICAgICAgICAgaWYgKHRoaXNUYXJnZXQuaGFzQ2xhc3MoJ2Nsb3NlJykgfHwgdGhpc1RhcmdldC5oYXNDbGFzcygndmktd2JlLW1vZGFsLWNvbnRhaW5lcicpKSBvYmouY2xvc2VFZGl0b3IoY2VsbCwgZmFsc2UpO1xyXG4gICAgICAgICAgICAgICAgaWYgKHRoaXNUYXJnZXQuaGFzQ2xhc3MoJ3NhdmUtbWV0YWRhdGEnKSkgb2JqLmNsb3NlRWRpdG9yKGNlbGwsIHRydWUpO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICB1cGRhdGVDZWxsKGNlbGwsIHZhbHVlLCBmb3JjZSkge1xyXG4gICAgICAgICAgICByZXR1cm4gdmFsdWU7XHJcbiAgICAgICAgfSxcclxuICAgIH07XHJcblxyXG4gICAgY3VzdG9tQ29sdW1uLm9yZGVyX25vdGVzID0ge1xyXG5cclxuICAgICAgICBjcmVhdGVDZWxsKGNlbGwsIGksIHZhbHVlLCBvYmopIHtcclxuICAgICAgICAgICAgbGV0IGhhc0l0ZW0gPSB2YWx1ZS5sZW5ndGggPyAndmktd2JlLWdhbGxlcnktaGFzLWl0ZW0nIDogJyc7XHJcblxyXG4gICAgICAgICAgICAkKGNlbGwpLmh0bWwoYDxkaXYgY2xhc3M9XCIke2hhc0l0ZW19XCI+PGkgY2xhc3M9XCJpY29uIGV5ZVwiLz48L2Rpdj5gKTtcclxuICAgICAgICAgICAgdGhpcy5vYmogPSBvYmo7XHJcblxyXG4gICAgICAgICAgICByZXR1cm4gY2VsbDtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBjbG9zZUVkaXRvcihjZWxsLCBzYXZlKSB7XHJcbiAgICAgICAgICAgICQoY2VsbCkuZmluZCgnLnZpLXdiZS1jZWxsLXBvcHVwJykucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgIHJldHVybiB0aGlzLm5vdGVzO1xyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIG9wZW5FZGl0b3IoY2VsbCwgZWwsIG9iaikge1xyXG4gICAgICAgICAgICBsZXQgeSA9IGNlbGwuZ2V0QXR0cmlidXRlKCdkYXRhLXknKSxcclxuICAgICAgICAgICAgICAgIHggPSBjZWxsLmdldEF0dHJpYnV0ZSgnZGF0YS14Jyk7XHJcblxyXG4gICAgICAgICAgICBsZXQgbm90ZXMgPSBvYmoub3B0aW9ucy5kYXRhW3ldW3hdLFxyXG4gICAgICAgICAgICAgICAgX25vdGUgPSAnJztcclxuXHJcbiAgICAgICAgICAgIHRoaXMubm90ZXMgPSBub3RlcztcclxuXHJcbiAgICAgICAgICAgIGlmIChub3Rlcy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgIGZvciAobGV0IG5vdGUgb2Ygbm90ZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICBsZXQgY29udGVudCA9IG5vdGUuY29udGVudC5yZXBsYWNlKC8oPzpcXHJcXG58XFxyfFxcbikvZywgJzxicj4nKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgY2xhc3NDb2xvciA9IG5vdGUuY3VzdG9tZXJfbm90ZSA/ICdjdXN0b21lcicgOiAobm90ZS5hZGRlZF9ieSA9PT0gJ3N5c3RlbScgPyAnc3lzdGVtJyA6ICdwcml2YXRlJyk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIF9ub3RlICs9IGA8ZGl2IGNsYXNzPVwidmktd2JlLW5vdGUtcm93XCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cInZpLXdiZS1ub3RlLXJvdy1jb250ZW50ICR7Y2xhc3NDb2xvcn1cIj4ke2NvbnRlbnR9PC9kaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9XCJ2aS13YmUtbm90ZS1yb3ctbWV0YVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAke25vdGUuZGF0ZX1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGEgaHJlZj1cIiNcIiBkYXRhLWNvbW1lbnRfaWQ9XCIke25vdGUuaWR9XCIgY2xhc3M9XCJ2aS13YmUtbm90ZS1yb3ctZGVsZXRlXCI+JHtfZi50ZXh0KCdEZWxldGUnKX08L2E+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9zcGFuPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+YDtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgbGV0IGdhbGxlcnlQb3B1cCA9ICQoYDxkaXYgY2xhc3M9XCJ2aS13YmUtY2VsbC1wb3B1cC1pbm5lclwiPiR7X25vdGV9PC9kaXY+YCk7XHJcblxyXG4gICAgICAgICAgICBfZi5jcmVhdGVFZGl0b3IoY2VsbCwgJ2RpdicsIGdhbGxlcnlQb3B1cCk7XHJcblxyXG4gICAgICAgICAgICBnYWxsZXJ5UG9wdXAub24oJ2NsaWNrJywgJy52aS13YmUtbm90ZS1yb3ctZGVsZXRlJywgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAgICAgbGV0ICR0aGlzQnRuID0gJCh0aGlzKSxcclxuICAgICAgICAgICAgICAgICAgICBpZCA9ICR0aGlzQnRuLmRhdGEoJ2NvbW1lbnRfaWQnKTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAoIWlkKSByZXR1cm47XHJcblxyXG4gICAgICAgICAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgICAgICAgICAgZGF0YToge3N1Yl9hY3Rpb246ICdkZWxldGVfb3JkZXJfbm90ZScsIGlkfSxcclxuICAgICAgICAgICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBfZi5sb2FkaW5nKClcclxuICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChyZXMuc3VjY2Vzcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGluZGV4ID0gbm90ZXMuZmluZEluZGV4KG5vdGUgPT4gbm90ZS5pZCA9PT0gaWQpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbm90ZXMuc3BsaWNlKGluZGV4LCAxKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzQnRuLmNsb3Nlc3QoJy52aS13YmUtbm90ZS1yb3cnKS5yZW1vdmUoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICBfZi5yZW1vdmVMb2FkaW5nKClcclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9KVxyXG4gICAgICAgICAgICB9KVxyXG4gICAgICAgIH0sXHJcblxyXG4gICAgICAgIHVwZGF0ZUNlbGwoY2VsbCwgdmFsdWUsIGZvcmNlKSB7XHJcbiAgICAgICAgICAgIHJldHVybiB2YWx1ZTtcclxuICAgICAgICB9LFxyXG4gICAgfTtcclxuXHJcbiAgICBjdXN0b21Db2x1bW4uc2VsZWN0MiA9IHtcclxuICAgICAgICB0eXBlOiAnc2VsZWN0MicsXHJcblxyXG4gICAgICAgIGNyZWF0ZUNlbGwoY2VsbCwgaSwgdmFsdWUsIG9iaikge1xyXG4gICAgICAgICAgICBsZXQge3NvdXJjZX0gPSBvYmoub3B0aW9ucy5jb2x1bW5zW2ldLCBuZXdWYWx1ZSA9IFtdO1xyXG4gICAgICAgICAgICBpZiAoQXJyYXkuaXNBcnJheShzb3VyY2UpICYmIHNvdXJjZS5sZW5ndGgpIG5ld1ZhbHVlID0gc291cmNlLmZpbHRlcihpdGVtID0+IHZhbHVlLmluY2x1ZGVzKGl0ZW0uaWQpKTtcclxuXHJcbiAgICAgICAgICAgIF9mLmZvcm1hdFRleHQoY2VsbCwgbmV3VmFsdWUpO1xyXG4gICAgICAgICAgICByZXR1cm4gY2VsbDtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBvcGVuRWRpdG9yKGNlbGwsIGVsLCBvYmopIHtcclxuICAgICAgICAgICAgbGV0IHkgPSBjZWxsLmdldEF0dHJpYnV0ZSgnZGF0YS15JyksXHJcbiAgICAgICAgICAgICAgICB4ID0gY2VsbC5nZXRBdHRyaWJ1dGUoJ2RhdGEteCcpO1xyXG5cclxuICAgICAgICAgICAgbGV0IHZhbHVlID0gb2JqLm9wdGlvbnMuZGF0YVt5XVt4XSxcclxuICAgICAgICAgICAgICAgIHNlbGVjdCA9ICQoJzxzZWxlY3QvPicpLFxyXG4gICAgICAgICAgICAgICAge3NvdXJjZSwgbXVsdGlwbGUsIHBsYWNlaG9sZGVyfSA9IG9iai5vcHRpb25zLmNvbHVtbnNbeF0sXHJcbiAgICAgICAgICAgICAgICBlZGl0b3IgPSBfZi5jcmVhdGVFZGl0b3IoY2VsbCwgJ2RpdicsIHNlbGVjdCk7XHJcblxyXG4gICAgICAgICAgICBzZWxlY3Quc2VsZWN0Mih7XHJcbiAgICAgICAgICAgICAgICBkYXRhOiBzb3VyY2UgfHwgW10sXHJcbiAgICAgICAgICAgICAgICBtdWx0aXBsZTogbXVsdGlwbGUsXHJcbiAgICAgICAgICAgICAgICBwbGFjZWhvbGRlcjogcGxhY2Vob2xkZXIsXHJcbiAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgc2VsZWN0LnZhbCh2YWx1ZSkudHJpZ2dlcignY2hhbmdlJyk7XHJcbiAgICAgICAgICAgICQoZWRpdG9yKS5maW5kKCcuc2VsZWN0Mi1zZWFyY2hfX2ZpZWxkJykudHJpZ2dlcignY2xpY2snKTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICBjbG9zZUVkaXRvcihjZWxsLCBzYXZlKSB7XHJcbiAgICAgICAgICAgIGxldCBjaGlsZCA9ICQoY2VsbCkuY2hpbGRyZW4oKSxcclxuICAgICAgICAgICAgICAgIGRhdGEgPSBjaGlsZC5maW5kKCdzZWxlY3QnKS52YWwoKTtcclxuXHJcbiAgICAgICAgICAgIGRhdGEgPSBkYXRhLm1hcChpdGVtID0+ICFpc05hTihpdGVtKSA/ICtpdGVtIDogaXRlbSk7XHJcblxyXG4gICAgICAgICAgICBjaGlsZC5yZW1vdmUoKTtcclxuICAgICAgICAgICAgJCgnLnNlbGVjdDItY29udGFpbmVyJykucmVtb3ZlKCk7XHJcblxyXG4gICAgICAgICAgICByZXR1cm4gZGF0YTtcclxuICAgICAgICB9LFxyXG5cclxuICAgICAgICB1cGRhdGVDZWxsKGNlbGwsIHZhbHVlLCBmb3JjZSwgb2JqLCB4KSB7XHJcbiAgICAgICAgICAgIGxldCB7c291cmNlfSA9IG9iai5vcHRpb25zLmNvbHVtbnNbeF0sIG5ld1ZhbHVlID0gW107XHJcblxyXG4gICAgICAgICAgICBpZiAoQXJyYXkuaXNBcnJheShzb3VyY2UpICYmIHNvdXJjZS5sZW5ndGgpIG5ld1ZhbHVlID0gc291cmNlLmZpbHRlcihpdGVtID0+IHZhbHVlLmluY2x1ZGVzKGl0ZW0uaWQpKTtcclxuXHJcbiAgICAgICAgICAgIF9mLmZvcm1hdFRleHQoY2VsbCwgbmV3VmFsdWUpO1xyXG5cclxuICAgICAgICAgICAgcmV0dXJuIHZhbHVlO1xyXG4gICAgICAgIH1cclxuICAgIH07XHJcblxyXG4vLy0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLy9cclxuICAgIGNvbHVtbkZpbHRlci5zb3VyY2VGb3JWYXJpYXRpb24gPSAoZWwsIGNlbGwsIHgsIHksIG9iaikgPT4ge1xyXG4gICAgICAgIGxldCBzb3VyY2UgPSBvYmoub3B0aW9ucy5jb2x1bW5zW3hdLnNvdXJjZTtcclxuICAgICAgICBsZXQgcHJvZHVjdFR5cGUgPSBfZi5nZXRQcm9kdWN0VHlwZUZyb21DZWxsKGNlbGwpO1xyXG4gICAgICAgIGlmIChwcm9kdWN0VHlwZSA9PT0gJ3ZhcmlhdGlvbicpIHtcclxuICAgICAgICAgICAgc291cmNlID0gb2JqLm9wdGlvbnMuY29sdW1uc1t4XS5zdWJTb3VyY2U7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIHJldHVybiBzb3VyY2U7XHJcbiAgICB9O1xyXG5cclxufSk7XHJcblxyXG5leHBvcnQge2N1c3RvbUNvbHVtbiwgY29sdW1uRmlsdGVyfTsiLCJpbXBvcnQgX2YgZnJvbSAnLi9mdW5jdGlvbnMnO1xyXG5pbXBvcnQge1BvcHVwfSBmcm9tIFwiLi9tb2RhbC1wb3B1cFwiO1xyXG5cclxuY29uc3QgJCA9IGpRdWVyeTtcclxuZXhwb3J0IGRlZmF1bHQgY2xhc3MgRmluZEFuZFJlcGxhY2VPcHRpb25zIHtcclxuICAgIGNvbnN0cnVjdG9yKG9iaiwgY2VsbHMsIHgsIHksIGUpIHtcclxuICAgICAgICB0aGlzLmNlbGxzID0gY2VsbHM7XHJcbiAgICAgICAgdGhpcy5vYmogPSBvYmo7XHJcbiAgICAgICAgdGhpcy54ID0gcGFyc2VJbnQoeCk7XHJcbiAgICAgICAgdGhpcy55ID0gcGFyc2VJbnQoeSk7XHJcbiAgICAgICAgdGhpcy5zZWFyY2hEYXRhID0gW107XHJcbiAgICAgICAgdGhpcy5zb3VyY2UgPSBvYmoub3B0aW9ucy5jb2x1bW5zW3hdLnNvdXJjZSB8fCBbXTtcclxuXHJcbiAgICAgICAgdGhpcy5ydW4oKTtcclxuICAgIH1cclxuXHJcbiAgICBydW4oKSB7XHJcbiAgICAgICAgbGV0ICR0aGlzID0gdGhpcztcclxuICAgICAgICBsZXQgZm9ybXVsYUh0bWwgPSB0aGlzLmNvbnRlbnQoKTtcclxuXHJcbiAgICAgICAgbGV0IGNlbGwgPSAkKGB0ZFtkYXRhLXg9JHt0aGlzLnggfHwgMH1dW2RhdGEteT0ke3RoaXMueSB8fCAwfV1gKTtcclxuICAgICAgICBuZXcgUG9wdXAoZm9ybXVsYUh0bWwsIGNlbGwpO1xyXG5cclxuICAgICAgICBmb3JtdWxhSHRtbC5maW5kKCcudmktd2JlLWZpbmQtc3RyaW5nJykuc2VsZWN0Mih7XHJcbiAgICAgICAgICAgIGRhdGE6IFt7aWQ6ICcnLCB0ZXh0OiAnJ30sIC4uLiR0aGlzLnNvdXJjZV1cclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgZm9ybXVsYUh0bWwuZmluZCgnLnZpLXdiZS1yZXBsYWNlLXN0cmluZycpLnNlbGVjdDIoe1xyXG4gICAgICAgICAgICBkYXRhOiBbe2lkOiAnJywgdGV4dDogJyd9LCAuLi4kdGhpcy5zb3VyY2VdXHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIGZvcm11bGFIdG1sLm9uKCdjbGljaycsICcudmktd2JlLWFwcGx5LWZvcm11bGEnLCB0aGlzLmFwcGx5Rm9ybXVsYS5iaW5kKHRoaXMpKTtcclxuICAgIH1cclxuXHJcbiAgICBjb250ZW50KCkge1xyXG4gICAgICAgIHJldHVybiAkKGA8ZGl2IGNsYXNzPVwidmktd2JlLWZvcm11bGEtY29udGFpbmVyXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImZpZWxkXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXY+JHtfZi50ZXh0KCdGaW5kJyl9PC9kaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxzZWxlY3QgcGxhY2Vob2xkZXI9XCJcIiBjbGFzcz1cInZpLXdiZS1maW5kLXN0cmluZ1wiPiA8L3NlbGVjdD5cclxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwiZmllbGRcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPGRpdj4ke19mLnRleHQoJ1JlcGxhY2UnKX08L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPHNlbGVjdCBwbGFjZWhvbGRlcj1cIlwiIGNsYXNzPVwidmktd2JlLXJlcGxhY2Utc3RyaW5nXCI+IDwvc2VsZWN0PlxyXG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgIDxidXR0b24gdHlwZT1cImJ1dHRvblwiIGNsYXNzPVwidmktdWkgYnV0dG9uIG1pbmkgdmktd2JlLWFwcGx5LWZvcm11bGFcIj4ke19mLnRleHQoJ1JlcGxhY2UnKX08L2J1dHRvbj5cclxuICAgICAgICAgICAgICAgICAgICA8cD5JZiAnRmluZCcgdmFsdWUgaXMgZW1wdHksIGFkZCB0byBzZWxlY3RlZCBjZWxscyB3aXRoICdSZXBsYWNlJyB2YWx1ZS48L3A+XHJcbiAgICAgICAgICAgICAgICAgICAgPHA+SWYgJ1JlcGxhY2UnIHZhbHVlIGlzIGVtcHR5LCByZW1vdmUgZnJvbSBzZWxlY3RlZCBjZWxscyB3aXRoICdGaW5kJyB2YWx1ZS48L3A+XHJcbiAgICAgICAgICAgICAgICA8L2Rpdj5gKTtcclxuICAgIH1cclxuXHJcbiAgICBhcHBseUZvcm11bGEoZSkge1xyXG4gICAgICAgIGxldCBmb3JtID0gJChlLnRhcmdldCkuY2xvc2VzdCgnLnZpLXdiZS1mb3JtdWxhLWNvbnRhaW5lcicpLFxyXG4gICAgICAgICAgICBmaW5kVmFsdWUgPSBmb3JtLmZpbmQoJy52aS13YmUtZmluZC1zdHJpbmcnKS52YWwoKSxcclxuICAgICAgICAgICAgcmVwbGFjZVZhbHVlID0gZm9ybS5maW5kKCcudmktd2JlLXJlcGxhY2Utc3RyaW5nJykudmFsKCksXHJcbiAgICAgICAgICAgIGV4Y2VsT2JqID0gdGhpcy5vYmo7XHJcblxyXG4gICAgICAgIGlmICghZmluZFZhbHVlICYmICFyZXBsYWNlVmFsdWUpIHJldHVybjtcclxuXHJcbiAgICAgICAgZmluZFZhbHVlID0gIWlzTmFOKGZpbmRWYWx1ZSkgPyArZmluZFZhbHVlIDogZmluZFZhbHVlO1xyXG4gICAgICAgIHJlcGxhY2VWYWx1ZSA9ICFpc05hTihyZXBsYWNlVmFsdWUpID8gK3JlcGxhY2VWYWx1ZSA6IHJlcGxhY2VWYWx1ZTtcclxuXHJcbiAgICAgICAgbGV0IGJyZWFrQ29udHJvbCA9IGZhbHNlLCByZWNvcmRzID0gW107XHJcbiAgICAgICAgbGV0IGggPSB0aGlzLmNlbGxzO1xyXG4gICAgICAgIGxldCBzdGFydCA9IGhbMV0sIGVuZCA9IGhbM10sIHggPSBoWzBdO1xyXG5cclxuICAgICAgICBmb3IgKGxldCB5ID0gc3RhcnQ7IHkgPD0gZW5kOyB5KyspIHtcclxuICAgICAgICAgICAgaWYgKGV4Y2VsT2JqLnJlY29yZHNbeV1beF0gJiYgIWV4Y2VsT2JqLnJlY29yZHNbeV1beF0uY2xhc3NMaXN0LmNvbnRhaW5zKCdyZWFkb25seScpICYmIGV4Y2VsT2JqLnJlY29yZHNbeV1beF0uc3R5bGUuZGlzcGxheSAhPT0gJ25vbmUnICYmIGJyZWFrQ29udHJvbCA9PT0gZmFsc2UpIHtcclxuICAgICAgICAgICAgICAgIGxldCB2YWx1ZSA9IGV4Y2VsT2JqLm9wdGlvbnMuZGF0YVt5XVt4XTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAoIXZhbHVlKSB2YWx1ZSA9IFtdO1xyXG5cclxuICAgICAgICAgICAgICAgIGxldCBuZXdWYWx1ZSA9IHZhbHVlLmZpbHRlcigoaXRlbSkgPT4gaXRlbSAhPT0gZmluZFZhbHVlKTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAodmFsdWUubGVuZ3RoICE9PSBuZXdWYWx1ZS5sZW5ndGggfHwgIWZpbmRWYWx1ZSkge1xyXG4gICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlLnB1c2gocmVwbGFjZVZhbHVlKTtcclxuICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICBuZXdWYWx1ZSA9IFsuLi5uZXcgU2V0KG5ld1ZhbHVlKV07XHJcblxyXG4gICAgICAgICAgICAgICAgcmVjb3Jkcy5wdXNoKGV4Y2VsT2JqLnVwZGF0ZUNlbGwoeCwgeSwgbmV3VmFsdWUpKTtcclxuICAgICAgICAgICAgICAgIGV4Y2VsT2JqLnVwZGF0ZUZvcm11bGFDaGFpbih4LCB5LCByZWNvcmRzKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgLy8gVXBkYXRlIGhpc3RvcnlcclxuICAgICAgICBleGNlbE9iai5zZXRIaXN0b3J5KHtcclxuICAgICAgICAgICAgYWN0aW9uOiAnc2V0VmFsdWUnLFxyXG4gICAgICAgICAgICByZWNvcmRzOiByZWNvcmRzLFxyXG4gICAgICAgICAgICBzZWxlY3Rpb246IGV4Y2VsT2JqLnNlbGVjdGVkQ2VsbCxcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgLy8gVXBkYXRlIHRhYmxlIHdpdGggY3VzdG9tIGNvbmZpZ3VyYXRpb24gaWYgYXBwbGljYWJsZVxyXG4gICAgICAgIGV4Y2VsT2JqLnVwZGF0ZVRhYmxlKCk7XHJcbiAgICB9XHJcblxyXG59IiwiaW1wb3J0IF9mIGZyb20gJy4vZnVuY3Rpb25zJztcclxuaW1wb3J0IHtQb3B1cH0gZnJvbSBcIi4vbW9kYWwtcG9wdXBcIjtcclxuaW1wb3J0IHtBdHRyaWJ1dGVzfSBmcm9tIFwiLi9hdHRyaWJ1dGVzXCI7XHJcblxyXG5jb25zdCAkID0galF1ZXJ5O1xyXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBGaW5kQW5kUmVwbGFjZVRhZ3Mge1xyXG4gICAgY29uc3RydWN0b3Iob2JqLCBjZWxscywgeCwgeSwgZSkge1xyXG4gICAgICAgIHRoaXMuY2VsbHMgPSBjZWxscztcclxuICAgICAgICB0aGlzLm9iaiA9IG9iajtcclxuICAgICAgICB0aGlzLnggPSBwYXJzZUludCh4KTtcclxuICAgICAgICB0aGlzLnkgPSBwYXJzZUludCh5KTtcclxuICAgICAgICB0aGlzLnNlYXJjaERhdGEgPSBbXTtcclxuXHJcbiAgICAgICAgdGhpcy5ydW4oKTtcclxuICAgIH1cclxuXHJcbiAgICBydW4oKSB7XHJcbiAgICAgICAgbGV0ICR0aGlzID0gdGhpcztcclxuICAgICAgICBsZXQgZm9ybXVsYUh0bWwgPSB0aGlzLmNvbnRlbnQoKTtcclxuICAgICAgICBsZXQgeTEgPSB0aGlzLmNlbGxzWzFdLCB5MiA9IHRoaXMuY2VsbHNbM107XHJcbiAgICAgICAgbGV0IHNlbGVjdERhdGEgPSBbe2lkOiAnJywgdGV4dDogJyd9XTtcclxuICAgICAgICBmb3IgKGxldCBpID0geTE7IGkgPD0geTI7IGkrKykge1xyXG4gICAgICAgICAgICBsZXQgdmFsdWUgPSB0aGlzLm9iai5vcHRpb25zLmRhdGFbaV1bdGhpcy54XTtcclxuICAgICAgICAgICAgc2VsZWN0RGF0YS5wdXNoKC4uLnZhbHVlKTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHNlbGVjdERhdGEgPSBzZWxlY3REYXRhLmZpbHRlcigoaXRlbSwgaW5kZXgsIHNlbGYpID0+XHJcbiAgICAgICAgICAgIGluZGV4ID09PSBzZWxmLmZpbmRJbmRleCgodCkgPT4gKFxyXG4gICAgICAgICAgICAgICAgdC5pZCA9PT0gaXRlbS5pZCAmJiB0LnRleHQgPT09IGl0ZW0udGV4dFxyXG4gICAgICAgICAgICApKVxyXG4gICAgICAgICk7XHJcblxyXG4gICAgICAgIGxldCBjZWxsID0gJChgdGRbZGF0YS14PSR7dGhpcy54IHx8IDB9XVtkYXRhLXk9JHt0aGlzLnkgfHwgMH1dYCk7XHJcbiAgICAgICAgbmV3IFBvcHVwKGZvcm11bGFIdG1sLCBjZWxsKTtcclxuXHJcbiAgICAgICAgZm9ybXVsYUh0bWwuZmluZCgnLnZpLXdiZS1maW5kLXN0cmluZycpLnNlbGVjdDIoe1xyXG4gICAgICAgICAgICBkYXRhOiBzZWxlY3REYXRhXHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIGZvcm11bGFIdG1sLmZpbmQoJy52aS13YmUtcmVwbGFjZS1zdHJpbmcnKS5zZWxlY3QyKHtcclxuICAgICAgICAgICAgbXVsdGlwbGU6IGZhbHNlLFxyXG4gICAgICAgICAgICBtaW5pbXVtSW5wdXRMZW5ndGg6IDMsXHJcbiAgICAgICAgICAgIGFqYXg6IHtcclxuICAgICAgICAgICAgICAgIHVybDogQXR0cmlidXRlcy5hamF4VXJsLFxyXG4gICAgICAgICAgICAgICAgdHlwZTogJ3Bvc3QnLFxyXG4gICAgICAgICAgICAgICAgZGF0YTogZnVuY3Rpb24gKHBhcmFtcykge1xyXG4gICAgICAgICAgICAgICAgICAgIHJldHVybiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC4uLkF0dHJpYnV0ZXMuYWpheERhdGEsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHN1Yl9hY3Rpb246ICdzZWFyY2hfdGFncycsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHNlYXJjaDogcGFyYW1zLnRlcm0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU6ICdwdWJsaWMnXHJcbiAgICAgICAgICAgICAgICAgICAgfTtcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICBwcm9jZXNzUmVzdWx0czogZnVuY3Rpb24gKGRhdGEpIHtcclxuICAgICAgICAgICAgICAgICAgICAkdGhpcy5zZWFyY2hEYXRhID0gZGF0YTtcclxuICAgICAgICAgICAgICAgICAgICByZXR1cm4ge3Jlc3VsdHM6IGRhdGF9O1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIGZvcm11bGFIdG1sLm9uKCdjbGljaycsICcudmktd2JlLWFwcGx5LWZvcm11bGEnLCB0aGlzLmFwcGx5Rm9ybXVsYS5iaW5kKHRoaXMpKTtcclxuICAgIH1cclxuXHJcbiAgICBjb250ZW50KCkge1xyXG4gICAgICAgIHJldHVybiAkKGA8ZGl2IGNsYXNzPVwidmktd2JlLWZvcm11bGEtY29udGFpbmVyXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImZpZWxkXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXY+JHtfZi50ZXh0KCdGaW5kJyl9PC9kaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxzZWxlY3QgcGxhY2Vob2xkZXI9XCJcIiBjbGFzcz1cInZpLXdiZS1maW5kLXN0cmluZ1wiPiA8L3NlbGVjdD5cclxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwiZmllbGRcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPGRpdj4ke19mLnRleHQoJ1JlcGxhY2UnKX08L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPHNlbGVjdCBwbGFjZWhvbGRlcj1cIlwiIGNsYXNzPVwidmktd2JlLXJlcGxhY2Utc3RyaW5nXCI+IDwvc2VsZWN0PlxyXG4gICAgICAgICAgICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgIDxidXR0b24gdHlwZT1cImJ1dHRvblwiIGNsYXNzPVwidmktdWkgYnV0dG9uIG1pbmkgdmktd2JlLWFwcGx5LWZvcm11bGFcIj4ke19mLnRleHQoJ1JlcGxhY2UnKX08L2J1dHRvbj5cclxuICAgICAgICAgICAgICAgICAgICA8cD5JZiAnRmluZCcgdmFsdWUgaXMgZW1wdHksIGFkZCB0byBzZWxlY3RlZCBjZWxscyB3aXRoICdSZXBsYWNlJyB2YWx1ZS48L3A+XHJcbiAgICAgICAgICAgICAgICAgICAgPHA+SWYgJ1JlcGxhY2UnIHZhbHVlIGlzIGVtcHR5LCByZW1vdmUgZnJvbSBzZWxlY3RlZCBjZWxscyB3aXRoICdGaW5kJyB2YWx1ZS48L3A+XHJcbiAgICAgICAgICAgICAgICA8L2Rpdj5gKTtcclxuICAgIH1cclxuXHJcbiAgICBhcHBseUZvcm11bGEoZSkge1xyXG4gICAgICAgIGxldCBmb3JtID0gJChlLnRhcmdldCkuY2xvc2VzdCgnLnZpLXdiZS1mb3JtdWxhLWNvbnRhaW5lcicpLFxyXG4gICAgICAgICAgICBmaW5kU3RyaW5nID0gZm9ybS5maW5kKCcudmktd2JlLWZpbmQtc3RyaW5nJykudmFsKCksXHJcbiAgICAgICAgICAgIHJlcGxhY2VTdHJpbmcgPSBmb3JtLmZpbmQoJy52aS13YmUtcmVwbGFjZS1zdHJpbmcnKS52YWwoKSxcclxuICAgICAgICAgICAgZXhjZWxPYmogPSB0aGlzLm9iajtcclxuXHJcbiAgICAgICAgaWYgKCFmaW5kU3RyaW5nICYmICFyZXBsYWNlU3RyaW5nKSByZXR1cm47XHJcblxyXG4gICAgICAgIGxldCByZXBsYWNlID0gdGhpcy5zZWFyY2hEYXRhLmZpbHRlcigoaXRlbSkgPT4gaXRlbS5pZCA9PT0gK3JlcGxhY2VTdHJpbmcpO1xyXG5cclxuICAgICAgICBsZXQgYnJlYWtDb250cm9sID0gZmFsc2UsIHJlY29yZHMgPSBbXTtcclxuICAgICAgICBsZXQgaCA9IHRoaXMuY2VsbHM7XHJcbiAgICAgICAgbGV0IHN0YXJ0ID0gaFsxXSwgZW5kID0gaFszXSwgeCA9IGhbMF07XHJcblxyXG4gICAgICAgIGZvciAobGV0IHkgPSBzdGFydDsgeSA8PSBlbmQ7IHkrKykge1xyXG4gICAgICAgICAgICBpZiAoZXhjZWxPYmoucmVjb3Jkc1t5XVt4XSAmJiAhZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5jbGFzc0xpc3QuY29udGFpbnMoJ3JlYWRvbmx5JykgJiYgZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5zdHlsZS5kaXNwbGF5ICE9PSAnbm9uZScgJiYgYnJlYWtDb250cm9sID09PSBmYWxzZSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IHZhbHVlID0gZXhjZWxPYmoub3B0aW9ucy5kYXRhW3ldW3hdO1xyXG4gICAgICAgICAgICAgICAgaWYgKCF2YWx1ZSkgdmFsdWUgPSBbXTtcclxuICAgICAgICAgICAgICAgIGxldCBuZXdWYWx1ZSA9IHZhbHVlLmZpbHRlcigoaXRlbSkgPT4gaXRlbS5pZCAhPT0gK2ZpbmRTdHJpbmcpO1xyXG5cclxuICAgICAgICAgICAgICAgIGlmICh2YWx1ZS5sZW5ndGggIT09IG5ld1ZhbHVlLmxlbmd0aCB8fCAhZmluZFN0cmluZykge1xyXG4gICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlLnB1c2goLi4ucmVwbGFjZSk7XHJcbiAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgbmV3VmFsdWUgPSBuZXdWYWx1ZS5maWx0ZXIoKGl0ZW0sIGluZGV4LCBzZWxmKSA9PlxyXG4gICAgICAgICAgICAgICAgICAgIGluZGV4ID09PSBzZWxmLmZpbmRJbmRleCgodCkgPT4gKHQuaWQgPT09IGl0ZW0uaWQgJiYgdC50ZXh0ID09PSBpdGVtLnRleHQpKVxyXG4gICAgICAgICAgICAgICAgKTtcclxuXHJcbiAgICAgICAgICAgICAgICByZWNvcmRzLnB1c2goZXhjZWxPYmoudXBkYXRlQ2VsbCh4LCB5LCBuZXdWYWx1ZSkpO1xyXG4gICAgICAgICAgICAgICAgZXhjZWxPYmoudXBkYXRlRm9ybXVsYUNoYWluKHgsIHksIHJlY29yZHMpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAvLyBVcGRhdGUgaGlzdG9yeVxyXG4gICAgICAgIGV4Y2VsT2JqLnNldEhpc3Rvcnkoe1xyXG4gICAgICAgICAgICBhY3Rpb246ICdzZXRWYWx1ZScsXHJcbiAgICAgICAgICAgIHJlY29yZHM6IHJlY29yZHMsXHJcbiAgICAgICAgICAgIHNlbGVjdGlvbjogZXhjZWxPYmouc2VsZWN0ZWRDZWxsLFxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBVcGRhdGUgdGFibGUgd2l0aCBjdXN0b20gY29uZmlndXJhdGlvbiBpZiBhcHBsaWNhYmxlXHJcbiAgICAgICAgZXhjZWxPYmoudXBkYXRlVGFibGUoKTtcclxuICAgIH1cclxuXHJcbn0iLCJpbXBvcnQgX2YgZnJvbSAnLi9mdW5jdGlvbnMnO1xyXG5pbXBvcnQge1BvcHVwfSBmcm9tIFwiLi9tb2RhbC1wb3B1cFwiO1xyXG5cclxuY29uc3QgJCA9IGpRdWVyeTtcclxuZXhwb3J0IGRlZmF1bHQgY2xhc3MgRmluZEFuZFJlcGxhY2Uge1xyXG4gICAgY29uc3RydWN0b3Iob2JqLCB4LCB5LCBlKSB7XHJcbiAgICAgICAgdGhpcy5fZGF0YSA9IHt9O1xyXG4gICAgICAgIHRoaXMuX2RhdGEuamV4Y2VsID0gb2JqO1xyXG4gICAgICAgIHRoaXMuX2RhdGEueCA9IHBhcnNlSW50KHgpO1xyXG4gICAgICAgIHRoaXMuX2RhdGEueSA9IHBhcnNlSW50KHkpO1xyXG4gICAgICAgIHRoaXMucnVuKCk7XHJcbiAgICB9XHJcblxyXG4gICAgZ2V0KGlkKSB7XHJcbiAgICAgICAgcmV0dXJuIHRoaXMuX2RhdGFbaWRdIHx8ICcnO1xyXG4gICAgfVxyXG5cclxuICAgIHJ1bigpIHtcclxuICAgICAgICBsZXQgZm9ybXVsYUh0bWwgPSB0aGlzLmNvbnRlbnQoKTtcclxuICAgICAgICBsZXQgY2VsbCA9ICQoYHRkW2RhdGEteD0ke3RoaXMuZ2V0KCd4JykgfHwgMH1dW2RhdGEteT0ke3RoaXMuZ2V0KCd5JykgfHwgMH1dYCk7XHJcbiAgICAgICAgbmV3IFBvcHVwKGZvcm11bGFIdG1sLCBjZWxsKTtcclxuICAgICAgICBmb3JtdWxhSHRtbC5vbignY2xpY2snLCAnLnZpLXdiZS1hcHBseS1mb3JtdWxhJywgdGhpcy5hcHBseUZvcm11bGEuYmluZCh0aGlzKSk7XHJcbiAgICB9XHJcblxyXG4gICAgY29udGVudCgpIHtcclxuICAgICAgICByZXR1cm4gJChgPGRpdiBjbGFzcz1cInZpLXdiZS1mb3JtdWxhLWNvbnRhaW5lclwiPlxyXG4gICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJmaWVsZFwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8aW5wdXQgdHlwZT1cInRleHRcIiBwbGFjZWhvbGRlcj1cIiR7X2YudGV4dCgnRmluZCcpfVwiIGNsYXNzPVwidmktd2JlLWZpbmQtc3RyaW5nXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImZpZWxkXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxpbnB1dCB0eXBlPVwidGV4dFwiIHBsYWNlaG9sZGVyPVwiJHtfZi50ZXh0KCdSZXBsYWNlJyl9XCIgY2xhc3M9XCJ2aS13YmUtcmVwbGFjZS1zdHJpbmdcIj5cclxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzcz1cInZpLXVpIGJ1dHRvbiBtaW5pIHZpLXdiZS1hcHBseS1mb3JtdWxhXCI+JHtfZi50ZXh0KCdSZXBsYWNlJyl9PC9idXR0b24+XHJcbiAgICAgICAgICAgICAgICA8L2Rpdj5gKTtcclxuICAgIH1cclxuXHJcbiAgICBhcHBseUZvcm11bGEoZSkge1xyXG4gICAgICAgIGxldCBmb3JtID0gJChlLnRhcmdldCkuY2xvc2VzdCgnLnZpLXdiZS1mb3JtdWxhLWNvbnRhaW5lcicpLFxyXG4gICAgICAgICAgICBmaW5kU3RyaW5nID0gZm9ybS5maW5kKCcudmktd2JlLWZpbmQtc3RyaW5nJykudmFsKCksXHJcbiAgICAgICAgICAgIHJlcGxhY2VTdHJpbmcgPSBmb3JtLmZpbmQoJy52aS13YmUtcmVwbGFjZS1zdHJpbmcnKS52YWwoKSxcclxuICAgICAgICAgICAgZXhjZWxPYmogPSB0aGlzLmdldCgnamV4Y2VsJyk7XHJcblxyXG4gICAgICAgIGlmICghZmluZFN0cmluZykgcmV0dXJuO1xyXG5cclxuICAgICAgICBsZXQgYnJlYWtDb250cm9sID0gZmFsc2UsIHJlY29yZHMgPSBbXTtcclxuICAgICAgICBsZXQgaCA9IGV4Y2VsT2JqLnNlbGVjdGVkQ29udGFpbmVyO1xyXG4gICAgICAgIGxldCBzdGFydCA9IGhbMV0sIGVuZCA9IGhbM10sIHggPSBoWzBdO1xyXG5cclxuICAgICAgICBmb3IgKGxldCB5ID0gc3RhcnQ7IHkgPD0gZW5kOyB5KyspIHtcclxuICAgICAgICAgICAgaWYgKGV4Y2VsT2JqLnJlY29yZHNbeV1beF0gJiYgIWV4Y2VsT2JqLnJlY29yZHNbeV1beF0uY2xhc3NMaXN0LmNvbnRhaW5zKCdyZWFkb25seScpICYmIGV4Y2VsT2JqLnJlY29yZHNbeV1beF0uc3R5bGUuZGlzcGxheSAhPT0gJ25vbmUnICYmIGJyZWFrQ29udHJvbCA9PT0gZmFsc2UpIHtcclxuICAgICAgICAgICAgICAgIGxldCB2YWx1ZSA9IGV4Y2VsT2JqLm9wdGlvbnMuZGF0YVt5XVt4XTtcclxuICAgICAgICAgICAgICAgIGxldCBuZXdWYWx1ZSA9IHZhbHVlLnJlcGxhY2VBbGwoZmluZFN0cmluZywgcmVwbGFjZVN0cmluZyk7XHJcbiAgICAgICAgICAgICAgICByZWNvcmRzLnB1c2goZXhjZWxPYmoudXBkYXRlQ2VsbCh4LCB5LCBuZXdWYWx1ZSkpO1xyXG4gICAgICAgICAgICAgICAgZXhjZWxPYmoudXBkYXRlRm9ybXVsYUNoYWluKHgsIHksIHJlY29yZHMpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAvLyBVcGRhdGUgaGlzdG9yeVxyXG4gICAgICAgIGV4Y2VsT2JqLnNldEhpc3Rvcnkoe1xyXG4gICAgICAgICAgICBhY3Rpb246ICdzZXRWYWx1ZScsXHJcbiAgICAgICAgICAgIHJlY29yZHM6IHJlY29yZHMsXHJcbiAgICAgICAgICAgIHNlbGVjdGlvbjogZXhjZWxPYmouc2VsZWN0ZWRDZWxsLFxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBVcGRhdGUgdGFibGUgd2l0aCBjdXN0b20gY29uZmlndXJhdGlvbiBpZiBhcHBsaWNhYmxlXHJcbiAgICAgICAgZXhjZWxPYmoudXBkYXRlVGFibGUoKTtcclxuICAgIH1cclxuXHJcbn0iLCJpbXBvcnQge0F0dHJpYnV0ZXMsIEkxOG59IGZyb20gXCIuL2F0dHJpYnV0ZXNcIjtcclxuaW1wb3J0IFRlbXBsYXRlcyBmcm9tIFwiLi90ZW1wbGF0ZXNcIjtcclxuXHJcbmNvbnN0ICQgPSBqUXVlcnk7XHJcbmNvbnN0IF9mID0ge1xyXG4gICAgc2V0SmV4Y2VsKG9iaikge1xyXG4gICAgICAgIHRoaXMuamV4Y2VsID0gb2JqO1xyXG4gICAgfSxcclxuXHJcbiAgICB0ZXh0KGtleSkge1xyXG4gICAgICAgIHJldHVybiBJMThuW2tleV0gfHwga2V5O1xyXG4gICAgfSxcclxuXHJcbiAgICBpc1VybDogKHVybCkgPT4ge1xyXG4gICAgICAgIHJldHVybiAvXihodHRwKHM/KTopXFwvXFwvLipcXC4oPzpqcGd8anBlZ3xnaWZ8cG5nfHdlYnApJC9pLnRlc3QodXJsKTtcclxuICAgIH0sXHJcblxyXG4gICAgZm9ybWF0VGV4dChjZWxsLCB2YWx1ZSkge1xyXG4gICAgICAgIGxldCB0ZXh0ID0gJyc7XHJcbiAgICAgICAgaWYgKHZhbHVlLmxlbmd0aCkge1xyXG4gICAgICAgICAgICBmb3IgKGxldCBrID0gMDsgayA8IHZhbHVlLmxlbmd0aDsgaysrKSB7XHJcbiAgICAgICAgICAgICAgICBpZiAodmFsdWVba10pIHRleHQgKz0gdmFsdWVba10udGV4dCArICc7ICc7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcbiAgICAgICAgY2VsbC5pbm5lclRleHQgPSB0ZXh0O1xyXG4gICAgfSxcclxuXHJcbiAgICBjcmVhdGVFZGl0b3IoY2VsbCwgdHlwZSwgY29udGVudCA9ICcnLCBkaXNwbGF5ID0gdHJ1ZSkge1xyXG4gICAgICAgIGxldCBlZGl0b3IgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KHR5cGUpO1xyXG5cclxuICAgICAgICBpZiAodHlwZSA9PT0gJ2RpdicpIHtcclxuICAgICAgICAgICAgJChlZGl0b3IpLmFwcGVuZChjb250ZW50KTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGVkaXRvci5zdHlsZS5taW5XaWR0aCA9ICczMDBweCc7XHJcblxyXG4gICAgICAgIGxldCBwb3B1cEhlaWdodCA9ICQoZWRpdG9yKS5pbm5lckhlaWdodCgpLFxyXG4gICAgICAgICAgICBzdGFnZSA9ICQoY2VsbCkub2Zmc2V0KCksXHJcbiAgICAgICAgICAgIHggPSBzdGFnZS5sZWZ0LFxyXG4gICAgICAgICAgICB5ID0gc3RhZ2UudG9wLFxyXG4gICAgICAgICAgICBjZWxsV2lkdGggPSAkKGNlbGwpLmlubmVyV2lkdGgoKSxcclxuICAgICAgICAgICAgaW5mbyA9IGNlbGwuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCk7XHJcblxyXG4gICAgICAgIGlmIChkaXNwbGF5KSB7XHJcbiAgICAgICAgICAgIGVkaXRvci5zdHlsZS5taW5IZWlnaHQgPSAoaW5mby5oZWlnaHQgLSAyKSArICdweCc7XHJcbiAgICAgICAgICAgIGVkaXRvci5zdHlsZS5tYXhIZWlnaHQgPSAod2luZG93LmlubmVySGVpZ2h0IC0geSAtIDUwKSArICdweCc7XHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgZWRpdG9yLnN0eWxlLm9wYWNpdHkgPSAwO1xyXG4gICAgICAgICAgICBlZGl0b3Iuc3R5bGUuZm9udFNpemUgPSAwO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgZWRpdG9yLmNsYXNzTGlzdC5hZGQoJ3ZpLXVpJywgJ3NlZ21lbnQnLCAndmktd2JlLWNlbGwtcG9wdXAnLCAndmktd2JlLWVkaXRpbmcnKTtcclxuICAgICAgICBjZWxsLmNsYXNzTGlzdC5hZGQoJ2VkaXRvcicpO1xyXG4gICAgICAgIGNlbGwuYXBwZW5kQ2hpbGQoZWRpdG9yKTtcclxuXHJcbiAgICAgICAgbGV0IHBvcHVwV2lkdGggPSAkKGVkaXRvcikuaW5uZXJXaWR0aCgpO1xyXG5cclxuICAgICAgICBpZiAoJCh0aGlzLmpleGNlbC5lbCkuaW5uZXJXaWR0aCgpIDwgeCArIHBvcHVwV2lkdGggKyBjZWxsV2lkdGgpIHtcclxuICAgICAgICAgICAgbGV0IGxlZnQgPSB4IC0gcG9wdXBXaWR0aCA+IDAgPyB4IC0gcG9wdXBXaWR0aCA6IDEwO1xyXG4gICAgICAgICAgICAkKGVkaXRvcikuY3NzKCdsZWZ0JywgbGVmdCArICdweCcpO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICQoZWRpdG9yKS5jc3MoJ2xlZnQnLCAoeCArIGNlbGxXaWR0aCkgKyAncHgnKTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGlmICh3aW5kb3cuaW5uZXJIZWlnaHQgPCB5ICsgcG9wdXBIZWlnaHQpIHtcclxuICAgICAgICAgICAgbGV0IGggPSB5IC0gcG9wdXBIZWlnaHQgPCAwID8gMCA6IHkgLSBwb3B1cEhlaWdodDtcclxuICAgICAgICAgICAgJChlZGl0b3IpLmNzcygndG9wJywgaCArICdweCcpO1xyXG4gICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICQoZWRpdG9yKS5jc3MoJ3RvcCcsIHkgKyAncHgnKTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHJldHVybiBlZGl0b3I7XHJcbiAgICB9LFxyXG5cclxuICAgIGNyZWF0ZU1vZGFsKGRhdGEgPSB7fSkge1xyXG4gICAgICAgIGxldCB7YWN0aW9uc30gPSBkYXRhO1xyXG4gICAgICAgIGxldCBhY3Rpb25zSHRtbCA9ICcnO1xyXG5cclxuICAgICAgICBpZiAoQXJyYXkuaXNBcnJheShhY3Rpb25zKSkge1xyXG4gICAgICAgICAgICBmb3IgKGxldCBpdGVtIG9mIGFjdGlvbnMpIHtcclxuICAgICAgICAgICAgICAgIGFjdGlvbnNIdG1sICs9IGA8c3BhbiBjbGFzcz1cIiR7aXRlbS5jbGFzc30gdmktdWkgYnV0dG9uIHRpbnlcIj4ke2l0ZW0udGV4dH08L3NwYW4+YDtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgcmV0dXJuICQoVGVtcGxhdGVzLm1vZGFsKHsuLi5kYXRhLCBhY3Rpb25zSHRtbH0pKTtcclxuICAgIH0sXHJcblxyXG4gICAgcmVtb3ZlTW9kYWwoY2VsbCkge1xyXG4gICAgICAgICQoY2VsbCkuZmluZCgnLnZpLXdiZS1tb2RhbC1jb250YWluZXInKS5yZW1vdmUoKTtcclxuICAgICAgICAkKCcuc2VsZWN0Mi1jb250YWluZXItLW9wZW4nKS5yZW1vdmUoKTtcclxuICAgIH0sXHJcblxyXG4gICAgZ2V0Q29sRnJvbUNvbHVtblR5cGUoY29sVHlwZSkge1xyXG4gICAgICAgIHJldHVybiBBdHRyaWJ1dGVzLmlkTWFwcGluZ0ZsaXBbY29sVHlwZV0gfHwgJyc7XHJcbiAgICB9LFxyXG5cclxuICAgIGdldFByb2R1Y3RUeXBlRnJvbUNlbGwoY2VsbCkge1xyXG4gICAgICAgIGxldCB5ID0gY2VsbC5nZXRBdHRyaWJ1dGUoJ2RhdGEteScpO1xyXG4gICAgICAgIGxldCB4ID0gdGhpcy5nZXRDb2xGcm9tQ29sdW1uVHlwZSgncHJvZHVjdF90eXBlJyk7XHJcbiAgICAgICAgcmV0dXJuIHRoaXMuamV4Y2VsLm9wdGlvbnMuZGF0YVt5XVt4XTtcclxuICAgIH0sXHJcblxyXG4gICAgZ2V0UHJvZHVjdFR5cGVGcm9tWSh5KSB7XHJcbiAgICAgICAgbGV0IHggPSB0aGlzLmdldENvbEZyb21Db2x1bW5UeXBlKCdwcm9kdWN0X3R5cGUnKTtcclxuICAgICAgICAvLyBjb25zb2xlLmxvZyh0aGlzLmpleGNlbC5vcHRpb25zLmRhdGEpXHJcbiAgICAgICAgcmV0dXJuIHRoaXMuamV4Y2VsLm9wdGlvbnMuZGF0YVt5XVt4XTtcclxuICAgIH0sXHJcblxyXG4gICAgZ2V0Q29sdW1uVHlwZSh4KSB7XHJcbiAgICAgICAgcmV0dXJuIEF0dHJpYnV0ZXMuaWRNYXBwaW5nW3hdXHJcbiAgICB9LFxyXG5cclxuICAgIHN0cmlwSHRtbChjb250ZW50KSB7XHJcbiAgICAgICAgcmV0dXJuICQoYDxkaXY+JHtjb250ZW50fTwvZGl2PmApLnRleHQoKTtcclxuICAgIH0sXHJcblxyXG4gICAgZ2V0RGF0YUZyb21DZWxsKG9iaiwgY2VsbCkge1xyXG4gICAgICAgIGxldCB5ID0gY2VsbC5nZXRBdHRyaWJ1dGUoJ2RhdGEteScpLFxyXG4gICAgICAgICAgICB4ID0gY2VsbC5nZXRBdHRyaWJ1dGUoJ2RhdGEteCcpO1xyXG4gICAgICAgIHJldHVybiBvYmoub3B0aW9ucy5kYXRhW3ldW3hdO1xyXG4gICAgfSxcclxuXHJcbiAgICBnZXRQcm9kdWN0SWRPZkNlbGwob2JqLCB0YXJnZXQpIHtcclxuICAgICAgICBpZiAodHlwZW9mIHRhcmdldCA9PT0gJ29iamVjdCcpIHtcclxuICAgICAgICAgICAgbGV0IHkgPSB0YXJnZXQuZ2V0QXR0cmlidXRlKCdkYXRhLXknKTtcclxuICAgICAgICAgICAgcmV0dXJuIG9iai5vcHRpb25zLmRhdGFbeV1bMF07XHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgcmV0dXJuIG9iai5vcHRpb25zLmRhdGFbdGFyZ2V0XVswXTtcclxuICAgICAgICB9XHJcbiAgICB9LFxyXG5cclxuICAgIGFqYXgoYXJncyA9IHt9KSB7XHJcbiAgICAgICAgbGV0IG9wdGlvbnMgPSBPYmplY3QuYXNzaWduKHtcclxuICAgICAgICAgICAgdXJsOiB3YmVQYXJhbXMuYWpheFVybCxcclxuICAgICAgICAgICAgdHlwZTogJ3Bvc3QnLFxyXG4gICAgICAgICAgICBkYXRhVHlwZTogJ2pzb24nLFxyXG4gICAgICAgIH0sIGFyZ3MpO1xyXG5cclxuICAgICAgICBvcHRpb25zLmRhdGEuYWN0aW9uID0gJ3ZpX3diZV9hamF4JztcclxuICAgICAgICBvcHRpb25zLmRhdGEudmlfd2JlX25vbmNlID0gd2JlUGFyYW1zLm5vbmNlO1xyXG4gICAgICAgIG9wdGlvbnMuZGF0YS50eXBlID0gd2JlUGFyYW1zLmVkaXRUeXBlO1xyXG5cclxuICAgICAgICAkLmFqYXgob3B0aW9ucyk7XHJcbiAgICB9LFxyXG5cclxuICAgIHBhZ2luYXRpb24obWF4UGFnZSwgY3VycmVudFBhZ2UpIHtcclxuICAgICAgICBjdXJyZW50UGFnZSA9IHBhcnNlSW50KGN1cnJlbnRQYWdlKTtcclxuICAgICAgICBtYXhQYWdlID0gcGFyc2VJbnQobWF4UGFnZSk7XHJcbiAgICAgICAgbGV0IHBhZ2luYXRpb24gPSAnJyxcclxuICAgICAgICAgICAgcHJldmlvdXNBcnJvdyA9IGA8YSBjbGFzcz1cIml0ZW0gJHtjdXJyZW50UGFnZSA9PT0gMSA/ICdkaXNhYmxlZCcgOiAnJ31cIiBkYXRhLXBhZ2U9XCIke2N1cnJlbnRQYWdlIC0gMX1cIj48aSBjbGFzcz1cImljb24gYW5nbGUgbGVmdFwiPiA8L2k+PC9hPmAsXHJcbiAgICAgICAgICAgIG5leHRBcnJvdyA9IGA8YSBjbGFzcz1cIml0ZW0gJHtjdXJyZW50UGFnZSA9PT0gbWF4UGFnZSA/ICdkaXNhYmxlZCcgOiAnJ31cIiBkYXRhLXBhZ2U9XCIke2N1cnJlbnRQYWdlICsgMX1cIj48aSBjbGFzcz1cImljb24gYW5nbGUgcmlnaHRcIj4gPC9pPjwvYT5gLFxyXG4gICAgICAgICAgICBnb1RvUGFnZSA9IGA8aW5wdXQgdHlwZT1cIm51bWJlclwiIGNsYXNzPVwidmktd2JlLWdvLXRvLXBhZ2VcIiB2YWx1ZT1cIiR7Y3VycmVudFBhZ2V9XCIgbWluPVwiMVwiIG1heD1cIiR7bWF4UGFnZX1cIi8+YDtcclxuXHJcbiAgICAgICAgZm9yIChsZXQgaSA9IDE7IGkgPD0gbWF4UGFnZTsgaSsrKSB7XHJcbiAgICAgICAgICAgIGlmIChbMSwgY3VycmVudFBhZ2UgLSAxLCBjdXJyZW50UGFnZSwgY3VycmVudFBhZ2UgKyAxLCBtYXhQYWdlXS5pbmNsdWRlcyhpKSkge1xyXG4gICAgICAgICAgICAgICAgcGFnaW5hdGlvbiArPSBgPGEgY2xhc3M9XCJpdGVtICR7Y3VycmVudFBhZ2UgPT09IGkgPyAnYWN0aXZlJyA6ICcnfVwiIGRhdGEtcGFnZT1cIiR7aX1cIj4ke2l9PC9hPmA7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgaWYgKGkgPT09IGN1cnJlbnRQYWdlIC0gMiAmJiBjdXJyZW50UGFnZSAtIDIgPiAxKSBwYWdpbmF0aW9uICs9IGA8YSBjbGFzcz1cIml0ZW0gZGlzYWJsZWRcIj4uLi48L2E+YDtcclxuICAgICAgICAgICAgaWYgKGkgPT09IGN1cnJlbnRQYWdlICsgMiAmJiBjdXJyZW50UGFnZSArIDIgPCBtYXhQYWdlKSBwYWdpbmF0aW9uICs9IGA8YSBjbGFzcz1cIml0ZW0gZGlzYWJsZWRcIj4uLi48L2E+YDtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHJldHVybiBgPGRpdiBjbGFzcz1cInZpLXVpIHBhZ2luYXRpb24gbWVudVwiPiR7cHJldmlvdXNBcnJvd30gJHtwYWdpbmF0aW9ufSAke25leHRBcnJvd30gPC9kaXY+ICR7Z29Ub1BhZ2V9YDtcclxuICAgIH0sXHJcblxyXG4gICAgc3Bpbm5lcigpIHtcclxuICAgICAgICByZXR1cm4gJCgnPHNwYW4gY2xhc3M9XCJ2aS13YmUtc3Bpbm5lclwiPjxzcGFuIGNsYXNzPVwidmktd2JlLXNwaW5uZXItaW5uZXJcIj4gPC9zcGFuPjwvc3Bhbj4nKVxyXG4gICAgfSxcclxuXHJcbiAgICBpc19sb2FkaW5nKCkge1xyXG4gICAgICAgIHJldHVybiAhIXRoaXMuX3NwaW5uZXI7XHJcbiAgICB9LFxyXG5cclxuICAgIGxvYWRpbmcoKSB7XHJcbiAgICAgICAgdGhpcy5fc3Bpbm5lciA9IHRoaXMuc3Bpbm5lcigpO1xyXG4gICAgICAgICQoJy52aS13YmUtbWVudS1iYXItY2VudGVyJykuaHRtbCh0aGlzLl9zcGlubmVyKTtcclxuICAgIH0sXHJcblxyXG4gICAgcmVtb3ZlTG9hZGluZygpIHtcclxuICAgICAgICB0aGlzLl9zcGlubmVyID0gbnVsbDtcclxuICAgICAgICAkKCcudmktd2JlLW1lbnUtYmFyLWNlbnRlcicpLmh0bWwoJycpO1xyXG4gICAgfSxcclxuXHJcbiAgICBub3RpY2UodGV4dCwgY29sb3IgPSAnYmxhY2snKSB7XHJcbiAgICAgICAgbGV0IGNvbnRlbnQgPSAkKGA8ZGl2IGNsYXNzPVwidmktd2JlLW5vdGljZVwiIHN0eWxlPVwiY29sb3I6JHtjb2xvcn1cIj4ke3RleHR9PC9kaXY+YCk7XHJcbiAgICAgICAgJCgnLnZpLXdiZS1tZW51LWJhci1jZW50ZXInKS5odG1sKGNvbnRlbnQpO1xyXG4gICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICBjb250ZW50LnJlbW92ZSgpO1xyXG4gICAgICAgIH0sIDUwMDApO1xyXG4gICAgfSxcclxuXHJcbiAgICBnZW5lcmF0ZUNvdXBvbkNvZGUoKSB7XHJcbiAgICAgICAgbGV0ICRyZXN1bHQgPSAnJztcclxuICAgICAgICBmb3IgKHZhciBpID0gMDsgaSA8IEF0dHJpYnV0ZXMuY291cG9uR2VuZXJhdGUuY2hhcl9sZW5ndGg7IGkrKykge1xyXG4gICAgICAgICAgICAkcmVzdWx0ICs9IEF0dHJpYnV0ZXMuY291cG9uR2VuZXJhdGUuY2hhcmFjdGVycy5jaGFyQXQoXHJcbiAgICAgICAgICAgICAgICBNYXRoLmZsb29yKE1hdGgucmFuZG9tKCkgKiBBdHRyaWJ1dGVzLmNvdXBvbkdlbmVyYXRlLmNoYXJhY3RlcnMubGVuZ3RoKVxyXG4gICAgICAgICAgICApO1xyXG4gICAgICAgIH1cclxuICAgICAgICAkcmVzdWx0ID0gQXR0cmlidXRlcy5jb3Vwb25HZW5lcmF0ZS5wcmVmaXggKyAkcmVzdWx0ICsgQXR0cmlidXRlcy5jb3Vwb25HZW5lcmF0ZS5zdWZmaXg7XHJcbiAgICAgICAgcmV0dXJuICRyZXN1bHQ7XHJcbiAgICB9XHJcbn07XHJcblxyXG5leHBvcnQgZGVmYXVsdCBfZjsiLCJjb25zdCAkID0galF1ZXJ5O1xyXG5cclxuY2xhc3MgTW9kYWwge1xyXG4gICAgY29uc3RydWN0b3IoKSB7XHJcblxyXG4gICAgfVxyXG59XHJcblxyXG5sZXQgcG9wdXBJbnN0YW5jZSA9IG51bGw7XHJcblxyXG5jbGFzcyBQb3B1cCB7XHJcbiAgICBjb25zdHJ1Y3Rvcihjb250ZW50LCBjZWxsKSB7XHJcbiAgICAgICAgaWYgKCFwb3B1cEluc3RhbmNlKSB7XHJcbiAgICAgICAgICAgICQoJ2JvZHknKS5vbignbW91c2Vkb3duIGtleWRvd24nLCB0aGlzLm1vdXNlZG93bik7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBwb3B1cEluc3RhbmNlID0gdGhpcztcclxuXHJcbiAgICAgICAgdGhpcy5wb3B1cCA9ICQoJy52aS13YmUtY29udGV4dC1wb3B1cCcpO1xyXG5cclxuICAgICAgICB0aGlzLnJlbmRlcihjb250ZW50LCAkKGNlbGwpKTtcclxuICAgIH1cclxuXHJcbiAgICBtb3VzZWRvd24oZSkge1xyXG4gICAgICAgIGxldCB0aGlzVGFyZ2V0ID0gJChlLnRhcmdldCksXHJcbiAgICAgICAgICAgIHBvcHVwID0gJCgnLnZpLXdiZS1jb250ZXh0LXBvcHVwJyk7XHJcblxyXG4gICAgICAgIGlmIChlLndoaWNoID09PSAyN1xyXG4gICAgICAgICAgICB8fCAhdGhpc1RhcmdldC5oYXNDbGFzcygndmktd2JlLWNvbnRleHQtcG9wdXAnKVxyXG4gICAgICAgICAgICAmJiB0aGlzVGFyZ2V0LmNsb3Nlc3QoJy52aS13YmUtY29udGV4dC1wb3B1cCcpLmxlbmd0aCA9PT0gMFxyXG4gICAgICAgICAgICAmJiBwb3B1cC5oYXNDbGFzcygndmktd2JlLXBvcHVwLWFjdGl2ZScpXHJcbiAgICAgICAgICAgICYmICF0aGlzVGFyZ2V0Lmhhc0NsYXNzKCdzZWxlY3QyLXNlYXJjaF9fZmllbGQnKVxyXG4gICAgICAgICkge1xyXG4gICAgICAgICAgICBwb3B1cC5lbXB0eSgpLnJlbW92ZUNsYXNzKCd2aS13YmUtcG9wdXAtYWN0aXZlJyk7XHJcbiAgICAgICAgICAgICQoJy5zZWxlY3QyLWNvbnRhaW5lci5zZWxlY3QyLWNvbnRhaW5lci0tZGVmYXVsdC5zZWxlY3QyLWNvbnRhaW5lci0tb3BlbicpLnJlbW92ZSgpO1xyXG4gICAgICAgIH1cclxuICAgIH1cclxuXHJcbiAgICByZW5kZXIoY29udGVudCwgY2VsbCkge1xyXG4gICAgICAgIGxldCB7cG9wdXB9ID0gdGhpcyxcclxuICAgICAgICAgICAgc3RhZ2UgPSBjZWxsLm9mZnNldCgpLFxyXG4gICAgICAgICAgICB4ID0gc3RhZ2UubGVmdCxcclxuICAgICAgICAgICAgeSA9IHN0YWdlLnRvcCxcclxuICAgICAgICAgICAgY2VsbFdpZHRoID0gY2VsbC5pbm5lcldpZHRoKCk7XHJcblxyXG4gICAgICAgIHBvcHVwLmVtcHR5KCk7XHJcbiAgICAgICAgcG9wdXAuYWRkQ2xhc3MoJ3ZpLXdiZS1wb3B1cC1hY3RpdmUnKS5odG1sKGNvbnRlbnQpO1xyXG5cclxuICAgICAgICBsZXQgcG9wdXBXaWR0aCA9IHBvcHVwLmlubmVyV2lkdGgoKSxcclxuICAgICAgICAgICAgcG9wdXBIZWlnaHQgPSBwb3B1cC5pbm5lckhlaWdodCgpO1xyXG5cclxuICAgICAgICBpZiAod2luZG93LmlubmVyV2lkdGggPCB4ICsgcG9wdXBXaWR0aCArIGNlbGxXaWR0aCkge1xyXG4gICAgICAgICAgICBsZXQgbGVmdCA9IHggLSBwb3B1cFdpZHRoID4gMCA/IHggLSBwb3B1cFdpZHRoIDogMTA7XHJcbiAgICAgICAgICAgIHBvcHVwLmNzcygnbGVmdCcsIGxlZnQgKyAncHgnKTtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICBwb3B1cC5jc3MoJ2xlZnQnLCAoeCArIGNlbGxXaWR0aCkgKyAncHgnKTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGxldCB3aW5kb3dJbm5lckhlaWdodCA9ICQoJyN2aS13YmUtZWRpdG9yJykuaW5uZXJIZWlnaHQoKTtcclxuICAgICAgICBpZiAod2luZG93SW5uZXJIZWlnaHQgPCB5ICsgcG9wdXBIZWlnaHQpIHtcclxuICAgICAgICAgICAgbGV0IGggPSB5IC0gcG9wdXBIZWlnaHQgPCAwID8gMCA6IHkgLSBwb3B1cEhlaWdodDtcclxuICAgICAgICAgICAgcG9wdXAuY3NzKCd0b3AnLCBoICsgJ3B4Jyk7XHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgcG9wdXAuY3NzKCd0b3AnLCB5ICsgJ3B4Jyk7XHJcbiAgICAgICAgfVxyXG4gICAgfVxyXG5cclxuICAgIGhpZGUoKSB7XHJcbiAgICAgICAgdGhpcy5wb3B1cC5yZW1vdmVDbGFzcygndmktd2JlLXBvcHVwLWFjdGl2ZScpO1xyXG4gICAgfVxyXG59XHJcblxyXG5leHBvcnQge01vZGFsLCBQb3B1cH0iLCJpbXBvcnQge0F0dHJpYnV0ZXN9IGZyb20gXCIuL2F0dHJpYnV0ZXNcIjtcclxuaW1wb3J0IF9mIGZyb20gXCIuL2Z1bmN0aW9uc1wiO1xyXG5cclxuY29uc3QgJCA9IGpRdWVyeTtcclxuXHJcbmV4cG9ydCBkZWZhdWx0IGNsYXNzIE11bHRpcGxlUHJvZHVjdEF0dHJpYnV0ZXMge1xyXG4gICAgY29uc3RydWN0b3Iob2JqLCBjZWxscywgeCwgeSwgZSkge1xyXG4gICAgICAgIHRoaXMuY2VsbHMgPSBjZWxscztcclxuICAgICAgICB0aGlzLm9iaiA9IG9iajtcclxuICAgICAgICB0aGlzLnggPSBwYXJzZUludCh4KTtcclxuICAgICAgICB0aGlzLnkgPSBwYXJzZUludCh5KTtcclxuXHJcbiAgICAgICAgdGhpcy5ydW4oKTtcclxuICAgIH1cclxuXHJcbiAgICBydW4oKSB7XHJcbiAgICAgICAgbGV0IGNlbGwgPSAkKGB0ZFtkYXRhLXg9JHt0aGlzLnggfHwgMH1dW2RhdGEteT0ke3RoaXMueSB8fCAwfV1gKTtcclxuXHJcbiAgICAgICAgbGV0ICR0aGlzID0gdGhpcywgaHRtbCA9ICcnO1xyXG5cclxuICAgICAgICBsZXQgbW9kYWwgPSBfZi5jcmVhdGVNb2RhbCh7XHJcbiAgICAgICAgICAgIGhlYWRlcjogX2YudGV4dCgnQXR0cmlidXRlcycpLFxyXG4gICAgICAgICAgICBjb250ZW50OiAnJyxcclxuICAgICAgICAgICAgYWN0aW9uczogW3tjbGFzczogJ3NhdmUtYXR0cmlidXRlcycsIHRleHQ6IF9mLnRleHQoJ0FwcGx5Jyl9XSxcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgdGhpcy5jb250ZW50KG1vZGFsKTtcclxuICAgICAgICAkKGNlbGwpLmFwcGVuZChtb2RhbCk7XHJcblxyXG4gICAgICAgIG1vZGFsLm9uKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgIGxldCB0aGlzVGFyZ2V0ID0gJChlLnRhcmdldCk7XHJcbiAgICAgICAgICAgIGlmICh0aGlzVGFyZ2V0Lmhhc0NsYXNzKCdjbG9zZScpIHx8IHRoaXNUYXJnZXQuaGFzQ2xhc3MoJ3ZpLXdiZS1tb2RhbC1jb250YWluZXInKSkgbW9kYWwucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgIGlmICh0aGlzVGFyZ2V0Lmhhc0NsYXNzKCdzYXZlLWF0dHJpYnV0ZXMnKSkge1xyXG4gICAgICAgICAgICAgICAgJHRoaXMuYWRkQXR0cmlidXRlcyhtb2RhbCk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuICAgIH1cclxuXHJcbiAgICBhZGRJbWFnZShpbWdJZCkge1xyXG5cclxuICAgICAgICBsZXQgZXhjZWxPYmogPSB0aGlzLm9iajtcclxuICAgICAgICBsZXQgYnJlYWtDb250cm9sID0gZmFsc2UsIHJlY29yZHMgPSBbXTtcclxuICAgICAgICBsZXQgaCA9IHRoaXMuY2VsbHM7XHJcbiAgICAgICAgbGV0IHN0YXJ0ID0gaFsxXSwgZW5kID0gaFszXSwgeCA9IGhbMF07XHJcblxyXG4gICAgICAgIGZvciAobGV0IHkgPSBzdGFydDsgeSA8PSBlbmQ7IHkrKykge1xyXG4gICAgICAgICAgICBpZiAoZXhjZWxPYmoucmVjb3Jkc1t5XVt4XSAmJiAhZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5jbGFzc0xpc3QuY29udGFpbnMoJ3JlYWRvbmx5JykgJiYgZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5zdHlsZS5kaXNwbGF5ICE9PSAnbm9uZScgJiYgYnJlYWtDb250cm9sID09PSBmYWxzZSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IHZhbHVlID0gZXhjZWxPYmoub3B0aW9ucy5kYXRhW3ldW3hdO1xyXG4gICAgICAgICAgICAgICAgaWYgKCF2YWx1ZSkgdmFsdWUgPSBbXTtcclxuXHJcbiAgICAgICAgICAgICAgICBsZXQgbmV3VmFsdWUgPSBbLi4ubmV3IFNldCh2YWx1ZSldO1xyXG4gICAgICAgICAgICAgICAgbmV3VmFsdWUucHVzaChpbWdJZCk7XHJcblxyXG4gICAgICAgICAgICAgICAgcmVjb3Jkcy5wdXNoKGV4Y2VsT2JqLnVwZGF0ZUNlbGwoeCwgeSwgbmV3VmFsdWUpKTtcclxuICAgICAgICAgICAgICAgIGV4Y2VsT2JqLnVwZGF0ZUZvcm11bGFDaGFpbih4LCB5LCByZWNvcmRzKTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgLy8gVXBkYXRlIGhpc3RvcnlcclxuICAgICAgICBleGNlbE9iai5zZXRIaXN0b3J5KHtcclxuICAgICAgICAgICAgYWN0aW9uOiAnc2V0VmFsdWUnLFxyXG4gICAgICAgICAgICByZWNvcmRzOiByZWNvcmRzLFxyXG4gICAgICAgICAgICBzZWxlY3Rpb246IGV4Y2VsT2JqLnNlbGVjdGVkQ2VsbCxcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgLy8gVXBkYXRlIHRhYmxlIHdpdGggY3VzdG9tIGNvbmZpZ3VyYXRpb24gaWYgYXBwbGljYWJsZVxyXG4gICAgICAgIGV4Y2VsT2JqLnVwZGF0ZVRhYmxlKCk7XHJcbiAgICB9XHJcblxyXG4gICAgYWRkQXR0cmlidXRlcyhtb2RhbCkge1xyXG4gICAgICAgIGxldCBuZXdBdHRyaWJ1dGVzID0gW10sXHJcbiAgICAgICAgICAgIGFkZEF0dHJPcHQgPSBtb2RhbC5maW5kKCcudmktd2JlLWFkZC1hdHRyaWJ1dGVzLW9wdGlvbicpLnZhbCgpO1xyXG5cclxuICAgICAgICBtb2RhbC5maW5kKCcudmktd2JlLWF0dHJpYnV0ZS1yb3cnKS5lYWNoKGZ1bmN0aW9uIChpLCByb3cpIHtcclxuICAgICAgICAgICAgbGV0IHBBdHRyID0gJChyb3cpLmRhdGEoJ2F0dHInKTtcclxuICAgICAgICAgICAgaWYgKHBBdHRyLmlzX3RheG9ub215KSB7XHJcbiAgICAgICAgICAgICAgICBwQXR0ci5vcHRpb25zID0gJChyb3cpLmZpbmQoJ3NlbGVjdCcpLnZhbCgpLm1hcChOdW1iZXIpO1xyXG4gICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgcEF0dHIubmFtZSA9ICQocm93KS5maW5kKCdpbnB1dC5jdXN0b20tYXR0ci1uYW1lJykudmFsKCk7XHJcbiAgICAgICAgICAgICAgICBsZXQgdmFsdWUgPSAkKHJvdykuZmluZCgndGV4dGFyZWEuY3VzdG9tLWF0dHItdmFsJykudmFsKCk7XHJcbiAgICAgICAgICAgICAgICBwQXR0ci52YWx1ZSA9IHZhbHVlLnRyaW0oKS5yZXBsYWNlKC9cXHMrL2csICcgJyk7XHJcbiAgICAgICAgICAgICAgICBwQXR0ci5vcHRpb25zID0gdmFsdWUuc3BsaXQoJ3wnKS5tYXAoaXRlbSA9PiBpdGVtLnRyaW0oKS5yZXBsYWNlKC9cXHMrL2csICcgJykpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIHBBdHRyLnZpc2libGUgPSAhISQocm93KS5maW5kKCcuYXR0ci12aXNpYmlsaXR5OmNoZWNrZWQnKS5sZW5ndGg7XHJcbiAgICAgICAgICAgIHBBdHRyLnZhcmlhdGlvbiA9ICEhJChyb3cpLmZpbmQoJy5hdHRyLXZhcmlhdGlvbjpjaGVja2VkJykubGVuZ3RoO1xyXG4gICAgICAgICAgICBwQXR0ci5wb3NpdGlvbiA9IGk7XHJcbiAgICAgICAgICAgIG5ld0F0dHJpYnV0ZXMucHVzaChwQXR0cilcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgY29uc29sZS5sb2cobmV3QXR0cmlidXRlcylcclxuICAgICAgICBpZiAobmV3QXR0cmlidXRlcy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgbGV0IGV4Y2VsT2JqID0gdGhpcy5vYmo7XHJcbiAgICAgICAgICAgIGxldCBicmVha0NvbnRyb2wgPSBmYWxzZSwgcmVjb3JkcyA9IFtdO1xyXG4gICAgICAgICAgICBsZXQgaCA9IHRoaXMuY2VsbHM7XHJcbiAgICAgICAgICAgIGxldCBzdGFydCA9IGhbMV0sIGVuZCA9IGhbM10sIHggPSBoWzBdO1xyXG5cclxuICAgICAgICAgICAgY29uc3QgZmluZEV4aXN0ID0gKHByb2R1Y3RBdHRycyA9IFtdLCBhdHRyTmFtZSkgPT4ge1xyXG4gICAgICAgICAgICAgICAgaWYgKHByb2R1Y3RBdHRycy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCBpbmRleCBpbiBwcm9kdWN0QXR0cnMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGF0dHIgPSBwcm9kdWN0QXR0cnNbaW5kZXhdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoYXR0ci5uYW1lID09PSBhdHRyTmFtZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuIGluZGV4O1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xyXG4gICAgICAgICAgICB9O1xyXG5cclxuICAgICAgICAgICAgZm9yIChsZXQgeSA9IHN0YXJ0OyB5IDw9IGVuZDsgeSsrKSB7XHJcbiAgICAgICAgICAgICAgICBpZiAoZXhjZWxPYmoucmVjb3Jkc1t5XVt4XSAmJiAhZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5jbGFzc0xpc3QuY29udGFpbnMoJ3JlYWRvbmx5JykgJiYgZXhjZWxPYmoucmVjb3Jkc1t5XVt4XS5zdHlsZS5kaXNwbGF5ICE9PSAnbm9uZScgJiYgYnJlYWtDb250cm9sID09PSBmYWxzZSkge1xyXG4gICAgICAgICAgICAgICAgICAgIGxldCB2YWx1ZSA9IGV4Y2VsT2JqLm9wdGlvbnMuZGF0YVt5XVt4XTtcclxuICAgICAgICAgICAgICAgICAgICBpZiAoIXZhbHVlKSB2YWx1ZSA9IFtdO1xyXG4gICAgICAgICAgICAgICAgICAgIGxldCBuZXdWYWx1ZSA9IFsuLi5uZXcgU2V0KHZhbHVlKV07XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IHBvc2l0aW9uSW5kZXggPSAwO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCBhdHRyIG9mIG5ld0F0dHJpYnV0ZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGF0dHJOYW1lID0gYXR0ci5uYW1lO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQga2V5ID0gZmluZEV4aXN0KG5ld1ZhbHVlLCBhdHRyTmFtZSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoa2V5ID09PSBmYWxzZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgYXR0ci5wb3NpdGlvbiA9IG5ld1ZhbHVlLmxlbmd0aCArIHBvc2l0aW9uSW5kZXgrKztcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIHBvc2l0aW9uSW5kZXgrKztcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlLnB1c2goYXR0cik7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzd2l0Y2ggKGFkZEF0dHJPcHQpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjYXNlICdyZXBsYWNlJzpcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYXR0ci5wb3NpdGlvbiA9IG5ld1ZhbHVlW2tleV0ucG9zaXRpb247XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ld1ZhbHVlW2tleV0gPSBhdHRyO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2FzZSAnbWVyZ2VfdGVybXMnOlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgY3VycmVudFRlcm1zID0gbmV3VmFsdWVba2V5XS5vcHRpb25zIHx8IFtdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgbmV3VGVybXMgPSBhdHRyLm9wdGlvbnMgfHwgW107XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCB0ZXJtcyA9IFsuLi5jdXJyZW50VGVybXMsIC4uLm5ld1Rlcm1zXTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmV3VmFsdWVba2V5XS5vcHRpb25zID0gWy4uLm5ldyBTZXQodGVybXMpXTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIHJlY29yZHMucHVzaChleGNlbE9iai51cGRhdGVDZWxsKHgsIHksIG5ld1ZhbHVlKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgZXhjZWxPYmoudXBkYXRlRm9ybXVsYUNoYWluKHgsIHksIHJlY29yZHMpO1xyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAvLyBVcGRhdGUgaGlzdG9yeVxyXG4gICAgICAgICAgICBleGNlbE9iai5zZXRIaXN0b3J5KHtcclxuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3NldFZhbHVlJyxcclxuICAgICAgICAgICAgICAgIHJlY29yZHM6IHJlY29yZHMsXHJcbiAgICAgICAgICAgICAgICBzZWxlY3Rpb246IGV4Y2VsT2JqLnNlbGVjdGVkQ2VsbCxcclxuICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAvLyBVcGRhdGUgdGFibGUgd2l0aCBjdXN0b20gY29uZmlndXJhdGlvbiBpZiBhcHBsaWNhYmxlXHJcbiAgICAgICAgICAgIGV4Y2VsT2JqLnVwZGF0ZVRhYmxlKCk7XHJcbiAgICAgICAgfVxyXG4gICAgICAgIG1vZGFsLnJlbW92ZSgpO1xyXG5cclxuICAgIH1cclxuXHJcbiAgICBjb250ZW50KG1vZGFsKSB7XHJcbiAgICAgICAgbGV0ICR0aGlzID0gdGhpcywgaHRtbCA9ICcnO1xyXG5cclxuICAgICAgICBsZXQge2F0dHJpYnV0ZXN9ID0gQXR0cmlidXRlcztcclxuICAgICAgICBsZXQgYWRkQXR0cmlidXRlID0gYDxvcHRpb24gdmFsdWU9XCJcIj4ke19mLnRleHQoJ0N1c3RvbSBwcm9kdWN0IGF0dHJpYnV0ZScpfTwvb3B0aW9uPmA7XHJcblxyXG4gICAgICAgIGZvciAobGV0IGF0dHIgaW4gYXR0cmlidXRlcykge1xyXG4gICAgICAgICAgICBhZGRBdHRyaWJ1dGUgKz0gYDxvcHRpb24gdmFsdWU9XCIke2F0dHJ9XCI+JHthdHRyaWJ1dGVzW2F0dHJdLmRhdGEuYXR0cmlidXRlX2xhYmVsfTwvb3B0aW9uPmA7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBhZGRBdHRyaWJ1dGUgPSBgPGRpdiBjbGFzcz1cInZpLXdiZS10YXhvbm9teS1oZWFkZXJcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzZWxlY3QgY2xhc3M9XCJ2aS13YmUtc2VsZWN0LXRheG9ub215XCI+JHthZGRBdHRyaWJ1dGV9PC9zZWxlY3Q+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpLXVpIGJ1dHRvbiB0aW55IHZpLXdiZS1hZGQtdGF4b25vbXlcIj4ke19mLnRleHQoJ0FkZCcpfTwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+YDtcclxuXHJcbiAgICAgICAgaHRtbCA9IGAke2FkZEF0dHJpYnV0ZX1cclxuICAgICAgICAgICAgICAgIDx0YWJsZSBjbGFzcz1cInZpLXVpIGNlbGxlZCB0YWJsZVwiPlxyXG4gICAgICAgICAgICAgICAgICAgIDx0aGVhZD5cclxuICAgICAgICAgICAgICAgICAgICA8dHI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDx0aD5OYW1lPC90aD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPHRoPkF0dHJpYnV0ZXM8L3RoPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8dGggd2lkdGg9XCIxXCI+QWN0aW9uczwvdGg+XHJcbiAgICAgICAgICAgICAgICAgICAgPC90cj5cclxuICAgICAgICAgICAgICAgICAgICA8L3RoZWFkPlxyXG4gICAgICAgICAgICAgICAgICAgIDx0Ym9keT4ke2h0bWx9PC90Ym9keT5cclxuICAgICAgICAgICAgICAgIDwvdGFibGU+YDtcclxuXHJcbiAgICAgICAgbGV0IGFkZEF0dHJpYnV0ZU9wdGlvbnMgPSBgPGRpdj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJ2aS13YmUtYWRkLWF0dHJpYnV0ZXMtb3B0aW9uLWxhYmVsXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgU2VsZWN0IGFjdGlvbiBpZiBleGlzdCBhdHRyaWJ1dGUgaW4gcHJvZHVjdFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c2VsZWN0IGNsYXNzPVwidmktd2JlLWFkZC1hdHRyaWJ1dGVzLW9wdGlvblwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxvcHRpb24gdmFsdWU9XCJub25lXCI+RG9uJ3QgYWRkPC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT1cInJlcGxhY2VcIj5SZXBsYWNlIGV4aXN0ZWQgYXR0cmlidXRlPC9vcHRpb24+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT1cIm1lcmdlX3Rlcm1zXCI+TWVyZ2UgdGVybXM8L29wdGlvbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvc2VsZWN0PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5gO1xyXG5cclxuICAgICAgICBtb2RhbC5maW5kKCcuY29udGVudCcpLmFwcGVuZChodG1sKTtcclxuICAgICAgICBtb2RhbC5maW5kKCcuYWN0aW9ucycpLmFwcGVuZChhZGRBdHRyaWJ1dGVPcHRpb25zKTtcclxuICAgICAgICBtb2RhbC5maW5kKCd0YWJsZSBzZWxlY3QnKS5zZWxlY3QyKHttdWx0aXBsZTogdHJ1ZX0pO1xyXG4gICAgICAgIG1vZGFsLmZpbmQoJ3Rib2R5Jykuc29ydGFibGUoe1xyXG4gICAgICAgICAgICBpdGVtczogJ3RyJyxcclxuICAgICAgICAgICAgY3Vyc29yOiAnbW92ZScsXHJcbiAgICAgICAgICAgIGF4aXM6ICd5JyxcclxuICAgICAgICAgICAgc2Nyb2xsU2Vuc2l0aXZpdHk6IDQwLFxyXG4gICAgICAgICAgICBmb3JjZVBsYWNlaG9sZGVyU2l6ZTogdHJ1ZSxcclxuICAgICAgICAgICAgaGVscGVyOiAnY2xvbmUnLFxyXG4gICAgICAgICAgICBoYW5kbGU6ICcuaWNvbi5tb3ZlJyxcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgY29uc3Qgc2V0T3B0aW9uRGlzYWJsZSA9ICgpID0+IHtcclxuICAgICAgICAgICAgbW9kYWwuZmluZCgnc2VsZWN0LnZpLXdiZS1zZWxlY3QtdGF4b25vbXkgb3B0aW9uJykucmVtb3ZlQXR0cignZGlzYWJsZWQnKTtcclxuICAgICAgICAgICAgbW9kYWwuZmluZCgnaW5wdXRbdHlwZT1oaWRkZW5dJykuZWFjaChmdW5jdGlvbiAoaSwgZWwpIHtcclxuICAgICAgICAgICAgICAgIGxldCB0YXggPSAkKGVsKS52YWwoKTtcclxuICAgICAgICAgICAgICAgIG1vZGFsLmZpbmQoYHNlbGVjdC52aS13YmUtc2VsZWN0LXRheG9ub215IG9wdGlvblt2YWx1ZT0nJHt0YXh9J11gKS5hdHRyKCdkaXNhYmxlZCcsICdkaXNhYmxlZCcpO1xyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9O1xyXG5cclxuICAgICAgICBzZXRPcHRpb25EaXNhYmxlKCk7XHJcblxyXG4gICAgICAgIG1vZGFsLm9uKCdjbGljaycsIGZ1bmN0aW9uIChlKSB7XHJcbiAgICAgICAgICAgIGxldCAkdGhpc1RhcmdldCA9ICQoZS50YXJnZXQpO1xyXG4gICAgICAgICAgICBpZiAoJHRoaXNUYXJnZXQuaGFzQ2xhc3MoJ3RyYXNoJykpIHtcclxuICAgICAgICAgICAgICAgICR0aGlzVGFyZ2V0LmNsb3Nlc3QoJ3RyJykucmVtb3ZlKCk7XHJcbiAgICAgICAgICAgICAgICBzZXRPcHRpb25EaXNhYmxlKCk7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIGlmICgkdGhpc1RhcmdldC5oYXNDbGFzcygndmktd2JlLWFkZC10YXhvbm9teScpKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgdGF4U2VsZWN0ID0gJCgnLnZpLXdiZS1zZWxlY3QtdGF4b25vbXknKSwgdGF4ID0gdGF4U2VsZWN0LnZhbCgpLFxyXG4gICAgICAgICAgICAgICAgICAgIGl0ZW0gPSB7bmFtZTogdGF4LCBvcHRpb25zOiBbXX07XHJcbiAgICAgICAgICAgICAgICBpZiAodGF4KSBpdGVtLmlzX3RheG9ub215ID0gMTtcclxuXHJcbiAgICAgICAgICAgICAgICBsZXQgcm93ID0gJCgkdGhpcy5jcmVhdGVSb3dUYWJsZShpdGVtKSk7XHJcbiAgICAgICAgICAgICAgICBtb2RhbC5maW5kKCd0YWJsZSB0Ym9keScpLmFwcGVuZChyb3cpO1xyXG4gICAgICAgICAgICAgICAgcm93LmZpbmQoJ3NlbGVjdCcpLnNlbGVjdDIoe211bHRpcGxlOiB0cnVlfSk7XHJcbiAgICAgICAgICAgICAgICBzZXRPcHRpb25EaXNhYmxlKCk7XHJcbiAgICAgICAgICAgICAgICB0YXhTZWxlY3QudmFsKCcnKS50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgaWYgKCR0aGlzVGFyZ2V0Lmhhc0NsYXNzKCd2aS13YmUtc2VsZWN0LWFsbC1hdHRyaWJ1dGVzJykpIHtcclxuICAgICAgICAgICAgICAgIGxldCB0ZCA9ICR0aGlzVGFyZ2V0LmNsb3Nlc3QoJ3RkJyk7XHJcbiAgICAgICAgICAgICAgICBsZXQgc2VsZWN0ID0gdGQuZmluZCgnc2VsZWN0Jyk7XHJcbiAgICAgICAgICAgICAgICBzZWxlY3QuZmluZCgnb3B0aW9uJykuYXR0cignc2VsZWN0ZWQnLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgIHNlbGVjdC50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgaWYgKCR0aGlzVGFyZ2V0Lmhhc0NsYXNzKCd2aS13YmUtc2VsZWN0LW5vLWF0dHJpYnV0ZXMnKSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IHRkID0gJHRoaXNUYXJnZXQuY2xvc2VzdCgndGQnKTtcclxuICAgICAgICAgICAgICAgIGxldCBzZWxlY3QgPSB0ZC5maW5kKCdzZWxlY3QnKTtcclxuICAgICAgICAgICAgICAgIHNlbGVjdC5maW5kKCdvcHRpb24nKS5hdHRyKCdzZWxlY3RlZCcsIGZhbHNlKTtcclxuICAgICAgICAgICAgICAgIHNlbGVjdC50cmlnZ2VyKCdjaGFuZ2UnKTtcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgaWYgKCR0aGlzVGFyZ2V0Lmhhc0NsYXNzKCd2aS13YmUtYWRkLW5ldy1hdHRyaWJ1dGUnKSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IG5ld0F0dHIgPSBwcm9tcHQoX2YudGV4dCgnRW50ZXIgYSBuYW1lIGZvciB0aGUgbmV3IGF0dHJpYnV0ZSB0ZXJtOicpKTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAoIW5ld0F0dHIpIHJldHVybjtcclxuXHJcbiAgICAgICAgICAgICAgICBsZXQgdHIgPSAkdGhpc1RhcmdldC5jbG9zZXN0KCd0ci52aS13YmUtYXR0cmlidXRlLXJvdycpLFxyXG4gICAgICAgICAgICAgICAgICAgIHRheEF0dHIgPSB0ci5hdHRyKCdkYXRhLWF0dHInKTtcclxuXHJcbiAgICAgICAgICAgICAgICBpZiAodGF4QXR0cikge1xyXG4gICAgICAgICAgICAgICAgICAgIHRheEF0dHIgPSBKU09OLnBhcnNlKHRheEF0dHIpO1xyXG4gICAgICAgICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBkYXRhOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJfYWN0aW9uOiAnYWRkX25ld19hdHRyaWJ1dGUnLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGF4b25vbXk6IHRheEF0dHIubmFtZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRlcm06IG5ld0F0dHJcclxuICAgICAgICAgICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzVGFyZ2V0LmFkZENsYXNzKCdsb2FkaW5nJylcclxuICAgICAgICAgICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzVGFyZ2V0LnJlbW92ZUNsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAocmVzLnN1Y2Nlc3MpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgc2VsZWN0ID0gdHIuZmluZCgnc2VsZWN0Jyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZWN0LmFwcGVuZChgPG9wdGlvbiB2YWx1ZT1cIiR7cmVzLmRhdGEudGVybV9pZH1cIiBzZWxlY3RlZD4ke3Jlcy5kYXRhLm5hbWV9PC9vcHRpb24+YCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZWN0LnRyaWdnZXIoJ2NoYW5nZScpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIEF0dHJpYnV0ZXMuYXR0cmlidXRlc1t0YXhBdHRyLm5hbWVdLnRlcm1zW3Jlcy5kYXRhLnRlcm1faWRdID0ge3NsdWc6IHJlcy5kYXRhLnNsdWcsIHRleHQ6IHJlcy5kYXRhLm5hbWV9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGFsZXJ0KHJlcy5kYXRhLm1lc3NhZ2UpXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG4gICAgfVxyXG5cclxuICAgIGNyZWF0ZVJvd1RhYmxlKGl0ZW0pIHtcclxuICAgICAgICBsZXQgYXR0ck5hbWUgPSAnJywgdmFsdWUgPSAnJztcclxuXHJcbiAgICAgICAgaWYgKGl0ZW0uaXNfdGF4b25vbXkpIHtcclxuICAgICAgICAgICAgbGV0IGF0dHJpYnV0ZSA9IEF0dHJpYnV0ZXMuYXR0cmlidXRlc1tpdGVtLm5hbWVdLFxyXG4gICAgICAgICAgICAgICAgdGVybXMgPSBhdHRyaWJ1dGUudGVybXMgfHwgW10sIG9wdGlvbnMgPSAnJztcclxuXHJcbiAgICAgICAgICAgIGF0dHJOYW1lID0gYCR7YXR0cmlidXRlLmRhdGEuYXR0cmlidXRlX2xhYmVsfTxpbnB1dCB0eXBlPVwiaGlkZGVuXCIgdmFsdWU9XCIke2l0ZW0ubmFtZX1cIi8+YDtcclxuXHJcbiAgICAgICAgICAgIGlmIChPYmplY3Qua2V5cyh0ZXJtcykubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgICAgICBmb3IgKGxldCBpZCBpbiB0ZXJtcykge1xyXG4gICAgICAgICAgICAgICAgICAgIGxldCBzZWxlY3RlZCA9IGl0ZW0ub3B0aW9ucy5pbmNsdWRlcyhwYXJzZUludChpZCkpID8gJ3NlbGVjdGVkJyA6ICcnO1xyXG4gICAgICAgICAgICAgICAgICAgIG9wdGlvbnMgKz0gYDxvcHRpb24gdmFsdWU9XCIke2lkfVwiICR7c2VsZWN0ZWR9PiR7dGVybXNbaWRdLnRleHR9PC9vcHRpb24+YDtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgdmFsdWUgPSBgPHNlbGVjdCBtdWx0aXBsZT4ke29wdGlvbnN9PC9zZWxlY3Q+XHJcbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cInZpLXdiZS1hdHRyaWJ1dGVzLWJ1dHRvbi1ncm91cFwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpLXVpIGJ1dHRvbiBtaW5pIHZpLXdiZS1zZWxlY3QtYWxsLWF0dHJpYnV0ZXNcIj4ke19mLnRleHQoJ1NlbGVjdCBhbGwnKX08L3NwYW4+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIG1pbmkgdmktd2JlLXNlbGVjdC1uby1hdHRyaWJ1dGVzXCI+JHtfZi50ZXh0KCdTZWxlY3Qgbm9uZScpfTwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9XCJ2aS11aSBidXR0b24gbWluaSB2aS13YmUtYWRkLW5ldy1hdHRyaWJ1dGVcIj4ke19mLnRleHQoJ0FkZCBuZXcnKX08L3NwYW4+XHJcbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+YDtcclxuICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICBhdHRyTmFtZSA9IGA8aW5wdXQgdHlwZT1cInRleHRcIiBjbGFzcz1cImN1c3RvbS1hdHRyLW5hbWVcIiB2YWx1ZT1cIiR7aXRlbS5uYW1lfVwiIHBsYWNlaG9sZGVyPVwiJHtfZi50ZXh0KCdDdXN0b20gYXR0cmlidXRlIG5hbWUnKX1cIi8+YDtcclxuICAgICAgICAgICAgdmFsdWUgPSBgPHRleHRhcmVhIGNsYXNzPVwiY3VzdG9tLWF0dHItdmFsXCIgcGxhY2Vob2xkZXI9XCIke19mLnRleHQoJ0VudGVyIHNvbWUgdGV4dCwgb3Igc29tZSBhdHRyaWJ1dGVzIGJ5IFwifFwiIHNlcGFyYXRpbmcgdmFsdWVzLicpfVwiPiR7aXRlbS52YWx1ZSB8fCAnJ308L3RleHRhcmVhPmA7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBhdHRyTmFtZSA9IGA8ZGl2IGNsYXNzPVwidmktd2JlLWF0dHJpYnV0ZS1uYW1lLWxhYmVsXCI+JHthdHRyTmFtZX08L2Rpdj5gO1xyXG5cclxuICAgICAgICBhdHRyTmFtZSArPSBgPGRpdj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9XCJjaGVja2JveFwiIGNsYXNzPVwiYXR0ci12aXNpYmlsaXR5XCIgJHtpdGVtLnZpc2libGUgPyAnY2hlY2tlZCcgOiAnJ30gdmFsdWU9XCIxXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxsYWJlbD4ke19mLnRleHQoJ1Zpc2libGUgb24gdGhlIHByb2R1Y3QgcGFnZScpfTwvbGFiZWw+XHJcbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+YDtcclxuXHJcbiAgICAgICAgYXR0ck5hbWUgKz0gYDxkaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxpbnB1dCB0eXBlPVwiY2hlY2tib3hcIiBjbGFzcz1cImF0dHItdmFyaWF0aW9uXCIgJHtpdGVtLnZhcmlhdGlvbiA/ICdjaGVja2VkJyA6ICcnfSB2YWx1ZT1cIjFcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPGxhYmVsPiR7X2YudGV4dCgnVXNlZCBmb3IgdmFyaWF0aW9ucyAoYXBwbHkgZm9yIHZhcmlhYmxlKScpfTwvbGFiZWw+XHJcbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+YDtcclxuXHJcbiAgICAgICAgcmV0dXJuIGA8dHIgY2xhc3M9XCJ2aS13YmUtYXR0cmlidXRlLXJvd1wiIGRhdGEtYXR0cj0nJHtKU09OLnN0cmluZ2lmeShpdGVtKX0nPlxyXG4gICAgICAgICAgICAgICAgICAgIDx0ZCBjbGFzcz1cInZpLXdiZS1sZWZ0XCI+JHthdHRyTmFtZX08L3RkPlxyXG4gICAgICAgICAgICAgICAgICAgIDx0ZD4ke3ZhbHVlfTwvdGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgPHRkIGNsYXNzPVwidmktd2JlLXJpZ2h0XCI+PGkgY2xhc3M9XCJpY29uIHRyYXNoXCI+IDwvaT4gPGkgY2xhc3M9XCJpY29uIG1vdmVcIj4gPC9pPjwvdGQ+XHJcbiAgICAgICAgICAgICAgICA8L3RyPmA7XHJcbiAgICB9XHJcblxyXG5cclxufSIsImltcG9ydCB7QXR0cmlidXRlc30gZnJvbSBcIi4vYXR0cmlidXRlc1wiO1xyXG5pbXBvcnQgX2YgZnJvbSBcIi4vZnVuY3Rpb25zXCI7XHJcblxyXG5jb25zdCAkID0galF1ZXJ5O1xyXG5cclxuZXhwb3J0IGNvbnN0IFNpZGViYXIgPSB7XHJcbiAgICBpbml0KCkge1xyXG4gICAgICAgICQoJy52aS11aS5tZW51IC5pdGVtJykudmlfdGFiKCk7XHJcbiAgICAgICAgdGhpcy5yZXZpc2lvbiA9IHt9O1xyXG4gICAgICAgIHRoaXMuc2lkZWJhciA9ICQoJyN2aS13YmUtc2lkZWJhcicpO1xyXG4gICAgICAgIHRoaXMuaGlzdG9yeUJvZHlUYWJsZSA9ICQoJyN2aS13YmUtaGlzdG9yeS1wb2ludHMtbGlzdCB0Ym9keScpO1xyXG5cclxuICAgICAgICB0aGlzLnNpZGViYXIub24oJ2NsaWNrJywgJy52aS13YmUtYXBwbHktZmlsdGVyJywgdGhpcy5hcHBseUZpbHRlci5iaW5kKHRoaXMpKTtcclxuICAgICAgICB0aGlzLnNpZGViYXIub24oJ2NsaWNrJywgJy52aS13YmUtZmlsdGVyLWxhYmVsJywgdGhpcy5maWx0ZXJJbnB1dExhYmVsRm9jdXMpO1xyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignZm9jdXMnLCAnLnZpLXdiZS1maWx0ZXItaW5wdXQnLCB0aGlzLmZpbHRlcklucHV0Rm9jdXMpO1xyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignYmx1cicsICcudmktd2JlLWZpbHRlci1pbnB1dCcsIHRoaXMuZmlsdGVySW5wdXRCbHVyKTtcclxuICAgICAgICB0aGlzLnNpZGViYXIub24oJ2NsaWNrJywgJy52aS13YmUtZ2V0LW1ldGEtZmllbGRzJywgdGhpcy5nZXRNZXRhRmllbGRzLmJpbmQodGhpcykpO1xyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignY2xpY2snLCAnLnZpLXdiZS1zYXZlLW1ldGEtZmllbGRzOm5vdCgubG9hZGluZyknLCB0aGlzLnNhdmVNZXRhRmllbGRzLmJpbmQodGhpcykpO1xyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignY2xpY2snLCAnLnZpLXdiZS1hZGQtbmV3LW1ldGEtZmllbGQnLCB0aGlzLmFkZE5ld01ldGFGaWVsZC5iaW5kKHRoaXMpKTtcclxuICAgICAgICB0aGlzLnNpZGViYXIuZmluZCgndGFibGUudmktd2JlLW1ldGEtZmllbGRzLWNvbnRhaW5lciB0Ym9keScpLnNvcnRhYmxlKHtheGlzOiAneScsfSk7XHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLmZpbmQoJ3RhYmxlLnZpLXdiZS1tZXRhLWZpZWxkcy1jb250YWluZXInKS5vbignY2xpY2snLCAnLnZpLXdiZS1yZW1vdmUtbWV0YS1yb3cnLCB0aGlzLnJlbW92ZU1ldGFSb3cpO1xyXG5cclxuICAgICAgICB0aGlzLnNpZGViYXIub24oJ2NsaWNrJywgJy52aS13YmUtc2F2ZS10YXhvbm9teS1maWVsZHM6bm90KC5sb2FkaW5nKScsIHRoaXMuc2F2ZVRheG9ub215RmllbGRzKTtcclxuXHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdjbGljaycsICcudmktd2JlLXNhdmUtc2V0dGluZ3MnLCB0aGlzLnNhdmVTZXR0aW5ncy5iaW5kKHRoaXMpKTtcclxuXHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdjbGljaycsICcudmktd2JlLXZpZXctaGlzdG9yeS1wb2ludCcsIHRoaXMudmlld0hpc3RvcnlQb2ludC5iaW5kKHRoaXMpKTtcclxuICAgICAgICB0aGlzLnNpZGViYXIub24oJ2NsaWNrJywgJy52aS13YmUtcmVjb3ZlcicsIHRoaXMucmVjb3Zlci5iaW5kKHRoaXMpKTtcclxuICAgICAgICB0aGlzLnNpZGViYXIub24oJ2NsaWNrJywgJy52aS13YmUtcmV2ZXJ0LXRoaXMtcG9pbnQnLCB0aGlzLnJldmVydEFsbFByb2R1Y3RzLmJpbmQodGhpcykpO1xyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignY2xpY2snLCAnLnZpLXdiZS1yZXZlcnQtdGhpcy1rZXknLCB0aGlzLnJldmVydFByb2R1Y3RBdHRyaWJ1dGUuYmluZCh0aGlzKSk7XHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdjbGljaycsICcudmktd2JlLXBhZ2luYXRpb24gYS5pdGVtJywgdGhpcy5jaGFuZ2VQYWdlLmJpbmQodGhpcykpO1xyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignY2hhbmdlJywgJy52aS13YmUtZ28tdG8tcGFnZScsIHRoaXMuY2hhbmdlUGFnZUJ5SW5wdXQuYmluZCh0aGlzKSk7XHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdjbGljaycsICcudmktd2JlLW11bHRpLXNlbGVjdC1jbGVhcicsIHRoaXMuY2xlYXJNdWx0aVNlbGVjdCk7XHJcblxyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignY2hhbmdlJywgJy52aS13YmUtbWV0YS1jb2x1bW4tdHlwZScsIHRoaXMubWV0YUZpZWxkQ2hhbmdlVHlwZSk7XHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdrZXl1cCcsICcudmktd2JlLXNlYXJjaC1tZXRha2V5JywgdGhpcy5zZWFyY2hNZXRhS2V5KTtcclxuXHJcbiAgICAgICAgdGhpcy5maWx0ZXIoKTtcclxuICAgICAgICB0aGlzLnNldHRpbmdzKCk7XHJcbiAgICAgICAgdGhpcy5tZXRhZmllbGRzKCk7XHJcbiAgICAgICAgdGhpcy5oaXN0b3J5KCk7XHJcblxyXG4gICAgICAgIHJldHVybiB0aGlzLnNpZGViYXI7XHJcbiAgICB9LFxyXG5cclxuICAgIGZpbHRlcigpIHtcclxuICAgICAgICBsZXQgZmlsdGVyRm9ybSA9ICQoJyN2aS13YmUtcHJvZHVjdHMtZmlsdGVyJyksXHJcbiAgICAgICAgICAgIGZpbHRlcklucHV0ID0gJCgnLnZpLXdiZS1maWx0ZXItaW5wdXQnKSxcclxuICAgICAgICAgICAgY3NzVG9wID0ge3RvcDogLTJ9LFxyXG4gICAgICAgICAgICBjc3NNaWRkbGUgPSB7dG9wOiAnNTAlJ307XHJcblxyXG4gICAgICAgIGZpbHRlcklucHV0LmVhY2goKGksIGVsKSA9PiB7XHJcbiAgICAgICAgICAgIGlmICgkKGVsKS52YWwoKSkgJChlbCkucGFyZW50KCkucHJldigpLmNzcyhjc3NUb3ApO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICBmaWx0ZXJJbnB1dC5vbignZm9jdXMnLCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgIGxldCBsYWJlbCA9ICQodGhpcykucHJldigpO1xyXG4gICAgICAgICAgICBsYWJlbC5jc3MoY3NzVG9wKTtcclxuICAgICAgICAgICAgJCh0aGlzKS5vbignYmx1cicsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgICAgIGlmICghJCh0aGlzKS52YWwoKSkgbGFiZWwuY3NzKGNzc01pZGRsZSk7XHJcbiAgICAgICAgICAgIH0pXHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIHRoaXMuc2lkZWJhci5vbignY2xpY2snLCAnLnZpLXdiZS1maWx0ZXItbGFiZWwnLCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgICQodGhpcykubmV4dCgpLnRyaWdnZXIoJ2ZvY3VzJyk7XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIGxldCBjbGVhcmFibGVGaWx0ZXIgPSBmaWx0ZXJGb3JtLmZpbmQoJy52aS13YmUudmktdWkuZHJvcGRvd24nKS5kcm9wZG93bih7Y2xlYXJhYmxlOiB0cnVlfSksXHJcbiAgICAgICAgICAgIGNvbXBhY3RGaWx0ZXIgPSBmaWx0ZXJGb3JtLmZpbmQoJy52aS11aS5jb21wYWN0LmRyb3Bkb3duJykuZHJvcGRvd24oKTtcclxuXHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdjbGljaycsICcudmktd2JlLWNsZWFyLWZpbHRlcicsIGZ1bmN0aW9uICgpIHtcclxuICAgICAgICAgICAgJCgnLnZpLXdiZS1maWx0ZXItbGFiZWwnKS5jc3MoY3NzTWlkZGxlKTtcclxuICAgICAgICAgICAgZmlsdGVySW5wdXQudmFsKCcnKTtcclxuICAgICAgICAgICAgY2xlYXJhYmxlRmlsdGVyLmRyb3Bkb3duKCdjbGVhcicpO1xyXG4gICAgICAgICAgICBjb21wYWN0RmlsdGVyLmZpbmQoJy5tZW51IC5pdGVtOmZpcnN0JykudHJpZ2dlcignY2xpY2snKTtcclxuICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdjaGFuZ2UnLCAnI3ZpLXdiZS1oYXNfZXhwaXJlX2RhdGUnLCBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgICAgIGxldCBleHBpcmVEYXRlR3JvdXAgPSAkKCcudmktd2JlLWV4cGlyZS1kYXRlLWdyb3VwJyk7XHJcbiAgICAgICAgICAgICQodGhpcykudmFsKCkgPT09ICd5ZXMnID8gZXhwaXJlRGF0ZUdyb3VwLnNob3coKSA6IGV4cGlyZURhdGVHcm91cC5oaWRlKCk7XHJcbiAgICAgICAgfSk7XHJcblxyXG4gICAgICAgIHRoaXMuc2lkZWJhci5maW5kKCcjdmktd2JlLWhhc19leHBpcmVfZGF0ZScpLnRyaWdnZXIoJ2NoYW5nZScpXHJcbiAgICB9LFxyXG5cclxuICAgIHNldHRpbmdzKCkge1xyXG4gICAgICAgIGxldCBzZXR0aW5nc0Zvcm0gPSAkKCcudmktd2JlLXNldHRpbmdzLXRhYicpO1xyXG4gICAgICAgIHNldHRpbmdzRm9ybS5maW5kKCdzZWxlY3QuZHJvcGRvd24nKS5kcm9wZG93bigpO1xyXG4gICAgfSxcclxuXHJcbiAgICBtZXRhZmllbGRzKCkge1xyXG4gICAgICAgIHRoaXMucmVuZGVyTWV0YUZpZWxkc1RhYmxlKEF0dHJpYnV0ZXMubWV0YUZpZWxkcyk7XHJcbiAgICB9LFxyXG5cclxuICAgIGhpc3RvcnkoKSB7XHJcbiAgICAgICAgdGhpcy5wYWdpbmF0aW9uKDEpO1xyXG4gICAgICAgIC8vIHRoaXMuc2F2ZVJldmlzaW9uKCk7XHJcbiAgICB9LFxyXG5cclxuICAgIHBhZ2luYXRpb24oY3VycmVudFBhZ2UsIG1heFBhZ2UgPSBBdHRyaWJ1dGVzLmhpc3RvcnlQYWdlcykge1xyXG4gICAgICAgIHRoaXMuc2lkZWJhci5maW5kKCcudmktd2JlLXBhZ2luYXRpb24nKS5odG1sKF9mLnBhZ2luYXRpb24obWF4UGFnZSwgY3VycmVudFBhZ2UpKTtcclxuICAgIH0sXHJcblxyXG4gICAgYXBwbHlGaWx0ZXIoZSkge1xyXG4gICAgICAgIGxldCAkdGhpcyA9IHRoaXMsIHRoaXNCdG4gPSAkKGUudGFyZ2V0KTtcclxuXHJcbiAgICAgICAgaWYgKHRoaXNCdG4uaGFzQ2xhc3MoJ2xvYWRpbmcnKSkgcmV0dXJuO1xyXG5cclxuICAgICAgICBfZi5hamF4KHtcclxuICAgICAgICAgICAgZGF0YToge1xyXG4gICAgICAgICAgICAgICAgc3ViX2FjdGlvbjogJ2FkZF9maWx0ZXJfZGF0YScsXHJcbiAgICAgICAgICAgICAgICBmaWx0ZXJfZGF0YTogJCgnI3ZpLXdiZS1wcm9kdWN0cy1maWx0ZXInKS5zZXJpYWxpemUoKSxcclxuICAgICAgICAgICAgICAgIGZpbHRlcl9rZXk6IEF0dHJpYnV0ZXMuZmlsdGVyS2V5XHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIGJlZm9yZVNlbmQoKSB7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLmFkZENsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLnJlbW92ZUNsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgICAgICAkdGhpcy5zaWRlYmFyLnRyaWdnZXIoJ2FmdGVyQWRkRmlsdGVyJywgW3Jlcy5kYXRhXSk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG4gICAgbGltaXRQcm9kdWN0UGVyUGFnZSgpIHtcclxuICAgICAgICBsZXQgdmFsdWUgPSAkKHRoaXMpLnZhbCgpO1xyXG4gICAgICAgIGlmICh2YWx1ZSA+IDUwKSAkKHRoaXMpLnZhbCg1MCk7XHJcbiAgICAgICAgaWYgKHZhbHVlIDwgMCkgJCh0aGlzKS52YWwoMCk7XHJcbiAgICB9LFxyXG5cclxuICAgIHNhdmVTZXR0aW5ncyhlKSB7XHJcbiAgICAgICAgbGV0ICR0aGlzID0gdGhpcywgdGhpc0J0biA9ICQoZS50YXJnZXQpO1xyXG5cclxuICAgICAgICBpZiAodGhpc0J0bi5oYXNDbGFzcygnbG9hZGluZycpKSByZXR1cm47XHJcblxyXG4gICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICBkYXRhOiB7XHJcbiAgICAgICAgICAgICAgICBzdWJfYWN0aW9uOiAnc2F2ZV9zZXR0aW5ncycsXHJcbiAgICAgICAgICAgICAgICBmaWVsZHM6ICQoJ2Zvcm0udmktd2JlLXNldHRpbmdzLXRhYicpLnNlcmlhbGl6ZSgpXHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIGJlZm9yZVNlbmQoKSB7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLmFkZENsYXNzKCdsb2FkaW5nJylcclxuICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgIGlmIChyZXMuc3VjY2Vzcykge1xyXG4gICAgICAgICAgICAgICAgICAgIEF0dHJpYnV0ZXMuc2V0dGluZ3MgPSByZXMuZGF0YS5zZXR0aW5ncztcclxuICAgICAgICAgICAgICAgICAgICAvLyBjbGVhckludGVydmFsKCR0aGlzLmF1dG9TYXZlUmV2aXNpb24pO1xyXG4gICAgICAgICAgICAgICAgICAgIC8vICR0aGlzLnNhdmVSZXZpc2lvbigpO1xyXG4gICAgICAgICAgICAgICAgICAgICR0aGlzLnNpZGViYXIudHJpZ2dlcignYWZ0ZXJTYXZlU2V0dGluZ3MnLCBbcmVzLmRhdGFdKTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIHRoaXNCdG4ucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuICAgIGZpbHRlcklucHV0TGFiZWxGb2N1cygpIHtcclxuICAgICAgICAkKHRoaXMpLm5leHQoKS5maW5kKCdpbnB1dCcpLnRyaWdnZXIoJ2ZvY3VzJyk7XHJcbiAgICB9LFxyXG5cclxuICAgIGZpbHRlcklucHV0Rm9jdXMoKSB7XHJcbiAgICAgICAgJCh0aGlzKS5wYXJlbnQoKS5wcmV2KCkuY3NzKHt0b3A6IC0yfSk7XHJcbiAgICB9LFxyXG5cclxuICAgIGZpbHRlcklucHV0Qmx1cigpIHtcclxuICAgICAgICBpZiAoISQodGhpcykudmFsKCkpICQodGhpcykucGFyZW50KCkucHJldigpLmNzcyh7dG9wOiAnNTAlJ30pO1xyXG4gICAgfSxcclxuXHJcbiAgICBnZXRNZXRhRmllbGRzKGUpIHtcclxuICAgICAgICBsZXQgJHRoaXMgPSB0aGlzLCB0aGlzQnRuID0gJChlLnRhcmdldCk7XHJcblxyXG4gICAgICAgIGlmICh0aGlzQnRuLmhhc0NsYXNzKCdsb2FkaW5nJykpIHJldHVybjtcclxuXHJcbiAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAnZ2V0X21ldGFfZmllbGRzJywgY3VycmVudF9tZXRhX2ZpZWxkczogJHRoaXMuZ2V0Q3VycmVudE1ldGFGaWVsZHMoKX0sXHJcbiAgICAgICAgICAgIGJlZm9yZVNlbmQoKSB7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLmFkZENsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAkdGhpcy5yZW5kZXJNZXRhRmllbGRzVGFibGUocmVzLmRhdGEpO1xyXG4gICAgICAgICAgICAgICAgQXR0cmlidXRlcy5tZXRhRmllbGRzID0gcmVzLmRhdGE7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLnJlbW92ZUNsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG4gICAgcmVuZGVyTWV0YUZpZWxkc1RhYmxlKGRhdGEpIHtcclxuICAgICAgICBsZXQgaHRtbCA9ICcnO1xyXG5cclxuICAgICAgICBmb3IgKGxldCBtZXRhS2V5IGluIGRhdGEpIHtcclxuICAgICAgICAgICAgaHRtbCArPSB0aGlzLnJlbmRlclJvdyhtZXRhS2V5LCBkYXRhKTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgICQoJy52aS13YmUtbWV0YS1maWVsZHMtY29udGFpbmVyIHRib2R5JykuaHRtbChodG1sKTtcclxuICAgIH0sXHJcblxyXG4gICAgcmVuZGVyUm93KG1ldGFLZXksIGRhdGEpIHtcclxuICAgICAgICBsZXQgbWV0YSA9IGRhdGFbbWV0YUtleV0gfHwge30sXHJcbiAgICAgICAgICAgIG9wdGlvbkh0bWwgPSAnJyxcclxuICAgICAgICAgICAgaW5wdXRUeXBlID0gbWV0YS5pbnB1dF90eXBlIHx8ICcnLFxyXG4gICAgICAgICAgICBvcHRpb25zID0ge1xyXG4gICAgICAgICAgICAgICAgdGV4dGlucHV0OiAnVGV4dCBpbnB1dCcsXHJcbiAgICAgICAgICAgICAgICB0ZXh0ZWRpdG9yOiAnVGV4dCBlZGl0b3InLFxyXG4gICAgICAgICAgICAgICAgbnVtYmVyaW5wdXQ6ICdOdW1iZXIgaW5wdXQnLFxyXG4gICAgICAgICAgICAgICAgYXJyYXk6ICdBcnJheScsXHJcbiAgICAgICAgICAgICAgICBqc29uOiAnSlNPTicsXHJcbiAgICAgICAgICAgICAgICBjaGVja2JveDogJ0NoZWNrYm94JyxcclxuICAgICAgICAgICAgICAgIGNhbGVuZGFyOiAnQ2FsZW5kYXInLFxyXG4gICAgICAgICAgICAgICAgaW1hZ2U6ICdJbWFnZScsXHJcbiAgICAgICAgICAgICAgICBzZWxlY3Q6ICdTZWxlY3QnLFxyXG4gICAgICAgICAgICAgICAgbXVsdGlzZWxlY3Q6ICdNdWx0aXNlbGVjdCcsXHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIG1ldGFWYWx1ZSA9IG1ldGEubWV0YV92YWx1ZSB8fCAnJyxcclxuICAgICAgICAgICAgc2hvcnRWYWx1ZSA9IG1ldGFWYWx1ZS5zbGljZSgwLCAxNSksXHJcbiAgICAgICAgICAgIGZ1bGxWYWx1ZUh0bWwgPSBtZXRhVmFsdWUubGVuZ3RoID4gMTYgPyBgPGRpdiBjbGFzcz1cInZpLXdiZS1mdWxsLW1ldGEtdmFsdWVcIj4ke21ldGFWYWx1ZX08L2Rpdj5gIDogJycsXHJcbiAgICAgICAgICAgIHNlbGVjdFNvdXJjZSA9ICcnO1xyXG5cclxuICAgICAgICBmb3IgKGxldCBvcHRpb25WYWx1ZSBpbiBvcHRpb25zKSB7XHJcbiAgICAgICAgICAgIG9wdGlvbkh0bWwgKz0gYDxvcHRpb24gdmFsdWU9XCIke29wdGlvblZhbHVlfVwiICR7b3B0aW9uVmFsdWUgPT09IGlucHV0VHlwZSA/ICdzZWxlY3RlZCcgOiAnJ30+JHtvcHRpb25zW29wdGlvblZhbHVlXX08L29wdGlvbj5gO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgc2hvcnRWYWx1ZSArPSBzaG9ydFZhbHVlLmxlbmd0aCA8IG1ldGFWYWx1ZS5sZW5ndGggPyAnLi4uJyA6ICcnO1xyXG5cclxuICAgICAgICBpZiAoaW5wdXRUeXBlID09PSAnc2VsZWN0JyB8fCBpbnB1dFR5cGUgPT09ICdtdWx0aXNlbGVjdCcpIHtcclxuICAgICAgICAgICAgc2VsZWN0U291cmNlICs9IGA8dGV4dGFyZWEgY2xhc3M9XCJ2aS13YmUtc2VsZWN0LW9wdGlvbnNcIj4ke21ldGEuc2VsZWN0X29wdGlvbnN9PC90ZXh0YXJlYT5gXHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICByZXR1cm4gYDx0cj5cclxuICAgICAgICAgICAgICAgICAgICA8dGQgY2xhc3M9XCJ2aS13YmUtbWV0YS1rZXlcIj4ke21ldGFLZXl9PC90ZD5cclxuICAgICAgICAgICAgICAgICAgICA8dGQ+PGlucHV0IHR5cGU9XCJ0ZXh0XCIgY2xhc3M9XCJ2aS13YmUtbWV0YS1jb2x1bW4tbmFtZVwiIHZhbHVlPVwiJHttZXRhLmNvbHVtbl9uYW1lIHx8ICcnfVwiPjwvdGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgPHRkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwidmktd2JlLWRpc3BsYXktbWV0YS12YWx1ZVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cInZpLXdiZS1zaG9ydC1tZXRhLXZhbHVlXCI+JHtzaG9ydFZhbHVlfTwvZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJHtmdWxsVmFsdWVIdG1sfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8L3RkPlxyXG4gICAgICAgICAgICAgICAgICAgIDx0ZD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPHNlbGVjdCBjbGFzcz1cInZpLXdiZS1tZXRhLWNvbHVtbi10eXBlXCI+JHtvcHRpb25IdG1sfTwvc2VsZWN0PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAke3NlbGVjdFNvdXJjZX1cclxuICAgICAgICAgICAgICAgICAgICA8L3RkPlxyXG4gICAgICAgICAgICAgICAgICAgIDx0ZCBjbGFzcz1cInZpLXdiZS1tZXRhLWZpZWxkLWFjdGl2ZS1jb2x1bW5cIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cInZpLXVpIHRvZ2dsZSBjaGVja2JveFwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgIDxpbnB1dCB0eXBlPVwiY2hlY2tib3hcIiBjbGFzcz1cInZpLXdiZS1tZXRhLWNvbHVtbi1hY3RpdmVcIiAke3BhcnNlSW50KG1ldGEuYWN0aXZlKSA/ICdjaGVja2VkJyA6ICcnfT5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICA8bGFiZWw+IDwvbGFiZWw+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDwvZGl2PiAgXHJcbiAgICAgICAgICAgICAgICAgICAgPC90ZD5cclxuICAgICAgICAgICAgICAgICAgICA8dGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJ2aS13YmUtbWV0YS1maWVsZC1hY3Rpb25zXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpLXVpIGJ1dHRvbiBiYXNpYyBtaW5pIHZpLXdiZS1yZW1vdmUtbWV0YS1yb3dcIj48aSBjbGFzcz1cImljb24gdHJhc2hcIj4gPC9pPjwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIGJhc2ljIG1pbmlcIj48aSBjbGFzcz1cImljb24gbW92ZVwiPiA8L2k+PC9zcGFuPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8L3RkPlxyXG4gICAgICAgICAgICAgICAgPC90cj5gO1xyXG4gICAgfSxcclxuXHJcbiAgICBtZXRhRmllbGRDaGFuZ2VUeXBlKCkge1xyXG4gICAgICAgIGxldCBzZWxlY3RUeXBlT3B0aW9ucyA9ICQoJzx0ZXh0YXJlYSBjbGFzcz1cInZpLXdiZS1zZWxlY3Qtb3B0aW9uc1wiPjwvdGV4dGFyZWE+Jyk7XHJcbiAgICAgICAgbGV0IHZhbCA9ICQodGhpcykudmFsKCk7XHJcbiAgICAgICAgbGV0IHNpYmxpbmdzID0gJCh0aGlzKS5zaWJsaW5ncygpO1xyXG4gICAgICAgIGlmICh2YWwgPT09ICdzZWxlY3QnIHx8IHZhbCA9PT0gJ211bHRpc2VsZWN0Jykge1xyXG4gICAgICAgICAgICBpZiAoIXNpYmxpbmdzLmxlbmd0aCkgJCh0aGlzKS5hZnRlcihzZWxlY3RUeXBlT3B0aW9ucyk7XHJcbiAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgc2libGluZ3MucmVtb3ZlKCk7XHJcbiAgICAgICAgfVxyXG4gICAgfSxcclxuXHJcbiAgICBzZWFyY2hNZXRhS2V5KCkge1xyXG4gICAgICAgIGxldCBmaWx0ZXIgPSAkKHRoaXMpLnZhbCgpLnRvTG93ZXJDYXNlKCk7XHJcbiAgICAgICAgJCgnLnZpLXdiZS1tZXRhLWZpZWxkcy1jb250YWluZXIgdGJvZHkgdHInKS5lYWNoKGZ1bmN0aW9uIChpLCB0cikge1xyXG4gICAgICAgICAgICBsZXQgbWV0YUtleSA9ICQodHIpLmZpbmQoJy52aS13YmUtbWV0YS1rZXknKS50ZXh0KCkudHJpbSgpLnRvTG93ZXJDYXNlKCk7XHJcbiAgICAgICAgICAgIGlmIChtZXRhS2V5LmluZGV4T2YoZmlsdGVyKSA+IC0xKSB7XHJcbiAgICAgICAgICAgICAgICAkKHRyKS5zaG93KCk7XHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICAkKHRyKS5oaWRlKCk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuICAgIH0sXHJcblxyXG4gICAgc2F2ZU1ldGFGaWVsZHMoZSkge1xyXG4gICAgICAgIGxldCB0aGlzQnRuID0gJChlLnRhcmdldCk7XHJcblxyXG4gICAgICAgIGlmICh0aGlzQnRuLmhhc0NsYXNzKCdsb2FkaW5nJykpIHJldHVybjtcclxuXHJcbiAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAnc2F2ZV9tZXRhX2ZpZWxkcycsIG1ldGFfZmllbGRzOiB0aGlzLmdldEN1cnJlbnRNZXRhRmllbGRzKCl9LFxyXG4gICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgdGhpc0J0bi5hZGRDbGFzcygnbG9hZGluZycpO1xyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgdGhpc0J0bi5yZW1vdmVDbGFzcygnbG9hZGluZycpO1xyXG4gICAgICAgICAgICAgICAgbG9jYXRpb24ucmVsb2FkKCk7XHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIGVycm9yKHJlcykge1xyXG4gICAgICAgICAgICAgICAgY29uc29sZS5sb2cocmVzKVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuICAgIGdldEN1cnJlbnRNZXRhRmllbGRzKCkge1xyXG4gICAgICAgIGxldCBtZXRhX2ZpZWxkcyA9IHt9O1xyXG4gICAgICAgIGxldCBtZXRhQXJyID0gQXR0cmlidXRlcy5tZXRhRmllbGRzO1xyXG4gICAgICAgICQoJ3RhYmxlLnZpLXdiZS1tZXRhLWZpZWxkcy1jb250YWluZXIgdGJvZHkgdHInKS5lYWNoKGZ1bmN0aW9uIChpLCByb3cpIHtcclxuICAgICAgICAgICAgbGV0IG1ldGFLZXkgPSAkKHJvdykuZmluZCgnLnZpLXdiZS1tZXRhLWtleScpLnRleHQoKTtcclxuICAgICAgICAgICAgbWV0YV9maWVsZHNbbWV0YUtleV0gPSB7XHJcbiAgICAgICAgICAgICAgICBjb2x1bW5fbmFtZTogJChyb3cpLmZpbmQoJy52aS13YmUtbWV0YS1jb2x1bW4tbmFtZScpLnZhbCgpLFxyXG4gICAgICAgICAgICAgICAgaW5wdXRfdHlwZTogJChyb3cpLmZpbmQoJy52aS13YmUtbWV0YS1jb2x1bW4tdHlwZScpLnZhbCgpLFxyXG4gICAgICAgICAgICAgICAgYWN0aXZlOiAkKHJvdykuZmluZCgnLnZpLXdiZS1tZXRhLWNvbHVtbi1hY3RpdmU6Y2hlY2tlZCcpLmxlbmd0aCxcclxuICAgICAgICAgICAgICAgIG1ldGFfdmFsdWU6IG1ldGFBcnJbbWV0YUtleV0gPyBtZXRhQXJyW21ldGFLZXldLm1ldGFfdmFsdWUgOiAnJyxcclxuICAgICAgICAgICAgICAgIHNlbGVjdF9vcHRpb25zOiAkKHJvdykuZmluZCgnLnZpLXdiZS1zZWxlY3Qtb3B0aW9ucycpLnZhbCgpLFxyXG4gICAgICAgICAgICB9O1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICByZXR1cm4gbWV0YV9maWVsZHM7XHJcbiAgICB9LFxyXG5cclxuICAgIGFkZE5ld01ldGFGaWVsZChlKSB7XHJcbiAgICAgICAgbGV0IGlucHV0ID0gJChlLmN1cnJlbnRUYXJnZXQpLnByZXYoKSxcclxuICAgICAgICAgICAgbWV0YUtleSA9IGlucHV0LnZhbCgpLFxyXG4gICAgICAgICAgICB2YWxpZGF0ZSA9IG1ldGFLZXkubWF0Y2goL15bXFx3XFxkXy1dKiQvZyk7XHJcblxyXG4gICAgICAgIGlmICghbWV0YUtleSB8fCAhdmFsaWRhdGUgfHwgQXR0cmlidXRlcy5tZXRhRmllbGRzW21ldGFLZXldKSByZXR1cm47XHJcblxyXG4gICAgICAgIGxldCBuZXdSb3cgPSB0aGlzLnJlbmRlclJvdyhtZXRhS2V5LCB7fSk7XHJcbiAgICAgICAgaWYgKG5ld1Jvdykge1xyXG4gICAgICAgICAgICBpbnB1dC52YWwoJycpO1xyXG4gICAgICAgICAgICAkKCd0YWJsZS52aS13YmUtbWV0YS1maWVsZHMtY29udGFpbmVyIHRib2R5JykuYXBwZW5kKG5ld1Jvdyk7XHJcbiAgICAgICAgfVxyXG4gICAgfSxcclxuXHJcbiAgICByZW1vdmVNZXRhUm93KCkge1xyXG4gICAgICAgICQodGhpcykuY2xvc2VzdCgndHInKS5yZW1vdmUoKTtcclxuICAgIH0sXHJcblxyXG4gICAgc2F2ZVRheG9ub215RmllbGRzKGUpIHtcclxuICAgICAgICBsZXQgdGhpc0J0biA9ICQoZS50YXJnZXQpO1xyXG4gICAgICAgIGxldCB0YXhvbm9teUZpZWxkcyA9IFtdO1xyXG5cclxuICAgICAgICAkKCd0YWJsZS52aS13YmUtdGF4b25vbXktZmllbGRzIC52aS13YmUtdGF4b25vbXktYWN0aXZlOmNoZWNrZWQnKS5lYWNoKGZ1bmN0aW9uIChpLCByb3cpIHtcclxuICAgICAgICAgICAgbGV0IHRheEtleSA9ICQodGhpcykuY2xvc2VzdCgndHInKS5maW5kKCcudmktd2JlLXRheG9ub215LWtleScpLnRleHQoKTtcclxuICAgICAgICAgICAgdGF4b25vbXlGaWVsZHMucHVzaCh0YXhLZXkpO1xyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICBpZiAodGF4b25vbXlGaWVsZHMubGVuZ3RoKSB7XHJcbiAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgZGF0YToge3N1Yl9hY3Rpb246ICdzYXZlX3RheG9ub215X2ZpZWxkcycsIHRheG9ub215X2ZpZWxkczogdGF4b25vbXlGaWVsZHN9LFxyXG4gICAgICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgICAgICB0aGlzQnRuLmFkZENsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICB0aGlzQnRuLnJlbW92ZUNsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgbG9jYXRpb24ucmVsb2FkKCk7XHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgZXJyb3IocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgY29uc29sZS5sb2cocmVzKVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9XHJcblxyXG4gICAgfSxcclxuXHJcbiAgICB2aWV3SGlzdG9yeVBvaW50KGUpIHtcclxuICAgICAgICBsZXQgdGhpc0J0biA9ICQoZS5jdXJyZW50VGFyZ2V0KSxcclxuICAgICAgICAgICAgaGlzdG9yeWlEID0gdGhpc0J0bi5kYXRhKCdpZCcpLFxyXG4gICAgICAgICAgICAkdGhpcyA9IHRoaXM7XHJcblxyXG4gICAgICAgIGlmICh0aGlzQnRuLmhhc0NsYXNzKCdsb2FkaW5nJykpIHJldHVybjtcclxuXHJcbiAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAndmlld19oaXN0b3J5X3BvaW50JywgaWQ6IGhpc3RvcnlpRH0sXHJcbiAgICAgICAgICAgIGJlZm9yZVNlbmQoKSB7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLmFkZENsYXNzKCdsb2FkaW5nJyk7XHJcbiAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgIGNvbXBsZXRlKCkge1xyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgdGhpc0J0bi5yZW1vdmVDbGFzcygnbG9hZGluZycpO1xyXG5cclxuICAgICAgICAgICAgICAgIGlmIChyZXMuc3VjY2VzcyAmJiByZXMuZGF0YSkge1xyXG4gICAgICAgICAgICAgICAgICAgIGxldCBwcm9kdWN0cyA9IHJlcy5kYXRhLmNvbXBhcmU7XHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IGh0bWwgPSAnJztcclxuICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCBpZCBpbiBwcm9kdWN0cykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgaXRlbSA9IHByb2R1Y3RzW2lkXTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgaHRtbCArPSBgPGRpdiBjbGFzcz1cInZpLXdiZS1oaXN0b3J5LXByb2R1Y3RcIiBkYXRhLXByb2R1Y3RfaWQ9XCIke2lkfVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cInRpdGxlXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGkgY2xhc3M9XCJkcm9wZG93biBpY29uXCI+PC9pPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICR7aXRlbS5uYW1lfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxzcGFuIGNsYXNzPVwidmktdWkgYnV0dG9uIG1pbmkgYmFzaWMgdmktd2JlLXJldmVydC10aGlzLXByb2R1Y3RcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGkgY2xhc3M9XCJpY29uIHVuZG9cIj4gPC9pPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZGl2PmA7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgdGFibGUgPSAnJztcclxuICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQga2V5IGluIGl0ZW0uZmllbGRzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgY3VycmVudFZhbCA9IHR5cGVvZiBpdGVtLmN1cnJlbnRba2V5XSA9PT0gJ3N0cmluZycgPyBpdGVtLmN1cnJlbnRba2V5XSA6IEpTT04uc3RyaW5naWZ5KGl0ZW0uY3VycmVudFtrZXldKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBoaXN0b3J5VmFsID0gdHlwZW9mIGl0ZW0uaGlzdG9yeVtrZXldID09PSAnc3RyaW5nJyA/IGl0ZW0uaGlzdG9yeVtrZXldIDogSlNPTi5zdHJpbmdpZnkoaXRlbS5oaXN0b3J5W2tleV0pO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGFibGUgKz0gYDx0cj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQ+JHtpdGVtLmZpZWxkc1trZXldfTwvdGQ+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRkPiR7Y3VycmVudFZhbH08L3RkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZD4ke2hpc3RvcnlWYWx9PC90ZD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGQgY2xhc3M9XCJcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9XCJ2aS11aSBidXR0b24gYmFzaWMgbWluaSB2aS13YmUtcmV2ZXJ0LXRoaXMta2V5XCIgZGF0YS1wcm9kdWN0X2lkPVwiJHtpZH1cIiBkYXRhLXByb2R1Y3Rfa2V5PVwiJHtrZXl9XCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzcz1cImljb24gdW5kb1wiPiA8L2k+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3RkPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj5gO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICB0YWJsZSA9IGA8dGFibGUgaWQ9XCJ2aS13YmUtaGlzdG9yeS1wb2ludC1kZXRhaWxcIiBjbGFzcz1cInZpLXVpIGNlbGxlZCB0YWJsZVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dGhlYWQ+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0cj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0aD5BdHRyaWJ1dGU8L3RoPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRoPkN1cnJlbnQ8L3RoPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRoPkhpc3Rvcnk8L3RoPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRoIGNsYXNzPVwiXCI+UmV2ZXJ0PC90aD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90cj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC90aGVhZD5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHRib2R5PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAke3RhYmxlfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L3Rib2R5PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvdGFibGU+YDtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGh0bWwgKz0gYDxkaXYgY2xhc3M9XCJjb250ZW50XCI+JHt0YWJsZX08L2Rpdj48L2Rpdj5gXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICBodG1sID0gJChgPGRpdiBjbGFzcz1cInZpLXVpIHN0eWxlZCBmbHVpZCBhY2NvcmRpb25cIj4ke2h0bWx9PC9kaXY+YCk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICQoJy52aS13YmUtaGlzdG9yeS1yZXZpZXcnKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAuaHRtbChodG1sKS5hdHRyKCdkYXRhLWhpc3RvcnlfaWQnLCBoaXN0b3J5aUQpXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC5wcmVwZW5kKGA8aDQ+SGlzdG9yeSBwb2ludDogJHtyZXMuZGF0YS5kYXRlfTwvaDQ+YClcclxuICAgICAgICAgICAgICAgICAgICAgICAgLmFwcGVuZChgPGRpdiBjbGFzcz1cInZpLXVpIGJ1dHRvbiB0aW55IHZpLXdiZS1yZXZlcnQtdGhpcy1wb2ludFwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAke19mLnRleHQoJ1JldmVydCBhbGwgcHJvZHVjdCBpbiB0aGlzIHBvaW50Jyl9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHA+ICR7X2YudGV4dCgnVGhlIGN1cnJlbnQgdmFsdWUgaXMgdGhlIHZhbHVlIG9mIHRoZSByZWNvcmRzIGluIGRhdGFiYXNlJyl9PC9wPmApO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBodG1sLmZpbmQoJy50aXRsZScpLm9uKCdjbGljaycsIChlKSA9PiAkdGhpcy5yZXZlcnRTaW5nbGVQcm9kdWN0KGUpKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgaHRtbC52aV9hY2NvcmRpb24oKTtcclxuICAgICAgICAgICAgICAgICAgICBodG1sLmZpbmQoJy50aXRsZTpmaXJzdCcpLnRyaWdnZXIoJ2NsaWNrJyk7XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KVxyXG4gICAgfSxcclxuXHJcbiAgICByZWNvdmVyKGUpIHtcclxuICAgICAgICBsZXQgdGhpc0J0biA9ICQoZS5jdXJyZW50VGFyZ2V0KSxcclxuICAgICAgICAgICAgaGlzdG9yeUlEID0gdGhpc0J0bi5kYXRhKCdpZCcpO1xyXG5cclxuICAgICAgICBpZiAodGhpc0J0bi5oYXNDbGFzcygnbG9hZGluZycpKSByZXR1cm47XHJcblxyXG4gICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICBkYXRhOiB7c3ViX2FjdGlvbjogJ3JldmVydF9oaXN0b3J5X2FsbF9wcm9kdWN0cycsIGhpc3RvcnlfaWQ6IGhpc3RvcnlJRH0sXHJcbiAgICAgICAgICAgIGJlZm9yZVNlbmQoKSB7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLmFkZENsYXNzKCdsb2FkaW5nJylcclxuICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgY29tcGxldGUoKSB7XHJcbiAgICAgICAgICAgICAgICB0aGlzQnRuLnJlbW92ZUNsYXNzKCdsb2FkaW5nJylcclxuICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgIGNvbnNvbGUubG9nKHJlcylcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH0pO1xyXG4gICAgfSxcclxuXHJcbiAgICByZXZlcnRTaW5nbGVQcm9kdWN0KGUpIHtcclxuICAgICAgICBsZXQgdGhpc0J0bjtcclxuICAgICAgICBpZiAoJChlLnRhcmdldCkuaGFzQ2xhc3MoJ3ZpLXdiZS1yZXZlcnQtdGhpcy1wcm9kdWN0JykpIHRoaXNCdG4gPSAkKGUudGFyZ2V0KTtcclxuICAgICAgICBpZiAoJChlLnRhcmdldCkucGFyZW50KCkuaGFzQ2xhc3MoJ3ZpLXdiZS1yZXZlcnQtdGhpcy1wcm9kdWN0JykpIHRoaXNCdG4gPSAkKGUudGFyZ2V0KS5wYXJlbnQoKTtcclxuXHJcbiAgICAgICAgaWYgKHRoaXNCdG4pIHtcclxuICAgICAgICAgICAgZS5zdG9wSW1tZWRpYXRlUHJvcGFnYXRpb24oKTtcclxuXHJcbiAgICAgICAgICAgIGxldCBwaWQgPSB0aGlzQnRuLmNsb3Nlc3QoJy52aS13YmUtaGlzdG9yeS1wcm9kdWN0JykuZGF0YSgncHJvZHVjdF9pZCcpLFxyXG4gICAgICAgICAgICAgICAgaGlzdG9yeUlEID0gdGhpc0J0bi5jbG9zZXN0KCcudmktd2JlLWhpc3RvcnktcmV2aWV3JykuZGF0YSgnaGlzdG9yeV9pZCcpO1xyXG5cclxuICAgICAgICAgICAgaWYgKHRoaXNCdG4uaGFzQ2xhc3MoJ2xvYWRpbmcnKSkgcmV0dXJuO1xyXG5cclxuICAgICAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgICAgICBkYXRhOiB7c3ViX2FjdGlvbjogJ3JldmVydF9oaXN0b3J5X3NpbmdsZV9wcm9kdWN0JywgaGlzdG9yeV9pZDogaGlzdG9yeUlELCBwaWQ6IHBpZH0sXHJcbiAgICAgICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgICAgIHRoaXNCdG4uYWRkQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIGNvbXBsZXRlKCkge1xyXG4gICAgICAgICAgICAgICAgICAgIHRoaXNCdG4ucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgY29uc29sZS5sb2cocmVzKVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9XHJcbiAgICB9LFxyXG5cclxuICAgIHJldmVydEFsbFByb2R1Y3RzKGUpIHtcclxuICAgICAgICBsZXQgdGhpc0J0biA9ICQoZS50YXJnZXQpO1xyXG4gICAgICAgIGxldCBoaXN0b3J5SUQgPSB0aGlzQnRuLmNsb3Nlc3QoJy52aS13YmUtaGlzdG9yeS1yZXZpZXcnKS5kYXRhKCdoaXN0b3J5X2lkJyk7XHJcblxyXG4gICAgICAgIGlmICh0aGlzQnRuLmhhc0NsYXNzKCdsb2FkaW5nJykpIHJldHVybjtcclxuXHJcbiAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAncmV2ZXJ0X2hpc3RvcnlfYWxsX3Byb2R1Y3RzJywgaGlzdG9yeV9pZDogaGlzdG9yeUlEfSxcclxuICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgIHRoaXNCdG4uYWRkQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBjb21wbGV0ZSgpIHtcclxuICAgICAgICAgICAgICAgIHRoaXNCdG4ucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgY29uc29sZS5sb2cocmVzKVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuICAgIHJldmVydFByb2R1Y3RBdHRyaWJ1dGUoZSkge1xyXG4gICAgICAgIGxldCB0aGlzQnRuID0gJChlLmN1cnJlbnRUYXJnZXQpLFxyXG4gICAgICAgICAgICBhdHRyaWJ1dGUgPSB0aGlzQnRuLmRhdGEoJ3Byb2R1Y3Rfa2V5JyksXHJcbiAgICAgICAgICAgIHBpZCA9IHRoaXNCdG4uY2xvc2VzdCgnLnZpLXdiZS1oaXN0b3J5LXByb2R1Y3QnKS5kYXRhKCdwcm9kdWN0X2lkJyksXHJcbiAgICAgICAgICAgIGhpc3RvcnlJRCA9IHRoaXNCdG4uY2xvc2VzdCgnLnZpLXdiZS1oaXN0b3J5LXJldmlldycpLmRhdGEoJ2hpc3RvcnlfaWQnKTtcclxuXHJcbiAgICAgICAgaWYgKHRoaXNCdG4uaGFzQ2xhc3MoJ2xvYWRpbmcnKSkgcmV0dXJuO1xyXG5cclxuICAgICAgICBfZi5hamF4KHtcclxuICAgICAgICAgICAgZGF0YToge3N1Yl9hY3Rpb246ICdyZXZlcnRfaGlzdG9yeV9wcm9kdWN0X2F0dHJpYnV0ZScsIGF0dHJpYnV0ZTogYXR0cmlidXRlLCBoaXN0b3J5X2lkOiBoaXN0b3J5SUQsIHBpZDogcGlkfSxcclxuICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgIHRoaXNCdG4uYWRkQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBjb21wbGV0ZSgpIHtcclxuICAgICAgICAgICAgICAgIHRoaXNCdG4ucmVtb3ZlQ2xhc3MoJ2xvYWRpbmcnKVxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgY29uc29sZS5sb2cocmVzKVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfSk7XHJcbiAgICB9LFxyXG5cclxuICAgIGNoYW5nZVBhZ2UoZSkge1xyXG4gICAgICAgIGxldCBwYWdlID0gcGFyc2VJbnQoJChlLmN1cnJlbnRUYXJnZXQpLmF0dHIoJ2RhdGEtcGFnZScpKTtcclxuICAgICAgICBpZiAoJChlLmN1cnJlbnRUYXJnZXQpLmhhc0NsYXNzKCdhY3RpdmUnKSB8fCAkKGUuY3VycmVudFRhcmdldCkuaGFzQ2xhc3MoJ2Rpc2FibGVkJykgfHwgIXBhZ2UpIHJldHVybjtcclxuICAgICAgICB0aGlzLmxvYWRIaXN0b3J5UGFnZShwYWdlKTtcclxuICAgIH0sXHJcblxyXG4gICAgY2hhbmdlUGFnZUJ5SW5wdXQoZSkge1xyXG4gICAgICAgIGxldCBwYWdlID0gcGFyc2VJbnQoJChlLnRhcmdldCkudmFsKCkpO1xyXG4gICAgICAgIGxldCBtYXggPSBwYXJzZUludCgkKGUudGFyZ2V0KS5hdHRyKCdtYXgnKSk7XHJcblxyXG4gICAgICAgIGlmIChwYWdlIDw9IG1heCAmJiBwYWdlID4gMCkgdGhpcy5sb2FkSGlzdG9yeVBhZ2UocGFnZSk7XHJcbiAgICB9LFxyXG5cclxuICAgIGNsZWFyTXVsdGlTZWxlY3QoKSB7XHJcbiAgICAgICAgJCh0aGlzKS5wYXJlbnQoKS5maW5kKCcudmktdWkuZHJvcGRvd24nKS5kcm9wZG93bignY2xlYXInKTtcclxuICAgIH0sXHJcblxyXG4gICAgbG9hZEhpc3RvcnlQYWdlKHBhZ2UpIHtcclxuICAgICAgICBsZXQgbG9hZGluZyA9IF9mLnNwaW5uZXIoKSxcclxuICAgICAgICAgICAgJHRoaXMgPSB0aGlzO1xyXG5cclxuICAgICAgICBpZiAocGFnZSkge1xyXG4gICAgICAgICAgICBfZi5hamF4KHtcclxuICAgICAgICAgICAgICAgIGRhdGFUeXBlOiAndGV4dCcsXHJcbiAgICAgICAgICAgICAgICBkYXRhOiB7c3ViX2FjdGlvbjogJ2xvYWRfaGlzdG9yeV9wYWdlJywgcGFnZTogcGFnZX0sXHJcbiAgICAgICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICR0aGlzLnNpZGViYXIuZmluZCgnLnZpLXdiZS1wYWdpbmF0aW9uJykucHJlcGVuZChsb2FkaW5nKTtcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICBjb21wbGV0ZSgpIHtcclxuICAgICAgICAgICAgICAgICAgICBsb2FkaW5nLnJlbW92ZSgpO1xyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgJHRoaXMucGFnaW5hdGlvbihwYWdlKTtcclxuICAgICAgICAgICAgICAgICAgICAkKCcjdmktd2JlLWhpc3RvcnktcG9pbnRzLWxpc3QgdGJvZHknKS5odG1sKHJlcyk7XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1cclxuICAgIH0sXHJcblxyXG4gICAgLy8gc2F2ZVJldmlzaW9uKCkge1xyXG4gICAgLy8gICAgIGxldCBhdXRvU2F2ZVRpbWUgPSBwYXJzZUludChBdHRyaWJ1dGVzLnNldHRpbmdzLmF1dG9fc2F2ZV9yZXZpc2lvbik7XHJcbiAgICAvLyAgICAgaWYgKGF1dG9TYXZlVGltZSA9PT0gMCkgcmV0dXJuO1xyXG4gICAgLy8gICAgIGxldCAkdGhpcyA9IHRoaXM7XHJcbiAgICAvL1xyXG4gICAgLy8gICAgIHRoaXMuYXV0b1NhdmVSZXZpc2lvbiA9IHNldEludGVydmFsKGZ1bmN0aW9uICgpIHtcclxuICAgIC8vICAgICAgICAgaWYgKE9iamVjdC5rZXlzKCR0aGlzLnJldmlzaW9uKS5sZW5ndGgpIHtcclxuICAgIC8vICAgICAgICAgICAgIGxldCBjdXJyZW50UGFnZSA9ICR0aGlzLnNpZGViYXIuZmluZCgnLnZpLXdiZS1wYWdpbmF0aW9uIGEuaXRlbS5hY3RpdmUnKS5kYXRhKCdwYWdlJykgfHwgMTtcclxuICAgIC8vICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgLy8gICAgICAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAnYXV0b19zYXZlX3JldmlzaW9uJywgZGF0YTogJHRoaXMucmV2aXNpb24sIHBhZ2U6IGN1cnJlbnRQYWdlIHx8IDF9LFxyXG4gICAgLy8gICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAvLyAgICAgICAgICAgICAgICAgICAgIGlmIChyZXMuc3VjY2Vzcykge1xyXG4gICAgLy8gICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5kYXRhLnBhZ2VzKSBBdHRyaWJ1dGVzLmhpc3RvcnlQYWdlcyA9IHJlcy5kYXRhLnBhZ2VzO1xyXG4gICAgLy8gICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5kYXRhLnVwZGF0ZVBhZ2UpICR0aGlzLmhpc3RvcnlCb2R5VGFibGUuaHRtbChyZXMuZGF0YS51cGRhdGVQYWdlKTtcclxuICAgIC8vICAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLnJldmlzaW9uID0ge307XHJcbiAgICAvLyAgICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy5wYWdpbmF0aW9uKGN1cnJlbnRQYWdlKTtcclxuICAgIC8vICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgLy8gICAgICAgICAgICAgICAgIH1cclxuICAgIC8vICAgICAgICAgICAgIH0pO1xyXG4gICAgLy8gICAgICAgICB9XHJcbiAgICAvL1xyXG4gICAgLy8gICAgIH0sIGF1dG9TYXZlVGltZSAqIDEwMDApXHJcbiAgICAvLyB9XHJcbn07XHJcbiIsImNvbnN0IFRlbXBsYXRlcyA9IHtcclxuICAgIG1vZGFsKGRhdGEgPSB7fSkge1xyXG4gICAgICAgIGxldCB7aGVhZGVyID0gJycsIGNvbnRlbnQgPSAnJywgYWN0aW9uc0h0bWwgPSAnJ30gPSBkYXRhO1xyXG4gICAgICAgIHJldHVybiBgPGRpdiBjbGFzcz1cInZpLXdiZS1tb2RhbC1jb250YWluZXJcIj5cclxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwidmktd2JlLW1vZGFsLW1haW4gdmktdWkgZm9ybSBzbWFsbFwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8aSBjbGFzcz1cImNsb3NlIGljb25cIj48L2k+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJ2aS13YmUtbW9kYWwtd3JhcHBlclwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGgzIGNsYXNzPVwiaGVhZGVyXCI+JHtoZWFkZXJ9PC9oMz5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJjb250ZW50XCI+JHtjb250ZW50fTwvZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImFjdGlvbnNcIj4ke2FjdGlvbnNIdG1sfTwvZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgIDwvZGl2PmA7XHJcbiAgICB9LFxyXG5cclxuICAgIGRlZmF1bHRBdHRyaWJ1dGVzKGRhdGEgPSB7fSkge1xyXG4gICAgICAgIGxldCB7aHRtbH0gPSBkYXRhO1xyXG4gICAgICAgIHJldHVybiBgPHRhYmxlIGNsYXNzPVwidmktdWkgY2VsbGVkIHRhYmxlXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPHRoZWFkPlxyXG4gICAgICAgICAgICAgICAgICAgIDx0cj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgPHRoPk5hbWU8L3RoPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICA8dGg+QXR0cmlidXRlPC90aD5cclxuICAgICAgICAgICAgICAgICAgICA8L3RyPlxyXG4gICAgICAgICAgICAgICAgICAgIDwvdGhlYWQ+XHJcbiAgICAgICAgICAgICAgICAgICAgPHRib2R5PlxyXG4gICAgICAgICAgICAgICAgICAgICR7aHRtbH1cclxuICAgICAgICAgICAgICAgICAgICA8L3Rib2R5PlxyXG4gICAgICAgICAgICAgICAgPC90YWJsZT5gO1xyXG4gICAgfSxcclxuXHJcbn07XHJcbmV4cG9ydCBkZWZhdWx0IFRlbXBsYXRlczsiLCJpbXBvcnQgX2YgZnJvbSAnLi9mdW5jdGlvbnMnO1xyXG5pbXBvcnQge1BvcHVwfSBmcm9tIFwiLi9tb2RhbC1wb3B1cFwiO1xyXG5cclxuY29uc3QgJCA9IGpRdWVyeTtcclxuXHJcbmV4cG9ydCBkZWZhdWx0IGNsYXNzIFRleHRNdWx0aUNlbGxzRWRpdCB7XHJcbiAgICBjb25zdHJ1Y3RvcihvYmosIHgsIHksIGUsIHdvcmRXcmFwKSB7XHJcbiAgICAgICAgdGhpcy5fZGF0YSA9IHt9O1xyXG4gICAgICAgIHRoaXMuX2RhdGEuamV4Y2VsID0gb2JqO1xyXG4gICAgICAgIHRoaXMuX2RhdGEueCA9IHBhcnNlSW50KHgpO1xyXG4gICAgICAgIHRoaXMuX2RhdGEueSA9IHBhcnNlSW50KHkpO1xyXG4gICAgICAgIHRoaXMuX3dvcmRXcmFwID0gd29yZFdyYXA7XHJcbiAgICAgICAgdGhpcy5ydW4oKTtcclxuICAgIH1cclxuXHJcbiAgICBnZXQoaWQpIHtcclxuICAgICAgICByZXR1cm4gdGhpcy5fZGF0YVtpZF0gfHwgJyc7XHJcbiAgICB9XHJcblxyXG4gICAgcnVuKCkge1xyXG4gICAgICAgIGxldCBmb3JtdWxhSHRtbCA9IHRoaXMuY29udGVudCgpO1xyXG4gICAgICAgIGxldCBjZWxsID0gJChgdGRbZGF0YS14PSR7dGhpcy5nZXQoJ3gnKSB8fCAwfV1bZGF0YS15PSR7dGhpcy5nZXQoJ3knKSB8fCAwfV1gKTtcclxuICAgICAgICBuZXcgUG9wdXAoZm9ybXVsYUh0bWwsIGNlbGwpO1xyXG4gICAgICAgIGZvcm11bGFIdG1sLm9uKCdjbGljaycsICcudmktd2JlLWFwcGx5LWZvcm11bGEnLCB0aGlzLmFwcGx5Rm9ybXVsYS5iaW5kKHRoaXMpKTtcclxuICAgICAgICAvLyBmb3JtdWxhSHRtbC5vbignY2hhbmdlJywgJy52aS13YmUtdGV4dC1pbnB1dCcsIHRoaXMuYXBwbHlGb3JtdWxhLmJpbmQodGhpcykpO1xyXG4gICAgfVxyXG5cclxuICAgIGNvbnRlbnQoKSB7XHJcbiAgICAgICAgbGV0IGlucHV0ID0gdGhpcy5fd29yZFdyYXAgPyBgPHRleHRhcmVhIGNsYXNzPVwidmktd2JlLXRleHQtaW5wdXRcIiByb3dzPVwiM1wiPjwvdGV4dGFyZWE+YCA6IGA8aW5wdXQgdHlwZT1cInRleHRcIiBwbGFjZWhvbGRlcj1cIiR7X2YudGV4dCgnQ29udGVudCcpfVwiIGNsYXNzPVwidmktd2JlLXRleHQtaW5wdXRcIj5gO1xyXG4gICAgICAgIHJldHVybiAkKGA8ZGl2IGNsYXNzPVwidmktd2JlLWZvcm11bGEtY29udGFpbmVyXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImZpZWxkXCI+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICR7aW5wdXR9XHJcbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XHJcbiAgICAgICAgICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJ2aS11aSBidXR0b24gbWluaSB2aS13YmUtYXBwbHktZm9ybXVsYVwiPiR7X2YudGV4dCgnU2F2ZScpfTwvYnV0dG9uPlxyXG4gICAgICAgICAgICAgICAgPC9kaXY+YCk7XHJcbiAgICB9XHJcblxyXG4gICAgYXBwbHlGb3JtdWxhKGUpIHtcclxuICAgICAgICBsZXQgZm9ybSA9ICQoZS50YXJnZXQpLmNsb3Nlc3QoJy52aS13YmUtZm9ybXVsYS1jb250YWluZXInKSxcclxuICAgICAgICAgICAgdmFsdWUgPSBmb3JtLmZpbmQoJy52aS13YmUtdGV4dC1pbnB1dCcpLnZhbCgpLFxyXG4gICAgICAgICAgICBleGNlbE9iaiA9IHRoaXMuZ2V0KCdqZXhjZWwnKTtcclxuXHJcbiAgICAgICAgbGV0IGJyZWFrQ29udHJvbCA9IGZhbHNlLCByZWNvcmRzID0gW107XHJcbiAgICAgICAgbGV0IGggPSBleGNlbE9iai5zZWxlY3RlZENvbnRhaW5lcjtcclxuICAgICAgICBsZXQgc3RhcnQgPSBoWzFdLCBlbmQgPSBoWzNdLCB4ID0gaFswXTtcclxuXHJcbiAgICAgICAgZm9yIChsZXQgeSA9IHN0YXJ0OyB5IDw9IGVuZDsgeSsrKSB7XHJcbiAgICAgICAgICAgIGlmIChleGNlbE9iai5yZWNvcmRzW3ldW3hdICYmICFleGNlbE9iai5yZWNvcmRzW3ldW3hdLmNsYXNzTGlzdC5jb250YWlucygncmVhZG9ubHknKSAmJiBleGNlbE9iai5yZWNvcmRzW3ldW3hdLnN0eWxlLmRpc3BsYXkgIT09ICdub25lJyAmJiBicmVha0NvbnRyb2wgPT09IGZhbHNlKSB7XHJcbiAgICAgICAgICAgICAgICByZWNvcmRzLnB1c2goZXhjZWxPYmoudXBkYXRlQ2VsbCh4LCB5LCB2YWx1ZSkpO1xyXG4gICAgICAgICAgICAgICAgZXhjZWxPYmoudXBkYXRlRm9ybXVsYUNoYWluKHgsIHksIHJlY29yZHMpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICAvLyBVcGRhdGUgaGlzdG9yeVxyXG4gICAgICAgIGV4Y2VsT2JqLnNldEhpc3Rvcnkoe1xyXG4gICAgICAgICAgICBhY3Rpb246ICdzZXRWYWx1ZScsXHJcbiAgICAgICAgICAgIHJlY29yZHM6IHJlY29yZHMsXHJcbiAgICAgICAgICAgIHNlbGVjdGlvbjogZXhjZWxPYmouc2VsZWN0ZWRDZWxsLFxyXG4gICAgICAgIH0pO1xyXG5cclxuICAgICAgICAvLyBVcGRhdGUgdGFibGUgd2l0aCBjdXN0b20gY29uZmlndXJhdGlvbiBpZiBhcHBsaWNhYmxlXHJcbiAgICAgICAgZXhjZWxPYmoudXBkYXRlVGFibGUoKTtcclxuICAgIH1cclxuXHJcbn0iLCIvLyBUaGUgbW9kdWxlIGNhY2hlXG52YXIgX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fID0ge307XG5cbi8vIFRoZSByZXF1aXJlIGZ1bmN0aW9uXG5mdW5jdGlvbiBfX3dlYnBhY2tfcmVxdWlyZV9fKG1vZHVsZUlkKSB7XG5cdC8vIENoZWNrIGlmIG1vZHVsZSBpcyBpbiBjYWNoZVxuXHR2YXIgY2FjaGVkTW9kdWxlID0gX193ZWJwYWNrX21vZHVsZV9jYWNoZV9fW21vZHVsZUlkXTtcblx0aWYgKGNhY2hlZE1vZHVsZSAhPT0gdW5kZWZpbmVkKSB7XG5cdFx0cmV0dXJuIGNhY2hlZE1vZHVsZS5leHBvcnRzO1xuXHR9XG5cdC8vIENyZWF0ZSBhIG5ldyBtb2R1bGUgKGFuZCBwdXQgaXQgaW50byB0aGUgY2FjaGUpXG5cdHZhciBtb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdID0ge1xuXHRcdC8vIG5vIG1vZHVsZS5pZCBuZWVkZWRcblx0XHQvLyBubyBtb2R1bGUubG9hZGVkIG5lZWRlZFxuXHRcdGV4cG9ydHM6IHt9XG5cdH07XG5cblx0Ly8gRXhlY3V0ZSB0aGUgbW9kdWxlIGZ1bmN0aW9uXG5cdF9fd2VicGFja19tb2R1bGVzX19bbW9kdWxlSWRdKG1vZHVsZSwgbW9kdWxlLmV4cG9ydHMsIF9fd2VicGFja19yZXF1aXJlX18pO1xuXG5cdC8vIFJldHVybiB0aGUgZXhwb3J0cyBvZiB0aGUgbW9kdWxlXG5cdHJldHVybiBtb2R1bGUuZXhwb3J0cztcbn1cblxuIiwiLy8gZGVmaW5lIGdldHRlciBmdW5jdGlvbnMgZm9yIGhhcm1vbnkgZXhwb3J0c1xuX193ZWJwYWNrX3JlcXVpcmVfXy5kID0gKGV4cG9ydHMsIGRlZmluaXRpb24pID0+IHtcblx0Zm9yKHZhciBrZXkgaW4gZGVmaW5pdGlvbikge1xuXHRcdGlmKF9fd2VicGFja19yZXF1aXJlX18ubyhkZWZpbml0aW9uLCBrZXkpICYmICFfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZXhwb3J0cywga2V5KSkge1xuXHRcdFx0T2JqZWN0LmRlZmluZVByb3BlcnR5KGV4cG9ydHMsIGtleSwgeyBlbnVtZXJhYmxlOiB0cnVlLCBnZXQ6IGRlZmluaXRpb25ba2V5XSB9KTtcblx0XHR9XG5cdH1cbn07IiwiX193ZWJwYWNrX3JlcXVpcmVfXy5vID0gKG9iaiwgcHJvcCkgPT4gKE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbChvYmosIHByb3ApKSIsIi8vIGRlZmluZSBfX2VzTW9kdWxlIG9uIGV4cG9ydHNcbl9fd2VicGFja19yZXF1aXJlX18uciA9IChleHBvcnRzKSA9PiB7XG5cdGlmKHR5cGVvZiBTeW1ib2wgIT09ICd1bmRlZmluZWQnICYmIFN5bWJvbC50b1N0cmluZ1RhZykge1xuXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBTeW1ib2wudG9TdHJpbmdUYWcsIHsgdmFsdWU6ICdNb2R1bGUnIH0pO1xuXHR9XG5cdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG59OyIsImltcG9ydCBfZiBmcm9tIFwiLi9mdW5jdGlvbnNcIjtcclxuaW1wb3J0IHtBdHRyaWJ1dGVzfSBmcm9tIFwiLi9hdHRyaWJ1dGVzXCI7XHJcbmltcG9ydCB7Q2FsY3VsYXRvciwgQ2FsY3VsYXRvckJhc2VPblJlZ3VsYXJQcmljZX0gZnJvbSBcIi4vY2FsY3VsYXRvclwiO1xyXG5pbXBvcnQge1NpZGViYXJ9IGZyb20gXCIuL3NpZGViYXJcIjtcclxuaW1wb3J0IEZpbmRBbmRSZXBsYWNlIGZyb20gXCIuL2ZpbmQtYW5kLXJlcGxhY2VcIjtcclxuaW1wb3J0IFRleHRNdWx0aUNlbGxzRWRpdCBmcm9tICcuL3RleHQtbXVsdGktY2VsbHMtZWRpdCc7XHJcbmltcG9ydCB7UG9wdXB9IGZyb20gXCIuL21vZGFsLXBvcHVwXCI7XHJcbmltcG9ydCBGaW5kQW5kUmVwbGFjZVRhZ3MgZnJvbSBcIi4vZmluZC1hbmQtcmVwbGFjZS10YWdzXCI7XHJcbmltcG9ydCBGaW5kQW5kUmVwbGFjZU9wdGlvbnMgZnJvbSBcIi4vZmluZC1hbmQtcmVwbGFjZS1vcHRpb25zXCI7XHJcbmltcG9ydCBBZGRJbWFnZVRvTXVsdGlHYWxsZXJ5IGZyb20gXCIuL2FkZC1pbWFnZS10by1tdWx0aS1nYWxsZXJ5XCI7XHJcbmltcG9ydCBNdWx0aXBsZVByb2R1Y3RBdHRyaWJ1dGVzIGZyb20gXCIuL211bHRpcGxlLXByb2R1Y3QtYXR0cmlidXRlc1wiO1xyXG5cclxualF1ZXJ5KGRvY3VtZW50KS5yZWFkeShmdW5jdGlvbiAoJCkge1xyXG5cclxuICAgIGNsYXNzIEJ1bGtFZGl0IHtcclxuICAgICAgICBjb25zdHJ1Y3RvcigpIHtcclxuICAgICAgICAgICAgdGhpcy5zaWRlYmFyID0gU2lkZWJhci5pbml0KCk7XHJcbiAgICAgICAgICAgIHRoaXMuY29tcGFyZSA9IFtdO1xyXG4gICAgICAgICAgICB0aGlzLnRyYXNoID0gW107XHJcbiAgICAgICAgICAgIHRoaXMudW5UcmFzaCA9IFtdO1xyXG4gICAgICAgICAgICB0aGlzLnJldmlzaW9uID0ge307XHJcbiAgICAgICAgICAgIHRoaXMuaXNBZGRpbmcgPSBmYWxzZTtcclxuXHJcbiAgICAgICAgICAgIHRoaXMuZWRpdG9yID0gJCgnI3ZpLXdiZS1jb250YWluZXInKTtcclxuICAgICAgICAgICAgdGhpcy5tZW51YmFyID0gJCgnI3ZpLXdiZS1tZW51LWJhcicpO1xyXG5cclxuICAgICAgICAgICAgdGhpcy5tZW51YmFyLm9uKCdjbGljaycsICcudmktd2JlLW9wZW4tc2lkZWJhcicsIHRoaXMub3Blbk1lbnUuYmluZCh0aGlzKSk7XHJcbiAgICAgICAgICAgIHRoaXMubWVudWJhci5vbignY2xpY2snLCAnYS5pdGVtOm5vdCgudmktd2JlLW9wZW4tc2lkZWJhciknLCB0aGlzLmNsb3NlTWVudS5iaW5kKHRoaXMpKTtcclxuXHJcbiAgICAgICAgICAgIHRoaXMubWVudWJhci5vbignY2xpY2snLCAnLnZpLXdiZS1uZXctcHJvZHVjdHMnLCB0aGlzLmFkZE5ld1Byb2R1Y3QuYmluZCh0aGlzKSk7XHJcbiAgICAgICAgICAgIHRoaXMubWVudWJhci5vbignY2xpY2snLCAnLnZpLXdiZS1uZXctY291cG9ucycsIHRoaXMuYWRkTmV3Q291cG9uLmJpbmQodGhpcykpO1xyXG4gICAgICAgICAgICB0aGlzLm1lbnViYXIub24oJ2NsaWNrJywgJy52aS13YmUtbmV3LW9yZGVycycsIHRoaXMuYWRkTmV3T3JkZXIuYmluZCh0aGlzKSk7XHJcblxyXG4gICAgICAgICAgICB0aGlzLm1lbnViYXIub24oJ2NsaWNrJywgJy52aS13YmUtZnVsbC1zY3JlZW4tYnRuJywgdGhpcy50b2dnbGVGdWxsU2NyZWVuLmJpbmQodGhpcykpO1xyXG4gICAgICAgICAgICB0aGlzLm1lbnViYXIub24oJ2NsaWNrJywgJy52aS13YmUtc2F2ZS1idXR0b24nLCB0aGlzLnNhdmUuYmluZCh0aGlzKSk7XHJcbiAgICAgICAgICAgIHRoaXMubWVudWJhci5vbignY2xpY2snLCAnLnZpLXdiZS1wYWdpbmF0aW9uIGEuaXRlbScsIHRoaXMuY2hhbmdlUGFnZS5iaW5kKHRoaXMpKTtcclxuICAgICAgICAgICAgdGhpcy5tZW51YmFyLm9uKCdjbGljaycsICcudmktd2JlLWdldC1wcm9kdWN0JywgdGhpcy5yZWxvYWRDdXJyZW50UGFnZS5iaW5kKHRoaXMpKTtcclxuICAgICAgICAgICAgdGhpcy5tZW51YmFyLm9uKCdjaGFuZ2UnLCAnLnZpLXdiZS1nby10by1wYWdlJywgdGhpcy5jaGFuZ2VQYWdlQnlJbnB1dC5iaW5kKHRoaXMpKTtcclxuXHJcbiAgICAgICAgICAgIHRoaXMuZWRpdG9yLm9uKCdjZWxsb25jaGFuZ2UnLCAndHInLCB0aGlzLmNlbGxPbkNoYW5nZS5iaW5kKHRoaXMpKTtcclxuICAgICAgICAgICAgdGhpcy5lZGl0b3Iub24oJ2NsaWNrJywgJy5qZXhjZWxfY29udGVudCcsIHRoaXMucmVtb3ZlRXhpc3RpbmdFZGl0b3IuYmluZCh0aGlzKSk7XHJcbiAgICAgICAgICAgIHRoaXMuZWRpdG9yLm9uKCdkYmxjbGljaycsIHRoaXMucmVtb3ZlQ29udGV4dFBvcHVwKTtcclxuXHJcbiAgICAgICAgICAgIHRoaXMuc2lkZWJhci5vbignYWZ0ZXJBZGRGaWx0ZXInLCB0aGlzLmFmdGVyQWRkRmlsdGVyLmJpbmQodGhpcykpO1xyXG4gICAgICAgICAgICB0aGlzLnNpZGViYXIub24oJ2FmdGVyU2F2ZVNldHRpbmdzJywgdGhpcy5hZnRlclNhdmVTZXR0aW5ncy5iaW5kKHRoaXMpKTtcclxuICAgICAgICAgICAgdGhpcy5zaWRlYmFyLm9uKCdjbGljaycsICcudmktd2JlLWNsb3NlLXNpZGViYXInLCB0aGlzLmNsb3NlTWVudS5iaW5kKHRoaXMpKTtcclxuXHJcbiAgICAgICAgICAgIHRoaXMuaW5pdCgpO1xyXG5cclxuICAgICAgICAgICAgJChkb2N1bWVudCkub24oJ2tleWRvd24nLCB0aGlzLmtleURvd25Db250cm9sLmJpbmQodGhpcykpO1xyXG4gICAgICAgICAgICAkKGRvY3VtZW50KS5vbigna2V5dXAnLCB0aGlzLmtleVVwQ29udHJvbC5iaW5kKHRoaXMpKTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHJlbW92ZUV4aXN0aW5nRWRpdG9yKGUpIHtcclxuICAgICAgICAgICAgaWYgKGUudGFyZ2V0ID09PSBlLmN1cnJlbnRUYXJnZXQpIHtcclxuICAgICAgICAgICAgICAgIGlmICh0aGlzLldvcmtCb29rICYmIHRoaXMuV29ya0Jvb2suZWRpdGlvbikge1xyXG4gICAgICAgICAgICAgICAgICAgIHRoaXMuV29ya0Jvb2suY2xvc2VFZGl0b3IodGhpcy5Xb3JrQm9vay5lZGl0aW9uWzBdLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAga2V5RG93bkNvbnRyb2woZSkge1xyXG4gICAgICAgICAgICBpZiAoKGUuY3RybEtleSB8fCBlLm1ldGFLZXkpICYmICFlLnNoaWZ0S2V5KSB7XHJcbiAgICAgICAgICAgICAgICBpZiAoZS53aGljaCA9PT0gODMpIHtcclxuICAgICAgICAgICAgICAgICAgICBlLnByZXZlbnREZWZhdWx0KCk7XHJcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5zYXZlKCk7XHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIHN3aXRjaCAoZS53aGljaCkge1xyXG4gICAgICAgICAgICAgICAgY2FzZSAyNzpcclxuICAgICAgICAgICAgICAgICAgICB0aGlzLnNpZGViYXIucmVtb3ZlQ2xhc3MoJ3ZpLXdiZS1vcGVuJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGtleVVwQ29udHJvbChlKSB7XHJcbiAgICAgICAgICAgIGlmIChlLnRhcmdldCAmJiAhZS50YXJnZXQuZ2V0QXR0cmlidXRlKCdyZWFkb25seScpKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgZGVjaW1hbCA9IGUudGFyZ2V0LmdldEF0dHJpYnV0ZSgnZGF0YS1jdXJyZW5jeScpO1xyXG4gICAgICAgICAgICAgICAgaWYgKGRlY2ltYWwpIHtcclxuICAgICAgICAgICAgICAgICAgICBsZXQgY3VycmVudFZhbHVlID0gZS50YXJnZXQudmFsdWU7XHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKGN1cnJlbnRWYWx1ZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgZGVjaW1hbEV4aXN0ID0gY3VycmVudFZhbHVlLmluZGV4T2YoZGVjaW1hbCk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoZGVjaW1hbEV4aXN0IDwgMSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHZhbHVlID0gY3VycmVudFZhbHVlLm1hdGNoKC9cXGQvZyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBlLnRhcmdldC52YWx1ZSA9IHZhbHVlID8gdmFsdWUuam9pbignJykgOiAnJztcclxuICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBzcGxpdCA9IGN1cnJlbnRWYWx1ZS5zcGxpdChkZWNpbWFsKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBpbnRlZ2VyLCBmcmFjdGlvbiA9ICcnO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaW50ZWdlciA9IHNwbGl0WzBdLm1hdGNoKC9bXFxkXS9nKS5qb2luKCcnKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoc3BsaXRbMV0pIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmcmFjdGlvbiA9IHNwbGl0WzFdLm1hdGNoKC9bXFxkXS9nKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmcmFjdGlvbiA9IGZyYWN0aW9uID8gZnJhY3Rpb24uam9pbignJykgOiAnJztcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBlLnRhcmdldC52YWx1ZSA9IGZyYWN0aW9uID8gYCR7aW50ZWdlcn0ke2RlY2ltYWx9JHtmcmFjdGlvbn1gIDogYCR7aW50ZWdlcn0ke2RlY2ltYWx9YDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgcmVtb3ZlQ29udGV4dFBvcHVwKCkge1xyXG4gICAgICAgICAgICAkKCcudmktd2JlLWNvbnRleHQtcG9wdXAnKS5yZW1vdmVDbGFzcygndmktd2JlLXBvcHVwLWFjdGl2ZScpXHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBpbml0KCkge1xyXG4gICAgICAgICAgICBpZiAod2JlUGFyYW1zLmNvbHVtbnMpIEF0dHJpYnV0ZXMuc2V0Q29sdW1ucyh3YmVQYXJhbXMuY29sdW1ucyk7XHJcbiAgICAgICAgICAgIHRoaXMucGFnaW5hdGlvbigxLCAxKTtcclxuICAgICAgICAgICAgdGhpcy53b3JrQm9va0luaXQoKTtcclxuICAgICAgICAgICAgdGhpcy5sb2FkUHJvZHVjdHMoKTtcclxuICAgICAgICAgICAgX2Yuc2V0SmV4Y2VsKHRoaXMuV29ya0Jvb2spO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgY2VsbE9uQ2hhbmdlKGUsIGRhdGEpIHtcclxuICAgICAgICAgICAgbGV0IHtjb2wgPSAnJ30gPSBkYXRhO1xyXG5cclxuICAgICAgICAgICAgaWYgKCFjb2wpIHJldHVybjtcclxuXHJcbiAgICAgICAgICAgIGxldCB0eXBlID0gQXR0cmlidXRlcy5pZE1hcHBpbmdbY29sXTtcclxuICAgICAgICAgICAgbGV0IHRoaXNSb3cgPSAkKGUudGFyZ2V0KTtcclxuXHJcbiAgICAgICAgICAgIHN3aXRjaCAodHlwZSkge1xyXG4gICAgICAgICAgICAgICAgY2FzZSAncHJvZHVjdF90eXBlJzpcclxuICAgICAgICAgICAgICAgICAgICB0aGlzUm93LmZpbmQoJ3RkJykuZWFjaChmdW5jdGlvbiAoaSwgZWwpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHggPSAkKGVsKS5kYXRhKCd4Jyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmICh4ICYmIHggIT09IDAgJiYgeCAhPT0gMSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJChlbCkucmVtb3ZlQ2xhc3MoJ3JlYWRvbmx5Jyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgbGV0IGRlcGVuZEFyciA9IEF0dHJpYnV0ZXMuY2VsbERlcGVuZFR5cGVbZGF0YS52YWx1ZV07XHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKEFycmF5LmlzQXJyYXkoZGVwZW5kQXJyKSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBkZXBlbmRBcnIuZm9yRWFjaChmdW5jdGlvbiAoZWwpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBwb3MgPSBBdHRyaWJ1dGVzLmlkTWFwcGluZ0ZsaXBbZWxdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpc1Jvdy5maW5kKGB0ZFtkYXRhLXg9JyR7cG9zfSddYCkuYWRkQ2xhc3MoJ3JlYWRvbmx5Jyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcblxyXG4gICAgICAgICAgICAgICAgY2FzZSAncG9zdF9kYXRlJzpcclxuICAgICAgICAgICAgICAgICAgICBsZXQgdmFsdWUgPSBkYXRhLnZhbHVlLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICB4ID0gX2YuZ2V0Q29sRnJvbUNvbHVtblR5cGUoJ3N0YXR1cycpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBjZWxsID0gdGhpc1Jvdy5maW5kKGB0ZFtkYXRhLXg9JyR7eH0nXWApLmdldCgwKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgdGltZSA9IChuZXcgRGF0ZSh2YWx1ZSkpLmdldFRpbWUoKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgbm93ID0gRGF0ZS5ub3coKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgc3RhdHVzID0gdGltZSA+IG5vdyA/ICdmdXR1cmUnIDogJ3B1Ymxpc2gnO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICB0aGlzLldvcmtCb29rLnNldFZhbHVlKGNlbGwsIHN0YXR1cyk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGJyZWFrO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICB3b3JrQm9va0luaXQoKSB7XHJcbiAgICAgICAgICAgIGxldCAkdGhpcyA9IHRoaXMsXHJcbiAgICAgICAgICAgICAgICBjb3VudENvbCA9IDAsXHJcbiAgICAgICAgICAgICAgICBkZWxldGVTZWxlY3RlZFJvd3MgPSBfZi50ZXh0KCdEZWxldGUgcm93cyB3aXRoIHNlbGVjdGVkIGNlbGxzJyksXHJcbiAgICAgICAgICAgICAgICBvbmNyZWF0ZXJvdyA9IG51bGwsXHJcbiAgICAgICAgICAgICAgICBjb250ZXh0TWVudUl0ZW1zLFxyXG4gICAgICAgICAgICAgICAgb25zZWxlY3Rpb24gPSBudWxsO1xyXG5cclxuICAgICAgICAgICAgZnVuY3Rpb24gc2V0VmFsdWVUb0NlbGwob2JqLCB2YWx1ZSkge1xyXG4gICAgICAgICAgICAgICAgbGV0IGJyZWFrQ29udHJvbCA9IGZhbHNlLCByZWNvcmRzID0gW10sIGggPSBvYmouc2VsZWN0ZWRDb250YWluZXIsIHN0YXJ0ID0gaFsxXSwgZW5kID0gaFszXSwgeCA9IGhbMF07XHJcblxyXG4gICAgICAgICAgICAgICAgZm9yIChsZXQgeSA9IHN0YXJ0OyB5IDw9IGVuZDsgeSsrKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKG9iai5yZWNvcmRzW3ldW3hdICYmICFvYmoucmVjb3Jkc1t5XVt4XS5jbGFzc0xpc3QuY29udGFpbnMoJ3JlYWRvbmx5JykgJiYgb2JqLnJlY29yZHNbeV1beF0uc3R5bGUuZGlzcGxheSAhPT0gJ25vbmUnICYmIGJyZWFrQ29udHJvbCA9PT0gZmFsc2UpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgcmVjb3Jkcy5wdXNoKG9iai51cGRhdGVDZWxsKHgsIHksIHZhbHVlKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIG9iai51cGRhdGVGb3JtdWxhQ2hhaW4oeCwgeSwgcmVjb3Jkcyk7XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgIG9iai5zZXRIaXN0b3J5KHthY3Rpb246ICdzZXRWYWx1ZScsIHJlY29yZHM6IHJlY29yZHMsIHNlbGVjdGlvbjogb2JqLnNlbGVjdGVkQ2VsbH0pO1xyXG4gICAgICAgICAgICAgICAgb2JqLnVwZGF0ZVRhYmxlKCk7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIHN3aXRjaCAoQXR0cmlidXRlcy5lZGl0VHlwZSkge1xyXG4gICAgICAgICAgICAgICAgY2FzZSAncHJvZHVjdHMnOlxyXG4gICAgICAgICAgICAgICAgICAgIGRlbGV0ZVNlbGVjdGVkUm93cyA9IGAke19mLnRleHQoJ0RlbGV0ZSByb3dzIHdpdGggc2VsZWN0ZWQgY2VsbHMnKX0gXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9XCJ2aS13YmUtY29udGV4dC1tZW51LW5vdGVcIj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgKCR7X2YudGV4dCgnVmFyaWF0aW9ucyBjYW5ub3QgcmV2ZXJ0IGFmdGVyIHNhdmUnKX0pXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9zcGFuPmA7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIG9uY3JlYXRlcm93ID0gZnVuY3Rpb24gKHJvdywgaikge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgcHJvZHVjdFR5cGUgPSBfZi5nZXRQcm9kdWN0VHlwZUZyb21ZKGopO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgZGVwZW5kQXJyID0gQXR0cmlidXRlcy5jZWxsRGVwZW5kVHlwZVtwcm9kdWN0VHlwZV07XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoQXJyYXkuaXNBcnJheShkZXBlbmRBcnIpKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBkZXBlbmRBcnIuZm9yRWFjaChmdW5jdGlvbiAoZWwpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgcG9zID0gQXR0cmlidXRlcy5pZE1hcHBpbmdGbGlwW2VsXTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkKHJvdykuZmluZChgdGRbZGF0YS14PScke3Bvc30nXWApLmFkZENsYXNzKCdyZWFkb25seScpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9O1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBvbnNlbGVjdGlvbiA9IGZ1bmN0aW9uIChlbCwgeDEsIHkxLCB4MiwgeTIsIG9yaWdpbikge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoeDEgPT09IHgyICYmIHkxID09PSB5Mikge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGNlbGwgPSB0aGlzLmdldENlbGxGcm9tQ29vcmRzKHgxLCB5MSksXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2hpbGQgPSAkKGNlbGwpLmNoaWxkcmVuKCk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGNoaWxkLmxlbmd0aCAmJiBjaGlsZC5oYXNDbGFzcygndmktd2JlLWdhbGxlcnktaGFzLWl0ZW0nKSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBpZHMgPSB0aGlzLm9wdGlvbnMuZGF0YVt5MV1beDFdLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpbWFnZXMgPSAnJztcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGlkcy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgaWQgb2YgaWRzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgc3JjID0gQXR0cmlidXRlcy5pbWdTdG9yYWdlW2lkXTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGltYWdlcyArPSBgPGxpIGNsYXNzPVwidmktd2JlLWdhbGxlcnktaW1hZ2VcIj48aW1nIHNyYz1cIiR7c3JjfVwiPjwvbGk+YDtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmV3IFBvcHVwKGA8dWwgY2xhc3M9XCJ2aS13YmUtZ2FsbGVyeS1pbWFnZXNcIj4ke2ltYWdlc308L3VsPmAsICQoY2VsbCkpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgfTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgY29udGV4dE1lbnVJdGVtcyA9IGZ1bmN0aW9uIChpdGVtcywgb2JqLCB4LCB5LCBlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLnJlbW92ZUNvbnRleHRQb3B1cCgpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGNlbGxzID0gb2JqLnNlbGVjdGVkQ29udGFpbmVyO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB4ID0gcGFyc2VJbnQoeCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHkgPSBwYXJzZUludCh5KTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChjZWxsc1swXSA9PT0gY2VsbHNbMl0gJiYgeCAhPT0gbnVsbCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgc3dpdGNoIChvYmoub3B0aW9ucy5jb2x1bW5zW3hdLnR5cGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjYXNlICdjaGVja2JveCc6XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0NoZWNrJyksXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZXRWYWx1ZVRvQ2VsbChvYmosIHRydWUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ1VuY2hlY2snKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uY2xpY2soZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNldFZhbHVlVG9DZWxsKG9iaiwgZmFsc2UpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNhc2UgJ251bWJlcic6XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0NhbGN1bGF0b3InKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uY2xpY2soZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ldyBDYWxjdWxhdG9yKG9iaiwgeCwgeSwgZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHggPiAxICYmIG9iai5vcHRpb25zLmNvbHVtbnNbeF0uaWQgPT09ICdzYWxlX3ByaWNlJyAmJiBvYmoub3B0aW9ucy5jb2x1bW5zW3ggLSAxXS5pZCA9PT0gJ3JlZ3VsYXJfcHJpY2UnKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aXRsZTogX2YudGV4dCgnQ2FsY3VsYXRvciBiYXNlIG9uIFJlZ3VsYXIgcHJpY2UnKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmV3IENhbGN1bGF0b3JCYXNlT25SZWd1bGFyUHJpY2Uob2JqLCB4LCB5LCBlKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNhc2UgJ3RleHQnOlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpdGxlOiBfZi50ZXh0KCdFZGl0IG11bHRpcGxlIGNlbGxzJyksXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBuZXcgVGV4dE11bHRpQ2VsbHNFZGl0KG9iaiwgeCwgeSwgZSwgb2JqLm9wdGlvbnMuY29sdW1uc1t4XS53b3JkV3JhcCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaXRlbXMucHVzaCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aXRsZTogX2YudGV4dCgnRmluZCBhbmQgUmVwbGFjZScpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25jbGljayhlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmV3IEZpbmRBbmRSZXBsYWNlKG9iaiwgeCwgeSwgZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgY2FzZSAnY2FsZW5kYXInOlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgY2VsbCA9ICQoYHRkW2RhdGEteD0ke3h9XVtkYXRhLXk9JHt5fV1gKS5nZXQoMCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICghJChjZWxsKS5oYXNDbGFzcygncmVhZG9ubHknKSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaXRlbXMucHVzaCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ09wZW4gZGF0ZSBwaWNrZXInKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgdmFsdWUgPSBvYmoub3B0aW9ucy5kYXRhW3ldW3hdO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFyIGVkaXRvciA9IF9mLmNyZWF0ZUVkaXRvcihjZWxsLCAnaW5wdXQnLCAnJywgZmFsc2UpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlZGl0b3IudmFsdWUgPSB2YWx1ZTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZWRpdG9yLnN0eWxlLmxlZnQgPSAndW5zZXQnO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGggPSBvYmouc2VsZWN0ZWRDb250YWluZXI7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBzdGFydCA9IGhbMV0sIGVuZCA9IGhbM107XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAob2JqLm9wdGlvbnMudGFibGVPdmVyZmxvdyA9PSB0cnVlIHx8IG9iai5vcHRpb25zLmZ1bGxzY3JlZW4gPT0gdHJ1ZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JqLm9wdGlvbnMuY29sdW1uc1t4XS5vcHRpb25zLnBvc2l0aW9uID0gdHJ1ZTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvYmoub3B0aW9ucy5jb2x1bW5zW3hdLm9wdGlvbnMudmFsdWUgPSBvYmoub3B0aW9ucy5kYXRhW3ldW3hdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvYmoub3B0aW9ucy5jb2x1bW5zW3hdLm9wdGlvbnMub3BlbmVkID0gdHJ1ZTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JqLm9wdGlvbnMuY29sdW1uc1t4XS5vcHRpb25zLm9uY2xvc2UgPSBmdW5jdGlvbiAoZWwsIHZhbHVlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgcmVjb3JkcyA9IFtdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdmFsdWUgPSBlbC52YWx1ZTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCB5ID0gc3RhcnQ7IHkgPD0gZW5kOyB5KyspIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAob2JqLnJlY29yZHNbeV1beF0gJiYgIW9iai5yZWNvcmRzW3ldW3hdLmNsYXNzTGlzdC5jb250YWlucygncmVhZG9ubHknKSAmJiBvYmoucmVjb3Jkc1t5XVt4XS5zdHlsZS5kaXNwbGF5ICE9PSAnbm9uZScpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVjb3Jkcy5wdXNoKG9iai51cGRhdGVDZWxsKHgsIHksIHZhbHVlKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9iai51cGRhdGVGb3JtdWxhQ2hhaW4oeCwgeSwgcmVjb3Jkcyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLy8gb2JqLmNsb3NlRWRpdG9yKGNlbGwsIHRydWUpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIFVwZGF0ZSBoaXN0b3J5XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvYmouc2V0SGlzdG9yeSh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYWN0aW9uOiAnc2V0VmFsdWUnLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlY29yZHM6IHJlY29yZHMsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2VsZWN0aW9uOiBvYmouc2VsZWN0ZWRDZWxsLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgLy8gVXBkYXRlIHRhYmxlIHdpdGggY3VzdG9tIGNvbmZpZ3VyYXRpb24gaWYgYXBwbGljYWJsZVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JqLnVwZGF0ZVRhYmxlKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH07XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIEN1cnJlbnQgdmFsdWVcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgalN1aXRlcy5jYWxlbmRhcihlZGl0b3IsIG9iai5vcHRpb25zLmNvbHVtbnNbeF0ub3B0aW9ucyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIEZvY3VzIG9uIGVkaXRvclxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlZGl0b3IuZm9jdXMoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNhc2UgJ2N1c3RvbSc6XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzd2l0Y2ggKG9iai5vcHRpb25zLmNvbHVtbnNbeF0uZWRpdG9yLnR5cGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNhc2UgJ3RleHRFZGl0b3InOlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aXRsZTogX2YudGV4dCgnRWRpdCBtdWx0aXBsZSBjZWxscycpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJCgnLnZpLXVpLm1vZGFsJykubW9kYWwoJ3Nob3cnKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICQoJy52aS11aS5tb2RhbCAuY2xvc2UuaWNvbicpLm9mZignY2xpY2snKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAodGlueW1jZS5nZXQoJ3ZpLXdiZS10ZXh0LWVkaXRvcicpID09PSBudWxsKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgJCgnI3ZpLXdiZS10ZXh0LWVkaXRvcicpLnZhbCgnJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgd3AuZWRpdG9yLmluaXRpYWxpemUoJ3ZpLXdiZS10ZXh0LWVkaXRvcicsIEF0dHJpYnV0ZXMudGlueU1jZU9wdGlvbnMpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aW55bWNlLmdldCgndmktd2JlLXRleHQtZWRpdG9yJykuc2V0Q29udGVudCgnJylcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAkKCcudmktd2JlLXRleHQtZWRpdG9yLXNhdmUnKS5vZmYoJ2NsaWNrJykub24oJ2NsaWNrJywgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBjb250ZW50ID0gd3AuZWRpdG9yLmdldENvbnRlbnQoJ3ZpLXdiZS10ZXh0LWVkaXRvcicpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNldFZhbHVlVG9DZWxsKG9iaiwgY29udGVudCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCQodGhpcykuaGFzQ2xhc3MoJ3ZpLXdiZS1jbG9zZScpKSAkKCcudmktdWkubW9kYWwnKS5tb2RhbCgnaGlkZScpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjYXNlICd0YWdzJzpcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0ZpbmQgYW5kIHJlcGxhY2UgdGFncycpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ldyBGaW5kQW5kUmVwbGFjZVRhZ3Mob2JqLCBjZWxscywgeCwgeSwgZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjYXNlICdzZWxlY3QyJzpcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0ZpbmQgYW5kIHJlcGxhY2Ugb3B0aW9ucycpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ldyBGaW5kQW5kUmVwbGFjZU9wdGlvbnMob2JqLCBjZWxscywgeCwgeSwgZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjYXNlICdnYWxsZXJ5JzpcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0FkZCBpbWFnZSB0byBzZWxlY3RlZCBjZWxscycpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ldyBBZGRJbWFnZVRvTXVsdGlHYWxsZXJ5KG9iaiwgY2VsbHMsIHgsIHksIGUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJyZWFrO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNhc2UgJ3Byb2R1Y3RfYXR0cmlidXRlcyc6XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaXRlbXMucHVzaCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpdGxlOiBfZi50ZXh0KCdBZGQgYXR0cmlidXRlcyB0byBwcm9kdWN0cycpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ldyBNdWx0aXBsZVByb2R1Y3RBdHRyaWJ1dGVzKG9iaiwgY2VsbHMsIHgsIHksIGUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJyZWFrO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGl0ZW1zLmxlbmd0aCkgaXRlbXMucHVzaCh7dHlwZTogJ2xpbmUnfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoY2VsbHNbMV0gPT09IGNlbGxzWzNdICYmIHkgIT09IG51bGwpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBwcm9kdWN0VHlwZSA9IF9mLmdldFByb2R1Y3RUeXBlRnJvbVkoeSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAocHJvZHVjdFR5cGUgPT09ICd2YXJpYWJsZScpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0FkZCB2YXJpYXRpb24nKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25jbGljaygpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChfZi5pc19sb2FkaW5nKCkpIHJldHVybjtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBfZi5hamF4KHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YToge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3ViX2FjdGlvbjogJ2FkZF92YXJpYXRpb24nLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgcGlkOiBfZi5nZXRQcm9kdWN0SWRPZkNlbGwob2JqLCB5KVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YubG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5zdWNjZXNzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JqLmluc2VydFJvdygwLCB5LCBmYWxzZSwgdHJ1ZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JqLnNldFJvd0RhdGEoeSArIDEsIHJlcy5kYXRhLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLnJlbW92ZUxvYWRpbmcoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaXRlbXMucHVzaCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpdGxlOiBgJHtfZi50ZXh0KCdDcmVhdGUgdmFyaWF0aW9ucyBmcm9tIGFsbCBhdHRyaWJ1dGVzJyl9IDxzcGFuIGNsYXNzPVwidmktd2JlLWNvbnRleHQtbWVudS1ub3RlXCI+KCR7X2YudGV4dCgnU2F2ZSBuZXcgYXR0cmlidXRlcyBiZWZvcmUnKX0pPC9zcGFuPmAsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uY2xpY2soKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoX2YuaXNfbG9hZGluZygpKSByZXR1cm47XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YToge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWJfYWN0aW9uOiAnbGlua19hbGxfdmFyaWF0aW9ucycsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBpZDogX2YuZ2V0UHJvZHVjdElkT2ZDZWxsKG9iaiwgeSlcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGJlZm9yZVNlbmQoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLmxvYWRpbmcoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICghcmVzLnN1Y2Nlc3MpIHJldHVybjtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5kYXRhLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVzLmRhdGEuZm9yRWFjaChmdW5jdGlvbiAoaXRlbSwgaSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9iai5pbnNlcnRSb3coMCwgeSArIGksIGZhbHNlLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvYmouc2V0Um93RGF0YSh5ICsgaSArIDEsIGl0ZW0sIHRydWUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSlcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YucmVtb3ZlTG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBfZi5ub3RpY2UoYCR7cmVzLmRhdGEubGVuZ3RofSAke19mLnRleHQoJ3ZhcmlhdGlvbnMgYXJlIGFkZGVkJyl9YClcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHt0eXBlOiAnbGluZSd9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAocHJvZHVjdFR5cGUgIT09ICd2YXJpYXRpb24nKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHBpZCA9IF9mLmdldFByb2R1Y3RJZE9mQ2VsbChvYmosIHkpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0R1cGxpY2F0ZScpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YuYWpheCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YToge3N1Yl9hY3Rpb246ICdkdXBsaWNhdGVfcHJvZHVjdCcsIHByb2R1Y3RfaWQ6IHBpZH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YubG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5kYXRhLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgcmVzLmRhdGEuZm9yRWFjaChmdW5jdGlvbiAoaXRlbSwgaSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9iai5pbnNlcnRSb3coMCwgeSArIGksIHRydWUsIHRydWUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9iai5zZXRSb3dEYXRhKHkgKyBpLCBpdGVtLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YucmVtb3ZlTG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aXRsZTogX2YudGV4dCgnR28gdG8gZWRpdCBwcm9kdWN0IHBhZ2UnKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25jbGljaygpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHdpbmRvdy5vcGVuKGAke0F0dHJpYnV0ZXMuYWRtaW5Vcmx9cG9zdC5waHA/cG9zdD0ke3BpZH0mYWN0aW9uPWVkaXRgLCAnX2JsYW5rJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaXRlbXMucHVzaCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpdGxlOiBfZi50ZXh0KCdWaWV3IG9uIFNpbmdsZSBwcm9kdWN0IHBhZ2UnKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25jbGljaygpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHdpbmRvdy5vcGVuKGAke0F0dHJpYnV0ZXMuZnJvbnRlbmRVcmx9P3A9JHtwaWR9JnBvc3RfdHlwZT1wcm9kdWN0JnByZXZpZXc9dHJ1ZWAsICdfYmxhbmsnKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBpdGVtcztcclxuICAgICAgICAgICAgICAgICAgICB9O1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuXHJcbiAgICAgICAgICAgICAgICBjYXNlICdvcmRlcnMnOlxyXG4gICAgICAgICAgICAgICAgICAgIGNvbnRleHRNZW51SXRlbXMgPSBmdW5jdGlvbiAoaXRlbXMsIG9iaiwgeCwgeSwgZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgY2VsbHMgPSBvYmouc2VsZWN0ZWRDb250YWluZXI7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHggPSBwYXJzZUludCh4KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgeSA9IHBhcnNlSW50KHkpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHggIT09IG51bGwgJiYgeSAhPT0gbnVsbCkge1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGZvciAobGV0IGFjdGlvbiBpbiBBdHRyaWJ1dGVzLm9yZGVyQWN0aW9ucykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aXRsZTogQXR0cmlidXRlcy5vcmRlckFjdGlvbnNbYWN0aW9uXSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25jbGljaygpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBvcmRlcl9pZHMgPSBbXTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCBpID0gY2VsbHNbMV07IGkgPD0gY2VsbHNbM107IGkrKykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9yZGVyX2lkcy5wdXNoKF9mLmdldFByb2R1Y3RJZE9mQ2VsbChvYmosIGkpKVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiBhY3Rpb24sIG9yZGVyX2lkc30sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YubG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgX2YucmVtb3ZlTG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGl0ZW1zLmxlbmd0aCkgaXRlbXMucHVzaCh7dHlwZTogJ2xpbmUnfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgYWRkTm90ZSA9IGZ1bmN0aW9uIChpc19jdXN0b21lcl9ub3RlID0gMCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBjZWxsID0gb2JqLmdldENlbGxGcm9tQ29vcmRzKGNlbGxzWzBdLCBjZWxsc1sxXSksXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbnRyb2wgPSAkKGA8ZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPGRpdiBjbGFzcz1cImZpZWxkXCI+IFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDx0ZXh0YXJlYSByb3dzPVwiM1wiPjwvdGV4dGFyZWE+XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxkaXYgY2xhc3M9XCJmaWVsZFwiPiBcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8c3BhbiBjbGFzcz1cInZpLXdiZS1hZGQtbm90ZSB2aS11aSBidXR0b24gdGlueVwiPlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAke19mLnRleHQoJ0FkZCcpfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvc3Bhbj5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIDwvZGl2PlxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8L2Rpdj5gKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHBvcHVwID0gbmV3IFBvcHVwKGNvbnRyb2wsICQoY2VsbCkpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBjb250cm9sLm9uKCdjbGljaycsICcudmktd2JlLWFkZC1ub3RlJywgZnVuY3Rpb24gKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgbm90ZSA9IGNvbnRyb2wuZmluZCgndGV4dGFyZWEnKS52YWwoKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmICghbm90ZSkgcmV0dXJuO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGggPSBvYmouc2VsZWN0ZWRDb250YWluZXI7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBzdGFydCA9IGhbMV0sIGVuZCA9IGhbM10sIHggPSBoWzBdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgaWRzID0gW107XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCB5ID0gc3RhcnQ7IHkgPD0gZW5kOyB5KyspIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlkcy5wdXNoKG9iai5vcHRpb25zLmRhdGFbeV1bMF0pXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBvcHVwLmhpZGUoKTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGF0YToge3N1Yl9hY3Rpb246ICdhZGRfb3JkZXJfbm90ZScsIGlkcywgbm90ZSwgaXNfY3VzdG9tZXJfbm90ZX0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLmxvYWRpbmcoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLnJlbW92ZUxvYWRpbmcoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSlcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH07XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaXRlbXMucHVzaCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0FkZCBwcml2YXRlIG5vdGUnKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBhZGROb3RlKDApO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpdGxlOiBfZi50ZXh0KCdBZGQgbm90ZSB0byBjdXN0b21lcicpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uY2xpY2soKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGFkZE5vdGUoMSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGl0ZW1zLmxlbmd0aCkgaXRlbXMucHVzaCh7dHlwZTogJ2xpbmUnfSk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGNlbGxzWzFdID09PSBjZWxsc1szXSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBvcmRlcl9pZCA9IF9mLmdldFByb2R1Y3RJZE9mQ2VsbChvYmosIHkpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0dvIHRvIGVkaXQgb3JkZXIgcGFnZScpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgd2luZG93Lm9wZW4oYCR7QXR0cmlidXRlcy5hZG1pblVybH1wb3N0LnBocD9wb3N0PSR7b3JkZXJfaWR9JmFjdGlvbj1lZGl0YCwgJ19ibGFuaycpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGl0ZW1zLmxlbmd0aCkgaXRlbXMucHVzaCh7dHlwZTogJ2xpbmUnfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBpdGVtcztcclxuICAgICAgICAgICAgICAgICAgICB9O1xyXG4gICAgICAgICAgICAgICAgICAgIGJyZWFrO1xyXG5cclxuICAgICAgICAgICAgICAgIGNhc2UgJ2NvdXBvbnMnOlxyXG4gICAgICAgICAgICAgICAgICAgIGNvbnRleHRNZW51SXRlbXMgPSBmdW5jdGlvbiAoaXRlbXMsIG9iaiwgeCwgeSwgZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBsZXQgY2VsbHMgPSBvYmouc2VsZWN0ZWRDb250YWluZXI7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHggPSBwYXJzZUludCh4KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgeSA9IHBhcnNlSW50KHkpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHggIT09IG51bGwgJiYgeSAhPT0gbnVsbCkge1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChjZWxsc1swXSA9PT0gY2VsbHNbMl0pIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgY29sVHlwZSA9IF9mLmdldENvbHVtblR5cGUoeCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGNvbFR5cGUgPT09ICdjb2RlJykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpdGVtcy5wdXNoKHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRpdGxlOiBfZi50ZXh0KCdHZW5lcmF0ZSBjb3Vwb24gY29kZScpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25jbGljaygpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgYnJlYWtDb250cm9sID0gZmFsc2UsIHJlY29yZHMgPSBbXSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaCA9IG9iai5zZWxlY3RlZENvbnRhaW5lciwgc3RhcnQgPSBoWzFdLCBlbmQgPSBoWzNdLCB4ID0gaFswXTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgeSA9IHN0YXJ0OyB5IDw9IGVuZDsgeSsrKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChvYmoucmVjb3Jkc1t5XVt4XSAmJiAhb2JqLnJlY29yZHNbeV1beF0uY2xhc3NMaXN0LmNvbnRhaW5zKCdyZWFkb25seScpICYmIG9iai5yZWNvcmRzW3ldW3hdLnN0eWxlLmRpc3BsYXkgIT09ICdub25lJyAmJiBicmVha0NvbnRyb2wgPT09IGZhbHNlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgdmFsdWUgPSBfZi5nZW5lcmF0ZUNvdXBvbkNvZGUoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlY29yZHMucHVzaChvYmoudXBkYXRlQ2VsbCh4LCB5LCB2YWx1ZSkpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb2JqLnVwZGF0ZUZvcm11bGFDaGFpbih4LCB5LCByZWNvcmRzKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvYmouc2V0SGlzdG9yeSh7YWN0aW9uOiAnc2V0VmFsdWUnLCByZWNvcmRzOiByZWNvcmRzLCBzZWxlY3Rpb246IG9iai5zZWxlY3RlZENlbGx9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvYmoudXBkYXRlVGFibGUoKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAob2JqLm9wdGlvbnMuY29sdW1uc1t4XS50eXBlID09PSAndGV4dCcpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaXRlbXMucHVzaCh7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aXRsZTogX2YudGV4dCgnRWRpdCBtdWx0aXBsZSBjZWxscycpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb25jbGljayhlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmV3IFRleHRNdWx0aUNlbGxzRWRpdChvYmosIHgsIHksIGUsIG9iai5vcHRpb25zLmNvbHVtbnNbeF0ud29yZFdyYXApO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0ZpbmQgYW5kIFJlcGxhY2UnKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uY2xpY2soZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5ldyBGaW5kQW5kUmVwbGFjZShvYmosIHgsIHksIGUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChvYmoub3B0aW9ucy5jb2x1bW5zW3hdLnR5cGUgPT09ICdjaGVja2JveCcpIHtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ0NoZWNrJyksXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBvbmNsaWNrKGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBzZXRWYWx1ZVRvQ2VsbChvYmosIHRydWUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGl0ZW1zLnB1c2goe1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdGl0bGU6IF9mLnRleHQoJ1VuY2hlY2snKSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG9uY2xpY2soZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNldFZhbHVlVG9DZWxsKG9iaiwgZmFsc2UpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChpdGVtcy5sZW5ndGgpIGl0ZW1zLnB1c2goe3R5cGU6ICdsaW5lJ30pO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBpdGVtcztcclxuICAgICAgICAgICAgICAgICAgICB9O1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgdGhpcy5Xb3JrQm9vayA9ICQoJyN2aS13YmUtc3ByZWFkc2hlZXQnKS5qZXhjZWwoe1xyXG4gICAgICAgICAgICAgICAgYWxsb3dJbnNlcnRSb3c6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgYWxsb3dJbnNlcnRDb2x1bW46IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgYWJvdXQ6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgZnJlZXplQ29sdW1uczogMyxcclxuICAgICAgICAgICAgICAgIHRhYmxlT3ZlcmZsb3c6IHRydWUsXHJcbiAgICAgICAgICAgICAgICB0YWJsZVdpZHRoOiAnMTAwJScsXHJcbiAgICAgICAgICAgICAgICB0YWJsZUhlaWdodDogJzEwMCUnLFxyXG4gICAgICAgICAgICAgICAgY29sdW1uczogQXR0cmlidXRlcy5jb2x1bW5zLFxyXG4gICAgICAgICAgICAgICAgc3RyaXBIVE1MOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgIGFsbG93RXhwb3J0OiBmYWxzZSxcclxuICAgICAgICAgICAgICAgIGFsbG93RGVsZXRlQ29sdW1uOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgIGFsbG93UmVuYW1lQ29sdW1uOiBmYWxzZSxcclxuICAgICAgICAgICAgICAgIGF1dG9JbmNyZW1lbnQ6IGZhbHNlLFxyXG4gICAgICAgICAgICAgICAgYWxsb3dYQ29weTogZmFsc2UsXHJcbiAgICAgICAgICAgICAgICB0ZXh0OiB7ZGVsZXRlU2VsZWN0ZWRSb3dzfSxcclxuICAgICAgICAgICAgICAgIG9uY3JlYXRlcm93LFxyXG4gICAgICAgICAgICAgICAgY29udGV4dE1lbnVJdGVtcyxcclxuICAgICAgICAgICAgICAgIG9uc2VsZWN0aW9uLFxyXG5cclxuICAgICAgICAgICAgICAgIG9uY2hhbmdlKGluc3RhbmNlLCBjZWxsLCBjb2wsIHJvdywgdmFsdWUsIG9sZFZhbHVlKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKEpTT04uc3RyaW5naWZ5KHZhbHVlKSAhPT0gSlNPTi5zdHJpbmdpZnkob2xkVmFsdWUpKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIGlmIChjID09IDApIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgLy8gICAgIHZhciBjb2x1bW5OYW1lID0gamV4Y2VsLmdldENvbHVtbk5hbWVGcm9tSWQoW2MgKyAxLCByXSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC8vICAgICBpbnN0YW5jZS5qZXhjZWwuc2V0VmFsdWUoY29sdW1uTmFtZSwgJycpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAvLyB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICQoY2VsbCkucGFyZW50KCkudHJpZ2dlcignY2VsbG9uY2hhbmdlJywge2NlbGwsIGNvbCwgcm93LCB2YWx1ZX0pO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHBpZCA9IHRoaXMub3B0aW9ucy5kYXRhW3Jvd11bMF07XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLmNvbXBhcmUucHVzaChwaWQpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy5jb21wYXJlID0gWy4uLm5ldyBTZXQoJHRoaXMuY29tcGFyZSldO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy5tZW51YmFyLmZpbmQoJy52aS13YmUtc2F2ZS1idXR0b24nKS5hZGRDbGFzcygndmktd2JlLXNhdmVhYmxlJyk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoISR0aGlzLmlzQWRkaW5nKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoISR0aGlzLnJldmlzaW9uW3BpZF0pICR0aGlzLnJldmlzaW9uW3BpZF0gPSB7fTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBjb2x1bW5UeXBlID0gX2YuZ2V0Q29sdW1uVHlwZShjb2wpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMucmV2aXNpb25bcGlkXVtjb2x1bW5UeXBlXSA9IG9sZFZhbHVlO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfSxcclxuXHJcbiAgICAgICAgICAgICAgICBvbmRlbGV0ZXJvdyhlbCwgcm93TnVtYmVyLCBudW1PZlJvd3MsIHJvd1JlY29yZHMpIHtcclxuICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCByb3cgb2Ygcm93UmVjb3Jkcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy50cmFzaC5wdXNoKHJvd1swXS5pbm5lclRleHQpO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKCR0aGlzLnRyYXNoLmxlbmd0aCkgJHRoaXMubWVudWJhci5maW5kKCcudmktd2JlLXNhdmUtYnV0dG9uJykuYWRkQ2xhc3MoJ3ZpLXdiZS1zYXZlYWJsZScpO1xyXG4gICAgICAgICAgICAgICAgfSxcclxuXHJcbiAgICAgICAgICAgICAgICBvbnVuZG8oZWwsIGhpc3RvcnlSZWNvcmQpIHtcclxuICAgICAgICAgICAgICAgICAgICBpZiAoaGlzdG9yeVJlY29yZCAmJiBoaXN0b3J5UmVjb3JkLmFjdGlvbiA9PT0gJ2RlbGV0ZVJvdycpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgcm93IG9mIGhpc3RvcnlSZWNvcmQucm93RGF0YSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMudW5UcmFzaC5wdXNoKHJvd1swXSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9LFxyXG5cclxuICAgICAgICAgICAgICAgIG9uYmVmb3JlY29weSgpIHtcclxuICAgICAgICAgICAgICAgICAgICBjb3VudENvbCA9IDA7XHJcbiAgICAgICAgICAgICAgICAgICAgJHRoaXMuZmlyc3RDZWxsQ29weSA9IG51bGw7XHJcbiAgICAgICAgICAgICAgICB9LFxyXG5cclxuICAgICAgICAgICAgICAgIG9uY29weWluZyh2YWx1ZSwgeCwgeSkge1xyXG4gICAgICAgICAgICAgICAgICAgIGlmICghJHRoaXMuZmlyc3RDZWxsQ29weSkgJHRoaXMuZmlyc3RDZWxsQ29weSA9IFt4LCB5XTtcclxuICAgICAgICAgICAgICAgICAgICBpZiAoJHRoaXMuZmlyc3RDZWxsQ29weVswXSAhPT0geCkgY291bnRDb2wrKztcclxuICAgICAgICAgICAgICAgIH0sXHJcblxyXG4gICAgICAgICAgICAgICAgb25iZWZvcmVwYXN0ZShkYXRhLCBzZWxlY3RlZENlbGwpIHtcclxuICAgICAgICAgICAgICAgICAgICBpZiAodHlwZW9mIGRhdGEgIT09ICdzdHJpbmcnKSByZXR1cm4gZGF0YTtcclxuICAgICAgICAgICAgICAgICAgICBkYXRhID0gZGF0YS50cmltKCk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGxldCB4ID0gc2VsZWN0ZWRDZWxsWzBdO1xyXG4gICAgICAgICAgICAgICAgICAgIGxldCBjZWxsVHlwZSA9IHRoaXMuY29sdW1uc1t4XS50eXBlO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBpZiAodHlwZW9mICR0aGlzLmZpcnN0Q2VsbENvcHkgPT09ICd1bmRlZmluZWQnKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChbJ3RleHQnLCAnbnVtYmVyJywgJ2N1c3RvbSddLmluY2x1ZGVzKGNlbGxUeXBlKSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGNlbGxUeXBlID09PSAnY3VzdG9tJykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCBlZGl0b3JUeXBlID0gdGhpcy5jb2x1bW5zW3hdLmVkaXRvci50eXBlO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBlZGl0b3JUeXBlID09PSAndGV4dEVkaXRvcicgPyBkYXRhIDogJyc7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiBkYXRhO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybiAnJztcclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIGxldCBzWCA9ICskdGhpcy5maXJzdENlbGxDb3B5WzBdLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICB0WCA9ICtzZWxlY3RlZENlbGxbMF0sXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHNYVHlwZSA9IHRoaXMuY29sdW1uc1tzWF0udHlwZSxcclxuICAgICAgICAgICAgICAgICAgICAgICAgdFhUeXBlID0gdGhpcy5jb2x1bW5zW3RYXS50eXBlO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBpZiAoKyR0aGlzLmZpcnN0Q2VsbENvcHlbMF0gIT09ICtzZWxlY3RlZENlbGxbMF0pIHtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChjb3VudENvbCA+IDApIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGFsZXJ0KCdDb3B5IHNpbmdsZSBjb2x1bW4gZWFjaCB0aW1lLicpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuICcnO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoc1hUeXBlICE9PSB0WFR5cGUpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGFsZXJ0KCdDYW4gbm90IHBhc3RlIGRhdGEgd2l0aCBkaWZmZXJlbnQgY29sdW1uIHR5cGUuJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gJyc7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIHJldHVybiBkYXRhO1xyXG4gICAgICAgICAgICAgICAgfSxcclxuXHJcbiAgICAgICAgICAgICAgICBvbnNjcm9sbChlbCkge1xyXG4gICAgICAgICAgICAgICAgICAgIGxldCBzZWxlY3RPcGVuaW5nID0gJChlbCkuZmluZCgnc2VsZWN0LnNlbGVjdDItaGlkZGVuLWFjY2Vzc2libGUnKTtcclxuICAgICAgICAgICAgICAgICAgICBpZiAoc2VsZWN0T3BlbmluZy5sZW5ndGgpIHNlbGVjdE9wZW5pbmcuc2VsZWN0MignY2xvc2UnKVxyXG4gICAgICAgICAgICAgICAgfSxcclxuXHJcbiAgICAgICAgICAgICAgICBvbmNyZWF0ZWVkaXRvcihlbCwgY2VsbCwgeCwgeSwgZWRpdG9yKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5jb2x1bW5zW3hdLmN1cnJlbmN5KSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGVkaXRvci5zZXRBdHRyaWJ1dGUoJ2RhdGEtY3VycmVuY3knLCB0aGlzLm9wdGlvbnMuY29sdW1uc1t4XS5jdXJyZW5jeSk7XHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGNsb3NlTWVudShlKSB7XHJcbiAgICAgICAgICAgIHRoaXMuc2lkZWJhci5yZW1vdmVDbGFzcygndmktd2JlLW9wZW4nKVxyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgb3Blbk1lbnUoZSkge1xyXG4gICAgICAgICAgICBsZXQgdGFiID0gJChlLmN1cnJlbnRUYXJnZXQpLmRhdGEoJ21lbnVfdGFiJyk7XHJcbiAgICAgICAgICAgIGxldCBjdXJyZW50VGFiID0gdGhpcy5zaWRlYmFyLmZpbmQoYGEuaXRlbVtkYXRhLXRhYj0nJHt0YWJ9J11gKTtcclxuICAgICAgICAgICAgaWYgKGN1cnJlbnRUYWIuaGFzQ2xhc3MoJ2FjdGl2ZScpICYmIHRoaXMuc2lkZWJhci5oYXNDbGFzcygndmktd2JlLW9wZW4nKSkge1xyXG4gICAgICAgICAgICAgICAgdGhpcy5zaWRlYmFyLnJlbW92ZUNsYXNzKCd2aS13YmUtb3BlbicpO1xyXG4gICAgICAgICAgICB9IGVsc2Uge1xyXG4gICAgICAgICAgICAgICAgdGhpcy5zaWRlYmFyLmFkZENsYXNzKCd2aS13YmUtb3BlbicpO1xyXG4gICAgICAgICAgICAgICAgY3VycmVudFRhYi50cmlnZ2VyKCdjbGljaycpO1xyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBhZGROZXdQcm9kdWN0KCkge1xyXG4gICAgICAgICAgICBpZiAoX2YuaXNfbG9hZGluZygpKSByZXR1cm47XHJcbiAgICAgICAgICAgIGxldCBwcm9kdWN0TmFtZSA9IHByb21wdChfZi50ZXh0KCdQbGVhc2UgZW50ZXIgbmV3IHByb2R1Y3QgbmFtZScpKTtcclxuXHJcbiAgICAgICAgICAgIGlmIChwcm9kdWN0TmFtZSkge1xyXG4gICAgICAgICAgICAgICAgbGV0ICR0aGlzID0gdGhpcztcclxuICAgICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAnYWRkX25ld19wcm9kdWN0JywgcHJvZHVjdF9uYW1lOiBwcm9kdWN0TmFtZX0sXHJcbiAgICAgICAgICAgICAgICAgICAgYmVmb3JlU2VuZCgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgX2YubG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICAgICAgc3VjY2VzcyhyZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMuaXNBZGRpbmcgPSB0cnVlO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy5Xb3JrQm9vay5pbnNlcnRSb3coMCwgMCwgdHJ1ZSwgdHJ1ZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLldvcmtCb29rLnNldFJvd0RhdGEoMCwgcmVzLmRhdGEsIHRydWUpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBfZi5yZW1vdmVMb2FkaW5nKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgICAgICBjb21wbGV0ZSgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMuaXNBZGRpbmcgPSBmYWxzZTtcclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9KVxyXG4gICAgICAgICAgICB9XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBhZGROZXdDb3Vwb24oKSB7XHJcbiAgICAgICAgICAgIGlmIChfZi5pc19sb2FkaW5nKCkpIHJldHVybjtcclxuXHJcbiAgICAgICAgICAgIGxldCAkdGhpcyA9IHRoaXM7XHJcblxyXG4gICAgICAgICAgICBfZi5hamF4KHtcclxuICAgICAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAnYWRkX25ld19jb3Vwb24nfSxcclxuICAgICAgICAgICAgICAgIGJlZm9yZVNlbmQoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgX2YubG9hZGluZygpO1xyXG4gICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgJHRoaXMuaXNBZGRpbmcgPSB0cnVlO1xyXG4gICAgICAgICAgICAgICAgICAgICR0aGlzLldvcmtCb29rLmluc2VydFJvdygwLCAwLCB0cnVlLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgICAgICAkdGhpcy5Xb3JrQm9vay5zZXRSb3dEYXRhKDAsIHJlcy5kYXRhLCB0cnVlKTtcclxuICAgICAgICAgICAgICAgICAgICBfZi5yZW1vdmVMb2FkaW5nKCk7XHJcbiAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgY29tcGxldGUoKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgJHRoaXMuaXNBZGRpbmcgPSBmYWxzZTtcclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfSlcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGFkZE5ld09yZGVyKCkge1xyXG4gICAgICAgICAgICB3aW5kb3cub3BlbigncG9zdC1uZXcucGhwP3Bvc3RfdHlwZT1zaG9wX29yZGVyJyk7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICB0b2dnbGVGdWxsU2NyZWVuKGUpIHtcclxuICAgICAgICAgICAgbGV0IGJvZHkgPSAkKCcud3AtYWRtaW4nKSwgc2NyZWVuQnRuID0gJChlLmN1cnJlbnRUYXJnZXQpO1xyXG4gICAgICAgICAgICBib2R5LnRvZ2dsZUNsYXNzKCd2aS13YmUtZnVsbC1zY3JlZW4nKTtcclxuXHJcbiAgICAgICAgICAgIGlmIChib2R5Lmhhc0NsYXNzKCd2aS13YmUtZnVsbC1zY3JlZW4nKSkge1xyXG4gICAgICAgICAgICAgICAgc2NyZWVuQnRuLmZpbmQoJ2kuaWNvbicpLnJlbW92ZUNsYXNzKCdleHRlcm5hbCBhbHRlcm5hdGUnKS5hZGRDbGFzcygnd2luZG93IGNsb3NlIG91dGxpbmUnKTtcclxuICAgICAgICAgICAgICAgIHNjcmVlbkJ0bi5hdHRyKCd0aXRsZScsICdFeGl0IGZ1bGwgc2NyZWVuJyk7XHJcbiAgICAgICAgICAgIH0gZWxzZSB7XHJcbiAgICAgICAgICAgICAgICBzY3JlZW5CdG4uZmluZCgnaS5pY29uJykucmVtb3ZlQ2xhc3MoJ3dpbmRvdyBjbG9zZSBvdXRsaW5lJykuYWRkQ2xhc3MoJ2V4dGVybmFsIGFsdGVybmF0ZScpO1xyXG4gICAgICAgICAgICAgICAgc2NyZWVuQnRuLmF0dHIoJ3RpdGxlJywgJ0Z1bGwgc2NyZWVuJyk7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICQuYWpheCh7XHJcbiAgICAgICAgICAgICAgICB1cmw6IEF0dHJpYnV0ZXMuYWpheFVybCxcclxuICAgICAgICAgICAgICAgIHR5cGU6ICdwb3N0JyxcclxuICAgICAgICAgICAgICAgIGRhdGFUeXBlOiAnanNvbicsXHJcbiAgICAgICAgICAgICAgICBkYXRhOiB7XHJcbiAgICAgICAgICAgICAgICAgICAgLi4uQXR0cmlidXRlcy5hamF4RGF0YSxcclxuICAgICAgICAgICAgICAgICAgICBzdWJfYWN0aW9uOiAnc2V0X2Z1bGxfc2NyZWVuX29wdGlvbicsXHJcbiAgICAgICAgICAgICAgICAgICAgc3RhdHVzOiBib2R5Lmhhc0NsYXNzKCd2aS13YmUtZnVsbC1zY3JlZW4nKVxyXG4gICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICB9KTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIGdldEFsbFJvd3MoKSB7XHJcbiAgICAgICAgICAgIHJldHVybiB0aGlzLldvcmtCb29rLmdldERhdGEoZmFsc2UsIHRydWUpO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgc2F2ZSgpIHtcclxuICAgICAgICAgICAgJCgndGQuZXJyb3InKS5yZW1vdmVDbGFzcygnZXJyb3InKTtcclxuXHJcbiAgICAgICAgICAgIGxldCAkdGhpcyA9IHRoaXMsXHJcbiAgICAgICAgICAgICAgICBwcm9kdWN0cyA9IHRoaXMuZ2V0QWxsUm93cygpLFxyXG4gICAgICAgICAgICAgICAgcHJvZHVjdHNGb3JTYXZlID0gW10sIHNrdUVycm9ycyA9IFtdO1xyXG5cclxuICAgICAgICAgICAgZm9yIChsZXQgcGlkIG9mIHRoaXMuY29tcGFyZSkge1xyXG4gICAgICAgICAgICAgICAgZm9yIChsZXQgcHJvZHVjdCBvZiBwcm9kdWN0cykge1xyXG4gICAgICAgICAgICAgICAgICAgIGlmIChwcm9kdWN0WzBdID09PSBwYXJzZUludChwaWQpKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHByb2R1Y3RzRm9yU2F2ZS5wdXNoKHByb2R1Y3QpO1xyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgaWYgKF9mLmlzX2xvYWRpbmcoKSkgcmV0dXJuO1xyXG5cclxuICAgICAgICAgICAgZnVuY3Rpb24gc2F2ZVN0ZXAoc3RlcCA9IDApIHtcclxuICAgICAgICAgICAgICAgIGxldCByYW5nZSA9IDMwLFxyXG4gICAgICAgICAgICAgICAgICAgIHN0YXJ0ID0gc3RlcCAqIHJhbmdlLFxyXG4gICAgICAgICAgICAgICAgICAgIGVuZCA9IHN0YXJ0ICsgcmFuZ2UsXHJcbiAgICAgICAgICAgICAgICAgICAgcHJvZHVjdHMgPSBwcm9kdWN0c0ZvclNhdmUuc2xpY2Uoc3RhcnQsIGVuZCksXHJcbiAgICAgICAgICAgICAgICAgICAgbGFzdFN0ZXAgPSAoc3RlcCArIDEpICogcmFuZ2UgPiBwcm9kdWN0c0ZvclNhdmUubGVuZ3RoO1xyXG5cclxuICAgICAgICAgICAgICAgIGlmICghKHByb2R1Y3RzLmxlbmd0aCB8fCAkdGhpcy50cmFzaC5sZW5ndGggfHwgJHRoaXMudW5UcmFzaC5sZW5ndGgpKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgaWYgKHN0ZXAgPT09IDApIF9mLm5vdGljZShfZi50ZXh0KCdOb3RoaW5nIGNoYW5nZSB0byBzYXZlJykpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICBpZiAobGFzdFN0ZXApIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHNrdUVycm9ycy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLm5vdGljZShfZi50ZXh0KCdJbnZhbGlkIG9yIGR1cGxpY2F0ZWQgU0tVJykpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxldCB4ID0gX2YuZ2V0Q29sRnJvbUNvbHVtblR5cGUoJ3NrdScpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGFsbFJvd3MgPSAkdGhpcy5Xb3JrQm9vay5nZXREYXRhKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoYWxsUm93cy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBmb3IgKGxldCB5IGluIGFsbFJvd3MpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IHJvdyA9IGFsbFJvd3NbeV07XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChza3VFcnJvcnMuaW5jbHVkZXMocm93WzBdKSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGV0IGNlbGwgPSAkdGhpcy5Xb3JrQm9vay5nZXRDZWxsRnJvbUNvb3Jkcyh4LCB5KTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICQoY2VsbCkuYWRkQ2xhc3MoJ2Vycm9yJyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGxldCBoaXN0b3JpZXMgPSAkdGhpcy5Xb3JrQm9vay5oaXN0b3J5O1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoaGlzdG9yaWVzLmxlbmd0aCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgZm9yIChsZXQgaGlzdG9yeSBvZiBoaXN0b3JpZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoaGlzdG9yeS5hY3Rpb24gIT09ICdkZWxldGVSb3cnKSBjb250aW51ZTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsZXQgaUZvckRlbCA9IFtdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGZvciAobGV0IGkgaW4gaGlzdG9yeS5yb3dEYXRhKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChoaXN0b3J5LnJvd0RhdGFbaV1bMV0gPiAwKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpRm9yRGVsLnB1c2gocGFyc2VJbnQoaSkpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAoaUZvckRlbC5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaGlzdG9yeS5yb3dEYXRhID0gaGlzdG9yeS5yb3dEYXRhLmZpbHRlcigoaXRlbSwgaSkgPT4gIWlGb3JEZWwuaW5jbHVkZXMoaSkpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBoaXN0b3J5LnJvd05vZGUgPSBoaXN0b3J5LnJvd05vZGUuZmlsdGVyKChpdGVtLCBpKSA9PiAhaUZvckRlbC5pbmNsdWRlcyhpKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGhpc3Rvcnkucm93UmVjb3JkcyA9IGhpc3Rvcnkucm93UmVjb3Jkcy5maWx0ZXIoKGl0ZW0sIGkpID0+ICFpRm9yRGVsLmluY2x1ZGVzKGkpKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgaGlzdG9yeS5udW1PZlJvd3MgPSBoaXN0b3J5Lm51bU9mUm93cyAtIGlGb3JEZWwubGVuZ3RoO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMuc2F2ZVJldmlzaW9uKCk7XHJcblxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgICAgICAgICAgcmV0dXJuO1xyXG4gICAgICAgICAgICAgICAgfVxyXG5cclxuICAgICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgICAgIGRhdGE6IHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgc3ViX2FjdGlvbjogJ3NhdmVfcHJvZHVjdHMnLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBwcm9kdWN0czogSlNPTi5zdHJpbmdpZnkocHJvZHVjdHMpLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICB0cmFzaDogJHRoaXMudHJhc2gsXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHVudHJhc2g6ICR0aGlzLnVuVHJhc2gsXHJcbiAgICAgICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBfZi5sb2FkaW5nKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy50cmFzaCA9IFtdO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy51blRyYXNoID0gW107XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLmNvbXBhcmUgPSBbXTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMubWVudWJhci5maW5kKCcudmktd2JlLXNhdmUtYnV0dG9uJykucmVtb3ZlQ2xhc3MoJ3ZpLXdiZS1zYXZlYWJsZScpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5kYXRhLnNrdUVycm9ycykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgc2t1RXJyb3JzID0gWy4uLm5ldyBTZXQoWy4uLnNrdUVycm9ycywgLi4ucmVzLmRhdGEuc2t1RXJyb3JzXSldO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICBfZi5yZW1vdmVMb2FkaW5nKCk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHNhdmVTdGVwKHN0ZXAgKyAxKTtcclxuICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgIGVycm9yKHJlcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhyZXMpXHJcbiAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgICAgIH1cclxuXHJcbiAgICAgICAgICAgIHNhdmVTdGVwKCk7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBsb2FkUHJvZHVjdHMocGFnZSA9IDEsIHJlQ3JlYXRlID0gZmFsc2UpIHtcclxuICAgICAgICAgICAgbGV0ICR0aGlzID0gdGhpcztcclxuXHJcbiAgICAgICAgICAgIGlmIChfZi5pc19sb2FkaW5nKCkpIHJldHVybjtcclxuXHJcbiAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgZGF0YToge1xyXG4gICAgICAgICAgICAgICAgICAgIHN1Yl9hY3Rpb246ICdsb2FkX3Byb2R1Y3RzJyxcclxuICAgICAgICAgICAgICAgICAgICBwYWdlOiBwYWdlLFxyXG4gICAgICAgICAgICAgICAgICAgIHJlX2NyZWF0ZTogcmVDcmVhdGVcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICBiZWZvcmVTZW5kKCkge1xyXG4gICAgICAgICAgICAgICAgICAgIF9mLmxvYWRpbmcoKTtcclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICBzdWNjZXNzKHJlcykge1xyXG4gICAgICAgICAgICAgICAgICAgIGlmIChyZXMuc3VjY2Vzcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBBdHRyaWJ1dGVzLmltZ1N0b3JhZ2UgPSByZXMuZGF0YS5pbWdfc3RvcmFnZTtcclxuXHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChyZUNyZWF0ZSkge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMuV29ya0Jvb2suZGVzdHJveSgpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChyZXMuZGF0YS5jb2x1bW5zKSBBdHRyaWJ1dGVzLnNldENvbHVtbnMocmVzLmRhdGEuY29sdW1ucyk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBpZiAocmVzLmRhdGEuaWRNYXBwaW5nKSBBdHRyaWJ1dGVzLmlkTWFwcGluZyA9IHJlcy5kYXRhLmlkTWFwcGluZztcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGlmIChyZXMuZGF0YS5pZE1hcHBpbmdGbGlwKSBBdHRyaWJ1dGVzLmlkTWFwcGluZ0ZsaXAgPSByZXMuZGF0YS5pZE1hcHBpbmdGbGlwO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICR0aGlzLndvcmtCb29rSW5pdCgpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy5Xb3JrQm9vay5vcHRpb25zLmRhdGEgPSByZXMuZGF0YS5wcm9kdWN0cztcclxuICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMuV29ya0Jvb2suc2V0RGF0YSgpO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy5wYWdpbmF0aW9uKHJlcy5kYXRhLm1heF9udW1fcGFnZXMsIHBhZ2UpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgX2YucmVtb3ZlTG9hZGluZygpO1xyXG5cclxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCFyZXMuZGF0YS5wcm9kdWN0cy5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIF9mLm5vdGljZShfZi50ZXh0KCdObyBpdGVtIHdhcyBmb3VuZCcpKTtcclxuICAgICAgICAgICAgICAgICAgICAgICAgfVxyXG4gICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgIH0sXHJcbiAgICAgICAgICAgICAgICBlcnJvcihyZXMpIHtcclxuICAgICAgICAgICAgICAgICAgICBjb25zb2xlLmxvZyhyZXMpXHJcbiAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgIH0pO1xyXG4gICAgICAgIH1cclxuXHJcbiAgICAgICAgcGFnaW5hdGlvbihtYXhQYWdlLCBjdXJyZW50UGFnZSkge1xyXG4gICAgICAgICAgICB0aGlzLm1lbnViYXIuZmluZCgnLnZpLXdiZS1wYWdpbmF0aW9uJykuaHRtbChfZi5wYWdpbmF0aW9uKG1heFBhZ2UsIGN1cnJlbnRQYWdlKSk7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBjaGFuZ2VQYWdlKGUpIHtcclxuICAgICAgICAgICAgbGV0IHBhZ2UgPSBwYXJzZUludCgkKGUuY3VycmVudFRhcmdldCkuYXR0cignZGF0YS1wYWdlJykpO1xyXG4gICAgICAgICAgICBpZiAoJChlLmN1cnJlbnRUYXJnZXQpLmhhc0NsYXNzKCdhY3RpdmUnKSB8fCAkKGUuY3VycmVudFRhcmdldCkuaGFzQ2xhc3MoJ2Rpc2FibGVkJykgfHwgIXBhZ2UpIHJldHVybjtcclxuICAgICAgICAgICAgdGhpcy5sb2FkUHJvZHVjdHMocGFnZSk7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBjaGFuZ2VQYWdlQnlJbnB1dChlKSB7XHJcbiAgICAgICAgICAgIGxldCBwYWdlID0gcGFyc2VJbnQoJChlLnRhcmdldCkudmFsKCkpO1xyXG4gICAgICAgICAgICBsZXQgbWF4ID0gcGFyc2VJbnQoJChlLnRhcmdldCkuYXR0cignbWF4JykpO1xyXG5cclxuICAgICAgICAgICAgaWYgKHBhZ2UgPD0gbWF4ICYmIHBhZ2UgPiAwKSB0aGlzLmxvYWRQcm9kdWN0cyhwYWdlKTtcclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHJlbG9hZEN1cnJlbnRQYWdlKCkge1xyXG4gICAgICAgICAgICB0aGlzLmxvYWRQcm9kdWN0cyh0aGlzLmdldEN1cnJlbnRQYWdlKCkpXHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBnZXRDdXJyZW50UGFnZSgpIHtcclxuICAgICAgICAgICAgcmV0dXJuIHRoaXMubWVudWJhci5maW5kKCcudmktd2JlLXBhZ2luYXRpb24gLml0ZW0uYWN0aXZlJykuZGF0YSgncGFnZScpIHx8IDE7XHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBhZnRlckFkZEZpbHRlcihldiwgZGF0YSkge1xyXG4gICAgICAgICAgICBBdHRyaWJ1dGVzLmltZ1N0b3JhZ2UgPSBkYXRhLmltZ19zdG9yYWdlO1xyXG4gICAgICAgICAgICB0aGlzLldvcmtCb29rLm9wdGlvbnMuZGF0YSA9IGRhdGEucHJvZHVjdHM7XHJcbiAgICAgICAgICAgIHRoaXMuV29ya0Jvb2suc2V0RGF0YSgpO1xyXG4gICAgICAgICAgICB0aGlzLnBhZ2luYXRpb24oZGF0YS5tYXhfbnVtX3BhZ2VzLCAxKTtcclxuICAgICAgICAgICAgaWYgKCFkYXRhLnByb2R1Y3RzLmxlbmd0aCkgX2Yubm90aWNlKF9mLnRleHQoJ05vIGl0ZW0gd2FzIGZvdW5kJykpXHJcbiAgICAgICAgfVxyXG5cclxuICAgICAgICBhZnRlclNhdmVTZXR0aW5ncyhldiwgZGF0YSkge1xyXG4gICAgICAgICAgICBpZiAoZGF0YS5maWVsZHNDaGFuZ2UpIHtcclxuICAgICAgICAgICAgICAgIHRoaXMubG9hZFByb2R1Y3RzKHRoaXMuZ2V0Q3VycmVudFBhZ2UoKSwgdHJ1ZSk7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9XHJcblxyXG4gICAgICAgIHNhdmVSZXZpc2lvbigpIHtcclxuICAgICAgICAgICAgbGV0ICR0aGlzID0gdGhpcztcclxuICAgICAgICAgICAgaWYgKE9iamVjdC5rZXlzKCR0aGlzLnJldmlzaW9uKS5sZW5ndGgpIHtcclxuICAgICAgICAgICAgICAgIGxldCBjdXJyZW50UGFnZSA9ICR0aGlzLnNpZGViYXIuZmluZCgnLnZpLXdiZS1wYWdpbmF0aW9uIGEuaXRlbS5hY3RpdmUnKS5kYXRhKCdwYWdlJykgfHwgMTtcclxuICAgICAgICAgICAgICAgIF9mLmFqYXgoe1xyXG4gICAgICAgICAgICAgICAgICAgIGRhdGE6IHtzdWJfYWN0aW9uOiAnYXV0b19zYXZlX3JldmlzaW9uJywgZGF0YTogJHRoaXMucmV2aXNpb24sIHBhZ2U6IGN1cnJlbnRQYWdlIHx8IDF9LFxyXG4gICAgICAgICAgICAgICAgICAgIHN1Y2Nlc3MocmVzKSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChyZXMuc3VjY2Vzcykge1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlcy5kYXRhLnVwZGF0ZVBhZ2UpICQoJyN2aS13YmUtaGlzdG9yeS1wb2ludHMtbGlzdCB0Ym9keScpLmh0bWwocmVzLmRhdGEudXBkYXRlUGFnZSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkdGhpcy5yZXZpc2lvbiA9IHt9O1xyXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgJHRoaXMuc2lkZWJhci5maW5kKCcudmktd2JlLXBhZ2luYXRpb24nKS5odG1sKF9mLnBhZ2luYXRpb24ocmVzLmRhdGEucGFnZXMsIGN1cnJlbnRQYWdlKSk7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cclxuICAgICAgICAgICAgICAgICAgICB9XHJcbiAgICAgICAgICAgICAgICB9KTtcclxuICAgICAgICAgICAgfVxyXG4gICAgICAgIH1cclxuXHJcbiAgICB9XHJcblxyXG4gICAgbmV3IEJ1bGtFZGl0KCk7XHJcblxyXG59KTtcclxuIl0sInNvdXJjZVJvb3QiOiIifQ==