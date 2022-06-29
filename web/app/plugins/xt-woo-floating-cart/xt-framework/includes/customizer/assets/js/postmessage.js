/* global xirkiPostMessageFields, WebFont */
var xirkiPostMessage = {

    windows: {
        _windows: [window],
        register: function(win) {
            this._windows.push(win);
            xirkiPostMessage.styleTag.syncStyleTags();
        },
        deregister: function(winName) {
            var index = this._windows.findIndex((win) => win.name === winName);
            this._windows.splice(index, 1);
        },
        get: function() {
            return this._windows;
        },
        getMain: function () {
            return window;
        }
    },

    stylesheet: {

        _stylesheets: [],

        load: function() {

            xirkiPostMessage.windows.get().forEach((win) => {

                this._stylesheets.push([].slice.call(win.document.styleSheets).find(function (item) {

                    return item.ownerNode.id === 'xirki-inline-styles'
                }));
            });
        },

        getRules: function(selector, mediaQuery) {

            if(mediaQuery) {
                mediaQuery = mediaQuery.replace('@media ', '');
            }

            var rules = [];

            this._stylesheets.forEach((stylesheet) => {

                [].slice.call(stylesheet.cssRules).forEach(function(rule) {

                    if(rule.media && mediaQuery && rule.media.mediaText === mediaQuery && rule.cssRules.length) {

                        return [].slice.call(rule.cssRules).forEach(function(media_rule) {

                            if(media_rule.selectorText && media_rule.selectorText === selector) {
                                rules.push(media_rule);
                            }
                        });

                    }else if(rule.selectorText && rule.selectorText === selector) {
                        rules.push(rule);
                    }
                });
            });

            return rules;
        },

        removeColorRule: function(output, mediaQuery) {

            var rules = this.getRules(output.element, mediaQuery);

            if(rules && rules.length) {
                rules.forEach(function(rule) {
                    if(output.hasOwnProperty('property')) {
                        rule.style.setProperty(output.property, '');
                    }else {
                        rule.style.setProperty('color', '');
                        rule.style.setProperty('background-color', '');
                        rule.style.setProperty('border-color', '');
                    }
                });
            }
        }
    },

    /**
     * The fields.
     *
     * @since 3.0.26
     */
    fields: {},

    /**
     * A collection of methods for the <style> tags.
     *
     * @since 3.0.26
     */
    styleTag: {

        /**
         * Add a <style> tag in <head> if it doesn't already exist.
         *
         * @since 3.0.26
         * @param {string} id - The field-ID.
         * @returns {string} - The target
         */
        add: function( id ) {

            var handle = 'xirki-postmessage-' + id;
            var target = '#' + handle;
            var style = '<style id="'+ handle + '"></style>';

            xirkiPostMessage.windows.get().forEach((win) => {

                // Force remove and re-append to make sure latest styles are always applied
                if ( win.jQuery(target).length ) {
                    win.jQuery(target).remove();
                }

                win.jQuery('head').append(style);
            });

            return target;
        },

        /**
         * Add a <style> tag in <head> if it doesn't already exist,
         * by calling the this.add method, and then add styles inside it.
         *
         * @since 3.0.26
         * @param {string} id - The field-ID.
         * @param {string} styles - The styles to add.
         * @returns {void}
         */
        addData: function( id, styles ) {

            var target = xirkiPostMessage.styleTag.add( id );

            xirkiPostMessage.windows.get().forEach((win) => {
                win.jQuery(target).text(styles);
            });
        },

        syncStyleTags: function() {

            var mainWindow = xirkiPostMessage.windows.getMain();

            mainWindow.jQuery('style[id*="xirki-postmessage-"]').each(function() {

                var id = mainWindow.jQuery(this).attr('id').replace('xirki-postmessage-', '');
                var styles = mainWindow.jQuery(this).text();

                xirkiPostMessage.styleTag.addData( id, styles );
            });

        }
    },

    /**
     * Common utilities.
     *
     * @since 3.0.26
     */
    util: {

        /**
         * Processes the value and applies any replacements and/or additions.
         *
         * @since 3.0.26
         * @param {Object} output - The output (js_vars) argument.
         * @param {mixed}  value - The value.
         * @param {string} controlType - The control-type.
         * @returns {string|false} - Returns false if value is excluded, otherwise a string.
         */
        processValue: function( output, value ) {
            var self     = this,
                settings = window.parent.wp.customize.get(),
                excluded = false;

            if ( 'object' === typeof value ) {
                _.each( value, function( subValue, key ) {
                    value[ key ] = self.processValue( output, subValue );
                } );
                return value;
            }

            output = _.defaults( output, {
                prefix: '',
                units: '',
                suffix: '',
                value_pattern: '$',
                pattern_replace: {},
                exclude: []
            } );

            if ( 1 <= output.exclude.length ) {
                _.each( output.exclude, function( exclusion ) {
                    if ( value == exclusion ) {
                        excluded = true;
                    }
                } );
            }

            if ( excluded ) {
                return false;
            }

            value = output.value_pattern.replace( new RegExp( '\\$', 'g' ), value );
            _.each( output.pattern_replace, function( id, placeholder ) {

                if ( ! _.isUndefined( settings[ id ] ) ) {
                    value = value.replace( placeholder, settings[ id ] );
                }
            } );
            return output.prefix + value + output.units + output.suffix;
        },

        /**
         * Make sure urls are properly formatted for background-image properties.
         *
         * @since 3.0.26
         * @param {string} url - The URL.
         * @returns {string}
         */
        backgroundImageValue: function( url ) {
            return ( -1 === url.indexOf( 'url(' ) ) ? 'url(' + url + ')' : url;
        },

        /**
         * Check if color value is light
         *
         * @since 3.0.26
         * @param {string} color - HEX or RGB color.
         * @returns {string}
         */

        colorIsLight: function (color) {

            // Variables for red, green, blue values
            var r, g, b, hsp;

            // Check the format of the color, HEX or RGB?
            if (color.match(/^rgb/)) {

                // If HEX --> store the red, green, blue values in separate variables
                color = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);

                r = color[1];
                g = color[2];
                b = color[3];

            } else {

                // If RGB --> Convert it to HEX: http://gist.github.com/983661
                color = +("0x" + color.slice(1).replace(
                    color.length < 5 && /./g, '$&$&'));

                r = color >> 16;
                g = color >> 8 & 255;
                b = color & 255;
            }


            // HSP (Highly Sensitive Poo) equation from http://alienryderflex.com/hsp.html
            hsp = Math.sqrt(
                0.299 * (r * r) +
                0.587 * (g * g) +
                0.114 * (b * b)
            );

            // Using the HSP value, determine whether the color is light or dark
            return (hsp > 127.5);
        }
    },

    /**
     * A collection of utilities for CSS generation.
     *
     * @since 3.0.26
     */
    css: {

        /**
         * Generates the CSS from the output (js_vars) parameter.
         *
         * @since 3.0.26
         * @param {Object} output - The output (js_vars) argument.
         * @param {mixed}  value - The value.
         * @param {string} controlType - The control-type.
         * @returns {string}
         */
        fromOutput: function( output, value, controlType ) {
            var styles      = '',
                xirkiParent = window.parent.xirki,
                googleFont  = '',
                mediaQuery  = false,
                processedValue;

            if ( output.js_callback && 'function' === typeof window[ output.js_callback ] ) {
                value = window[ output.js_callback[0] ]( value, output.js_callback[1] );
            }

            if((controlType === 'xirki-color' || controlType ===  'xirki-typography')) {

                mediaQuery = (output.media_query && 'string' === typeof output.media_query && ! _.isEmpty( output.media_query )) ? output.media_query : false;

                xirkiPostMessage.stylesheet.removeColorRule(output, mediaQuery);
            }

            switch ( controlType ) {
                case 'xirki-typography':
                    styles += output.element + '{';
                    _.each( value, function( val, key ) {
                        if ( output.choice && key !== output.choice ) {
                            return;
                        }

                        processedValue = xirkiPostMessage.util.processValue( output, val );

                        if ( false != processedValue ) {
                            styles += key + ':' + processedValue + ';';
                        }
                    } );
                    styles += '}';

                    // Check if this is a googlefont so that we may load it.
                    if ( ! _.isUndefined( WebFont ) && value['font-family'] && 'google' === xirkiParent.util.webfonts.getFontType( value['font-family'] ) ) {

                        // Calculate the googlefont params.
                        googleFont = value['font-family'].replace( /\"/g, '&quot;' );
                        if ( value.variant ) {
                            if ( 'regular' === value.variant ) {
                                googleFont += ':400';
                            } else if ( 'italic' === value.variant ) {
                                googleFont += ':400i';
                            } else {
                                googleFont += ':' + value.variant;
                            }
                        }
                        googleFont += ':cyrillic,cyrillic-ext,devanagari,greek,greek-ext,khmer,latin,latin-ext,vietnamese,hebrew,arabic,bengali,gujarati,tamil,telugu,thai';
                        WebFont.load( {
                            google: {
                                families: [ googleFont ]
                            }
                        } );
                    }
                    break;
                case 'xirki-background':
                case 'xirki-dimensions':
                case 'xirki-multicolor':
                case 'xirki-sortable':
                    styles += output.element + '{';
                    _.each( value, function( val, key ) {
                        if ( output.choice && key !== output.choice ) {
                            return;
                        }
                        if ( 'background-image' === key ) {
                            val = xirkiPostMessage.util.backgroundImageValue( val );
                        }

                        processedValue = xirkiPostMessage.util.processValue( output, val );

                        if ( false !== processedValue ) {

                            // Mostly used for padding, margin & position properties.
                            if ( output.property ) {
                                styles += output.property;
                                if ( '' !== output.property && ( 'top' === key || 'bottom' === key || 'left' === key || 'right' === key ) ) {
                                    styles += '-' + key;
                                }
                                styles += ':' + processedValue + ';';
                            } else {
                                styles += key + ':' + processedValue + ';';
                            }
                        }
                    } );
                    styles += '}';
                    break;
                default:
                    if ( 'xirki-image' === controlType ) {
                        value = ( ! _.isUndefined( value.url ) ) ? xirkiPostMessage.util.backgroundImageValue( value.url ) : xirkiPostMessage.util.backgroundImageValue( value );
                    }
                    if ( _.isObject( value ) ) {
                        styles += output.element + '{';
                        _.each( value, function( val, key ) {
                            if ( output.choice && key !== output.choice ) {
                                return;
                            }
                            processedValue = xirkiPostMessage.util.processValue( output, val );
                            if ( ! output.property ) {
                                output.property = key;
                            }
                            if ( false !== processedValue ) {
                                styles += output.property + ':' + processedValue + ';';
                            }
                        } );
                        styles += '}';
                    } else {
                        processedValue = xirkiPostMessage.util.processValue( output, value );

                        if ( false !== processedValue ) {
                            styles += output.element + '{' + output.property + ':' + processedValue + ';}';
                        }
                    }
                    break;
            }

            // Get the media-query.
            if ( output.media_query && 'string' === typeof output.media_query && ! _.isEmpty( output.media_query ) ) {
                mediaQuery = output.media_query;
                if ( -1 === mediaQuery.indexOf( '@media' ) ) {
                    mediaQuery = '@media ' + mediaQuery;
                }
            }

            // If we have a media-query, add it and return.
            if ( mediaQuery ) {
                return mediaQuery + '{' + styles + '}';
            }

            // Return the styles.
            return styles;
        }
    },

    /**
     * A collection of utilities to change the HTML in the document.
     *
     * @since 3.0.26
     */
    html: {

        /**
         * Modifies the HTML from the output (js_vars) parameter.
         *
         * @since 3.0.26
         * @param {Object} output - The output (js_vars) argument.
         * @param {mixed}  value - The value.
         * @returns {string}
         */
        fromOutput: function( output, value ) {

            if ( output.js_callback && 'function' === typeof window[ output.js_callback ] ) {
                value = window[ output.js_callback[0] ]( value, output.js_callback[1] );
            }

            if ( _.isObject( value ) || _.isArray( value ) ) {
                if ( ! output.choice ) {
                    return;
                }
                _.each( value, function( val, key ) {
                    if ( output.choice && key !== output.choice ) {
                        return;
                    }
                    value = val;
                } );
            }
            value = xirkiPostMessage.util.processValue( output, value );

            xirkiPostMessage.windows.get().forEach((win) => {
                if (output.attr) {
                    win.jQuery(output.element).attr(output.attr, value);
                } else {
                    win.jQuery(output.element).html(value);
                }
            });
        }
    },

    /**
     * A collection of utilities to allow toggling a CSS class.
     *
     * @since 3.0.26
     */
    toggleClass: {

        /**
         * Toggles a CSS class from the output (js_vars) parameter.
         *
         * @since 3.0.21
         * @param {Object} output - The output (js_vars) argument.
         * @param {mixed}  value - The value.
         */
        fromOutput: function( output, value ) {
            if ( 'undefined' === typeof output.class || 'undefined' === typeof output.value ) {
                return;
            }

            xirkiPostMessage.windows.get().forEach((win) => {
                if (value === output.value && !jQuery(output.element).hasClass(output.class)) {
                    win.jQuery(output.element).addClass(output.class);
                } else {
                    win.jQuery(output.element).removeClass(output.class);
                }
            });
        }
    },

    /**
     * Switch element CSS class using field value
     *
     */
    class: {

        /**
         * Toggles a CSS class from the output (js_vars) parameter.
         *
         * @since 3.0.21
         * @param {Object} output - The output (js_vars) argument.
         * @param {mixed}  value - The value.
         */
        fromOutput: function( output, value, oldVal ) {

            if ( 'undefined' === typeof output.element) {
                return;
            }

            oldVal = xirkiPostMessage.util.processValue( output, oldVal );
            value = xirkiPostMessage.util.processValue( output, value );

            xirkiPostMessage.windows.get().forEach((win) => {
                win.jQuery(output.element).removeClass(oldVal).addClass(value);
            });
        }
    },

    /**
     * Switch element dark / light CSS class based on color field value
     *
     */
    dark_light_color_class: {

        /**
         * Switch element dark / light CSS class based on color field value
         *
         * @since 3.0.21
         * @param {Object} output - The output (js_vars) argument.
         * @param {mixed}  value - The value.
         */
        fromOutput: function( output, value, oldVal, controlType ) {

            if ( 'undefined' === typeof output.element || controlType !== 'xirki-color') {
                return;
            }

            var dark_class = 'is-dark';
            var light_class = 'is-light';

            var old_color_is_light = xirkiPostMessage.util.colorIsLight(oldVal);
            var color_is_light = xirkiPostMessage.util.colorIsLight(value);

            if (old_color_is_light) {
                oldVal = light_class;
            }else{
                oldVal = dark_class;
            }

            if (color_is_light) {
                value = light_class;
            }else{
                value = dark_class;
            }

            oldVal = xirkiPostMessage.util.processValue( output, oldVal );
            value = xirkiPostMessage.util.processValue( output, value );

            xirkiPostMessage.windows.get().forEach((win) => {
                win.jQuery(output.element).removeClass(oldVal).addClass(value);
            });
        }
    }
};

jQuery( document ).ready( function() {

    xirkiPostMessage.stylesheet.load();

    _.each( xirkiPostMessageFields, function( field ) {
        wp.customize( field.settings, function( value ) {

            var oldVal = value();

            value.bind( function( newVal ) {
                var styles = '';

                _.each( field.js_vars, function( output ) {
                    if ( ! output.function || 'undefined' === typeof xirkiPostMessage[ output.function ] ) {
                        output.function = 'css';
                    }

                    if ( 'css' === output.function ) {
                        styles += xirkiPostMessage.css.fromOutput( output, newVal, field.type );
                    } else {
                        xirkiPostMessage[ output.function ].fromOutput( output, newVal, oldVal, field.type );
                    }
                } );

                xirkiPostMessage.styleTag.addData( field.id, styles );

                oldVal = newVal;
            } );
        } );
    } );
} );
