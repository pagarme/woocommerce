;
(function(context, $) {

    'use strict';

    var MONSTER = function(namespace, callback) {
        var parts = namespace.split('\.'),
            parent = context,
            count = parts.length,
            i = 0;

        for (i; i < count; i++) {
            parent[parts[i]] = (count - 1 === i) ? MONSTER.Builder() : parent[parts[i]] || {};
            parent = parent[parts[i]];
        }

        if ('function' === typeof callback) {

            if (!~namespace.indexOf('Components')) {
                parent.click = MONSTER.click.bind(parent);
                parent.ajax = MONSTER.ajax.bind(parent);
            }

            callback.call(null, parent, $, MONSTER.utils);
        }

        return parent;
    };

    MONSTER.getElements = function(context) {
        var elements = {},
            byElement = this.byElement.bind(context);

        context.find('[data-element]').each(function(index, element) {
            var name = this.utils.toDataSetName(element.dataset.element);

            if (!elements[name]) {
                elements[name] = byElement(element.dataset.element);
            }
        }.bind(this));

        return elements;
    };

    MONSTER.byAction = function(name, el) {
        var container = (el || this);
        return container.find('[data-action="' + name + '"]');
    };

    MONSTER.byElement = function(name) {
        return this.find('[data-element="' + name + '"]');
    };

    MONSTER.event = function(instance, event, action) {
        var handle = MONSTER.utils.getEventCallbackName(action, event);
        this.byAction(action).on(event, $.proxy(instance, handle));
    };

    MONSTER.getInstance = function(instance, context) {
        context.byAction = this.byAction.bind(context);
        instance.$el = context;
        instance.data = context.data();
        instance.on = this.event.bind(context, instance);
        instance.elements = this.getElements(context);
        instance.addPrefix = this.utils.addPrefix;
        instance.prefix = this.utils.prefix();
        instance.ajax = this.ajax.bind(instance);
        instance.click = this.click.bind(instance);

        return instance;
    };

    MONSTER.Builder = function() {
        var Kernel, Builder;
        var self = this;

        Kernel = function() {};
        Builder = function(context) {
            var instance = new Kernel();
            instance = self.getInstance(instance, context);

            instance.start.apply(instance, arguments);

            return instance;
        };

        Builder.fn = Builder.prototype;
        Kernel.prototype = Builder.fn;
        Builder.fn.start = function() {};

        return Builder;
    };

    MONSTER.ajax = function(options, done, fail) {
        var ajax, defaults = {
            method: 'POST',
            url: MONSTER.utils.getAjaxUrl(),
            data: {}
        };

        ajax = $.ajax($.extend(defaults, (options || {})));

        ajax.done($.proxy(this, (done || '_done')));
        ajax.fail($.proxy(this, (fail || '_fail')));
    };

    MONSTER.click = function(action, context) {
        var instance = (context || this);
        MONSTER.byAction(action, instance.$el).on('click', $.proxy(instance, MONSTER.utils.getEventCallbackName(action)));
    };

    MONSTER.utils = {

        getGlobalVars: function(name) {
            return (window.PagarmeGlobalVars || {})[name];
        },

        prefix: function() {
            return (this.getGlobalVars('prefix') || 'monster')
        },

        getAjaxUrl: function() {
            return this.getGlobalVars('ajaxUrl');
        },

        getLocale: function() {
            return this.getGlobalVars('WPLANG');
        },

        getSpinnerUrl: function() {
            return this.getGlobalVars('spinnerUrl');
        },

        getPathUrl: function(url) {
            return decodeURIComponent(url).split(/[?#]/)[0];
        },

        getTime: function() {
            return (new Date()).getTime();
        },

        encodeUrl: function(url) {
            return encodeURIComponent(url);
        },

        decodeUrl: function(url) {
            return decodeURIComponent(url);
        },

        ucfirst: function(text) {
            return this.parseName(text, /(\b[a-z])/g);
        },

        toDataSetName: function(text) {
            return this.parseName(text, /(-)\w/g);
        },

        hasParam: function() {
            return ~window.location.href.indexOf('?');
        },

        getPathName: function() {
            return window.location.pathname;
        },

        getEventCallbackName: function(action, event) {
            return this.ucfirst(['_on', (event || 'click'), action].join('-'));
        },

        addPrefix: function(tag, separator) {
            var sep = (separator || '-');
            return this.prefix() + sep + tag;
        },

        getSpinner: function() {
            var img = document.createElement('img');
            img.src = this.getSpinnerUrl();
            img.className = this.prefix() + '-spinner';

            return img;
        },

        isMobile: function() {
            return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|Tablet OS|IEMobile|Opera Mini/i.test(
                navigator.userAgent
            ));
        },

        parseName: function(text, regex) {
            return text.replace(regex, function(match) {
                return match.toUpperCase();
            }).replace(/-/g, '');
        },

        remove: function(element) {
            element.fadeOut('fast', function() {
                element.remove();
            });
        },

        getId: function(id) {
            if (!id) {
                return false;
            }

            return document.getElementById(id);
        },

        findComponent: function(selector, callback) {
            var components = $(this).find(selector);

            if (components.length && typeof callback === 'function') {
                callback.call(null, components, $(this));
            }

            return components.length;
        },

        get: function(key, defaultVal) {
            var query, vars, varsLength, pair, i;

            if (!this.hasParam()) {
                return (defaultVal || '');
            }

            query = window.location.search.substring(1);
            vars = query.split('&');
            varsLength = vars.length;

            for (i = 0; i < varsLength; i++) {
                pair = vars[i].split('=');

                if (pair[0] === key) {
                    return pair[1];
                }
            }

            return (defaultVal || '');
        },

        strToCode: function(str) {
            var hash = 0,
                strLen = str.length,
                i, chr;

            if (!strLen) {
                return hash;
            }

            for (i = 0; i < strLen; i++) {
                chr = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0;
            }

            return Math.abs(hash);
        }
    };

    context.MONSTER = MONSTER;

})(window, jQuery);;
MONSTER('Pagarme.BuildComponents', function(Model, $, utils) {

    Model.create = function(container) {
        var components = '[data-' + utils.addPrefix('component') + ']',
            findComponent = utils.findComponent.bind(container);

        findComponent(components, $.proxy(this, '_start'));
    };

    Model._start = function(components) {
        if (typeof Pagarme.Components === 'undefined') {
            return;
        }

        this._iterator(components);
    };

    Model._iterator = function(components) {
        var name;

        components.each(function(index, component) {
            component = $(component);
            name = utils.ucfirst(this.getComponent(component));
            this._callback(name, component);
        }.bind(this));
    };

    Model.getComponent = function(component) {
        var component = component.data(utils.addPrefix('component'));

        if (!component) {
            return '';
        }

        return component;
    };

    Model._callback = function(name, component) {
        var callback = Pagarme.Components[name];

        if (typeof callback == 'function') {
            callback.call(null, component);
            return;
        }

        console.log('Component "' + name + '" is not a function.');
    };

}, {});;
MONSTER('Pagarme.BuildCreate', function(Model, $, utils) {

    Model.init = function(container, names) {
        if (!names.length) {
            return;
        }

        this.$el = container;
        names.forEach(this.findNames.bind(this));
    };

    Model.findNames = function(name, index) {
        this.callback(Pagarme[utils.ucfirst(name)]);
    };

    Model.callback = function(callback) {
        if (typeof callback !== 'function') {
            return;
        }

        callback.create(this.$el);
    };

}, {});;
/**
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
(function(factory, jQuery, Zepto) {

    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports === 'object') {
        module.exports = factory(require('jquery'));
    } else {
        factory(jQuery || Zepto);
    }

}(function($) {

    var Mask = function(el, mask, options) {

        var p = {
            invalid: [],
            getCaret: function() {
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
                    .on('change.mask', function() {
                        el.data('changed', true);
                    })
                    .on('blur.mask', function() {
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
                    .on('focus.mask', function(e) {
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
                var maskChunks = [],
                    translation, pattern, optional, recursive, oRecursive, r;

                for (var i = 0; i < mask.length; i++) {
                    translation = jMask.translation[mask.charAt(i)];

                    if (translation) {

                        pattern = translation.pattern.toString().replace(/.{1}$|^.{1}/g, '');
                        optional = translation.optional;
                        recursive = translation.recursive;

                        if (recursive) {
                            maskChunks.push(mask.charAt(i));
                            oRecursive = { digit: mask.charAt(i), pattern: pattern };
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
                    oValue = el.data('mask-previus-value') || '',
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
                    var newVal = p.getMasked(),
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
                    m = 0,
                    maskLen = mask.length,
                    v = 0,
                    valLen = value.length,
                    offset = 1,
                    addMethod = 'push',
                    resetPos = -1,
                    lastMaskChar,
                    check;

                if (options.reverse) {
                    addMethod = 'unshift';
                    offset = -1;
                    lastMaskChar = 0;
                    m = maskLen - 1;
                    v = valLen - 1;
                    check = function() {
                        return m > -1 && v > -1;
                    };
                } else {
                    lastMaskChar = maskLen - 1;
                    check = function() {
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
                            p.invalid.push({ p: v, v: valDigit, e: translation.pattern });
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
            callbacks: function(e) {
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
        var jMask = this,
            oldValue = p.val(),
            regexMask;

        mask = typeof mask === 'function' ? mask(p.val(), undefined, el, options) : mask;

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

            jMask.clearIfNotMatch = $.jMaskGlobals.clearIfNotMatch;
            jMask.byPassKeys = $.jMaskGlobals.byPassKeys;
            jMask.translation = $.extend({}, $.jMaskGlobals.translation, options.translation);

            jMask = $.extend(true, {}, jMask, options);

            regexMask = p.getRegexMask();

            if (onlyMask) {
                p.events();
                p.val(p.getMasked());
            } else {
                if (options.placeholder) {
                    el.attr('placeholder', options.placeholder);
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
    var HTMLAttributes = function() {
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
            var el = document.createElement('div'),
                isSupported;

            eventName = 'on' + eventName;
            isSupported = (eventName in el);

            if (!isSupported) {
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
            $.maskWatchers[selector] = setInterval(function() {
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
            '0': { pattern: /\d/ },
            '9': { pattern: /\d/, optional: true },
            '#': { pattern: /\d/, recursive: true },
            'A': { pattern: /[a-zA-Z0-9]/ },
            'S': { pattern: /[a-zA-Z]/ }
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
}, window.jQuery, window.Zepto));;;
(function($) {

    $.fn.isEmptyValue = function() {
        return !($.trim(this.val()));
    };

})(jQuery);;
/*!
 * sweetalert2 v6.6.2
 * Released under the MIT License.
 */
(function(global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
        (global.Sweetalert2 = factory());
}(this, (function() {
    'use strict';

    var defaultParams = {
        title: '',
        titleText: '',
        text: '',
        html: '',
        type: null,
        customClass: '',
        target: 'body',
        animation: true,
        allowOutsideClick: true,
        allowEscapeKey: true,
        allowEnterKey: true,
        showConfirmButton: true,
        showCancelButton: false,
        preConfirm: null,
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6',
        confirmButtonClass: null,
        cancelButtonText: 'Cancel',
        cancelButtonColor: '#aaa',
        cancelButtonClass: null,
        buttonsStyling: true,
        reverseButtons: false,
        focusCancel: false,
        showCloseButton: false,
        showLoaderOnConfirm: false,
        imageUrl: null,
        imageWidth: null,
        imageHeight: null,
        imageClass: null,
        timer: null,
        width: 500,
        padding: 20,
        background: '#fff',
        input: null,
        inputPlaceholder: '',
        inputValue: '',
        inputOptions: {},
        inputAutoTrim: true,
        inputClass: null,
        inputAttributes: {},
        inputValidator: null,
        progressSteps: [],
        currentProgressStep: null,
        progressStepsDistance: '40px',
        onOpen: null,
        onClose: null
    };

    var swalPrefix = 'swal2-';

    var prefix = function prefix(items) {
        var result = {};
        for (var i in items) {
            result[items[i]] = swalPrefix + items[i];
        }
        return result;
    };

    var swalClasses = prefix(['container', 'shown', 'iosfix', 'modal', 'overlay', 'fade', 'show', 'hide', 'noanimation', 'close', 'title', 'content', 'buttonswrapper', 'confirm', 'cancel', 'icon', 'image', 'input', 'file', 'range', 'select', 'radio', 'checkbox', 'textarea', 'inputerror', 'validationerror', 'progresssteps', 'activeprogressstep', 'progresscircle', 'progressline', 'loading', 'styled']);

    var iconTypes = prefix(['success', 'warning', 'info', 'question', 'error']);

    /*
     * Set hover, active and focus-states for buttons (source: http://www.sitepoint.com/javascript-generate-lighter-darker-color)
     */
    var colorLuminance = function colorLuminance(hex, lum) {
        // Validate hex string
        hex = String(hex).replace(/[^0-9a-f]/gi, '');
        if (hex.length < 6) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        lum = lum || 0;

        // Convert to decimal and change luminosity
        var rgb = '#';
        for (var i = 0; i < 3; i++) {
            var c = parseInt(hex.substr(i * 2, 2), 16);
            c = Math.round(Math.min(Math.max(0, c + c * lum), 255)).toString(16);
            rgb += ('00' + c).substr(c.length);
        }

        return rgb;
    };

    var uniqueArray = function uniqueArray(arr) {
        var result = [];
        for (var i in arr) {
            if (result.indexOf(arr[i]) === -1) {
                result.push(arr[i]);
            }
        }
        return result;
    };

    /* global MouseEvent */

    // Remember state in cases where opening and handling a modal will fiddle with it.
    var states = {
        previousWindowKeyDown: null,
        previousActiveElement: null,
        previousBodyPadding: null
    };

    /*
     * Add modal + overlay to DOM
     */
    var init = function init(params) {
        if (typeof document === 'undefined') {
            console.error('SweetAlert2 requires document to initialize');
            return;
        }

        var container = document.createElement('div');
        container.className = swalClasses.container;
        container.innerHTML = sweetHTML;

        var targetElement = document.querySelector(params.target);
        if (!targetElement) {
            console.warn('SweetAlert2: Can\'t find the target "' + params.target + '"');
            targetElement = document.body;
        }
        targetElement.appendChild(container);

        var modal = getModal();
        var input = getChildByClass(modal, swalClasses.input);
        var file = getChildByClass(modal, swalClasses.file);
        var range = modal.querySelector('.' + swalClasses.range + ' input');
        var rangeOutput = modal.querySelector('.' + swalClasses.range + ' output');
        var select = getChildByClass(modal, swalClasses.select);
        var checkbox = modal.querySelector('.' + swalClasses.checkbox + ' input');
        var textarea = getChildByClass(modal, swalClasses.textarea);

        input.oninput = function() {
            sweetAlert.resetValidationError();
        };

        input.onkeydown = function(event) {
            setTimeout(function() {
                if (event.keyCode === 13 && params.allowEnterKey) {
                    event.stopPropagation();
                    sweetAlert.clickConfirm();
                }
            }, 0);
        };

        file.onchange = function() {
            sweetAlert.resetValidationError();
        };

        range.oninput = function() {
            sweetAlert.resetValidationError();
            rangeOutput.value = range.value;
        };

        range.onchange = function() {
            sweetAlert.resetValidationError();
            range.previousSibling.value = range.value;
        };

        select.onchange = function() {
            sweetAlert.resetValidationError();
        };

        checkbox.onchange = function() {
            sweetAlert.resetValidationError();
        };

        textarea.oninput = function() {
            sweetAlert.resetValidationError();
        };

        return modal;
    };

    /*
     * Manipulate DOM
     */

    var sweetHTML = ('\n <div role="dialog" aria-labelledby="' + swalClasses.title + '" aria-describedby="' + swalClasses.content + '" class="' + swalClasses.modal + '" tabindex="-1">\n   <ul class="' + swalClasses.progresssteps + '"></ul>\n   <div class="' + swalClasses.icon + ' ' + iconTypes.error + '">\n     <span class="swal2-x-mark"><span class="swal2-x-mark-line-left"></span><span class="swal2-x-mark-line-right"></span></span>\n   </div>\n   <div class="' + swalClasses.icon + ' ' + iconTypes.question + '">?</div>\n   <div class="' + swalClasses.icon + ' ' + iconTypes.warning + '">!</div>\n   <div class="' + swalClasses.icon + ' ' + iconTypes.info + '">i</div>\n   <div class="' + swalClasses.icon + ' ' + iconTypes.success + '">\n     <div class="swal2-success-circular-line-left"></div>\n     <span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>\n     <div class="swal2-success-ring"></div> <div class="swal2-success-fix"></div>\n     <div class="swal2-success-circular-line-right"></div>\n   </div>\n   <img class="' + swalClasses.image + '">\n   <h2 class="' + swalClasses.title + '" id="' + swalClasses.title + '"></h2>\n   <div id="' + swalClasses.content + '" class="' + swalClasses.content + '"></div>\n   <input class="' + swalClasses.input + '">\n   <input type="file" class="' + swalClasses.file + '">\n   <div class="' + swalClasses.range + '">\n     <output></output>\n     <input type="range">\n   </div>\n   <select class="' + swalClasses.select + '"></select>\n   <div class="' + swalClasses.radio + '"></div>\n   <label for="' + swalClasses.checkbox + '" class="' + swalClasses.checkbox + '">\n     <input type="checkbox">\n   </label>\n   <textarea class="' + swalClasses.textarea + '"></textarea>\n   <div class="' + swalClasses.validationerror + '"></div>\n   <div class="' + swalClasses.buttonswrapper + '">\n     <button type="button" class="' + swalClasses.confirm + '">OK</button>\n     <button type="button" class="' + swalClasses.cancel + '">Cancel</button>\n   </div>\n   <button type="button" class="' + swalClasses.close + '" aria-label="Close this dialog">&times;</button>\n </div>\n').replace(/(^|\n)\s*/g, '');

    var getContainer = function getContainer() {
        return document.body.querySelector('.' + swalClasses.container);
    };

    var getModal = function getModal() {
        return getContainer() ? getContainer().querySelector('.' + swalClasses.modal) : null;
    };

    var getIcons = function getIcons() {
        var modal = getModal();
        return modal.querySelectorAll('.' + swalClasses.icon);
    };

    var elementByClass = function elementByClass(className) {
        return getContainer() ? getContainer().querySelector('.' + className) : null;
    };

    var getTitle = function getTitle() {
        return elementByClass(swalClasses.title);
    };

    var getContent = function getContent() {
        return elementByClass(swalClasses.content);
    };

    var getImage = function getImage() {
        return elementByClass(swalClasses.image);
    };

    var getButtonsWrapper = function getButtonsWrapper() {
        return elementByClass(swalClasses.buttonswrapper);
    };

    var getProgressSteps = function getProgressSteps() {
        return elementByClass(swalClasses.progresssteps);
    };

    var getValidationError = function getValidationError() {
        return elementByClass(swalClasses.validationerror);
    };

    var getConfirmButton = function getConfirmButton() {
        return elementByClass(swalClasses.confirm);
    };

    var getCancelButton = function getCancelButton() {
        return elementByClass(swalClasses.cancel);
    };

    var getCloseButton = function getCloseButton() {
        return elementByClass(swalClasses.close);
    };

    var getFocusableElements = function getFocusableElements(focusCancel) {
        var buttons = [getConfirmButton(), getCancelButton()];
        if (focusCancel) {
            buttons.reverse();
        }
        var focusableElements = buttons.concat(Array.prototype.slice.call(getModal().querySelectorAll('button, input:not([type=hidden]), textarea, select, a, *[tabindex]:not([tabindex="-1"])')));
        return uniqueArray(focusableElements);
    };

    var hasClass = function hasClass(elem, className) {
        if (elem.classList) {
            return elem.classList.contains(className);
        }
        return false;
    };

    var focusInput = function focusInput(input) {
        input.focus();

        // place cursor at end of text in text input
        if (input.type !== 'file') {
            // http://stackoverflow.com/a/2345915/1331425
            var val = input.value;
            input.value = '';
            input.value = val;
        }
    };

    var addClass = function addClass(elem, className) {
        if (!elem || !className) {
            return;
        }
        var classes = className.split(/\s+/).filter(Boolean);
        classes.forEach(function(className) {
            elem.classList.add(className);
        });
    };

    var removeClass = function removeClass(elem, className) {
        if (!elem || !className) {
            return;
        }
        var classes = className.split(/\s+/).filter(Boolean);
        classes.forEach(function(className) {
            elem.classList.remove(className);
        });
    };

    var getChildByClass = function getChildByClass(elem, className) {
        for (var i = 0; i < elem.childNodes.length; i++) {
            if (hasClass(elem.childNodes[i], className)) {
                return elem.childNodes[i];
            }
        }
    };

    var show = function show(elem, display) {
        if (!display) {
            display = 'block';
        }
        elem.style.opacity = '';
        elem.style.display = display;
    };

    var hide = function hide(elem) {
        elem.style.opacity = '';
        elem.style.display = 'none';
    };

    var empty = function empty(elem) {
        while (elem.firstChild) {
            elem.removeChild(elem.firstChild);
        }
    };

    // borrowed from jqeury $(elem).is(':visible') implementation
    var isVisible = function isVisible(elem) {
        return elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length;
    };

    var removeStyleProperty = function removeStyleProperty(elem, property) {
        if (elem.style.removeProperty) {
            elem.style.removeProperty(property);
        } else {
            elem.style.removeAttribute(property);
        }
    };

    var fireClick = function fireClick(node) {
        if (!isVisible(node)) {
            return false;
        }

        // Taken from http://www.nonobtrusive.com/2011/11/29/programatically-fire-crossbrowser-click-event-with-javascript/
        // Then fixed for today's Chrome browser.
        if (typeof MouseEvent === 'function') {
            // Up-to-date approach
            var mevt = new MouseEvent('click', {
                view: window,
                bubbles: false,
                cancelable: true
            });
            node.dispatchEvent(mevt);
        } else if (document.createEvent) {
            // Fallback
            var evt = document.createEvent('MouseEvents');
            evt.initEvent('click', false, false);
            node.dispatchEvent(evt);
        } else if (document.createEventObject) {
            node.fireEvent('onclick');
        } else if (typeof node.onclick === 'function') {
            node.onclick();
        }
    };

    var animationEndEvent = function() {
        var testEl = document.createElement('div');
        var transEndEventNames = {
            'WebkitAnimation': 'webkitAnimationEnd',
            'OAnimation': 'oAnimationEnd oanimationend',
            'msAnimation': 'MSAnimationEnd',
            'animation': 'animationend'
        };
        for (var i in transEndEventNames) {
            if (transEndEventNames.hasOwnProperty(i) && testEl.style[i] !== undefined) {
                return transEndEventNames[i];
            }
        }

        return false;
    }();

    // Reset previous window keydown handler and focued element
    var resetPrevState = function resetPrevState() {
        window.onkeydown = states.previousWindowKeyDown;
        if (states.previousActiveElement && states.previousActiveElement.focus) {
            var x = window.scrollX;
            var y = window.scrollY;
            states.previousActiveElement.focus();
            if (x && y) {
                // IE has no scrollX/scrollY support
                window.scrollTo(x, y);
            }
        }
    };

    // Measure width of scrollbar
    // https://github.com/twbs/bootstrap/blob/master/js/modal.js#L279-L286
    var measureScrollbar = function measureScrollbar() {
        var supportsTouch = 'ontouchstart' in window || navigator.msMaxTouchPoints;
        if (supportsTouch) {
            return 0;
        }
        var scrollDiv = document.createElement('div');
        scrollDiv.style.width = '50px';
        scrollDiv.style.height = '50px';
        scrollDiv.style.overflow = 'scroll';
        document.body.appendChild(scrollDiv);
        var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
        document.body.removeChild(scrollDiv);
        return scrollbarWidth;
    };

    // JavaScript Debounce Function
    // Simplivied version of https://davidwalsh.name/javascript-debounce-function
    var debounce = function debounce(func, wait) {
        var timeout = void 0;
        return function() {
            var later = function later() {
                timeout = null;
                func();
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function(obj) {
        return typeof obj;
    } : function(obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };





















    var _extends = Object.assign || function(target) {
        for (var i = 1; i < arguments.length; i++) {
            var source = arguments[i];

            for (var key in source) {
                if (Object.prototype.hasOwnProperty.call(source, key)) {
                    target[key] = source[key];
                }
            }
        }

        return target;
    };

    var modalParams = _extends({}, defaultParams);
    var queue = [];
    var swal2Observer = void 0;

    /*
     * Set type, text and actions on modal
     */
    var setParameters = function setParameters(params) {
        var modal = getModal() || init(params);

        for (var param in params) {
            if (!defaultParams.hasOwnProperty(param) && param !== 'extraParams') {
                console.warn('SweetAlert2: Unknown parameter "' + param + '"');
            }
        }

        // Set modal width
        modal.style.width = typeof params.width === 'number' ? params.width + 'px' : params.width;

        modal.style.padding = params.padding + 'px';
        modal.style.background = params.background;
        var successIconParts = modal.querySelectorAll('[class^=swal2-success-circular-line], .swal2-success-fix');
        for (var i = 0; i < successIconParts.length; i++) {
            successIconParts[i].style.background = params.background;
        }

        var title = getTitle();
        var content = getContent();
        var buttonsWrapper = getButtonsWrapper();
        var confirmButton = getConfirmButton();
        var cancelButton = getCancelButton();
        var closeButton = getCloseButton();

        // Title
        if (params.titleText) {
            title.innerText = params.titleText;
        } else {
            title.innerHTML = params.title.split('\n').join('<br>');
        }

        // Content
        if (params.text || params.html) {
            if (_typeof(params.html) === 'object') {
                content.innerHTML = '';
                if (0 in params.html) {
                    for (var _i = 0; _i in params.html; _i++) {
                        content.appendChild(params.html[_i].cloneNode(true));
                    }
                } else {
                    content.appendChild(params.html.cloneNode(true));
                }
            } else if (params.html) {
                content.innerHTML = params.html;
            } else if (params.text) {
                content.textContent = params.text;
            }
            show(content);
        } else {
            hide(content);
        }

        // Close button
        if (params.showCloseButton) {
            show(closeButton);
        } else {
            hide(closeButton);
        }

        // Custom Class
        modal.className = swalClasses.modal;
        if (params.customClass) {
            addClass(modal, params.customClass);
        }

        // Progress steps
        var progressStepsContainer = getProgressSteps();
        var currentProgressStep = parseInt(params.currentProgressStep === null ? sweetAlert.getQueueStep() : params.currentProgressStep, 10);
        if (params.progressSteps.length) {
            show(progressStepsContainer);
            empty(progressStepsContainer);
            if (currentProgressStep >= params.progressSteps.length) {
                console.warn('SweetAlert2: Invalid currentProgressStep parameter, it should be less than progressSteps.length ' + '(currentProgressStep like JS arrays starts from 0)');
            }
            params.progressSteps.forEach(function(step, index) {
                var circle = document.createElement('li');
                addClass(circle, swalClasses.progresscircle);
                circle.innerHTML = step;
                if (index === currentProgressStep) {
                    addClass(circle, swalClasses.activeprogressstep);
                }
                progressStepsContainer.appendChild(circle);
                if (index !== params.progressSteps.length - 1) {
                    var line = document.createElement('li');
                    addClass(line, swalClasses.progressline);
                    line.style.width = params.progressStepsDistance;
                    progressStepsContainer.appendChild(line);
                }
            });
        } else {
            hide(progressStepsContainer);
        }

        // Icon
        var icons = getIcons();
        for (var _i2 = 0; _i2 < icons.length; _i2++) {
            hide(icons[_i2]);
        }
        if (params.type) {
            var validType = false;
            for (var iconType in iconTypes) {
                if (params.type === iconType) {
                    validType = true;
                    break;
                }
            }
            if (!validType) {
                console.error('SweetAlert2: Unknown alert type: ' + params.type);
                return false;
            }
            var icon = modal.querySelector('.' + swalClasses.icon + '.' + iconTypes[params.type]);
            show(icon);

            // Animate icon
            if (params.animation) {
                switch (params.type) {
                    case 'success':
                        addClass(icon, 'swal2-animate-success-icon');
                        addClass(icon.querySelector('.swal2-success-line-tip'), 'swal2-animate-success-line-tip');
                        addClass(icon.querySelector('.swal2-success-line-long'), 'swal2-animate-success-line-long');
                        break;
                    case 'error':
                        addClass(icon, 'swal2-animate-error-icon');
                        addClass(icon.querySelector('.swal2-x-mark'), 'swal2-animate-x-mark');
                        break;
                    default:
                        break;
                }
            }
        }

        // Custom image
        var image = getImage();
        if (params.imageUrl) {
            image.setAttribute('src', params.imageUrl);
            show(image);

            if (params.imageWidth) {
                image.setAttribute('width', params.imageWidth);
            } else {
                image.removeAttribute('width');
            }

            if (params.imageHeight) {
                image.setAttribute('height', params.imageHeight);
            } else {
                image.removeAttribute('height');
            }

            image.className = swalClasses.image;
            if (params.imageClass) {
                addClass(image, params.imageClass);
            }
        } else {
            hide(image);
        }

        // Cancel button
        if (params.showCancelButton) {
            cancelButton.style.display = 'inline-block';
        } else {
            hide(cancelButton);
        }

        // Confirm button
        if (params.showConfirmButton) {
            removeStyleProperty(confirmButton, 'display');
        } else {
            hide(confirmButton);
        }

        // Buttons wrapper
        if (!params.showConfirmButton && !params.showCancelButton) {
            hide(buttonsWrapper);
        } else {
            show(buttonsWrapper);
        }

        // Edit text on cancel and confirm buttons
        confirmButton.innerHTML = params.confirmButtonText;
        cancelButton.innerHTML = params.cancelButtonText;

        // Set buttons to selected background colors
        if (params.buttonsStyling) {
            confirmButton.style.backgroundColor = params.confirmButtonColor;
            cancelButton.style.backgroundColor = params.cancelButtonColor;
        }

        // Add buttons custom classes
        confirmButton.className = swalClasses.confirm;
        addClass(confirmButton, params.confirmButtonClass);
        cancelButton.className = swalClasses.cancel;
        addClass(cancelButton, params.cancelButtonClass);

        // Buttons styling
        if (params.buttonsStyling) {
            addClass(confirmButton, swalClasses.styled);
            addClass(cancelButton, swalClasses.styled);
        } else {
            removeClass(confirmButton, swalClasses.styled);
            removeClass(cancelButton, swalClasses.styled);

            confirmButton.style.backgroundColor = confirmButton.style.borderLeftColor = confirmButton.style.borderRightColor = '';
            cancelButton.style.backgroundColor = cancelButton.style.borderLeftColor = cancelButton.style.borderRightColor = '';
        }

        // CSS animation
        if (params.animation === true) {
            removeClass(modal, swalClasses.noanimation);
        } else {
            addClass(modal, swalClasses.noanimation);
        }
    };

    /*
     * Animations
     */
    var openModal = function openModal(animation, onComplete) {
        var container = getContainer();
        var modal = getModal();

        if (animation) {
            addClass(modal, swalClasses.show);
            addClass(container, swalClasses.fade);
            removeClass(modal, swalClasses.hide);
        } else {
            removeClass(modal, swalClasses.fade);
        }
        show(modal);

        // scrolling is 'hidden' until animation is done, after that 'auto'
        container.style.overflowY = 'hidden';
        if (animationEndEvent && !hasClass(modal, swalClasses.noanimation)) {
            modal.addEventListener(animationEndEvent, function swalCloseEventFinished() {
                modal.removeEventListener(animationEndEvent, swalCloseEventFinished);
                container.style.overflowY = 'auto';
            });
        } else {
            container.style.overflowY = 'auto';
        }

        addClass(document.documentElement, swalClasses.shown);
        addClass(document.body, swalClasses.shown);
        addClass(container, swalClasses.shown);
        fixScrollbar();
        iOSfix();
        states.previousActiveElement = document.activeElement;
        if (onComplete !== null && typeof onComplete === 'function') {
            setTimeout(function() {
                onComplete(modal);
            });
        }
    };

    var fixScrollbar = function fixScrollbar() {
        // for queues, do not do this more than once
        if (states.previousBodyPadding !== null) {
            return;
        }
        // if the body has overflow
        if (document.body.scrollHeight > window.innerHeight) {
            // add padding so the content doesn't shift after removal of scrollbar
            states.previousBodyPadding = document.body.style.paddingRight;
            document.body.style.paddingRight = measureScrollbar() + 'px';
        }
    };

    var undoScrollbar = function undoScrollbar() {
        if (states.previousBodyPadding !== null) {
            document.body.style.paddingRight = states.previousBodyPadding;
            states.previousBodyPadding = null;
        }
    };

    // Fix iOS scrolling http://stackoverflow.com/q/39626302/1331425
    var iOSfix = function iOSfix() {
        var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (iOS && !hasClass(document.body, swalClasses.iosfix)) {
            var offset = document.body.scrollTop;
            document.body.style.top = offset * -1 + 'px';
            addClass(document.body, swalClasses.iosfix);
        }
    };

    var undoIOSfix = function undoIOSfix() {
        if (hasClass(document.body, swalClasses.iosfix)) {
            var offset = parseInt(document.body.style.top, 10);
            removeClass(document.body, swalClasses.iosfix);
            document.body.style.top = '';
            document.body.scrollTop = offset * -1;
        }
    };

    // SweetAlert entry point
    var sweetAlert = function sweetAlert() {
        for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
            args[_key] = arguments[_key];
        }

        if (args[0] === undefined) {
            console.error('SweetAlert2 expects at least 1 attribute!');
            return false;
        }

        var params = _extends({}, modalParams);

        switch (_typeof(args[0])) {
            case 'string':
                params.title = args[0];
                params.html = args[1];
                params.type = args[2];

                break;

            case 'object':
                _extends(params, args[0]);
                params.extraParams = args[0].extraParams;

                if (params.input === 'email' && params.inputValidator === null) {
                    params.inputValidator = function(email) {
                        return new Promise(function(resolve, reject) {
                            var emailRegex = /^[a-zA-Z0-9.+_-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
                            if (emailRegex.test(email)) {
                                resolve();
                            } else {
                                reject('Invalid email address');
                            }
                        });
                    };
                }

                if (params.input === 'url' && params.inputValidator === null) {
                    params.inputValidator = function(url) {
                        return new Promise(function(resolve, reject) {
                            var urlRegex = /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([/\w .-]*)*\/?$/;
                            if (urlRegex.test(url)) {
                                resolve();
                            } else {
                                reject('Invalid URL');
                            }
                        });
                    };
                }
                break;

            default:
                console.error('SweetAlert2: Unexpected type of argument! Expected "string" or "object", got ' + _typeof(args[0]));
                return false;
        }

        setParameters(params);

        var container = getContainer();
        var modal = getModal();

        return new Promise(function(resolve, reject) {
            // Close on timer
            if (params.timer) {
                modal.timeout = setTimeout(function() {
                    sweetAlert.closeModal(params.onClose);
                    reject('timer');
                }, params.timer);
            }

            // Get input element by specified type or, if type isn't specified, by params.input
            var getInput = function getInput(inputType) {
                inputType = inputType || params.input;
                if (!inputType) {
                    return null;
                }
                switch (inputType) {
                    case 'select':
                    case 'textarea':
                    case 'file':
                        return getChildByClass(modal, swalClasses[inputType]);
                    case 'checkbox':
                        return modal.querySelector('.' + swalClasses.checkbox + ' input');
                    case 'radio':
                        return modal.querySelector('.' + swalClasses.radio + ' input:checked') || modal.querySelector('.' + swalClasses.radio + ' input:first-child');
                    case 'range':
                        return modal.querySelector('.' + swalClasses.range + ' input');
                    default:
                        return getChildByClass(modal, swalClasses.input);
                }
            };

            // Get the value of the modal input
            var getInputValue = function getInputValue() {
                var input = getInput();
                if (!input) {
                    return null;
                }
                switch (params.input) {
                    case 'checkbox':
                        return input.checked ? 1 : 0;
                    case 'radio':
                        return input.checked ? input.value : null;
                    case 'file':
                        return input.files.length ? input.files[0] : null;
                    default:
                        return params.inputAutoTrim ? input.value.trim() : input.value;
                }
            };

            // input autofocus
            if (params.input) {
                setTimeout(function() {
                    var input = getInput();
                    if (input) {
                        focusInput(input);
                    }
                }, 0);
            }

            var confirm = function confirm(value) {
                if (params.showLoaderOnConfirm) {
                    sweetAlert.showLoading();
                }

                if (params.preConfirm) {
                    params.preConfirm(value, params.extraParams).then(function(preConfirmValue) {
                        sweetAlert.closeModal(params.onClose);
                        resolve(preConfirmValue || value);
                    }, function(error) {
                        sweetAlert.hideLoading();
                        if (error) {
                            sweetAlert.showValidationError(error);
                        }
                    });
                } else {
                    sweetAlert.closeModal(params.onClose);
                    resolve(value);
                }
            };

            // Mouse interactions
            var onButtonEvent = function onButtonEvent(event) {
                var e = event || window.event;
                var target = e.target || e.srcElement;
                var confirmButton = getConfirmButton();
                var cancelButton = getCancelButton();
                var targetedConfirm = confirmButton && (confirmButton === target || confirmButton.contains(target));
                var targetedCancel = cancelButton && (cancelButton === target || cancelButton.contains(target));

                switch (e.type) {
                    case 'mouseover':
                    case 'mouseup':
                        if (params.buttonsStyling) {
                            if (targetedConfirm) {
                                confirmButton.style.backgroundColor = colorLuminance(params.confirmButtonColor, -0.1);
                            } else if (targetedCancel) {
                                cancelButton.style.backgroundColor = colorLuminance(params.cancelButtonColor, -0.1);
                            }
                        }
                        break;
                    case 'mouseout':
                        if (params.buttonsStyling) {
                            if (targetedConfirm) {
                                confirmButton.style.backgroundColor = params.confirmButtonColor;
                            } else if (targetedCancel) {
                                cancelButton.style.backgroundColor = params.cancelButtonColor;
                            }
                        }
                        break;
                    case 'mousedown':
                        if (params.buttonsStyling) {
                            if (targetedConfirm) {
                                confirmButton.style.backgroundColor = colorLuminance(params.confirmButtonColor, -0.2);
                            } else if (targetedCancel) {
                                cancelButton.style.backgroundColor = colorLuminance(params.cancelButtonColor, -0.2);
                            }
                        }
                        break;
                    case 'click':
                        // Clicked 'confirm'
                        if (targetedConfirm && sweetAlert.isVisible()) {
                            sweetAlert.disableButtons();
                            if (params.input) {
                                var inputValue = getInputValue();

                                if (params.inputValidator) {
                                    sweetAlert.disableInput();
                                    params.inputValidator(inputValue, params.extraParams).then(function() {
                                        sweetAlert.enableButtons();
                                        sweetAlert.enableInput();
                                        confirm(inputValue);
                                    }, function(error) {
                                        sweetAlert.enableButtons();
                                        sweetAlert.enableInput();
                                        if (error) {
                                            sweetAlert.showValidationError(error);
                                        }
                                    });
                                } else {
                                    confirm(inputValue);
                                }
                            } else {
                                confirm(true);
                            }

                            // Clicked 'cancel'
                        } else if (targetedCancel && sweetAlert.isVisible()) {
                            sweetAlert.disableButtons();
                            sweetAlert.closeModal(params.onClose);
                            reject('cancel');
                        }
                        break;
                    default:
                }
            };

            var buttons = modal.querySelectorAll('button');
            for (var i = 0; i < buttons.length; i++) {
                buttons[i].onclick = onButtonEvent;
                buttons[i].onmouseover = onButtonEvent;
                buttons[i].onmouseout = onButtonEvent;
                buttons[i].onmousedown = onButtonEvent;
            }

            // Closing modal by close button
            getCloseButton().onclick = function() {
                sweetAlert.closeModal(params.onClose);
                reject('close');
            };

            // Closing modal by overlay click
            container.onclick = function(e) {
                if (e.target !== container) {
                    return;
                }
                if (params.allowOutsideClick) {
                    sweetAlert.closeModal(params.onClose);
                    reject('overlay');
                }
            };

            var buttonsWrapper = getButtonsWrapper();
            var confirmButton = getConfirmButton();
            var cancelButton = getCancelButton();

            // Reverse buttons (Confirm on the right side)
            if (params.reverseButtons) {
                confirmButton.parentNode.insertBefore(cancelButton, confirmButton);
            } else {
                confirmButton.parentNode.insertBefore(confirmButton, cancelButton);
            }

            // Focus handling
            var setFocus = function setFocus(index, increment) {
                var focusableElements = getFocusableElements(params.focusCancel);
                // search for visible elements and select the next possible match
                for (var _i3 = 0; _i3 < focusableElements.length; _i3++) {
                    index = index + increment;

                    // rollover to first item
                    if (index === focusableElements.length) {
                        index = 0;

                        // go to last item
                    } else if (index === -1) {
                        index = focusableElements.length - 1;
                    }

                    // determine if element is visible
                    var el = focusableElements[index];
                    if (isVisible(el)) {
                        return el.focus();
                    }
                }
            };

            var handleKeyDown = function handleKeyDown(event) {
                var e = event || window.event;
                var keyCode = e.keyCode || e.which;

                if ([9, 13, 32, 27, 37, 38, 39, 40].indexOf(keyCode) === -1) {
                    // Don't do work on keys we don't care about.
                    return;
                }

                var targetElement = e.target || e.srcElement;

                var focusableElements = getFocusableElements(params.focusCancel);
                var btnIndex = -1; // Find the button - note, this is a nodelist, not an array.
                for (var _i4 = 0; _i4 < focusableElements.length; _i4++) {
                    if (targetElement === focusableElements[_i4]) {
                        btnIndex = _i4;
                        break;
                    }
                }

                // TAB
                if (keyCode === 9) {
                    if (!e.shiftKey) {
                        // Cycle to the next button
                        setFocus(btnIndex, 1);
                    } else {
                        // Cycle to the prev button
                        setFocus(btnIndex, -1);
                    }
                    e.stopPropagation();
                    e.preventDefault();

                    // ARROWS - switch focus between buttons
                } else if (keyCode === 37 || keyCode === 38 || keyCode === 39 || keyCode === 40) {
                    // focus Cancel button if Confirm button is currently focused
                    if (document.activeElement === confirmButton && isVisible(cancelButton)) {
                        cancelButton.focus();
                        // and vice versa
                    } else if (document.activeElement === cancelButton && isVisible(confirmButton)) {
                        confirmButton.focus();
                    }

                    // ENTER/SPACE
                } else if (keyCode === 13 || keyCode === 32) {
                    if (btnIndex === -1 && params.allowEnterKey) {
                        // ENTER/SPACE clicked outside of a button.
                        if (params.focusCancel) {
                            fireClick(cancelButton, e);
                        } else {
                            fireClick(confirmButton, e);
                        }
                        e.stopPropagation();
                        e.preventDefault();
                    }

                    // ESC
                } else if (keyCode === 27 && params.allowEscapeKey === true) {
                    sweetAlert.closeModal(params.onClose);
                    reject('esc');
                }
            };

            states.previousWindowKeyDown = window.onkeydown;
            window.onkeydown = handleKeyDown;

            // Loading state
            if (params.buttonsStyling) {
                confirmButton.style.borderLeftColor = params.confirmButtonColor;
                confirmButton.style.borderRightColor = params.confirmButtonColor;
            }

            /**
             * Show spinner instead of Confirm button and disable Cancel button
             */
            sweetAlert.showLoading = sweetAlert.enableLoading = function() {
                show(buttonsWrapper);
                show(confirmButton, 'inline-block');
                addClass(buttonsWrapper, swalClasses.loading);
                addClass(modal, swalClasses.loading);
                confirmButton.disabled = true;
                cancelButton.disabled = true;
            };

            /**
             * Show spinner instead of Confirm button and disable Cancel button
             */
            sweetAlert.hideLoading = sweetAlert.disableLoading = function() {
                if (!params.showConfirmButton) {
                    hide(confirmButton);
                    if (!params.showCancelButton) {
                        hide(getButtonsWrapper());
                    }
                }
                removeClass(buttonsWrapper, swalClasses.loading);
                removeClass(modal, swalClasses.loading);
                confirmButton.disabled = false;
                cancelButton.disabled = false;
            };

            sweetAlert.getTitle = function() {
                return getTitle();
            };
            sweetAlert.getContent = function() {
                return getContent();
            };
            sweetAlert.getInput = function() {
                return getInput();
            };
            sweetAlert.getImage = function() {
                return getImage();
            };
            sweetAlert.getButtonsWrapper = function() {
                return getButtonsWrapper();
            };
            sweetAlert.getConfirmButton = function() {
                return getConfirmButton();
            };
            sweetAlert.getCancelButton = function() {
                return getCancelButton();
            };

            sweetAlert.enableButtons = function() {
                confirmButton.disabled = false;
                cancelButton.disabled = false;
            };

            sweetAlert.disableButtons = function() {
                confirmButton.disabled = true;
                cancelButton.disabled = true;
            };

            sweetAlert.enableConfirmButton = function() {
                confirmButton.disabled = false;
            };

            sweetAlert.disableConfirmButton = function() {
                confirmButton.disabled = true;
            };

            sweetAlert.enableInput = function() {
                var input = getInput();
                if (!input) {
                    return false;
                }
                if (input.type === 'radio') {
                    var radiosContainer = input.parentNode.parentNode;
                    var radios = radiosContainer.querySelectorAll('input');
                    for (var _i5 = 0; _i5 < radios.length; _i5++) {
                        radios[_i5].disabled = false;
                    }
                } else {
                    input.disabled = false;
                }
            };

            sweetAlert.disableInput = function() {
                var input = getInput();
                if (!input) {
                    return false;
                }
                if (input && input.type === 'radio') {
                    var radiosContainer = input.parentNode.parentNode;
                    var radios = radiosContainer.querySelectorAll('input');
                    for (var _i6 = 0; _i6 < radios.length; _i6++) {
                        radios[_i6].disabled = true;
                    }
                } else {
                    input.disabled = true;
                }
            };

            // Set modal min-height to disable scrolling inside the modal
            sweetAlert.recalculateHeight = debounce(function() {
                var modal = getModal();
                if (!modal) {
                    return;
                }
                var prevState = modal.style.display;
                modal.style.minHeight = '';
                show(modal);
                modal.style.minHeight = modal.scrollHeight + 1 + 'px';
                modal.style.display = prevState;
            }, 50);

            // Show block with validation error
            sweetAlert.showValidationError = function(error) {
                var validationError = getValidationError();
                validationError.innerHTML = error;
                show(validationError);

                var input = getInput();
                if (input) {
                    focusInput(input);
                    addClass(input, swalClasses.inputerror);
                }
            };

            // Hide block with validation error
            sweetAlert.resetValidationError = function() {
                var validationError = getValidationError();
                hide(validationError);
                sweetAlert.recalculateHeight();

                var input = getInput();
                if (input) {
                    removeClass(input, swalClasses.inputerror);
                }
            };

            sweetAlert.getProgressSteps = function() {
                return params.progressSteps;
            };

            sweetAlert.setProgressSteps = function(progressSteps) {
                params.progressSteps = progressSteps;
                setParameters(params);
            };

            sweetAlert.showProgressSteps = function() {
                show(getProgressSteps());
            };

            sweetAlert.hideProgressSteps = function() {
                hide(getProgressSteps());
            };

            sweetAlert.enableButtons();
            sweetAlert.hideLoading();
            sweetAlert.resetValidationError();

            // inputs
            var inputTypes = ['input', 'file', 'range', 'select', 'radio', 'checkbox', 'textarea'];
            var input = void 0;
            for (var _i7 = 0; _i7 < inputTypes.length; _i7++) {
                var inputClass = swalClasses[inputTypes[_i7]];
                var inputContainer = getChildByClass(modal, inputClass);
                input = getInput(inputTypes[_i7]);

                // set attributes
                if (input) {
                    for (var j in input.attributes) {
                        if (input.attributes.hasOwnProperty(j)) {
                            var attrName = input.attributes[j].name;
                            if (attrName !== 'type' && attrName !== 'value') {
                                input.removeAttribute(attrName);
                            }
                        }
                    }
                    for (var attr in params.inputAttributes) {
                        input.setAttribute(attr, params.inputAttributes[attr]);
                    }
                }

                // set class
                inputContainer.className = inputClass;
                if (params.inputClass) {
                    addClass(inputContainer, params.inputClass);
                }

                hide(inputContainer);
            }

            var populateInputOptions = void 0;
            switch (params.input) {
                case 'text':
                case 'email':
                case 'password':
                case 'number':
                case 'tel':
                case 'url':
                    input = getChildByClass(modal, swalClasses.input);
                    input.value = params.inputValue;
                    input.placeholder = params.inputPlaceholder;
                    input.type = params.input;
                    show(input);
                    break;
                case 'file':
                    input = getChildByClass(modal, swalClasses.file);
                    input.placeholder = params.inputPlaceholder;
                    input.type = params.input;
                    show(input);
                    break;
                case 'range':
                    var range = getChildByClass(modal, swalClasses.range);
                    var rangeInput = range.querySelector('input');
                    var rangeOutput = range.querySelector('output');
                    rangeInput.value = params.inputValue;
                    rangeInput.type = params.input;
                    rangeOutput.value = params.inputValue;
                    show(range);
                    break;
                case 'select':
                    var select = getChildByClass(modal, swalClasses.select);
                    select.innerHTML = '';
                    if (params.inputPlaceholder) {
                        var placeholder = document.createElement('option');
                        placeholder.innerHTML = params.inputPlaceholder;
                        placeholder.value = '';
                        placeholder.disabled = true;
                        placeholder.selected = true;
                        select.appendChild(placeholder);
                    }
                    populateInputOptions = function populateInputOptions(inputOptions) {
                        for (var optionValue in inputOptions) {
                            var option = document.createElement('option');
                            option.value = optionValue;
                            option.innerHTML = inputOptions[optionValue];
                            if (params.inputValue === optionValue) {
                                option.selected = true;
                            }
                            select.appendChild(option);
                        }
                        show(select);
                        select.focus();
                    };
                    break;
                case 'radio':
                    var radio = getChildByClass(modal, swalClasses.radio);
                    radio.innerHTML = '';
                    populateInputOptions = function populateInputOptions(inputOptions) {
                        for (var radioValue in inputOptions) {
                            var radioInput = document.createElement('input');
                            var radioLabel = document.createElement('label');
                            var radioLabelSpan = document.createElement('span');
                            radioInput.type = 'radio';
                            radioInput.name = swalClasses.radio;
                            radioInput.value = radioValue;
                            if (params.inputValue === radioValue) {
                                radioInput.checked = true;
                            }
                            radioLabelSpan.innerHTML = inputOptions[radioValue];
                            radioLabel.appendChild(radioInput);
                            radioLabel.appendChild(radioLabelSpan);
                            radioLabel.for = radioInput.id;
                            radio.appendChild(radioLabel);
                        }
                        show(radio);
                        var radios = radio.querySelectorAll('input');
                        if (radios.length) {
                            radios[0].focus();
                        }
                    };
                    break;
                case 'checkbox':
                    var checkbox = getChildByClass(modal, swalClasses.checkbox);
                    var checkboxInput = getInput('checkbox');
                    checkboxInput.type = 'checkbox';
                    checkboxInput.value = 1;
                    checkboxInput.id = swalClasses.checkbox;
                    checkboxInput.checked = Boolean(params.inputValue);
                    var label = checkbox.getElementsByTagName('span');
                    if (label.length) {
                        checkbox.removeChild(label[0]);
                    }
                    label = document.createElement('span');
                    label.innerHTML = params.inputPlaceholder;
                    checkbox.appendChild(label);
                    show(checkbox);
                    break;
                case 'textarea':
                    var textarea = getChildByClass(modal, swalClasses.textarea);
                    textarea.value = params.inputValue;
                    textarea.placeholder = params.inputPlaceholder;
                    show(textarea);
                    break;
                case null:
                    break;
                default:
                    console.error('SweetAlert2: Unexpected type of input! Expected "text", "email", "password", "number", "tel", "select", "radio", "checkbox", "textarea", "file" or "url", got "' + params.input + '"');
                    break;
            }

            if (params.input === 'select' || params.input === 'radio') {
                if (params.inputOptions instanceof Promise) {
                    sweetAlert.showLoading();
                    params.inputOptions.then(function(inputOptions) {
                        sweetAlert.hideLoading();
                        populateInputOptions(inputOptions);
                    });
                } else if (_typeof(params.inputOptions) === 'object') {
                    populateInputOptions(params.inputOptions);
                } else {
                    console.error('SweetAlert2: Unexpected type of inputOptions! Expected object or Promise, got ' + _typeof(params.inputOptions));
                }
            }

            openModal(params.animation, params.onOpen);

            // Focus the first element (input or button)
            if (params.allowEnterKey) {
                setFocus(-1, 1);
            } else {
                if (document.activeElement) {
                    document.activeElement.blur();
                }
            }

            // fix scroll
            getContainer().scrollTop = 0;

            // Observe changes inside the modal and adjust height
            if (typeof MutationObserver !== 'undefined' && !swal2Observer) {
                swal2Observer = new MutationObserver(sweetAlert.recalculateHeight);
                swal2Observer.observe(modal, { childList: true, characterData: true, subtree: true });
            }
        });
    };

    /*
     * Global function to determine if swal2 modal is shown
     */
    sweetAlert.isVisible = function() {
        return !!getModal();
    };

    /*
     * Global function for chaining sweetAlert modals
     */
    sweetAlert.queue = function(steps) {
        queue = steps;
        var resetQueue = function resetQueue() {
            queue = [];
            document.body.removeAttribute('data-swal2-queue-step');
        };
        var queueResult = [];
        return new Promise(function(resolve, reject) {
            (function step(i, callback) {
                if (i < queue.length) {
                    document.body.setAttribute('data-swal2-queue-step', i);

                    sweetAlert(queue[i]).then(function(result) {
                        queueResult.push(result);
                        step(i + 1, callback);
                    }, function(dismiss) {
                        resetQueue();
                        reject(dismiss);
                    });
                } else {
                    resetQueue();
                    resolve(queueResult);
                }
            })(0);
        });
    };

    /*
     * Global function for getting the index of current modal in queue
     */
    sweetAlert.getQueueStep = function() {
        return document.body.getAttribute('data-swal2-queue-step');
    };

    /*
     * Global function for inserting a modal to the queue
     */
    sweetAlert.insertQueueStep = function(step, index) {
        if (index && index < queue.length) {
            return queue.splice(index, 0, step);
        }
        return queue.push(step);
    };

    /*
     * Global function for deleting a modal from the queue
     */
    sweetAlert.deleteQueueStep = function(index) {
        if (typeof queue[index] !== 'undefined') {
            queue.splice(index, 1);
        }
    };

    /*
     * Global function to close sweetAlert
     */
    sweetAlert.close = sweetAlert.closeModal = function(onComplete) {
        var container = getContainer();
        var modal = getModal();
        if (!modal) {
            return;
        }
        removeClass(modal, swalClasses.show);
        addClass(modal, swalClasses.hide);
        clearTimeout(modal.timeout);

        resetPrevState();

        var removeModalAndResetState = function removeModalAndResetState() {
            if (container.parentNode) {
                container.parentNode.removeChild(container);
            }
            removeClass(document.documentElement, swalClasses.shown);
            removeClass(document.body, swalClasses.shown);
            undoScrollbar();
            undoIOSfix();
        };

        // If animation is supported, animate
        if (animationEndEvent && !hasClass(modal, swalClasses.noanimation)) {
            modal.addEventListener(animationEndEvent, function swalCloseEventFinished() {
                modal.removeEventListener(animationEndEvent, swalCloseEventFinished);
                if (hasClass(modal, swalClasses.hide)) {
                    removeModalAndResetState();
                }
            });
        } else {
            // Otherwise, remove immediately
            removeModalAndResetState();
        }
        if (onComplete !== null && typeof onComplete === 'function') {
            setTimeout(function() {
                onComplete(modal);
            });
        }
    };

    /*
     * Global function to click 'Confirm' button
     */
    sweetAlert.clickConfirm = function() {
        return getConfirmButton().click();
    };

    /*
     * Global function to click 'Cancel' button
     */
    sweetAlert.clickCancel = function() {
        return getCancelButton().click();
    };

    /**
     * Set default params for each popup
     * @param {Object} userParams
     */
    sweetAlert.setDefaults = function(userParams) {
        if (!userParams || (typeof userParams === 'undefined' ? 'undefined' : _typeof(userParams)) !== 'object') {
            return console.error('SweetAlert2: the argument for setDefaults() is required and has to be a object');
        }

        for (var param in userParams) {
            if (!defaultParams.hasOwnProperty(param) && param !== 'extraParams') {
                console.warn('SweetAlert2: Unknown parameter "' + param + '"');
                delete userParams[param];
            }
        }

        _extends(modalParams, userParams);
    };

    /**
     * Reset default params for each popup
     */
    sweetAlert.resetDefaults = function() {
        modalParams = _extends({}, defaultParams);
    };

    sweetAlert.noop = function() {};

    sweetAlert.version = '6.6.2';

    sweetAlert.default = sweetAlert;

    return sweetAlert;

})));
if (window.Sweetalert2) window.sweetAlert = window.swal = window.Sweetalert2;;
/*
 * iziModal | v1.5.1
 * http://izimodal.marcelodolce.com
 * by Marcelo Dolce.
 */
(function(factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = function(root, jQuery) {
            if (jQuery === undefined) {
                if (typeof window !== 'undefined') {
                    jQuery = require('jquery');
                } else {
                    jQuery = require('jquery')(root);
                }
            }
            factory(jQuery);
            return jQuery;
        };
    } else {
        factory(jQuery);
    }
}(function($) {

    var $window = $(window),
        $document = $(document),
        PLUGIN_NAME = 'iziModal',
        STATES = {
            CLOSING: 'closing',
            CLOSED: 'closed',
            OPENING: 'opening',
            OPENED: 'opened',
            DESTROYED: 'destroyed'
        };

    function whichAnimationEvent() {
        var t,
            el = document.createElement("fakeelement"),
            animations = {
                "animation": "animationend",
                "OAnimation": "oAnimationEnd",
                "MozAnimation": "animationend",
                "WebkitAnimation": "webkitAnimationEnd"
            };
        for (t in animations) {
            if (el.style[t] !== undefined) {
                return animations[t];
            }
        }
    }

    function isIE(version) {
        if (version === 9) {
            return navigator.appVersion.indexOf("MSIE 9.") !== -1;
        } else {
            userAgent = navigator.userAgent;
            return userAgent.indexOf("MSIE ") > -1 || userAgent.indexOf("Trident/") > -1;
        }
    }

    function clearValue(value) {
        var separators = /%|px|em|cm|vh|vw/;
        return parseInt(String(value).split(separators)[0]);
    }

    var animationEvent = whichAnimationEvent(),
        isMobile = (/Mobi/.test(navigator.userAgent)) ? true : false;

    window.$iziModal = {};
    window.$iziModal.autoOpen = 0;
    window.$iziModal.history = false;

    var iziModal = function(element, options) {
        this.init(element, options);
    };

    iziModal.prototype = {

        constructor: iziModal,

        init: function(element, options) {

            var that = this;
            this.$element = $(element);

            if (this.$element[0].id !== undefined && this.$element[0].id !== '') {
                this.id = this.$element[0].id;
            } else {
                let array = new Uint8Array(3);
                window.crypto.getRandomValues(array);
                this.id = PLUGIN_NAME + Math.floor((array[0] * array[1] * array[2]) + 1);
                this.$element.attr('id', this.id);
            }
            this.classes = (this.$element.attr('class') !== undefined) ? this.$element.attr('class') : '';
            this.content = this.$element.html();
            this.state = STATES.CLOSED;
            this.options = options;
            this.width = 0;
            this.timer = null;
            this.timerTimeout = null;
            this.progressBar = null;
            this.isPaused = false;
            this.isFullscreen = false;
            this.headerHeight = 0;
            this.modalHeight = 0;
            this.$overlay = $('<div class="' + PLUGIN_NAME + '-overlay" style="background-color:' + options.overlayColor + '"></div>');
            this.$navigate = $('<div class="' + PLUGIN_NAME + '-navigate"><div class="' + PLUGIN_NAME + '-navigate-caption">Use</div><button class="' + PLUGIN_NAME + '-navigate-prev"></button><button class="' + PLUGIN_NAME + '-navigate-next"></button></div>');
            this.group = {
                name: this.$element.attr('data-' + PLUGIN_NAME + '-group'),
                index: null,
                ids: []
            };
            this.$element.attr('aria-hidden', 'true');
            this.$element.attr('aria-labelledby', this.id);
            this.$element.attr('role', 'dialog');

            if (!this.$element.hasClass('iziModal')) {
                this.$element.addClass('iziModal');
            }

            if (this.group.name === undefined && options.group !== "") {
                this.group.name = options.group;
                this.$element.attr('data-' + PLUGIN_NAME + '-group', options.group);
            }
            if (this.options.loop === true) {
                this.$element.attr('data-' + PLUGIN_NAME + '-loop', true);
            }

            $.each(this.options, function(index, val) {
                var attr = that.$element.attr('data-' + PLUGIN_NAME + '-' + index);
                try {
                    if (typeof attr !== typeof undefined) {

                        if (attr === "" || attr == "true") {
                            options[index] = true;
                        } else if (attr == "false") {
                            options[index] = false;
                        } else if (typeof val == 'function') {
                            options[index] = new Function(attr);
                        } else {
                            options[index] = attr;
                        }
                    }
                } catch (exc) {}
            });

            if (options.appendTo !== false) {
                this.$element.appendTo(options.appendTo);
            }

            if (options.iframe === true) {
                this.$element.html('<div class="' + PLUGIN_NAME + '-wrap"><div class="' + PLUGIN_NAME + '-content"><iframe class="' + PLUGIN_NAME + '-iframe"></iframe>' + this.content + "</div></div>");

                if (options.iframeHeight !== null) {
                    this.$element.find('.' + PLUGIN_NAME + '-iframe').css('height', options.iframeHeight);
                }
            } else {
                this.$element.html('<div class="' + PLUGIN_NAME + '-wrap"><div class="' + PLUGIN_NAME + '-content">' + this.content + '</div></div>');
            }

            if (this.options.background !== null) {
                this.$element.css('background', this.options.background);
            }

            this.$wrap = this.$element.find('.' + PLUGIN_NAME + '-wrap');

            if (options.zindex !== null && !isNaN(parseInt(options.zindex))) {
                this.$element.css('z-index', options.zindex);
                this.$navigate.css('z-index', options.zindex - 1);
                this.$overlay.css('z-index', options.zindex - 2);
            }

            if (options.radius !== "") {
                this.$element.css('border-radius', options.radius);
            }

            if (options.padding !== "") {
                this.$element.find('.' + PLUGIN_NAME + '-content').css('padding', options.padding);
            }

            if (options.theme !== "") {
                if (options.theme === "light") {
                    this.$element.addClass(PLUGIN_NAME + '-light');
                } else {
                    this.$element.addClass(options.theme);
                }
            }

            if (options.rtl === true) {
                this.$element.addClass(PLUGIN_NAME + '-rtl');
            }

            if (options.openFullscreen === true) {
                this.isFullscreen = true;
                this.$element.addClass('isFullscreen');
            }

            this.createHeader();
            this.recalcWidth();
            this.recalcVerticalPos();

            if (that.options.afterRender && (typeof(that.options.afterRender) === "function" || typeof(that.options.afterRender) === "object")) {
                that.options.afterRender(that);
            }

        },

        createHeader: function() {

            this.$header = $('<div class="' + PLUGIN_NAME + '-header"><h2 class="' + PLUGIN_NAME + '-header-title">' + this.options.title + '</h2><p class="' + PLUGIN_NAME + '-header-subtitle">' + this.options.subtitle + '</p><div class="' + PLUGIN_NAME + '-header-buttons"></div></div>');

            if (this.options.closeButton === true) {
                this.$header.find('.' + PLUGIN_NAME + '-header-buttons').append('<a href="javascript:void(0)" class="' + PLUGIN_NAME + '-button ' + PLUGIN_NAME + '-button-close" data-' + PLUGIN_NAME + '-close></a>');
            }

            if (this.options.fullscreen === true) {
                this.$header.find('.' + PLUGIN_NAME + '-header-buttons').append('<a href="javascript:void(0)" class="' + PLUGIN_NAME + '-button ' + PLUGIN_NAME + '-button-fullscreen" data-' + PLUGIN_NAME + '-fullscreen></a>');
            }

            if (this.options.timeoutProgressbar === true && !isNaN(parseInt(this.options.timeout)) && this.options.timeout !== false && this.options.timeout !== 0) {
                this.$header.prepend('<div class="' + PLUGIN_NAME + '-progressbar"><div style="background-color:' + this.options.timeoutProgressbarColor + '"></div></div>');
            }

            if (this.options.subtitle === '') {
                this.$header.addClass(PLUGIN_NAME + '-noSubtitle');
            }

            if (this.options.title !== "") {

                if (this.options.headerColor !== null) {
                    if (this.options.borderBottom === true) {
                        this.$element.css('border-bottom', '3px solid ' + this.options.headerColor + '');
                    }
                    this.$header.css('background', this.options.headerColor);
                }
                if (this.options.icon !== null || this.options.iconText !== null) {

                    this.$header.prepend('<i class="' + PLUGIN_NAME + '-header-icon"></i>');

                    if (this.options.icon !== null) {
                        this.$header.find('.' + PLUGIN_NAME + '-header-icon').addClass(this.options.icon).css('color', this.options.iconColor);
                    }
                    if (this.options.iconText !== null) {
                        this.$header.find('.' + PLUGIN_NAME + '-header-icon').html(this.options.iconText);
                    }
                }
                this.$element.css('overflow', 'hidden').prepend(this.$header);
            }
        },

        setGroup: function(groupName) {

            var that = this,
                group = this.group.name || groupName;
            this.group.ids = [];

            if (groupName !== undefined && groupName !== this.group.name) {
                group = groupName;
                this.group.name = group;
                this.$element.attr('data-' + PLUGIN_NAME + '-group', group);
            }
            if (group !== undefined && group !== "") {

                var count = 0;
                $.each($('.' + PLUGIN_NAME + '[data-' + PLUGIN_NAME + '-group=' + group + ']'), function(index, val) {

                    that.group.ids.push($(this)[0].id);

                    if (that.id == $(this)[0].id) {
                        that.group.index = count;
                    }
                    count++;
                });
            }
        },

        toggle: function() {

            if (this.state == STATES.OPENED) {
                this.close();
            }
            if (this.state == STATES.CLOSED) {
                this.open();
            }
        },

        open: function(param) {

            var that = this;

            $.each($('.' + PLUGIN_NAME), function(index, modal) {
                if ($(modal).data().iziModal !== undefined) {
                    var state = $(modal).iziModal('getState');
                    if (state == 'opened' || state == 'opening') {
                        $(modal).iziModal('close');
                    }
                }
            });

            (function urlHash() {
                if (that.options.history) {
                    var oldTitle = document.title;
                    document.title = oldTitle + " - " + that.options.title;
                    document.location.hash = that.id;
                    document.title = oldTitle;
                    //history.pushState({}, that.options.title, "#"+that.id);
                    window.$iziModal.history = true;
                } else {
                    window.$iziModal.history = false;
                }
            })();

            function opened() {

                // console.info('[ '+PLUGIN_NAME+' | '+that.id+' ] Opened.');

                that.state = STATES.OPENED;
                that.$element.trigger(STATES.OPENED);

                if (that.options.onOpened && (typeof(that.options.onOpened) === "function" || typeof(that.options.onOpened) === "object")) {
                    that.options.onOpened(that);
                }
            }

            function bindEvents() {

                // Close when button pressed
                that.$element.off('click', '[data-' + PLUGIN_NAME + '-close]').on('click', '[data-' + PLUGIN_NAME + '-close]', function(e) {
                    e.preventDefault();

                    var transition = $(e.currentTarget).attr('data-' + PLUGIN_NAME + '-transitionOut');

                    if (transition !== undefined) {
                        that.close({ transition: transition });
                    } else {
                        that.close();
                    }
                });

                // Expand when button pressed
                that.$element.off('click', '[data-' + PLUGIN_NAME + '-fullscreen]').on('click', '[data-' + PLUGIN_NAME + '-fullscreen]', function(e) {
                    e.preventDefault();
                    if (that.isFullscreen === true) {
                        that.isFullscreen = false;
                        that.$element.removeClass('isFullscreen');
                    } else {
                        that.isFullscreen = true;
                        that.$element.addClass('isFullscreen');
                    }
                    if (that.options.onFullscreen && typeof(that.options.onFullscreen) === "function") {
                        that.options.onFullscreen(that);
                    }
                    that.$element.trigger('fullscreen', that);
                });

                // Next modal
                that.$navigate.off('click', '.' + PLUGIN_NAME + '-navigate-next').on('click', '.' + PLUGIN_NAME + '-navigate-next', function(e) {
                    that.next(e);
                });
                that.$element.off('click', '[data-' + PLUGIN_NAME + '-next]').on('click', '[data-' + PLUGIN_NAME + '-next]', function(e) {
                    that.next(e);
                });

                // Previous modal
                that.$navigate.off('click', '.' + PLUGIN_NAME + '-navigate-prev').on('click', '.' + PLUGIN_NAME + '-navigate-prev', function(e) {
                    that.prev(e);
                });
                that.$element.off('click', '[data-' + PLUGIN_NAME + '-prev]').on('click', '[data-' + PLUGIN_NAME + '-prev]', function(e) {
                    that.prev(e);
                });
            }

            if (this.state == STATES.CLOSED) {

                bindEvents();

                this.setGroup();
                this.state = STATES.OPENING;
                this.$element.trigger(STATES.OPENING);
                this.$element.attr('aria-hidden', 'false');

                // console.info('[ '+PLUGIN_NAME+' | '+this.id+' ] Opening...');

                if (this.options.iframe === true) {

                    this.$element.find('.' + PLUGIN_NAME + '-content').addClass(PLUGIN_NAME + '-content-loader');

                    this.$element.find('.' + PLUGIN_NAME + '-iframe').on('load', function() {
                        $(this).parent().removeClass(PLUGIN_NAME + '-content-loader');
                    });

                    var href = null;
                    try {
                        href = $(param.currentTarget).attr('href') !== "" ? $(param.currentTarget).attr('href') : null;
                    } catch (e) {
                        // console.warn(e);
                    }
                    if ((this.options.iframeURL !== null) && (href === null || href === undefined)) {
                        href = this.options.iframeURL;
                    }
                    if (href === null || href === undefined) {
                        throw new Error("Failed to find iframe URL");
                    }
                    this.$element.find('.' + PLUGIN_NAME + '-iframe').attr('src', href);
                }


                if (this.options.bodyOverflow || isMobile) {
                    $('html').addClass(PLUGIN_NAME + '-isOverflow');
                    if (isMobile) {
                        $('body').css('overflow', 'hidden');
                    }
                }

                if (this.options.onOpening && typeof(this.options.onOpening) === "function") {
                    this.options.onOpening(this);
                }
                (function open() {

                    if (that.group.ids.length > 1) {

                        that.$navigate.appendTo('body');
                        that.$navigate.addClass('fadeIn');

                        if (that.options.navigateCaption === true) {
                            that.$navigate.find('.' + PLUGIN_NAME + '-navigate-caption').show();
                        }

                        var modalWidth = that.$element.outerWidth();
                        if (that.options.navigateArrows !== false) {
                            if (that.options.navigateArrows === 'closeScreenEdge') {
                                that.$navigate.find('.' + PLUGIN_NAME + '-navigate-prev').css('left', 0).show();
                                that.$navigate.find('.' + PLUGIN_NAME + '-navigate-next').css('right', 0).show();
                            } else {
                                that.$navigate.find('.' + PLUGIN_NAME + '-navigate-prev').css('margin-left', -((modalWidth / 2) + 84)).show();
                                that.$navigate.find('.' + PLUGIN_NAME + '-navigate-next').css('margin-right', -((modalWidth / 2) + 84)).show();
                            }
                        } else {
                            that.$navigate.find('.' + PLUGIN_NAME + '-navigate-prev').hide();
                            that.$navigate.find('.' + PLUGIN_NAME + '-navigate-next').hide();
                        }

                        var loop;
                        if (that.group.index === 0) {

                            loop = $('.' + PLUGIN_NAME + '[data-' + PLUGIN_NAME + '-group="' + that.group.name + '"][data-' + PLUGIN_NAME + '-loop]').length;

                            if (loop === 0 && that.options.loop === false)
                                that.$navigate.find('.' + PLUGIN_NAME + '-navigate-prev').hide();
                        }
                        if (that.group.index + 1 === that.group.ids.length) {

                            loop = $('.' + PLUGIN_NAME + '[data-' + PLUGIN_NAME + '-group="' + that.group.name + '"][data-' + PLUGIN_NAME + '-loop]').length;

                            if (loop === 0 && that.options.loop === false)
                                that.$navigate.find('.' + PLUGIN_NAME + '-navigate-next').hide();
                        }
                    }

                    if (that.options.overlay === true) {

                        if (that.options.appendToOverlay === false) {
                            that.$overlay.appendTo('body');
                        } else {
                            that.$overlay.appendTo(that.options.appendToOverlay);
                        }
                    }

                    if (that.options.transitionInOverlay) {
                        that.$overlay.addClass(that.options.transitionInOverlay);
                    }

                    var transitionIn = that.options.transitionIn;

                    if (typeof param == 'object') {
                        if (param.transition !== undefined || param.transitionIn !== undefined) {
                            transitionIn = param.transition || param.transitionIn;
                        }
                    }

                    if (transitionIn !== '' && animationEvent !== undefined) {

                        that.$element.addClass("transitionIn " + transitionIn).show();
                        that.$wrap.one(animationEvent, function() {

                            that.$element.removeClass(transitionIn + " transitionIn");
                            that.$overlay.removeClass(that.options.transitionInOverlay);
                            that.$navigate.removeClass('fadeIn');

                            opened();
                        });

                    } else {

                        that.$element.show();
                        opened();
                    }

                    if (that.options.pauseOnHover === true && that.options.pauseOnHover === true && that.options.timeout !== false && !isNaN(parseInt(that.options.timeout)) && that.options.timeout !== false && that.options.timeout !== 0) {

                        that.$element.off('mouseenter').on('mouseenter', function(event) {
                            event.preventDefault();
                            that.isPaused = true;
                        });
                        that.$element.off('mouseleave').on('mouseleave', function(event) {
                            event.preventDefault();
                            that.isPaused = false;
                        });
                    }

                })();

                if (this.options.timeout !== false && !isNaN(parseInt(this.options.timeout)) && this.options.timeout !== false && this.options.timeout !== 0) {

                    if (this.options.timeoutProgressbar === true) {

                        this.progressBar = {
                            hideEta: null,
                            maxHideTime: null,
                            currentTime: new Date().getTime(),
                            el: this.$element.find('.' + PLUGIN_NAME + '-progressbar > div'),
                            updateProgress: function() {
                                if (!that.isPaused) {

                                    that.progressBar.currentTime = that.progressBar.currentTime + 10;

                                    var percentage = ((that.progressBar.hideEta - (that.progressBar.currentTime)) / that.progressBar.maxHideTime) * 100;
                                    that.progressBar.el.width(percentage + '%');
                                    if (percentage < 0) {
                                        that.close();
                                    }
                                }
                            }
                        };
                        if (this.options.timeout > 0) {

                            this.progressBar.maxHideTime = parseFloat(this.options.timeout);
                            this.progressBar.hideEta = new Date().getTime() + this.progressBar.maxHideTime;
                            this.timerTimeout = setInterval(this.progressBar.updateProgress, 10);
                        }

                    } else {

                        this.timerTimeout = setTimeout(function() {
                            that.close();
                        }, that.options.timeout);
                    }
                }

                // Close on overlay click
                if (this.options.overlayClose && !this.$element.hasClass(this.options.transitionOut)) {
                    this.$overlay.click(function() {
                        that.close();
                    });
                }

                if (this.options.focusInput) {
                    this.$element.find(':input:not(button):enabled:visible:first').focus(); // Focus on the first field
                }

                (function updateTimer() {
                    that.recalcLayout();
                    that.timer = setTimeout(updateTimer, 300);
                })();

                // Close when the Escape key is pressed
                $document.on('keydown.' + PLUGIN_NAME, function(e) {
                    if (that.options.closeOnEscape && e.keyCode === 27) {
                        that.close();
                    }
                });

            }

        },

        close: function(param) {

            var that = this;

            function closed() {

                // console.info('[ '+PLUGIN_NAME+' | '+that.id+' ] Closed.');

                that.state = STATES.CLOSED;
                that.$element.trigger(STATES.CLOSED);

                if (that.options.iframe === true) {
                    that.$element.find('.' + PLUGIN_NAME + '-iframe').attr('src', "");
                }

                if (that.options.bodyOverflow || isMobile) {
                    $('html').removeClass(PLUGIN_NAME + '-isOverflow');
                    if (isMobile) {
                        $('body').css('overflow', 'auto');
                    }
                }

                if (that.options.onClosed && typeof(that.options.onClosed) === "function") {
                    that.options.onClosed(that);
                }

                if (that.options.restoreDefaultContent === true) {
                    that.$element.find('.' + PLUGIN_NAME + '-content').html(that.content);
                }

                if ($('.' + PLUGIN_NAME + ':visible').length === 0) {
                    $('html').removeClass(PLUGIN_NAME + '-isAttached');
                }
            }

            if (this.state == STATES.OPENED || this.state == STATES.OPENING) {

                $document.off('keydown.' + PLUGIN_NAME);

                this.state = STATES.CLOSING;
                this.$element.trigger(STATES.CLOSING);
                this.$element.attr('aria-hidden', 'true');

                // console.info('[ '+PLUGIN_NAME+' | '+this.id+' ] Closing...');

                clearTimeout(this.timer);
                clearTimeout(this.timerTimeout);

                if (that.options.onClosing && typeof(that.options.onClosing) === "function") {
                    that.options.onClosing(this);
                }

                var transitionOut = this.options.transitionOut;

                if (typeof param == 'object') {
                    if (param.transition !== undefined || param.transitionOut !== undefined) {
                        transitionOut = param.transition || param.transitionOut;
                    }
                }

                if ((transitionOut === false || transitionOut === '') || animationEvent === undefined) {

                    this.$element.hide();
                    this.$overlay.remove();
                    this.$navigate.remove();
                    closed();

                } else {

                    this.$element.attr('class', [
                        this.classes,
                        PLUGIN_NAME,
                        transitionOut,
                        this.options.theme == 'light' ? PLUGIN_NAME + '-light' : this.options.theme,
                        this.isFullscreen === true ? 'isFullscreen' : '',
                        this.options.rtl ? PLUGIN_NAME + '-rtl' : ''
                    ].join(' '));

                    this.$overlay.attr('class', PLUGIN_NAME + "-overlay " + this.options.transitionOutOverlay);

                    if (that.options.navigateArrows !== false) {
                        this.$navigate.attr('class', PLUGIN_NAME + "-navigate fadeOut");
                    }

                    this.$element.one(animationEvent, function() {

                        if (that.$element.hasClass(transitionOut)) {
                            that.$element.removeClass(transitionOut + " transitionOut").hide();
                        }
                        that.$overlay.removeClass(that.options.transitionOutOverlay).remove();
                        that.$navigate.removeClass('fadeOut').remove();
                        closed();
                    });

                }

            }
        },

        next: function(e) {

            var that = this;
            var transitionIn = 'fadeInRight';
            var transitionOut = 'fadeOutLeft';
            var modal = $('.' + PLUGIN_NAME + ':visible');
            var modals = {};
            modals.out = this;

            if (e !== undefined && typeof e !== 'object') {
                e.preventDefault();
                modal = $(e.currentTarget);
                transitionIn = modal.attr('data-' + PLUGIN_NAME + '-transitionIn');
                transitionOut = modal.attr('data-' + PLUGIN_NAME + '-transitionOut');
            } else if (e !== undefined) {
                if (e.transitionIn !== undefined) {
                    transitionIn = e.transitionIn;
                }
                if (e.transitionOut !== undefined) {
                    transitionOut = e.transitionOut;
                }
            }

            this.close({ transition: transitionOut });

            setTimeout(function() {

                var loop = $('.' + PLUGIN_NAME + '[data-' + PLUGIN_NAME + '-group="' + that.group.name + '"][data-' + PLUGIN_NAME + '-loop]').length;
                for (var i = that.group.index + 1; i <= that.group.ids.length; i++) {

                    try {
                        modals.in = $("#" + that.group.ids[i]).data().iziModal;
                    } catch (log) {
                        // console.info('[ '+PLUGIN_NAME+' ] No next modal.');
                    }
                    if (typeof modals.in !== 'undefined') {

                        $("#" + that.group.ids[i]).iziModal('open', { transition: transitionIn });
                        break;

                    } else {

                        if (i == that.group.ids.length && loop > 0 || that.options.loop === true) {

                            for (var index = 0; index <= that.group.ids.length; index++) {

                                modals.in = $("#" + that.group.ids[index]).data().iziModal;
                                if (typeof modals.in !== 'undefined') {
                                    $("#" + that.group.ids[index]).iziModal('open', { transition: transitionIn });
                                    break;
                                }
                            }
                        }
                    }
                }

            }, 200);

            $(document).trigger(PLUGIN_NAME + "-group-change", modals);
        },

        prev: function(e) {
            var that = this;
            var transitionIn = 'fadeInLeft';
            var transitionOut = 'fadeOutRight';
            var modal = $('.' + PLUGIN_NAME + ':visible');
            var modals = {};
            modals.out = this;

            if (e !== undefined && typeof e !== 'object') {
                e.preventDefault();
                modal = $(e.currentTarget);
                transitionIn = modal.attr('data-' + PLUGIN_NAME + '-transitionIn');
                transitionOut = modal.attr('data-' + PLUGIN_NAME + '-transitionOut');

            } else if (e !== undefined) {

                if (e.transitionIn !== undefined) {
                    transitionIn = e.transitionIn;
                }
                if (e.transitionOut !== undefined) {
                    transitionOut = e.transitionOut;
                }
            }

            this.close({ transition: transitionOut });

            setTimeout(function() {

                var loop = $('.' + PLUGIN_NAME + '[data-' + PLUGIN_NAME + '-group="' + that.group.name + '"][data-' + PLUGIN_NAME + '-loop]').length;

                for (var i = that.group.index; i >= 0; i--) {

                    try {
                        modals.in = $("#" + that.group.ids[i - 1]).data().iziModal;
                    } catch (log) {
                        // console.info('[ '+PLUGIN_NAME+' ] No previous modal.');
                    }
                    if (typeof modals.in !== 'undefined') {

                        $("#" + that.group.ids[i - 1]).iziModal('open', { transition: transitionIn });
                        break;

                    } else {

                        if (i === 0 && loop > 0 || that.options.loop === true) {

                            for (var index = that.group.ids.length - 1; index >= 0; index--) {

                                modals.in = $("#" + that.group.ids[index]).data().iziModal;
                                if (typeof modals.in !== 'undefined') {
                                    $("#" + that.group.ids[index]).iziModal('open', { transition: transitionIn });
                                    break;
                                }
                            }
                        }
                    }
                }

            }, 200);

            $(document).trigger(PLUGIN_NAME + "-group-change", modals);
        },

        destroy: function() {
            var e = $.Event('destroy');

            this.$element.trigger(e);

            $document.off('keydown.' + PLUGIN_NAME);

            clearTimeout(this.timer);
            clearTimeout(this.timerTimeout);

            if (this.options.iframe === true) {
                this.$element.find('.' + PLUGIN_NAME + '-iframe').remove();
            }
            this.$element.html(this.$element.find('.' + PLUGIN_NAME + '-content').html());

            this.$element.off('click', '[data-' + PLUGIN_NAME + '-close]');
            this.$element.off('click', '[data-' + PLUGIN_NAME + '-fullscreen]');

            this.$element
                .off('.' + PLUGIN_NAME)
                .removeData(PLUGIN_NAME)
                .attr('style', '');

            this.$overlay.remove();
            this.$navigate.remove();
            this.$element.trigger(STATES.DESTROYED);
            this.$element = null;
        },

        getState: function() {

            return this.state;
        },

        getGroup: function() {

            return this.group;
        },

        setWidth: function(width) {

            this.options.width = width;

            this.recalcWidth();

            var modalWidth = this.$element.outerWidth();
            if (this.options.navigateArrows === true || this.options.navigateArrows == 'closeToModal') {
                this.$navigate.find('.' + PLUGIN_NAME + '-navigate-prev').css('margin-left', -((modalWidth / 2) + 84)).show();
                this.$navigate.find('.' + PLUGIN_NAME + '-navigate-next').css('margin-right', -((modalWidth / 2) + 84)).show();
            }

        },

        setTop: function(top) {

            this.options.top = top;

            this.recalcVerticalPos(false);
        },

        setBottom: function(bottom) {

            this.options.bottom = bottom;

            this.recalcVerticalPos(false);

        },

        setHeader: function(status) {

            if (status) {
                this.$element.find('.' + PLUGIN_NAME + '-header').show();
            } else {
                this.headerHeight = 0;
                this.$element.find('.' + PLUGIN_NAME + '-header').hide();
            }
        },

        setTitle: function(title) {

            this.options.title = title;

            if (this.headerHeight === 0) {
                this.createHeader();
            }

            if (this.$header.find('.' + PLUGIN_NAME + '-header-title').length === 0) {
                this.$header.append('<h2 class="' + PLUGIN_NAME + '-header-title"></h2>');
            }

            this.$header.find('.' + PLUGIN_NAME + '-header-title').html(title);
        },

        setSubtitle: function(subtitle) {

            if (subtitle === '') {

                this.$header.find('.' + PLUGIN_NAME + '-header-subtitle').remove();
                this.$header.addClass(PLUGIN_NAME + '-noSubtitle');

            } else {

                if (this.$header.find('.' + PLUGIN_NAME + '-header-subtitle').length === 0) {
                    this.$header.append('<p class="' + PLUGIN_NAME + '-header-subtitle"></p>');
                }
                this.$header.removeClass(PLUGIN_NAME + '-noSubtitle');

            }

            this.$header.find('.' + PLUGIN_NAME + '-header-subtitle').html(subtitle);
            this.options.subtitle = subtitle;
        },

        setIcon: function(icon) {

            if (this.$header.find('.' + PLUGIN_NAME + '-header-icon').length === 0) {
                this.$header.prepend('<i class="' + PLUGIN_NAME + '-header-icon"></i>');
            }
            this.$header.find('.' + PLUGIN_NAME + '-header-icon').attr('class', PLUGIN_NAME + '-header-icon ' + icon);
            this.options.icon = icon;
        },

        setIconText: function(iconText) {

            this.$header.find('.' + PLUGIN_NAME + '-header-icon').html(iconText);
            this.options.iconText = iconText;
        },

        setHeaderColor: function(headerColor) {
            if (this.options.borderBottom === true) {
                this.$element.css('border-bottom', '3px solid ' + headerColor + '');
            }
            this.$header.css('background', headerColor);
            this.options.headerColor = headerColor;
        },

        setBackground: function(background) {
            if (background === false) {
                this.options.background = null;
                this.$element.css('background', '');
            } else {
                this.$element.css('background', background);
                this.options.background = background;
            }
        },

        setZindex: function(zIndex) {

            if (!isNaN(parseInt(this.options.zindex))) {
                this.options.zindex = zIndex;
                this.$element.css('z-index', zIndex);
                this.$navigate.css('z-index', zIndex - 1);
                this.$overlay.css('z-index', zIndex - 2);
            }
        },

        setFullscreen: function(value) {

            if (value) {
                this.isFullscreen = true;
                this.$element.addClass('isFullscreen');
            } else {
                this.isFullscreen = false;
                this.$element.removeClass('isFullscreen');
            }

        },

        setContent: function(content) {

            if (typeof content == "object") {
                var replace = content.default || false;
                if (replace === true) {
                    this.content = content.content;
                }
                content = content.content;
            }
            if (this.options.iframe === false) {
                this.$element.find('.' + PLUGIN_NAME + '-content').html(content);
            }

        },

        setTransitionIn: function(transition) {

            this.options.transitionIn = transition;
        },

        setTransitionOut: function(transition) {

            this.options.transitionOut = transition;
        },

        resetContent: function() {

            this.$element.find('.' + PLUGIN_NAME + '-content').html(this.content);

        },

        startLoading: function() {

            if (!this.$element.find('.' + PLUGIN_NAME + '-loader').length) {
                this.$element.append('<div class="' + PLUGIN_NAME + '-loader fadeIn"></div>');
            }
            this.$element.find('.' + PLUGIN_NAME + '-loader').css({
                top: this.headerHeight,
                borderRadius: this.options.radius
            });
        },

        stopLoading: function() {

            var $loader = this.$element.find('.' + PLUGIN_NAME + '-loader');

            if (!$loader.length) {
                this.$element.prepend('<div class="' + PLUGIN_NAME + '-loader fadeIn"></div>');
                $loader = this.$element.find('.' + PLUGIN_NAME + '-loader').css('border-radius', this.options.radius);
            }
            $loader.removeClass('fadeIn').addClass('fadeOut');
            setTimeout(function() {
                $loader.remove();
            }, 600);
        },

        recalcWidth: function() {

            var that = this;

            this.$element.css('max-width', this.options.width);

            if (isIE()) {
                var modalWidth = that.options.width;

                if (modalWidth.toString().split("%").length > 1) {
                    modalWidth = that.$element.outerWidth();
                }
                that.$element.css({
                    left: '50%',
                    marginLeft: -(modalWidth / 2)
                });
            }
        },

        recalcVerticalPos: function(first) {

            if (this.options.top !== null && this.options.top !== false) {
                this.$element.css('margin-top', this.options.top);
                if (this.options.top === 0) {
                    this.$element.css({
                        borderTopRightRadius: 0,
                        borderTopLeftRadius: 0
                    });
                }
            } else {
                if (first === false) {
                    this.$element.css({
                        marginTop: '',
                        borderRadius: this.options.radius
                    });
                }
            }
            if (this.options.bottom !== null && this.options.bottom !== false) {
                this.$element.css('margin-bottom', this.options.bottom);
                if (this.options.bottom === 0) {
                    this.$element.css({
                        borderBottomRightRadius: 0,
                        borderBottomLeftRadius: 0
                    });
                }
            } else {
                if (first === false) {
                    this.$element.css({
                        marginBottom: '',
                        borderRadius: this.options.radius
                    });
                }
            }

        },

        recalcLayout: function() {

            var that = this,
                windowHeight = $window.height(),
                modalHeight = this.$element.outerHeight(),
                modalWidth = this.$element.outerWidth(),
                contentHeight = this.$element.find('.' + PLUGIN_NAME + '-content')[0].scrollHeight,
                outerHeight = contentHeight + this.headerHeight,
                wrapperHeight = this.$element.innerHeight() - this.headerHeight,
                modalMargin = parseInt(-((this.$element.innerHeight() + 1) / 2)) + 'px',
                scrollTop = this.$wrap.scrollTop(),
                borderSize = 0;

            if (isIE()) {
                if (modalWidth >= $window.width() || this.isFullscreen === true) {
                    this.$element.css({
                        left: '0',
                        marginLeft: ''
                    });
                } else {
                    this.$element.css({
                        left: '50%',
                        marginLeft: -(modalWidth / 2)
                    });
                }
            }

            if (this.options.borderBottom === true && this.options.title !== "") {
                borderSize = 3;
            }

            if (this.$element.find('.' + PLUGIN_NAME + '-header').length && this.$element.find('.' + PLUGIN_NAME + '-header').is(':visible')) {
                this.headerHeight = parseInt(this.$element.find('.' + PLUGIN_NAME + '-header').innerHeight());
                this.$element.css('overflow', 'hidden');
            } else {
                this.headerHeight = 0;
                this.$element.css('overflow', '');
            }

            if (this.$element.find('.' + PLUGIN_NAME + '-loader').length) {
                this.$element.find('.' + PLUGIN_NAME + '-loader').css('top', this.headerHeight);
            }

            if (modalHeight !== this.modalHeight) {
                this.modalHeight = modalHeight;

                if (this.options.onResize && typeof(this.options.onResize) === "function") {
                    this.options.onResize(this);
                }
            }

            if (this.state == STATES.OPENED || this.state == STATES.OPENING) {

                if (this.options.iframe === true) {

                    // If the height of the window is smaller than the modal with iframe
                    if (windowHeight < (this.options.iframeHeight + this.headerHeight + borderSize) || this.isFullscreen === true) {
                        this.$element.find('.' + PLUGIN_NAME + '-iframe').css('height', windowHeight - (this.headerHeight + borderSize));
                    } else {
                        this.$element.find('.' + PLUGIN_NAME + '-iframe').css('height', this.options.iframeHeight);
                    }
                }

                if (modalHeight == windowHeight) {
                    this.$element.addClass('isAttached');
                } else {
                    this.$element.removeClass('isAttached');
                }

                if (this.isFullscreen === false && this.$element.width() >= $window.width()) {
                    this.$element.find('.' + PLUGIN_NAME + '-button-fullscreen').hide();
                } else {
                    this.$element.find('.' + PLUGIN_NAME + '-button-fullscreen').show();
                }
                this.recalcButtons();

                if (this.isFullscreen === false) {
                    windowHeight = windowHeight - (clearValue(this.options.top) || 0) - (clearValue(this.options.bottom) || 0);
                }
                // If the modal is larger than the height of the window..
                if (outerHeight > windowHeight) {
                    if (this.options.top > 0 && this.options.bottom === null && contentHeight < $window.height()) {
                        this.$element.addClass('isAttachedBottom');
                    }
                    if (this.options.bottom > 0 && this.options.top === null && contentHeight < $window.height()) {
                        this.$element.addClass('isAttachedTop');
                    }
                    $('html').addClass(PLUGIN_NAME + '-isAttached');
                    this.$element.css('height', windowHeight);

                } else {
                    this.$element.css('height', contentHeight + (this.headerHeight + borderSize));
                    this.$element.removeClass('isAttachedTop isAttachedBottom');
                    $('html').removeClass(PLUGIN_NAME + '-isAttached');
                }

                (function applyScroll() {
                    if (contentHeight > wrapperHeight && outerHeight > windowHeight) {
                        that.$element.addClass('hasScroll');
                        that.$wrap.css('height', modalHeight - (that.headerHeight + borderSize));
                    } else {
                        that.$element.removeClass('hasScroll');
                        that.$wrap.css('height', 'auto');
                    }
                })();

                (function applyShadow() {
                    if (wrapperHeight + scrollTop < (contentHeight - 30)) {
                        that.$element.addClass('hasShadow');
                    } else {
                        that.$element.removeClass('hasShadow');
                    }
                })();

            }
        },

        recalcButtons: function() {
            var widthButtons = this.$header.find('.' + PLUGIN_NAME + '-header-buttons').innerWidth() + 10;
            if (this.options.rtl === true) {
                this.$header.css('padding-left', widthButtons);
            } else {
                this.$header.css('padding-right', widthButtons);
            }
        }

    };

    function escapeHash(hash) {
        return '#' + encodeURIComponent(hash.substr(1));
    }

    $window.off('load.' + PLUGIN_NAME).on('load.' + PLUGIN_NAME, function(e) {

        var modalHash = escapeHash(document.location.hash);

        if (window.$iziModal.autoOpen === 0 && !$('.' + PLUGIN_NAME).is(":visible")) {

            try {
                var data = $(modalHash).data();
                if (typeof data !== 'undefined') {
                    if (data.iziModal.options.autoOpen !== false) {
                        $(modalHash).iziModal("open");
                    }
                }
            } catch (exc) { /* console.warn(exc); */ }
        }

    });

    $window.off('hashchange.' + PLUGIN_NAME).on('hashchange.' + PLUGIN_NAME, function(e) {

        var modalHash = escapeHash(document.location.hash);
        var data = $(modalHash).data();

        if (modalHash !== "") {
            try {
                if (typeof data !== 'undefined' && $(modalHash).iziModal('getState') !== 'opening') {

                    setTimeout(function() {
                        $(modalHash).iziModal("open");
                    }, 200);
                }
            } catch (exc) { /* console.warn(exc); */ }

        } else {

            if (window.$iziModal.history) {
                $.each($('.' + PLUGIN_NAME), function(index, modal) {
                    if ($(modal).data().iziModal !== undefined) {
                        var state = $(modal).iziModal('getState');
                        if (state == 'opened' || state == 'opening') {
                            $(modal).iziModal('close');
                        }
                    }
                });
            }
        }


    });

    $document.off('click', '[data-' + PLUGIN_NAME + '-open]').on('click', '[data-' + PLUGIN_NAME + '-open]', function(e) {
        e.preventDefault();

        var modal = $('.' + PLUGIN_NAME + ':visible');
        var openModal = $(e.currentTarget).attr('data-' + PLUGIN_NAME + '-open');
        var transitionIn = $(e.currentTarget).attr('data-' + PLUGIN_NAME + '-transitionIn');
        var transitionOut = $(e.currentTarget).attr('data-' + PLUGIN_NAME + '-transitionOut');

        if (transitionOut !== undefined) {
            modal.iziModal('close', {
                transition: transitionOut
            });
        } else {
            modal.iziModal('close');
        }

        setTimeout(function() {
            if (transitionIn !== undefined) {
                $(openModal).iziModal('open', {
                    transition: transitionIn
                });
            } else {
                $(openModal).iziModal('open');
            }
        }, 200);
    });

    $document.off('keyup.' + PLUGIN_NAME).on('keyup.' + PLUGIN_NAME, function(event) {

        if ($('.' + PLUGIN_NAME + ':visible').length) {
            var modal = $('.' + PLUGIN_NAME + ':visible')[0].id,
                group = $("#" + modal).iziModal('getGroup'),
                e = event || window.event,
                target = e.target || e.srcElement,
                modals = {};

            if (modal !== undefined && group.name !== undefined && !e.ctrlKey && !e.metaKey && !e.altKey && target.tagName.toUpperCase() !== 'INPUT' && target.tagName.toUpperCase() != 'TEXTAREA') { //&& $(e.target).is('body')

                if (e.keyCode === 37) { // left

                    $("#" + modal).iziModal('prev', e);
                } else if (e.keyCode === 39) { // right

                    $("#" + modal).iziModal('next', e);

                }
            }
        }
    });

    $.fn[PLUGIN_NAME] = function(option, args) {


        if (!$(this).length && typeof option == "object") {

            var newEL = {
                $el: document.createElement("div"),
                id: this.selector.split('#'),
                class: this.selector.split('.')
            };

            if (newEL.id.length > 1) {
                try {
                    newEL.$el = document.createElement(id[0]);
                } catch (exc) {}

                newEL.$el.id = this.selector.split('#')[1].trim();

            } else if (newEL.class.length > 1) {
                try {
                    newEL.$el = document.createElement(newEL.class[0]);
                } catch (exc) {}

                for (var x = 1; x < newEL.class.length; x++) {
                    newEL.$el.classList.add(newEL.class[x].trim());
                }
            }
            document.body.appendChild(newEL.$el);

            this.push($(this.selector));
        }
        var objs = this;

        for (var i = 0; i < objs.length; i++) {

            var $this = $(objs[i]);
            var data = $this.data(PLUGIN_NAME);
            var options = $.extend({}, $.fn[PLUGIN_NAME].defaults, $this.data(), typeof option == 'object' && option);

            if (!data && (!option || typeof option == 'object')) {

                $this.data(PLUGIN_NAME, (data = new iziModal($this, options)));
            } else if (typeof option == 'string' && typeof data != 'undefined') {

                return data[option].apply(data, [].concat(args));
            }
            if (options.autoOpen) { // Automatically open the modal if autoOpen setted true or ms

                if (!isNaN(parseInt(options.autoOpen))) {

                    setTimeout(function() {
                        data.open();
                    }, options.autoOpen);

                } else if (options.autoOpen === true) {

                    data.open();
                }
                window.$iziModal.autoOpen++;
            }
        }

        return this;
    };

    $.fn[PLUGIN_NAME].defaults = {
        title: '',
        subtitle: '',
        headerColor: '#88A0B9',
        background: null,
        theme: '', // light
        icon: null,
        iconText: null,
        iconColor: '',
        rtl: false,
        width: 600,
        top: null,
        bottom: null,
        borderBottom: true,
        padding: 0,
        radius: 3,
        zindex: 999,
        iframe: false,
        iframeHeight: 400,
        iframeURL: null,
        focusInput: true,
        group: '',
        loop: false,
        navigateCaption: true,
        navigateArrows: true, // Boolean, 'closeToModal', 'closeScreenEdge'
        history: false,
        restoreDefaultContent: false,
        autoOpen: 0, // Boolean, Number
        bodyOverflow: false,
        fullscreen: false,
        openFullscreen: false,
        closeOnEscape: true,
        closeButton: true,
        appendTo: 'body', // or false
        appendToOverlay: 'body', // or false
        overlay: true,
        overlayClose: true,
        overlayColor: 'rgba(0, 0, 0, 0.4)',
        timeout: false,
        timeoutProgressbar: false,
        pauseOnHover: false,
        timeoutProgressbarColor: 'rgba(255,255,255,0.5)',
        transitionIn: 'comingIn', // comingIn, bounceInDown, bounceInUp, fadeInDown, fadeInUp, fadeInLeft, fadeInRight, flipInX
        transitionOut: 'comingOut', // comingOut, bounceOutDown, bounceOutUp, fadeOutDown, fadeOutUp, , fadeOutLeft, fadeOutRight, flipOutX
        transitionInOverlay: 'fadeIn',
        transitionOutOverlay: 'fadeOut',
        onFullscreen: function() {},
        onResize: function() {},
        onOpening: function() {},
        onOpened: function() {},
        onClosing: function() {},
        onClosed: function() {},
        afterRender: function() {}
    };

    $.fn[PLUGIN_NAME].Constructor = iziModal;

    return $.fn.iziModal;

}));;
MONSTER('Pagarme.Application', function(Model, $, utils) {

    var createNames = [
        // Name for instance method create() if not component
    ];

    Model.init = function(container) {
        Model.setArrayIncludesPolyfill();
        Pagarme.BuildComponents.create(container);
        Pagarme.BuildCreate.init(container, createNames);
    };

    Model.setArrayIncludesPolyfill = function() {
        if (!Array.prototype.includes) {
            Object.defineProperty(Array.prototype, 'includes', {
                value: function(searchElement, fromIndex) {

                    if (this == null) {
                        throw new TypeError('"this" is null or not defined');
                    }

                    var o = Object(this);
                    var len = o.length >>> 0;

                    if (len === 0) {
                        return false;
                    }

                    var n = fromIndex | 0;
                    var k = Math.max(n >= 0 ? n : len - Math.abs(n), 0);

                    while (k < len) {
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

});;
MONSTER('Pagarme.Components.Capture', function(Model, $, Utils) {

    Model.fn.start = function() {
        this.init();
        this.lock = false;
    };

    Model.fn.init = function() {
        this.addEventListener();
        this.startModal();
    };

    Model.fn.addEventListener = function() {
        this.$el.find('[data-ref]:enabled').on('click', function(e) {
            e.preventDefault();
            var target = e.currentTarget;
            var selector = '[data-charge-action=' + target.dataset.ref + '-' + target.dataset.type + ']';
            $(selector).iziModal('open');
        });
    };

    Model.fn.onClickCancel = function(e) {
        e.preventDefault();
        this.handleEvents(e, 'cancel');
    };

    Model.fn.onClickCapture = function(e) {
        e.preventDefault();
        this.handleEvents(e, 'capture');
    };

    Model.fn.handleEvents = function(DOMEvent, type) {
        var target = $(DOMEvent.currentTarget);
        var wrapper = target.closest('[data-charge]');
        var chargeId = wrapper.data('charge');
        var amount = wrapper.find('[data-element=amount]').val();

        this.request(type, chargeId, amount);
    };

    Model.fn.request = function(mode, chargeId, amount) {
        if (this.lock) {
            return;
        }
        this.lock = true;
        this.requestInProgress();
        var ajax = $.ajax({
            'url': MONSTER.utils.getAjaxUrl(),
            'method': 'POST',
            'data': {
                'action': 'STW3dqRT6E',
                'mode': mode,
                'charge_id': chargeId,
                'amount': amount
            }
        });

        ajax.done(this._onDone.bind(this));
        ajax.fail(this._onFail.bind(this));
    };

    Model.fn.startModal = function() {
        var self = this;
        $('.modal').iziModal({
            padding: 20,
            onOpening: function(modal) {
                var amount = modal.$element.find('[data-element=amount]');
                const options = {
                    reverse: true,
                    onKeyPress: function(amountValue, event, field) {
                        if (!event.originalEvent) {
                            return;
                        }

                        amountValue = amountValue.replace(/^0+/, '')
                        if (amountValue[0] === ',') {
                            amountValue = '0' + amountValue;
                        }

                        if (amountValue && amountValue.length <= 2) {
                            amountValue = ('000' + amountValue).slice(-3);
                            field.val(amountValue);
                            field.trigger('input');
                            return;
                        }

                        field.val(amountValue);
                    }
                };
                amount.mask("#.##0,00", options);
                modal.$element.on('click', '[data-action=capture]', self.onClickCapture.bind(self));
                modal.$element.on('click', '[data-action=cancel]', self.onClickCancel.bind(self));
            }
        });
    };

    Model.fn.requestInProgress = function() {
        swal({
            title: ' ',
            text: 'Processando...',
            allowOutsideClick: false
        });
        swal.showLoading();
    };

    Model.fn._onDone = function(response) {
        this.lock = false;
        $('.modal').iziModal('close');
        swal.close();
        swal({
            type: 'success',
            title: ' ',
            html: response.data.message,
            showConfirmButton: false,
            timer: 2000
        }).then(
            function() {},
            function(dismiss) {
                window.location.reload();
            }
        );
    };

    Model.fn._onFail = function(xhr) {
        this.lock = false;
        swal.close();
        var data = JSON.parse(xhr.responseText);
        swal({
            type: 'error',
            title: ' ',
            html: data.message,
            showConfirmButton: false,
            timer: 2000
        });
    };

});;
MONSTER('Pagarme.Components.Settings', function(Model, $, Utils) {

    var errorClass = Utils.addPrefix('field-error');

    Model.fn.start = function() {
        this.init();
    };

    Model.fn.init = function() {
        this.installments = $('[data-field="installments"]');
        this.billet = $('[data-field="billet"]');
        this.installmentsMax = $('[data-field="installments-maximum"]');
        this.installmentsInterest = $('[data-field="installments-interest"]');
        this.installmentsMinAmount = $('[data-field="installments-min-amount"]');
        this.installmentsByFlag = $('[data-field="installments-by-flag"]');
        this.installmentsWithoutInterest = $('[data-field="installments-without-interest"]');
        this.installmentsInterestIncrease = $('[data-field="installments-interest-increase"]');
        this.antifraudSection = $('h3[id*="woo-pagarme-payments_section_antifraud"]');
        this.antifraudEnabled = $('[data-field="antifraud-enabled"]');
        this.voucherEnabled = $('#woocommerce_woo-pagarme-payments_enable_voucher');
        this.antifraudMinValue = $('[data-field="antifraud-min-value"]');
        this.ccBrands = $('[data-field="flags-select"]');
        this.ccAllowSave = $('[data-field="cc-allow-save"]');
        this.billetBank = $('[data-field="billet-bank"]');
        this.softDescriptor = $('[data-field="soft-descriptor"]');
        this.voucherSection = $('h3[id*="woo-pagarme-payments_section_voucher"]');
        this.voucherSoftDescriptor = $('[data-field="voucher-soft-descriptor"]');
        this.VoucherccBrands = $('[data-field="voucher-flags-select"]');
        this.cardWallet = $('[data-field="card-wallet"]');
        this.voucherCardWallet = $('[data-field="voucher-card-wallet"]');
        this.disintegrate = $('#btn-uninstall-hub');

        this.isGatewayIntegrationType = $('input[id*="woo-pagarme-payments_is_gateway_integration_type"]').prop("checked");
        this.installmentsMaxByFlag = this.installmentsByFlag.find('input[name*="cc_installments_by_flag[max_installment]"]');
        this.installmentsWithoutInterestByFlag = this.installmentsByFlag.find('input[name*="cc_installments_by_flag[no_interest]"]');

        this.handleInstallmentFieldsVisibility(this.elements.installmentsTypeSelect.val());
        this.handleGatewayIntegrationFieldsVisibility(this.isGatewayIntegrationType);
        this.handleBilletBankRequirement();

        this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments();
        this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag();

        this.setInstallmentsByFlags(null, true);

        this.addEventListener();
    };

    Model.fn.addEventListener = function() {
        this.on('keyup', 'soft-descriptor');
        this.on('change', 'environment');
        this.on('change', 'installments-type');
        this.on('change', 'is-gateway-integration-type');
        this.on('change', 'enable-billet');
        this.on('change', 'enable-multimethods-billet-card');

        this.elements.flagsSelect.on('select2:unselecting', this._onChangeFlags.bind(this));
        this.elements.flagsSelect.on('select2:selecting', this._onChangeFlags.bind(this));

        $('#mainform').on('submit', this._onSubmitForm.bind(this));
    };

    Model.fn._onKeyupSoftDescriptor = function(event) {
        var isGatewayIntegrationType = $('input[id*="woo-pagarme-payments_is_gateway_integration_type"]').prop("checked");

        if (!isGatewayIntegrationType && event.currentTarget.value.length > 13) {
            $(event.currentTarget).addClass(errorClass);
            return;
        }

        if (isGatewayIntegrationType && event.currentTarget.value.length > 22) {
            $(event.currentTarget).addClass(errorClass);
            return;
        }

        $(event.currentTarget).removeClass(errorClass);
    };

    Model.fn._onSubmitForm = function(event) {
        this.toTop = false;
        this.items = [];

        this.elements.validate.each(this._eachValidate.bind(this));

        return !~this.items.indexOf(true);
    };

    Model.fn._onChangeInstallmentsType = function(event) {
        this.handleInstallmentFieldsVisibility(event.currentTarget.value);
    };

    Model.fn._onChangeIsGatewayIntegrationType = function(event) {
        this.handleGatewayIntegrationFieldsVisibility(event.currentTarget.checked);
    };

    Model.fn._onChangeEnableBillet = function() {
        this.handleBilletBankRequirement();
    };

    Model.fn._onChangeEnableMultimethodsBilletCard = function() {
        this.handleBilletBankRequirement();
    };

    Model.fn._onChangeFlags = function(event) {
        this.setInstallmentsByFlags(event, false);
    };

    Model.fn._eachValidate = function(index, field) {
        var rect;
        var element = $(field),
            empty = element.isEmptyValue(),
            invalidMaxLength = element.val().length > element.prop("maxLength"),
            isFieldInvalid = empty || invalidMaxLength,
            func = isFieldInvalid ? 'addClass' : 'removeClass';

        if (!element.is(':visible')) {
            return;
        }

        element[func](errorClass);

        this.items[index] = isFieldInvalid;

        if (!isFieldInvalid) {
            return;
        }

        field.placeholder = field.dataset.errorMsg;

        if (!this.toTop) {
            this.toTop = true;
            rect = field.getBoundingClientRect();
            window.scrollTo(0, (rect.top + window.scrollY) - 32);
        }
    };

    Model.fn.handleInstallmentFieldsVisibility = function(value) {
        var installmentsMaxContainer = this.installmentsMax.closest('tr'),
            installmentsInterestContainer = this.installmentsInterest.closest('tr'),
            installmentsMinAmountContainer = this.installmentsMinAmount.closest("tr"),
            installmentsByFlagContainer = this.installmentsByFlag.closest('tr'),
            installmentsWithoutInterestContainer = this.installmentsWithoutInterest.closest('tr'),
            installmentsInterestIncreaseContainer = this.installmentsInterestIncrease.closest('tr');

        if (value == 1) {
            installmentsMaxContainer.show();
            installmentsMinAmountContainer.show();
            installmentsInterestContainer.show();
            installmentsInterestIncreaseContainer.show();
            installmentsWithoutInterestContainer.show();
            installmentsByFlagContainer.hide();
        } else {
            if (this.elements.flagsSelect.val()) {
                installmentsByFlagContainer.show();
                this.setInstallmentsByFlags(null, true);
            }
            installmentsMaxContainer.hide();
            installmentsMinAmountContainer.hide();
            installmentsInterestContainer.hide();
            installmentsInterestIncreaseContainer.hide();
            installmentsWithoutInterestContainer.hide();
        }
    };

    Model.fn.getOnlyGatewayBrands = function() {
        return 'option[value="credz"], ' +
            'option[value="sodexoalimentacao"], ' +
            'option[value="sodexocultura"], ' +
            'option[value="sodexogift"], ' +
            'option[value="sodexopremium"], ' +
            'option[value="sodexorefeicao"], ' +
            'option[value="sodexocombustivel"], ' +
            'option[value="vr"], ' +
            'option[value="alelo"], ' +
            'option[value="banese"], ' +
            'option[value="cabal"]';
    };

    Model.fn.getOnlyGatewayInstallments = function() {
        var installments = '';
        var maxInstallmentsLength = this.installmentsMax.children('option').length;

        for (let i = 13; i <= maxInstallmentsLength + 1; i++) {
            installments += `option[value="${i}"], `;
        }

        return installments.slice(0, -2);
    };

    Model.fn.setOriginalSelect = function(select) {
        if (select.data("originalHTML") === undefined) {
            select.data("originalHTML", select.html());
        }
    };

    Model.fn.removeOptions = function(select, options) {
        this.setOriginalSelect(select);
        options.remove();
    };

    Model.fn.restoreOptions = function(select) {
        var originalHTML = select.data("originalHTML");
        if (originalHTML !== undefined) {
            select.html(originalHTML);
        }
    };

    Model.fn.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments = function() {
        var installmentsMaxElement = this.installmentsMax;

        installmentsMaxElement.on('change', function() {
            setMaxInstallmentsWithoutInterest($(this).val());
        });

        function setMaxInstallmentsWithoutInterest(installmentsMax) {
            var installmentsWithoutInterest = $('[data-field="installments-without-interest"]');
            installmentsWithoutInterest.children('option').hide();
            installmentsWithoutInterest.children('option').filter(function() {
                return parseInt($(this).val()) <= installmentsMax;
            }).show();
            installmentsWithoutInterest.val(installmentsMax).change();
        }
    };

    Model.fn.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag = function() {
        var installmentsMaxElement = this.installmentsMaxByFlag;

        installmentsMaxElement.on('change', function() {
            setMaxInstallmentsWithoutInterest(
                $(this).val(),
                $(this).closest('tr').attr("data-flag")
            );
        });

        function setMaxInstallmentsWithoutInterest(installmentsMax, brandName) {
            var setMaxInstallmentsWithoutInterestOnFlag = $('[data-field="installments-by-flag"]')
                .find(`input[name*="cc_installments_by_flag[no_interest][${brandName}]"]`);
            setMaxInstallmentsWithoutInterestOnFlag.prop("max", installmentsMax);
        }
    };

    Model.fn.setupPSPOptions = function(
        antifraudEnabled,
        antifraudMinValue,
        ccAllowSave,
        billetBank,
        voucherSoftDescriptor,
        VoucherccBrands,
        cardWallet,
        voucherEnabled,
        voucherCardWallet,
        disintegrate
    ) {
        antifraudEnabled.hide();
        antifraudMinValue.hide();
        ccAllowSave.hide();
        billetBank.hide();
        this.antifraudSection.hide();
        this.voucherSection.hide();
        voucherSoftDescriptor.hide();
        VoucherccBrands.hide();
        cardWallet.hide();
        voucherEnabled.hide();
        voucherCardWallet.hide();
        disintegrate.hide();

        this.ccAllowSave.prop("checked", false);
        var $optionsToRemove = this.ccBrands.find(this.getOnlyGatewayBrands());
        this.removeOptions(this.ccBrands, $optionsToRemove);

        $("#woo-pagarme-payments_max_length_span").html("13");
        this.softDescriptor.prop('maxlength', 13);

        var $optionsToRemoveInstallments = this.installmentsMax.find(
            this.getOnlyGatewayInstallments()
        );
        var $optionsToRemoveInstallmentsWithoutInterest = this.installmentsWithoutInterest.find(
            this.getOnlyGatewayInstallments()
        );
        this.removeOptions(this.installmentsMax, $optionsToRemoveInstallments);
        this.removeOptions(this.installmentsWithoutInterest, $optionsToRemoveInstallmentsWithoutInterest);

        this.installmentsMaxByFlag.prop("max", 12);
    };

    Model.fn.setupGatewayOptions = function(
        antifraudEnabled,
        antifraudMinValue,
        ccAllowSave,
        billetBank,
        voucherSoftDescriptor,
        VoucherccBrands,
        cardWallet,
        voucherEnabled,
        voucherCardWallet,
        disintegrate
    ) {
        antifraudEnabled.show();
        antifraudMinValue.show();
        ccAllowSave.show();
        billetBank.show();
        this.antifraudSection.show();
        this.voucherSection.show();
        voucherSoftDescriptor.show();
        VoucherccBrands.show();
        cardWallet.show();
        voucherCardWallet.show();
        voucherEnabled.show();
        disintegrate.show();

        this.restoreOptions(this.ccBrands);

        $("#woo-pagarme-payments_max_length_span").html("22");
        this.softDescriptor.prop('maxlength', 22);

        this.restoreOptions(this.installmentsMax);
        this.restoreOptions(this.installmentsWithoutInterest);

        this.installmentsMaxByFlag.prop("max", 24);
    };

    Model.fn.handleGatewayIntegrationFieldsVisibility = function(isGateway) {

        var antifraudEnabled = this.antifraudEnabled.closest('tr'),
            antifraudMinValue = this.antifraudMinValue.closest('tr'),
            ccAllowSave = this.ccAllowSave.closest('tr'),
            billetBank = this.billetBank.closest('tr'),
            voucherSoftDescriptor = this.voucherSoftDescriptor.closest('tr'),
            VoucherccBrands = this.VoucherccBrands.closest('tr'),
            voucherEnabled = this.voucherEnabled.closest('tr'),
            voucherCardWallet = this.voucherCardWallet.closest('tr'),
            disintegrate = this.disintegrate.closest('p'),
            cardWallet = this.cardWallet.closest('tr');

        if (isGateway) {
            return this.setupGatewayOptions(
                antifraudEnabled,
                antifraudMinValue,
                ccAllowSave,
                billetBank,
                voucherSoftDescriptor,
                VoucherccBrands,
                cardWallet,
                voucherEnabled,
                voucherCardWallet,
                disintegrate
            );

        }

        return this.setupPSPOptions(
            antifraudEnabled,
            antifraudMinValue,
            ccAllowSave,
            billetBank,
            voucherSoftDescriptor,
            VoucherccBrands,
            cardWallet,
            voucherEnabled,
            voucherCardWallet,
            disintegrate
        );
    };

    Model.fn.handleBilletBankRequirement = function() {
        const billetBankElementId = '#woocommerce_woo-pagarme-payments_billet_bank';
        let bankRequirementFields = $('[data-requires-field="billet-bank"]');
        let billetBankIsRequired = false;

        bankRequirementFields.each(function() {
            if ($(this).prop("checked")) {
                billetBankIsRequired = true;
                return false;
            }
        });

        if (billetBankIsRequired) {
            $(billetBankElementId).attr('required', true);
            return;
        }

        $(billetBankElementId).attr('required', false);
    };

    Model.fn.setInstallmentsByFlags = function(event, firstLoad) {
        var flags = this.elements.flagsSelect.val() || [];
        var flagsWrapper = this.installmentsByFlag.closest('tr');
        var allFlags = $('[data-flag]');

        if (parseInt(this.elements.installmentsTypeSelect.val()) !== 2) {
            allFlags.hide();
            flagsWrapper.hide();
            return;
        }

        if (!firstLoad) {
            var selectedItem = event.params.args.data.id;
            var filtered = flags;

            flagsWrapper.show();

            if (event.params.name == 'unselect') {
                filtered = flags.filter(function(i) {
                    return i != selectedItem;
                });

                if (filtered.length == 0) {
                    this.installmentsByFlag.closest('tr').hide();
                }
            } else {
                filtered.push(selectedItem);
            }

            allFlags.hide();

            filtered.map(function(item) {
                var element = $('[data-flag=' + item + ']');
                element.show();
            });
        } else {
            if (flags.length === 0) {
                allFlags.hide();
                flagsWrapper.hide();
                return;
            }

            allFlags.each(function(index, item) {
                item = $(item);
                if (!flags.includes(item.data('flag'))) {
                    item.hide();
                } else {
                    item.show();
                }
            });
        }
    };

});


;
jQuery(function($) {
    var context = $('body');

    Pagarme.vars = {
        body: context
    };

    Pagarme.Application.init.apply(null, [context]);
});
