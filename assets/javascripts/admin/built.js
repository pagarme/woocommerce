;(function(context, $) {

    'use strict';

    var MONSTER = function(namespace, callback) {
        var parts  = namespace.split( '\.' )
          , parent = context
          , count  = parts.length
          , i      = 0
        ;

        for ( i; i < count; i++ ) {
            parent[parts[i]] = ( count - 1 === i ) ? MONSTER.Builder() : parent[parts[i]] || {};
            parent           = parent[parts[i]];
        }

        if ( 'function' === typeof callback ) {

            if ( !~namespace.indexOf( 'Components' ) ) {
                parent.click = MONSTER.click.bind( parent );
                parent.ajax  = MONSTER.ajax.bind( parent );
            }

            callback.call( null, parent, $, MONSTER.utils );
        }

        return parent;
    };

    MONSTER.getElements = function(context) {
        var elements  = {}
          , byElement = this.byElement.bind( context )
        ;

        context.find( '[data-element]' ).each(function(index, element) {
            var name = this.utils.toDataSetName( element.dataset.element );

            if ( !elements[name] ) {
                elements[name] = byElement( element.dataset.element );
            }
        }.bind( this ) );

        return elements;
    };

    MONSTER.byAction = function(name, el) {
        var container = ( el || this );
        return container.find( '[data-action="' + name + '"]' );
    };

    MONSTER.byElement = function(name) {
        return this.find( '[data-element="' + name + '"]' );
    };

    MONSTER.event = function(instance, event, action) {
        var handle = MONSTER.utils.getEventCallbackName( action, event );
        this.byAction( action ).on( event, $.proxy( instance, handle ) );
    };

    MONSTER.getInstance = function(instance, context) {
        context.byAction   = this.byAction.bind( context );
        instance.$el       = context;
        instance.data      = context.data();
        instance.on        = this.event.bind( context, instance );
        instance.elements  = this.getElements( context );
        instance.addPrefix = this.utils.addPrefix;
        instance.prefix    = this.utils.prefix();
        instance.ajax      = this.ajax.bind( instance );
        instance.click     = this.click.bind( instance );

        return instance;
    };

    MONSTER.Builder = function() {
        var Kernel, Builder;
        var self = this;

        Kernel  = function() {};
        Builder = function(context) {
            var instance = new Kernel();
            instance     = self.getInstance( instance, context );

            instance.start.apply( instance, arguments );

            return instance;
        };

        Builder.fn       = Builder.prototype;
        Kernel.prototype = Builder.fn;
        Builder.fn.start = function() {};

        return Builder;
    };

    MONSTER.ajax = function(options, done, fail) {
        var ajax
          , defaults = {
            method : 'POST',
            url    : MONSTER.utils.getAjaxUrl(),
            data   : {}
        };

        ajax = $.ajax( $.extend( defaults, ( options || {} ) ) );

        ajax.done( $.proxy( this, ( done || '_done' ) ) );
        ajax.fail( $.proxy( this, ( fail || '_fail' ) ) );
    };

    MONSTER.click = function(action, context) {
        var instance = ( context || this );
        MONSTER.byAction( action, instance.$el ).on( 'click', $.proxy( instance, MONSTER.utils.getEventCallbackName( action ) ) );
    };

    MONSTER.utils = {

        getGlobalVars: function(name) {
            return ( window.MundiPaggGlobalVars || {} )[name];
        },

        prefix: function() {
            return ( this.getGlobalVars( 'prefix' ) || 'monster' )
        },

        getAjaxUrl: function() {
            return this.getGlobalVars( 'ajaxUrl' );
        },

        getLocale: function() {
            return this.getGlobalVars( 'WPLANG' );
        },

        getSpinnerUrl: function() {
            return this.getGlobalVars( 'spinnerUrl' );
        },

        getPathUrl: function(url) {
            return decodeURIComponent( url ).split(/[?#]/)[0];
        },

        getTime: function() {
            return ( new Date() ).getTime();
        },

        encodeUrl: function(url) {
            return encodeURIComponent( url );
        },

        decodeUrl: function(url) {
            return decodeURIComponent( url );
        },

        ucfirst: function(text) {
            return this.parseName( text, /(\b[a-z])/g );
        },

        toDataSetName: function(text) {
            return this.parseName( text, /(-)\w/g );
        },

        hasParam: function() {
            return ~window.location.href.indexOf( '?' );
        },

        getPathName: function() {
            return window.location.pathname;
        },

        getEventCallbackName: function(action, event) {
            return this.ucfirst( [ '_on', ( event || 'click' ), action ].join( '-' ) );
        },

        addPrefix: function(tag, separator) {
            var sep = ( separator || '-' );
            return this.prefix() + sep + tag;
        },

        getSpinner: function() {
            var img       = document.createElement( 'img' );
            img.src       = this.getSpinnerUrl();
            img.className = this.prefix() + '-spinner';

            return img;
        },

        isMobile: function() {
            return ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|Tablet OS|IEMobile|Opera Mini/i.test(
                navigator.userAgent
            ) );
        },

        parseName: function(text, regex) {
            return text.replace( regex, function(match) {
                return match.toUpperCase();
            }).replace( /-/g, '' );
        },

        remove: function(element) {
            element.fadeOut( 'fast', function() {
                element.remove();
            });
        },

        getId: function(id) {
            if ( !id ) {
                return false;
            }

            return document.getElementById( id );
        },

        findComponent: function(selector, callback) {
            var components = $(this).find( selector );

            if ( components.length && typeof callback === 'function' ) {
                callback.call( null, components, $(this) );
            }

            return components.length;
        },

        get: function(key, defaultVal) {
            var query, vars, varsLength, pair, i;

            if ( !this.hasParam() ) {
                return ( defaultVal || '' );
            }

            query      = window.location.search.substring(1);
            vars       = query.split( '&' );
            varsLength = vars.length;

            for ( i = 0; i < varsLength; i++ ) {
                pair = vars[i].split( '=' );

                if ( pair[0] === key ) {
                    return pair[1];
                }
            }

            return ( defaultVal || '' );
        },

        strToCode: function(str) {
            var hash   = 0
              , strLen = str.length
              , i
              , chr
            ;

            if ( !strLen ) {
                return hash;
            }

            for ( i = 0; i < strLen; i++ ) {
                chr   = str.charCodeAt( i );
                hash  = ( ( hash << 5 ) - hash ) + chr;
                hash |= 0;
            }

            return Math.abs( hash );
        }
    };

    context.MONSTER = MONSTER;

})( window, jQuery );
;MONSTER( 'Mundipagg.BuildComponents', function(Model, $, utils) {

	Model.create = function(container) {
		var components    = '[data-' + utils.addPrefix( 'component' ) + ']'
		  , findComponent = utils.findComponent.bind( container )
		;

		findComponent( components, $.proxy( this, '_start' ) );
	};

	Model._start = function(components) {
		if ( typeof Mundipagg.Components === 'undefined' ) {
			return;
		}

		this._iterator( components );
	};

	Model._iterator = function(components) {
		var name;

		components.each( function(index, component) {
			component = $( component );
			name      = utils.ucfirst( this.getComponent( component ) );
			this._callback( name, component );
		}.bind( this ) );
	};

	Model.getComponent = function(component) {
		var component = component.data( utils.addPrefix( 'component' ) );

		if ( !component ) {
			return '';
		}

		return component;
	};

	Model._callback = function(name, component) {
		var callback = Mundipagg.Components[name];

		if ( typeof callback == 'function' ) {
			callback.call( null, component );
			return;
		}

		console.log( 'Component "' + name + '" is not a function.' );
	};

}, {} );
;MONSTER( 'Mundipagg.BuildCreate', function(Model, $, utils) {

	Model.init = function(container, names) {
		if ( !names.length ) {
			return;
		}

		this.$el = container;
		names.forEach( this.findNames.bind( this ) );
	};

	Model.findNames = function(name, index) {
		this.callback( Mundipagg[utils.ucfirst( name )] );
	};

	Model.callback = function(callback) {
		if ( typeof callback !== 'function' ) {
			return;
		}

		callback.create( this.$el );
	};

}, {} );
;/**
 * jquery.mask.js
 * @version: v1.14.10
 * @author: Igor Escobar
 *
 * Created by Igor Escobar on 2012-03-10. Please report any bug at http://blog.igorescobar.com
 *
 * Copyright (c) 2012 Igor Escobar http://blog.igorescobar.com
 *
 * The MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

/* jshint laxbreak: true */
/* jshint maxcomplexity:17 */
/* global define */

'use strict';

// UMD (Universal Module Definition) patterns for JavaScript modules that work everywhere.
// https://github.com/umdjs/umd/blob/master/jqueryPluginCommonjs.js
(function (factory, jQuery, Zepto) {

    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    } else {
        factory(jQuery || Zepto);
    }

}(function ($) {

    var Mask = function (el, mask, options) {

        var p = {
            invalid: [],
            getCaret: function () {
                try {
                    var sel,
                        pos = 0,
                        ctrl = el.get(0),
                        dSel = document.selection,
                        cSelStart = ctrl.selectionStart;

                    // IE Support
                    if (dSel && navigator.appVersion.indexOf('MSIE 10') === -1) {
                        sel = dSel.createRange();
                        sel.moveStart('character', -p.val().length);
                        pos = sel.text.length;
                    }
                    // Firefox support
                    else if (cSelStart || cSelStart === '0') {
                        pos = cSelStart;
                    }

                    return pos;
                } catch (e) {}
            },
            setCaret: function(pos) {
                try {
                    if (el.is(':focus')) {
                        var range, ctrl = el.get(0);

                        // Firefox, WebKit, etc..
                        if (ctrl.setSelectionRange) {
                            ctrl.setSelectionRange(pos, pos);
                        } else { // IE
                            range = ctrl.createTextRange();
                            range.collapse(true);
                            range.moveEnd('character', pos);
                            range.moveStart('character', pos);
                            range.select();
                        }
                    }
                } catch (e) {}
            },
            events: function() {
                el
                .on('keydown.mask', function(e) {
                    el.data('mask-keycode', e.keyCode || e.which);
                    el.data('mask-previus-value', el.val());
                })
                .on($.jMaskGlobals.useInput ? 'input.mask' : 'keyup.mask', p.behaviour)
                .on('paste.mask drop.mask', function() {
                    setTimeout(function() {
                        el.keydown().keyup();
                    }, 100);
                })
                .on('change.mask', function(){
                    el.data('changed', true);
                })
                .on('blur.mask', function(){
                    if (oldValue !== p.val() && !el.data('changed')) {
                        el.trigger('change');
                    }
                    el.data('changed', false);
                })
                // it's very important that this callback remains in this position
                // otherwhise oldValue it's going to work buggy
                .on('blur.mask', function() {
                    oldValue = p.val();
                })
                // select all text on focus
                .on('focus.mask', function (e) {
                    if (options.selectOnFocus === true) {
                        $(e.target).select();
                    }
                })
                // clear the value if it not complete the mask
                .on('focusout.mask', function() {
                    if (options.clearIfNotMatch && !regexMask.test(p.val())) {
                       p.val('');
                   }
                });
            },
            getRegexMask: function() {
                var maskChunks = [], translation, pattern, optional, recursive, oRecursive, r;

                for (var i = 0; i < mask.length; i++) {
                    translation = jMask.translation[mask.charAt(i)];

                    if (translation) {

                        pattern = translation.pattern.toString().replace(/.{1}$|^.{1}/g, '');
                        optional = translation.optional;
                        recursive = translation.recursive;

                        if (recursive) {
                            maskChunks.push(mask.charAt(i));
                            oRecursive = {digit: mask.charAt(i), pattern: pattern};
                        } else {
                            maskChunks.push(!optional && !recursive ? pattern : (pattern + '?'));
                        }

                    } else {
                        maskChunks.push(mask.charAt(i).replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'));
                    }
                }

                r = maskChunks.join('');

                if (oRecursive) {
                    r = r.replace(new RegExp('(' + oRecursive.digit + '(.*' + oRecursive.digit + ')?)'), '($1)?')
                         .replace(new RegExp(oRecursive.digit, 'g'), oRecursive.pattern);
                }

                return new RegExp(r);
            },
            destroyEvents: function() {
                el.off(['input', 'keydown', 'keyup', 'paste', 'drop', 'blur', 'focusout', ''].join('.mask '));
            },
            val: function(v) {
                var isInput = el.is('input'),
                    method = isInput ? 'val' : 'text',
                    r;

                if (arguments.length > 0) {
                    if (el[method]() !== v) {
                        el[method](v);
                    }
                    r = el;
                } else {
                    r = el[method]();
                }

                return r;
            },
            calculateCaretPosition: function(caretPos, newVal) {
                var newValL = newVal.length,
                    oValue  = el.data('mask-previus-value') || '',
                    oValueL = oValue.length;

                // edge cases when erasing digits
                if (el.data('mask-keycode') === 8 && oValue !== newVal) {
                    caretPos = caretPos - (newVal.slice(0, caretPos).length - oValue.slice(0, caretPos).length);

                // edge cases when typing new digits
                } else if (oValue !== newVal) {
                    // if the cursor is at the end keep it there
                    if (caretPos >= oValueL) {
                        caretPos = newValL;
                    } else {
                        caretPos = caretPos + (newVal.slice(0, caretPos).length - oValue.slice(0, caretPos).length);
                    }
                }

                return caretPos;
            },
            behaviour: function(e) {
                e = e || window.event;
                p.invalid = [];

                var keyCode = el.data('mask-keycode');

                if ($.inArray(keyCode, jMask.byPassKeys) === -1) {
                    var newVal   = p.getMasked(),
                        caretPos = p.getCaret();

                    setTimeout(function(caretPos, newVal) {
                      p.setCaret(p.calculateCaretPosition(caretPos, newVal));
                    }, 10, caretPos, newVal);

                    p.val(newVal);
                    p.setCaret(caretPos);
                    return p.callbacks(e);
                }
            },
            getMasked: function(skipMaskChars, val) {
                var buf = [],
                    value = val === undefined ? p.val() : val + '',
                    m = 0, maskLen = mask.length,
                    v = 0, valLen = value.length,
                    offset = 1, addMethod = 'push',
                    resetPos = -1,
                    lastMaskChar,
                    check;

                if (options.reverse) {
                    addMethod = 'unshift';
                    offset = -1;
                    lastMaskChar = 0;
                    m = maskLen - 1;
                    v = valLen - 1;
                    check = function () {
                        return m > -1 && v > -1;
                    };
                } else {
                    lastMaskChar = maskLen - 1;
                    check = function () {
                        return m < maskLen && v < valLen;
                    };
                }

                var lastUntranslatedMaskChar;
                while (check()) {
                    var maskDigit = mask.charAt(m),
                        valDigit = value.charAt(v),
                        translation = jMask.translation[maskDigit];

                    if (translation) {
                        if (valDigit.match(translation.pattern)) {
                            buf[addMethod](valDigit);
                             if (translation.recursive) {
                                if (resetPos === -1) {
                                    resetPos = m;
                                } else if (m === lastMaskChar) {
                                    m = resetPos - offset;
                                }

                                if (lastMaskChar === resetPos) {
                                    m -= offset;
                                }
                            }
                            m += offset;
                        } else if (valDigit === lastUntranslatedMaskChar) {
                            // matched the last untranslated (raw) mask character that we encountered
                            // likely an insert offset the mask character from the last entry; fall
                            // through and only increment v
                            lastUntranslatedMaskChar = undefined;
                        } else if (translation.optional) {
                            m += offset;
                            v -= offset;
                        } else if (translation.fallback) {
                            buf[addMethod](translation.fallback);
                            m += offset;
                            v -= offset;
                        } else {
                          p.invalid.push({p: v, v: valDigit, e: translation.pattern});
                        }
                        v += offset;
                    } else {
                        if (!skipMaskChars) {
                            buf[addMethod](maskDigit);
                        }

                        if (valDigit === maskDigit) {
                            v += offset;
                        } else {
                            lastUntranslatedMaskChar = maskDigit;
                        }

                        m += offset;
                    }
                }

                var lastMaskCharDigit = mask.charAt(lastMaskChar);
                if (maskLen === valLen + 1 && !jMask.translation[lastMaskCharDigit]) {
                    buf.push(lastMaskCharDigit);
                }

                return buf.join('');
            },
            callbacks: function (e) {
                var val = p.val(),
                    changed = val !== oldValue,
                    defaultArgs = [val, e, el, options],
                    callback = function(name, criteria, args) {
                        if (typeof options[name] === 'function' && criteria) {
                            options[name].apply(this, args);
                        }
                    };

                callback('onChange', changed === true, defaultArgs);
                callback('onKeyPress', changed === true, defaultArgs);
                callback('onComplete', val.length === mask.length, defaultArgs);
                callback('onInvalid', p.invalid.length > 0, [val, e, el, p.invalid, options]);
            }
        };

        el = $(el);
        var jMask = this, oldValue = p.val(), regexMask;

        mask = typeof mask === 'function' ? mask(p.val(), undefined, el,  options) : mask;

        // public methods
        jMask.mask = mask;
        jMask.options = options;
        jMask.remove = function() {
            var caret = p.getCaret();
            p.destroyEvents();
            p.val(jMask.getCleanVal());
            p.setCaret(caret);
            return el;
        };

        // get value without mask
        jMask.getCleanVal = function() {
           return p.getMasked(true);
        };

        // get masked value without the value being in the input or element
        jMask.getMaskedVal = function(val) {
           return p.getMasked(false, val);
        };

       jMask.init = function(onlyMask) {
            onlyMask = onlyMask || false;
            options = options || {};

            jMask.clearIfNotMatch  = $.jMaskGlobals.clearIfNotMatch;
            jMask.byPassKeys       = $.jMaskGlobals.byPassKeys;
            jMask.translation      = $.extend({}, $.jMaskGlobals.translation, options.translation);

            jMask = $.extend(true, {}, jMask, options);

            regexMask = p.getRegexMask();

            if (onlyMask) {
                p.events();
                p.val(p.getMasked());
            } else {
                if (options.placeholder) {
                    el.attr('placeholder' , options.placeholder);
                }

                // this is necessary, otherwise if the user submit the form
                // and then press the "back" button, the autocomplete will erase
                // the data. Works fine on IE9+, FF, Opera, Safari.
                if (el.data('mask')) {
                  el.attr('autocomplete', 'off');
                }

                // detect if is necessary let the user type freely.
                // for is a lot faster than forEach.
                for (var i = 0, maxlength = true; i < mask.length; i++) {
                    var translation = jMask.translation[mask.charAt(i)];
                    if (translation && translation.recursive) {
                        maxlength = false;
                        break;
                    }
                }

                if (maxlength) {
                    el.attr('maxlength', mask.length);
                }

                p.destroyEvents();
                p.events();

                var caret = p.getCaret();
                p.val(p.getMasked());
                p.setCaret(caret);
            }
        };

        jMask.init(!el.is('input'));
    };

    $.maskWatchers = {};
    var HTMLAttributes = function () {
        var input = $(this),
            options = {},
            prefix = 'data-mask-',
            mask = input.attr('data-mask');

        if (input.attr(prefix + 'reverse')) {
            options.reverse = true;
        }

        if (input.attr(prefix + 'clearifnotmatch')) {
            options.clearIfNotMatch = true;
        }

        if (input.attr(prefix + 'selectonfocus') === 'true') {
           options.selectOnFocus = true;
        }

        if (notSameMaskObject(input, mask, options)) {
            return input.data('mask', new Mask(this, mask, options));
        }
    },
    notSameMaskObject = function(field, mask, options) {
        options = options || {};
        var maskObject = $(field).data('mask'),
            stringify = JSON.stringify,
            value = $(field).val() || $(field).text();
        try {
            if (typeof mask === 'function') {
                mask = mask(value);
            }
            return typeof maskObject !== 'object' || stringify(maskObject.options) !== stringify(options) || maskObject.mask !== mask;
        } catch (e) {}
    },
    eventSupported = function(eventName) {
        var el = document.createElement('div'), isSupported;

        eventName = 'on' + eventName;
        isSupported = (eventName in el);

        if ( !isSupported ) {
            el.setAttribute(eventName, 'return;');
            isSupported = typeof el[eventName] === 'function';
        }
        el = null;

        return isSupported;
    };

    $.fn.mask = function(mask, options) {
        options = options || {};
        var selector = this.selector,
            globals = $.jMaskGlobals,
            interval = globals.watchInterval,
            watchInputs = options.watchInputs || globals.watchInputs,
            maskFunction = function() {
                if (notSameMaskObject(this, mask, options)) {
                    return $(this).data('mask', new Mask(this, mask, options));
                }
            };

        $(this).each(maskFunction);

        if (selector && selector !== '' && watchInputs) {
            clearInterval($.maskWatchers[selector]);
            $.maskWatchers[selector] = setInterval(function(){
                $(document).find(selector).each(maskFunction);
            }, interval);
        }
        return this;
    };

    $.fn.masked = function(val) {
        return this.data('mask').getMaskedVal(val);
    };

    $.fn.unmask = function() {
        clearInterval($.maskWatchers[this.selector]);
        delete $.maskWatchers[this.selector];
        return this.each(function() {
            var dataMask = $(this).data('mask');
            if (dataMask) {
                dataMask.remove().removeData('mask');
            }
        });
    };

    $.fn.cleanVal = function() {
        return this.data('mask').getCleanVal();
    };

    $.applyDataMask = function(selector) {
        selector = selector || $.jMaskGlobals.maskElements;
        var $selector = (selector instanceof $) ? selector : $(selector);
        $selector.filter($.jMaskGlobals.dataMaskAttr).each(HTMLAttributes);
    };

    var globals = {
        maskElements: 'input,td,span,div',
        dataMaskAttr: '*[data-mask]',
        dataMask: true,
        watchInterval: 300,
        watchInputs: true,
        // old versions of chrome dont work great with input event
        useInput: !/Chrome\/[2-4][0-9]|SamsungBrowser/.test(window.navigator.userAgent) && eventSupported('input'),
        watchDataMask: false,
        byPassKeys: [9, 16, 17, 18, 36, 37, 38, 39, 40, 91],
        translation: {
            '0': {pattern: /\d/},
            '9': {pattern: /\d/, optional: true},
            '#': {pattern: /\d/, recursive: true},
            'A': {pattern: /[a-zA-Z0-9]/},
            'S': {pattern: /[a-zA-Z]/}
        }
    };

    $.jMaskGlobals = $.jMaskGlobals || {};
    globals = $.jMaskGlobals = $.extend(true, {}, globals, $.jMaskGlobals);

    // looking for inputs with data-mask attribute
    if (globals.dataMask) {
        $.applyDataMask();
    }

    setInterval(function() {
        if ($.jMaskGlobals.watchDataMask) {
            $.applyDataMask();
        }
    }, globals.watchInterval);
}, window.jQuery, window.Zepto));
;;(function($) {

	$.fn.isEmptyValue = function() {
		return !( $.trim( this.val() ) );
	};

})( jQuery );;MONSTER( 'Mundipagg.Application', function(Model, $, utils) {

	var createNames = [
		// Name for instance method create() if not component
	];

	Model.init = function(container) {
		Model.setArrayIncludesPolyfill();
		Mundipagg.BuildComponents.create( container );
		Mundipagg.BuildCreate.init( container, createNames );
	};

	Model.setArrayIncludesPolyfill = function() {
		if ( ! Array.prototype.includes ) {
			Object.defineProperty( Array.prototype, 'includes', {
				value: function(searchElement, fromIndex) {

					if ( this == null ) {
						throw new TypeError('"this" is null or not defined');
					}

					var o   = Object(this);
					var len = o.length >>> 0;

					if ( len === 0 ) {
						return false;
					}

					var n = fromIndex | 0;
					var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

					while ( k < len ) {
						if (o[k] === searchElement) {
							return true;
						}
						k++;
					}

					return false;
				}
			});
		}
	};

});
;MONSTER( 'Mundipagg.Components.Settings', function(Model, $, Utils) {

	var errorClass = Utils.addPrefix( 'field-error' );

	Model.fn.start = function() {
		this.init();
	};

	Model.fn.init = function() {
		this.sandboxSecretKey             = $( '[data-field="sandbox-secret-key"]' );
		this.sandboxPublicKey             = $( '[data-field="sandbox-public-key"]' );
		this.productionSecretKey          = $( '[data-field="production-secret-key"]' );
		this.productionPublicKey          = $( '[data-field="production-public-key"]' );
		this.installments                 = $( '[data-field="installments"]' );
		this.billet                       = $( '[data-field="billet"]' );
		this.installmentsMax              = $( '[data-field="installments-maximum"]' );
		this.installmentsInterest         = $( '[data-field="installments-interest"]' );
		this.installmentsByFlag           = $( '[data-field="installments-by-flag"]' );
		this.installmentsWithoutInterest  = $( '[data-field="installments-without-interest"]' );
		this.installmentsInterestIncrease = $( '[data-field="installments-interest-increase"]' );

		this.handleEnvironmentFieldsVisibility( this.elements.environmentSelect.val() );
		this.handleInstallmentFieldsVisibility( this.elements.installmentsTypeSelect.val() );

		this.setInstallmentsByFlags( null, true );

		this.addEventListener();
	};

	Model.fn.addEventListener = function() {
		this.on( 'keyup', 'soft-descriptor' );
		this.on( 'change', 'environment' );
		this.on( 'change', 'installments-type' );

		this.elements.flagsSelect.on( 'select2:unselecting', this._onChangeFlags.bind(this) );
		this.elements.flagsSelect.on( 'select2:selecting', this._onChangeFlags.bind(this) );

		$( '#mainform' ).on( 'submit', this._onSubmitForm.bind( this ) );
	};

	Model.fn._onKeyupSoftDescriptor = function( event ) {
		if ( event.currentTarget.value.length > 13 ) {
			$( event.currentTarget ).addClass( errorClass );
			return;
		}

		$( event.currentTarget ).removeClass( errorClass );
	};

	Model.fn._onSubmitForm = function( event ) {
		this.toTop = false;
		this.items = [];

		this.elements.validate.each( this._eachValidate.bind( this ) );

		return !~this.items.indexOf( true );
	};

	Model.fn._onChangeEnvironment = function( event ) {
		this.handleEnvironmentFieldsVisibility( event.currentTarget.value );
	};

	Model.fn._onChangeInstallmentsType = function( event ) {
		this.handleInstallmentFieldsVisibility( event.currentTarget.value );
	};

	Model.fn._onChangeFlags = function( event ) {
		this.setInstallmentsByFlags( event, false );
	};

	Model.fn._eachValidate = function( index, field ) {
		var rect;
		var element = $( field )
		  , empty   = element.isEmptyValue()
		  , func    = empty ? 'addClass' : 'removeClass'
		;

		if ( ! element.is( ':visible' ) ) {
			return;
		}

		element[func]( errorClass );

		this.items[index] = empty;

		if ( ! empty ) {
			return;
		}

		field.placeholder = field.dataset.errorMsg;

		if ( ! this.toTop ) {
			this.toTop = true;
			rect       = field.getBoundingClientRect();
			window.scrollTo( 0, ( rect.top + window.scrollY ) - 32 );
		}
	};

	Model.fn.handleEnvironmentFieldsVisibility = function( value ) {
		var sandboxPublicKeyContainer    = this.sandboxPublicKey.closest( 'tr' )
		  , sandboxSecretKeyContainer 	 = this.sandboxSecretKey.closest( 'tr' )
		  , productionPublicKeyContainer = this.productionPublicKey.closest( 'tr' )
		  , productionSecretKeyContainer = this.productionSecretKey.closest( 'tr' )
		;

		if ( value == 'sandbox' ) {
			sandboxPublicKeyContainer.show();
			sandboxSecretKeyContainer.show();
			productionPublicKeyContainer.hide();
			productionSecretKeyContainer.hide();
		} else {
			productionPublicKeyContainer.show();
			productionSecretKeyContainer.show();
			sandboxPublicKeyContainer.hide();
			sandboxSecretKeyContainer.hide();
		}
	};

	Model.fn.handleInstallmentFieldsVisibility = function( value ) {
		var installmentsMaxContainer      		  = this.installmentsMax.closest( 'tr' )
		  , installmentsInterestContainer 		  = this.installmentsInterest.closest( 'tr' )
		  , installmentsByFlagContainer   		  = this.installmentsByFlag.closest( 'tr' )
		  , installmentsWithoutInterestContainer  = this.installmentsWithoutInterest.closest( 'tr' )
		  , installmentsInterestIncreaseContainer = this.installmentsInterestIncrease.closest( 'tr' )
		;

		if ( value == 1 ) {
			installmentsMaxContainer.show();
			installmentsInterestContainer.show();
			installmentsInterestIncreaseContainer.show();
			installmentsWithoutInterestContainer.show();
			installmentsByFlagContainer.hide();
		} else {
			if ( this.elements.flagsSelect.val() ) {
				installmentsByFlagContainer.show();
			}
			installmentsMaxContainer.hide();
			installmentsInterestContainer.hide();
			installmentsInterestIncreaseContainer.hide();
			installmentsWithoutInterestContainer.hide();
		}
	};

	Model.fn.setInstallmentsByFlags = function( event, firstLoad ) {
		var flags        = this.elements.flagsSelect.val() || [];
		var flagsWrapper = this.installmentsByFlag.closest( 'tr' );

		if ( ! firstLoad ) {
			var selectedItem = event.params.args.data.id;
			var filtered     = flags;

			flagsWrapper.show();

			if ( event.params.name == 'unselect' ) {
				filtered = flags.filter(function(i) {
					return i != selectedItem;
				});

				if ( filtered.length == 0 ) {
					this.installmentsByFlag.closest( 'tr' ).hide();
				}
			} else {
				filtered.push( selectedItem );
			}

			filtered.map(function(item) {
				var element = $( '[data-flag=' + selectedItem + ']' );
				if ( ! filtered.includes( selectedItem ) ) {
					element.hide();
				} else {
					element.show();
				}
			});
		} else {
			var selector = $( '[data-flag]' );

			if ( flags.length === 0 ) {
				selector.hide();
				flagsWrapper.hide();
				return;
			}

			selector.each(function(index, item) {
				item = $(item);
				if ( ! flags.includes( item.data( 'flag' ) ) ) {
					item.hide();
				} else {
					item.show();
				}
			});
		}
	};

});
;jQuery(function($) {
	var context = $( 'body' );

	Mundipagg.vars = {
		body : context
	};

	Mundipagg.Application.init.apply( null, [context] );
});
