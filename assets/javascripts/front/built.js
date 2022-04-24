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
MONSTER('Pagarme.Application', function(Model, $, utils) {

    var createNames = [];

    Model.init = function(container) {
        Pagarme.BuildComponents.create(container);
        Pagarme.BuildCreate.init(container, createNames);
    };

});;
MONSTER('Pagarme.CheckoutErrors', function(Model, $, utils) {

    Model.create = function(context) {
        this.context = context;
        this.init();
    };

    Model.init = function() {
        $('body').on('onPagarmeCheckoutFail', this.error.bind(this));
    };

    Model.error = function(event, errorThrown) {
        var error, rect;
        var element = $('#wcmp-checkout-errors');

        swal.close();

        this.errorList = '';

        for (error in errorThrown.errors) {
            (errorThrown.errors[error] || []).forEach(this.parseErrorsList.bind(this, error));
        }

        element.find('.woocommerce-error').html(this.errorList);
        element.slideDown();

        rect = element.get(0).getBoundingClientRect();

        jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');

        window.scrollTo(0, (rect.top + window.scrollY) - 40);
    };

    Model.parseErrorsList = function(error, message, index) {
        this.errorList += '<li>' + this.translateErrors(error, message) + '<li>';
    };

    Model.translateErrors = function(error, message) {
        error = error.replace('request.', '');
        var output = error + ': ' + message;
        var ptBrMessages = PagarmeGlobalVars.checkoutErrors.pt_BR;

        if (PagarmeGlobalVars.WPLANG != 'pt_BR') {
            return output;
        }

        if (ptBrMessages.hasOwnProperty(output)) {
            return ptBrMessages[output];
        }

        return output;
    };

});;
MONSTER('Pagarme.Components.CheckoutTransparent', function(Model, $, utils) {

    Model.fn.start = function() {
        this.lock = false;

        this.addEventListener();

        Pagarme.CheckoutErrors.create(this);

        if (typeof $().select2 === 'function') {
            this.applySelect2();
        }

        $('div#woo-pagarme-payment-methods > ul li input:first').attr('checked', 'checked');
        $('div#woo-pagarme-payment-methods > ul li:first').find('.payment_box').show();

        $('input#payment_method_woo-pagarme-payments').click(function() {
            $('div#woo-pagarme-payment-methods').removeAttr('style');

            $('div#woo-pagarme-payment-methods > ul li').find(function() {
                $('input:checked:last').nextAll().show();
            });
        });

    };

    Model.fn.addEventListener = function() {

        $('#place_order').on('click', this._onSubmit.bind(this));

        this.click('tab');
        this.click('choose-payment');

        if (this.elements.cardHolderName) {
            this.elements.cardHolderName.on('keypress', this.removeSpecialChars);
            this.elements.cardHolderName.on('blur', this.removeSpecialChars);
        }

        $('[data-required=true]').on('keypress', this.setAsValid);
        $('[data-required=true]').on('blur', this.setAsValid);


        if (this.elements.cardNumber) {
            this.elements.cardNumber.on('keyup', this.updateInstallments);
            this.elements.cardNumber.on('keydown', this.updateInstallments);
        }
    };

    Model.fn._onSubmit = function(e) {
        e.preventDefault();

        if (!this.validate()) {
            jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');
            return false;
        }

        $('body').on('onPagarmeCheckoutDone', function() {
            if ($('input[name=payment_method]').val() == '2_cards') {
                return;
            }
        }.bind(this));

        $('body').on('onPagarme2CardsDone', function() {
            if (window.Pagarme2Cards === 2) {
                this.loadSwal();
            }
            window.Pagarme2Cards = 0;
        }.bind(this));

        jQuery('#wcmp-submit').attr('disabled', 'disabled');

        $('body').trigger('onPagarmeSubmit', [e])

        if (
            $('input[name=payment_method]').val() === 'billet' ||
            $('input[name=payment_method]').val() === 'pix') {
            this.loadSwal();
        }


    };

    Model.fn._onClickTab = function(event) {
        window.location = this.data.paymentUrl + '&tab=' + event.currentTarget.dataset.ref + $(event.currentTarget).attr('href');
    };

    Model.fn._onClickChoosePayment = function(e) {
        var target = $(e.currentTarget);
        var forms = $('.wc-credit-card-form');

        forms.attr('disabled', true);
        target.prev().removeAttr('disabled');
    };

    Model.fn._done = function(response) {
        this.lock = false;
        if (!response.success) {
            this.failMessage(
                this.getFailMessage(response.data)
            );
        } else {
            this.successMessage();
        }

        var self = this;

        if (response.data.status == "failed") {
            swal({
                type: 'error',
                html: this.getFailMessage()
            }).then(function() {
                window.location.href = self.data.returnUrl;
            });
        }
    };

    Model.fn._fail = function(jqXHR, textStatus, errorThrown) {
        this.lock = false;
        this.failMessage();
    };

    Model.fn.getFailMessage = function(message = "") {
        if (!message) {
            return "Transação não autorizada."
        }

        return message;
    };

    Model.fn.failMessage = function(message) {
        swal({
            type: 'error',
            html: message || this.data.swal.text_default
        });
    };

    Model.fn.successMessage = function() {
        var self = this;

        swal({
            type: 'success',
            html: this.data.swal.text_success,
            allowOutsideClick: false
        }).then(function() {
            window.location.href = self.data.returnUrl;
        });
    };

    Model.fn.applySelect2 = function() {
        this.$el.byAction('select2').select2({
            width: '100%',
            minimumResultsForSearch: 20
        });

        this.$el.find('[data-element=state]').select2({
            width: '100%',
            minimumResultsForSearch: 20
        });
    };

    Model.fn.removeSpecialChars = function() {
        this.value = this.value.replace(/[^a-zA-Z ]/g, "");
    };

    Model.fn.setAsValid = function() {
        if (this.value) {
            $(this).removeClass('invalid');
        }
    };

    Model.fn.updateInstallments = function(e) {
        if (!this.value) {
            var option = '<option value="">...</option>';
            var select = $(e.currentTarget)
                .closest('fieldset')
                .find('[data-element=installments]');

            if (select.data('type') == 2) {
                select.html(option);
            }
        }
    };

    Model.fn.validate = function() {
        var requiredFields = $('[data-required=true]:visible'),
            isValid = true;

        requiredFields.each(function(index, item) {
            var field = $(item);
            if (!$.trim(field.val())) {
                if (field.attr('id') == 'installments') {
                    field = field.next(); //Select2 span
                }
                field.addClass('invalid');
                isValid = false;
            }
        });

        return isValid;
    };

    Model.fn._onOpenSwal = function() {
        if (this.lock) {
            return;
        }

        this.lock = true;

        swal.showLoading();

        var inputsSubmit = this.$el.serializeArray();

        this.ajax({
            url: this.data.apiRequest,
            data: {
                order: this.data.order,
                fields: inputsSubmit
            },
            success: function(data) {
                if (data.success == false) {
                    jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');
                }
            },
            fail: function(data) {
                jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');
            }
        });
    };

    Model.fn.hasCardId = function(wrapper) {
        var element = wrapper.find('[data-element="choose-credit-card"]');

        if (element === undefined || element.length === 0) {
            return false;
        }

        return element.val().trim() !== '';
    };

    Model.fn.requestInProgress = function() {
        swal({
            title: this.data.swal.title,
            text: this.data.swal.text,
            allowOutsideClick: false
        });
        swal.showLoading();
    };

    Model.fn.isTwoCardsPayment = function(firstInput, secondInput) {
        return firstInput.id.includes("card") && secondInput.id.includes("card");
    };

    Model.fn.isBilletAndCardPayment = function(firstInput, secondInput) {
        return (firstInput.id.includes("card") && secondInput.id.includes("billet")) ||
            (firstInput.id.includes("billet") && secondInput.id.includes("card"));
    };

    Model.fn.refreshBothInstallmentsSelects = function(event, secondInput) {
        event.currentTarget = secondInput;
        event.target = secondInput;
    };

    Model.fn.refreshCardInstallmentSelect = function(event, secondInput) {
        const targetInput = event.target.id.includes("card") ? event.target : secondInput;

        event.currentTarget = targetInput;
        event.target = targetInput;
    }

});;
MONSTER('Pagarme.Components.Installments', function(Model, $, utils) {
    Model.fn.start = function() {
        this.lock = false;
        this.total = this.$el.data('total');
        this.addEventListener();
    };

    Model.fn.addEventListener = function() {
        if (this.$el.data('type') == 2) {
            $('body').on('pagarmeChangeBrand', this.onChangeBrand.bind(this));
        }

        $('body').on('pagarmeBlurCardOrderValue', this.onBlurCardOrderValue.bind(this));
    };

    Model.fn.onChangeBrand = function(event, brand, cardNumberLength, wrapper) {
        var cardOrderValue = wrapper.find('[data-element=card-order-value]');

        if (cardOrderValue.length) {
            this.total = cardOrderValue.val();
            this.total = this.total.replace('.', '');
            this.total = this.total.replace(',', '.');
        }

        if (cardNumberLength >= 13 && cardNumberLength <= 19) {
            this.request(brand, this.total, wrapper);
        }
    };

    Model.fn.onBlurCardOrderValue = function(event, brand, total, wrapper) {
        this.request(brand, total, wrapper);
    };

    Model.fn.request = function(brand, total, wrapper) {
        return;
        var storageName = btoa(brand + total);
        var storage = sessionStorage.getItem(storageName);
        var select = wrapper.find('[data-element=installments]');

        if (storage) {
            select.html(storage);
            return false;
        }

        var ajax = $.ajax({
            'url': MONSTER.utils.getAjaxUrl(),
            'data': {
                'action': 'xqRhBHJ5sW',
                'flag': brand,
                'total': total
            }
        });

        ajax.done($.proxy(this._done, this, select, storageName));
        ajax.fail(this._fail.bind(this));

        if (this.lock) {
            return;
        }

        this.lock = true;

        this.showLoader();

    };

    Model.fn._done = function(select, storageName, response) {
        this.lock = false;
        select.html(response);
        sessionStorage.setItem(storageName, response);
        this.removeLoader();
    };

    Model.fn._fail = function() {
        this.lock = false;
        this.removeLoader();
    };

    Model.fn.showLoader = function() {
        $('#wcmp-checkout-form').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    };

    Model.fn.removeLoader = function() {
        $('#wcmp-checkout-form').unblock();
    };
});;
MONSTER('Pagarme.Components.PagarmeCheckout', function(Model, $, utils) {

    window.Pagarme2Cards = 0;

    window.pagarmeQrCodeCopy = function() {
        const qrCodeElement = document.getElementById("pagarme-qr-code");

        if (!qrCodeElement) {
            return;
        }

        const rawCode = qrCodeElement.getAttribute("rawCode");

        const input = document.createElement('input');
        document.body.appendChild(input)
        input.value = rawCode;
        input.select();
        document.execCommand('copy', false);
        input.remove();

        alert("Código copiado.");

    }

    Model.fn.start = function() {
        this.script = $('[data-pagarmecheckout-app-id]');
        this.form = $('[data-pagarmecheckout-form]');
        this.suffix = this.$el.data('pagarmecheckoutSuffix') || 1;
        this.creditCardNumber = this.$el.find('[data-pagarmecheckout-element="number"]');
        this.creditCardBrand = this.$el.find('[data-pagarmecheckout-element="brand"]');
        this.brandInput = this.$el.find('[data-pagarmecheckout-element="brand-input"]');
        this.chooseCreditCard = this.$el.closest('fieldset').find('[data-element="choose-credit-card"]');
        this.cvv = this.$el.find('[data-pagarmecheckout-element="cvv"]');
        this.appId = this.script.data('pagarmecheckoutAppId');
        this.apiURL = 'https://api.mundipagg.com/core/v1/tokens?appId=' + this.appId;

        this.addEventListener();
    };

    Model.fn.addEventListener = function() {
        this.creditCardNumber.on('keyup', this.keyEventHandlerCard.bind(this));
        $('body').on('onPagarmeSubmit', this.onSubmit.bind(this));
    };

    Model.fn.hasCardId = function() {
        if (this.chooseCreditCard === undefined || this.chooseCreditCard.length === 0) {
            return false;
        }
        return this.chooseCreditCard.val().trim() !== '';
    };

    Model.fn.createCheckoutObj = function(fields) {
        var obj = {},
            i = 0,
            length = fields.length,
            prop, key;
        obj['type'] = 'credit_card';
        for (i = 0; i < length; i += 1) {
            if (fields[i].getAttribute('data-pagarmecheckout-element') === 'exp_date') {
                var sep = fields[i].getAttribute('data-pagarmecheckout-separator') ? fields[i].getAttribute('data-pagarmecheckout-separator') : '/';
                var values = fields[i].value.split(sep);
                obj['exp_month'] = values[0];
                obj['exp_year'] = values[1];
            } else {
                prop = fields[i].getAttribute('data-pagarmecheckout-element');
                key = fields[i].value;

                if (prop == 'brand') {
                    key = fields[i].getAttribute('data-pagarmecheckout-brand');
                }
            }
            obj[prop] = key;
        }
        return obj;
    };

    Model.fn.disableFields = function(fields) {
        for (var i = 0; i < fields.length; i += 1) {
            fields[i].setAttribute('disabled', 'disabled');
        }
    };

    Model.fn.enableFields = function(fields) {
        for (var i = 0; i < fields.length; i += 1) {
            fields[i].removeAttribute('disabled');
        }
    };

    Model.fn.getAPIData = function(url, data, success, fail) {
        var xhr = new XMLHttpRequest();
        var suffix = this.suffix;

        xhr.open('POST', url);
        xhr.onreadystatechange = function() {
            if (xhr.readyState < 4) {
                return;
            }
            if (xhr.status == 200) {
                success.call(null, xhr.responseText, suffix);
            } else {
                var errorObj = {};
                if (xhr.response) {
                    errorObj = JSON.parse(xhr.response);
                    errorObj.statusCode = xhr.status;
                } else {
                    errorObj.statusCode = 503;
                }

                fail.call(null, errorObj, suffix);
            }
        };

        xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
        xhr.send(JSON.stringify({
            card: data
        }));

        return xhr;
    };

    Model.fn.getBrand = function(types, bin) {
        var oldPrefix = '';
        var currentBrand;
        for (var i = 0; i < types.length; i += 1) {
            var current_type = types[i];
            for (var j = 0; j < current_type.prefixes.length; j += 1) {
                var prefix = current_type.prefixes[j].toString();
                if (bin.indexOf(prefix) === 0 && oldPrefix.length < prefix.length) {
                    oldPrefix = prefix;
                    currentBrand = current_type.brand;
                }
            }
        }
        return currentBrand;
    };

    Model.fn.changeBrand = function(brand, cardNumberLength) {
        var $brand = this.creditCardBrand.get(0);
        var wrapper = this.creditCardBrand.closest('fieldset');
        var imageSrc = 'https://cdn.mundipagg.com/assets/images/logos/brands/png/';
        var $img = $('img', $brand)[0];
        var src;

        $brand.setAttribute('data-pagarmecheckout-brand', brand);
        this.brandInput.val(brand);

        jQuery('body').trigger('pagarmeChangeBrand', [brand, cardNumberLength, wrapper]);

        if (brand === '') {
            $brand.innerHTML = '';
        } else {
            if ($brand.getAttribute('data-pagarmecheckout-brand-image') !== null) {
                src = imageSrc + brand + '.png';
                if (!$img) {
                    var $newImg = document.createElement('img');
                    $newImg.setAttribute('src', src);
                    $newImg.setAttribute('style', 'float: right;\n' +
                        'border: 0;\n' +
                        'padding: 0;\n' +
                        'max-height: 1.618em;');
                    $brand.appendChild($newImg);
                } else {
                    $img.setAttribute('src', src);
                }
            }
        }
    };

    Model.fn.keyEventHandlerCard = function(event) {
        var elem = event.currentTarget;
        var cardNumber = elem.value.replace(/\s/g, '');
        var bin = cardNumber.substr(0, 6);
        var types = this.getCardTypes();
        var brand;
        if (cardNumber.length >= 6) {
            brand = this.getBrand(types, bin);
            if (brand) {
                this.changeBrand(brand, cardNumber.length);
            } else {
                this.changeBrand('', cardNumber.length);
            }
        } else {
            this.changeBrand('', cardNumber.length);
        }
    };

    Model.fn.serialize = function(obj) {
        var str = [];
        for (var p in obj) {
            if (obj.hasOwnProperty(p)) {
                str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
            }
        }
        return str.join("&");
    };

    Model.fn.getCardTypes = function() {
        return [{
            brand: 'vr',
            brandName: 'VR',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [637036, 627416, 636350, 637037]
        }, {
            brand: 'mais',
            brandName: 'Mais',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [628028]
        }, {
            brand: 'paqueta',
            brandName: 'Paqueta',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [960371]
        }, {
            brand: 'sodexo',
            brandName: 'Sodexo',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [603389, 606071, 606069, 600818, 606070, 606068]
        }, {
            brand: 'hipercard',
            brandName: 'Hipercard',
            gaps: [4, 8, 12],
            lenghts: [13, 16, 19],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [384100, 384140, 384160, 60, 606282, 637095, 637568, 637599, 637609, 637612, 637600]
        }, {
            brand: 'discover',
            brandName: 'Discover',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 4,
            prefixes: [6011, 622, 64, 65]
        }, {
            brand: 'diners',
            brandName: 'Diners',
            gaps: [4, 8, 12],
            lenghts: [14, 16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [300, 301, 302, 303, 304, 305, 36, 38]
        }, {
            brand: 'amex',
            brandName: 'Amex',
            gaps: [4, 10],
            lenghts: [15],
            mask: '/(\\d{1,4})(\\d{1,6})?(\\d{1,5})?/g',
            cvv: 4,
            prefixes: [34, 37]
        }, {
            brand: 'aura',
            brandName: 'Aura',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [50]
        }, {
            brand: 'jcb',
            brandName: 'JCB',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [35, 2131, 1800]
        }, {
            brand: 'visa',
            brandName: 'Visa',
            gaps: [4, 8, 12],
            lenghts: [13, 16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [4]
        }, {
            brand: 'mastercard',
            brandName: 'Mastercard',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [5, 2]
        }, {
            brand: 'elo',
            brandName: 'Elo',
            gaps: [4, 8, 12],
            lenghts: [16],
            mask: '/(\\d{1,4})/g',
            cvv: 3,
            prefixes: [401178, 401179, 431274, 438935, 451416, 457393, 457631, 457632, 498405, 498410, 498411, 498412, 498418, 498419, 498420, 498421, 498422, 498427, 498428, 498429, 498432, 498433, 498472, 498473, 498487, 498493, 498494, 498497, 498498, 504175, 506699, 506700, 506701, 506702, 506703, 506704, 506705, 506706, 506707, 506708, 506709, 506710, 506711, 506712, 506713, 506714, 506715, 506716, 506717, 506718, 506719, 506720, 506721, 506722, 506723, 506724, 506725, 506726, 506727, 506728, 506729, 506730, 506731, 506732, 506733, 506734, 506735, 506736, 506737, 506738, 506739, 506740, 506741, 506742, 506743, 506744, 506745, 506746, 506747, 506748, 506749, 506750, 506751, 506752, 506753, 506754, 506755, 506756, 506757, 506758, 506759, 506760, 506761, 506762, 506763, 506764, 506765, 506766, 506767, 506768, 506769, 506770, 506771, 506772, 506773, 506774, 506775, 506776, 506777, 506778, 509000, 509001, 509002, 509003, 509004, 509005, 509006, 509007, 509008, 509009, 509010, 509011, 509012, 509013, 509014, 509015, 509016, 509017, 509018, 509019, 509020, 509021, 509022, 509023, 509024, 509025, 509026, 509027, 509028, 509029, 509030, 509031, 509032, 509033, 509034, 509035, 509036, 509037, 509038, 509039, 509040, 509041, 509042, 509043, 509044, 509045, 509046, 509047, 509048, 509049, 509050, 509051, 509052, 509053, 509054, 509055, 509056, 509057, 509058, 509059, 509060, 509061, 509062, 509063, 509064, 509065, 509066, 509067, 509068, 509069, 509070, 509071, 509072, 509073, 509074, 509075, 509076, 509077, 509078, 509079, 509080, 509081, 509082, 509083, 509084, 509085, 509086, 509087, 509088, 509089, 509090, 509091, 509092, 509093, 509094, 509095, 509096, 509097, 509098, 509099, 509100, 509101, 509102, 509103, 509104, 509105, 509106, 509107, 509108, 509109, 509110, 509111, 509112, 509113, 509114, 509115, 509116, 509117, 509118, 509119, 509120, 509121, 509122, 509123, 509124, 509125, 509126, 509127, 509128, 509129, 509130, 509131, 509132, 509133, 509134, 509135, 509136, 509137, 509138, 509139, 509140, 509141, 509142, 509143, 509144, 509145, 509146, 509147, 509148, 509149, 509150, 509151, 509152, 509153, 509154, 509155, 509156, 509157, 509158, 509159, 509160, 509161, 509162, 509163, 509164, 509165, 509166, 509167, 509168, 509169, 509170, 509171, 509172, 509173, 509174, 509175, 509176, 509177, 509178, 509179, 509180, 509181, 509182, 509183, 509184, 509185, 509186, 509187, 509188, 509189, 509190, 509191, 509192, 509193, 509194, 509195, 509196, 509197, 509198, 509199, 509200, 509201, 509202, 509203, 509204, 509205, 509206, 509207, 509208, 509209, 509210, 509211, 509212, 509213, 509214, 509215, 509216, 509217, 509218, 509219, 509220, 509221, 509222, 509223, 509224, 509225, 509226, 509227, 509228, 509229, 509230, 509231, 509232, 509233, 509234, 509235, 509236, 509237, 509238, 509239, 509240, 509241, 509242, 509243, 509244, 509245, 509246, 509247, 509248, 509249, 509250, 509251, 509252, 509253, 509254, 509255, 509256, 509257, 509258, 509259, 509260, 509261, 509262, 509263, 509264, 509265, 509266, 509267, 509268, 509269, 509270, 509271, 509272, 509273, 509274, 509275, 509276, 509277, 509278, 509279, 509280, 509281, 509282, 509283, 509284, 509285, 509286, 509287, 509288, 509289, 509290, 509291, 509292, 509293, 509294, 509295, 509296, 509297, 509298, 509299, 509300, 509301, 509302, 509303, 509304, 509305, 509306, 509307, 509308, 509309, 509310, 509311, 509312, 509313, 509314, 509315, 509316, 509317, 509318, 509319, 509320, 509321, 509322, 509323, 509324, 509325, 509326, 509327, 509328, 509329, 509330, 509331, 509332, 509333, 509334, 509335, 509336, 509337, 509338, 509339, 509340, 509341, 509342, 509343, 509344, 509345, 509346, 509347, 509348, 509349, 509350, 509351, 509352, 509353, 509354, 509355, 509356, 509357, 509358, 509359, 509360, 509361, 509362, 509363, 509364, 509365, 509366, 509367, 509368, 509369, 509370, 509371, 509372, 509373, 509374, 509375, 509376, 509377, 509378, 509379, 509380, 509381, 509382, 509383, 509384, 509385, 509386, 509387, 509388, 509389, 509390, 509391, 509392, 509393, 509394, 509395, 509396, 509397, 509398, 509399, 509400, 509401, 509402, 509403, 509404, 509405, 509406, 509407, 509408, 509409, 509410, 509411, 509412, 509413, 509414, 509415, 509416, 509417, 509418, 509419, 509420, 509421, 509422, 509423, 509424, 509425, 509426, 509427, 509428, 509429, 509430, 509431, 509432, 509433, 509434, 509435, 509436, 509437, 509438, 509439, 509440, 509441, 509442, 509443, 509444, 509445, 509446, 509447, 509448, 509449, 509450, 509451, 509452, 509453, 509454, 509455, 509456, 509457, 509458, 509459, 509460, 509461, 509462, 509463, 509464, 509465, 509466, 509467, 509468, 509469, 509470, 509471, 509472, 509473, 509474, 509475, 509476, 509477, 509478, 509479, 509480, 509481, 509482, 509483, 509484, 509485, 509486, 509487, 509488, 509489, 509490, 509491, 509492, 509493, 509494, 509495, 509496, 509497, 509498, 509499, 509500, 509501, 509502, 509503, 509504, 509505, 509506, 509507, 509508, 509509, 509510, 509511, 509512, 509513, 509514, 509515, 509516, 509517, 509518, 509519, 509520, 509521, 509522, 509523, 509524, 509525, 509526, 509527, 509528, 509529, 509530, 509531, 509532, 509533, 509534, 509535, 509536, 509537, 509538, 509539, 509540, 509541, 509542, 509543, 509544, 509545, 509546, 509547, 509548, 509549, 509550, 509551, 509552, 509553, 509554, 509555, 509556, 509557, 509558, 509559, 509560, 509561, 509562, 509563, 509564, 509565, 509566, 509567, 509568, 509569, 509570, 509571, 509572, 509573, 509574, 509575, 509576, 509577, 509578, 509579, 509580, 509581, 509582, 509583, 509584, 509585, 509586, 509587, 509588, 509589, 509590, 509591, 509592, 509593, 509594, 509595, 509596, 509597, 509598, 509599, 509600, 509601, 509602, 509603, 509604, 509605, 509606, 509607, 509608, 509609, 509610, 509611, 509612, 509613, 509614, 509615, 509616, 509617, 509618, 509619, 509620, 509621, 509622, 509623, 509624, 509625, 509626, 509627, 509628, 509629, 509630, 509631, 509632, 509633, 509634, 509635, 509636, 509637, 509638, 509639, 509640, 509641, 509642, 509643, 509644, 509645, 509646, 509647, 509648, 509649, 509650, 509651, 509652, 509653, 509654, 509655, 509656, 509657, 509658, 509659, 509660, 509661, 509662, 509663, 509664, 509665, 509666, 509667, 509668, 509669, 509670, 509671, 509672, 509673, 509674, 509675, 509676, 509677, 509678, 509679, 509680, 509681, 509682, 509683, 509684, 509685, 509686, 509687, 509688, 509689, 509690, 509691, 509692, 509693, 509694, 509695, 509696, 509697, 509698, 509699, 509700, 509701, 509702, 509703, 509704, 509705, 509706, 509707, 509708, 509709, 509710, 509711, 509712, 509713, 509714, 509715, 509716, 509717, 509718, 509719, 509720, 509721, 509722, 509723, 509724, 509725, 509726, 509727, 509728, 509729, 509730, 509731, 509732, 509733, 509734, 509735, 509736, 509737, 509738, 509739, 509740, 509741, 509742, 509743, 509744, 509745, 509746, 509747, 509748, 509749, 509750, 509751, 509752, 509753, 509754, 509755, 509756, 509757, 509758, 509759, 509760, 509761, 509762, 509763, 509764, 509765, 509766, 509767, 509768, 509769, 509770, 509771, 509772, 509773, 509774, 509775, 509776, 509777, 509778, 509779, 509780, 509781, 509782, 509783, 509784, 509785, 509786, 509787, 509788, 509789, 509790, 509791, 509792, 509793, 509794, 509795, 509796, 509797, 509798, 509799, 509800, 509801, 509802, 509803, 509804, 509805, 509806, 509807, 509808, 509809, 509810, 509811, 509812, 509813, 509814, 509815, 509816, 509817, 509818, 509819, 509820, 509821, 509822, 509823, 509824, 509825, 509826, 509827, 509828, 509829, 509830, 509831, 509832, 509833, 509834, 509835, 509836, 509837, 509838, 509839, 509840, 509841, 509842, 509843, 509844, 509845, 509846, 509847, 509848, 509849, 509850, 509851, 509852, 509853, 509854, 509855, 509856, 509857, 509858, 509859, 509860, 509861, 509862, 509863, 509864, 509865, 509866, 509867, 509868, 509869, 509870, 509871, 509872, 509873, 509874, 509875, 509876, 509877, 509878, 509879, 509880, 509881, 509882, 509883, 509884, 509885, 509886, 509887, 509888, 509889, 509890, 509891, 509892, 509893, 509894, 509895, 509896, 509897, 509898, 509899, 509900, 509901, 509902, 509903, 509904, 509905, 509906, 509907, 509908, 509909, 509910, 509911, 509912, 509913, 509914, 509915, 509916, 509917, 509918, 509919, 509920, 509921, 509922, 509923, 509924, 509925, 509926, 509927, 509928, 509929, 509930, 509931, 509932, 509933, 509934, 509935, 509936, 509937, 509938, 509939, 509940, 509941, 509942, 509943, 509944, 509945, 509946, 509947, 509948, 509949, 509950, 509951, 509952, 509953, 509954, 509955, 509956, 509957, 509958, 509959, 509960, 509961, 509962, 509963, 509964, 509965, 509966, 509967, 509968, 509969, 509970, 509971, 509972, 509973, 509974, 509975, 509976, 509977, 509978, 509979, 509980, 509981, 509982, 509983, 509984, 509985, 509986, 509987, 509988, 509989, 509990, 509991, 509992, 509993, 509994, 509995, 509996, 509997, 509998, 509999, 627780, 636297, 636368, 650031, 650032, 650033, 650035, 650036, 650037, 650038, 650039, 650040, 650041, 650042, 650043, 650044, 650045, 650046, 650047, 650048, 650049, 650050, 650051, 650405, 650406, 650407, 650408, 650409, 650410, 650411, 650412, 650413, 650414, 650415, 650416, 650417, 650418, 650419, 650420, 650421, 650422, 650423, 650424, 650425, 650426, 650427, 650428, 650429, 650430, 650431, 650432, 650433, 650434, 650435, 650436, 650437, 650438, 650439, 650485, 650486, 650487, 650488, 650489, 650490, 650491, 650492, 650493, 650494, 650495, 650496, 650497, 650498, 650499, 650500, 650501, 650502, 650503, 650504, 650505, 650506, 650507, 650508, 650509, 650510, 650511, 650512, 650513, 650514, 650515, 650516, 650517, 650518, 650519, 650520, 650521, 650522, 650523, 650524, 650525, 650526, 650527, 650528, 650529, 650530, 650531, 650532, 650533, 650534, 650535, 650536, 650537, 650538, 650541, 650542, 650543, 650544, 650545, 650546, 650547, 650548, 650549, 650550, 650551, 650552, 650553, 650554, 650555, 650556, 650557, 650558, 650559, 650560, 650561, 650562, 650563, 650564, 650565, 650566, 650567, 650568, 650569, 650570, 650571, 650572, 650573, 650574, 650575, 650576, 650577, 650578, 650579, 650580, 650581, 650582, 650583, 650584, 650585, 650586, 650587, 650588, 650589, 650590, 650591, 650592, 650593, 650594, 650595, 650596, 650597, 650598, 650700, 650701, 650702, 650703, 650704, 650705, 650706, 650707, 650708, 650709, 650710, 650711, 650712, 650713, 650714, 650715, 650716, 650717, 650718, 650720, 650721, 650722, 650723, 650724, 650725, 650726, 650727, 650901, 650902, 650903, 650904, 650905, 650906, 650907, 650908, 650909, 650910, 650911, 650912, 650913, 650914, 650915, 650916, 650917, 650918, 650919, 650920, 651652, 651653, 651654, 651655, 651656, 651657, 651658, 651659, 651660, 651661, 651662, 651663, 651664, 651665, 651666, 651667, 651668, 651669, 651670, 651671, 651672, 651673, 651674, 651675, 651676, 651677, 651678, 651679, 655000, 655001, 655002, 655003, 655004, 655005, 655006, 655007, 655008, 655009, 655010, 655011, 655012, 655013, 655014, 655015, 655016, 655017, 655018, 655019, 655021, 655022, 655023, 655024, 655025, 655026, 655027, 655028, 655029, 655030, 655031, 655032, 655033, 655034, 655035, 655036, 655037, 655038, 655039, 655040, 655041, 655042, 655043, 655044, 655045, 655046, 655047, 655048, 655049, 655050, 655051, 655052, 655053, 655054, 655055, 655056, 655057, 655058, 637095, 650921, 650978]
        }];
    };

    Model.fn.onSubmit = function(e) {
        if (this.hasCardId()) {
            $('body').trigger('onPagarmeCheckoutDone');

            if ($('input[name=payment_method]').val() == '2_cards') {
                window.Pagarme2Cards = window.Pagarme2Cards + 1;
                if (window.Pagarme2Cards === 2) {
                    $('body').trigger('onPagarme2CardsDone');
                }
            }
            return;
        }

        var $this = this;
        var markedInputs = this.$el.find('[data-pagarmecheckout-element]');
        var notMarkedInputs = this.$el.find('input:not([data-pagarmecheckout-element])');
        var checkoutObj = this.createCheckoutObj(markedInputs);
        var callbackObj = {};
        var $hidden = this.$el.find('[name="pagarmetoken' + this.suffix + '"]');
        var cb;

        if ($hidden) {
            $hidden.remove();
        }

        e.preventDefault();

        swal.close();

        swal({
            title: '',
            text: 'Gerando transação segura...',
            allowOutsideClick: false
        });

        swal.showLoading();

        this.getAPIData(
            this.apiURL,
            checkoutObj,
            function(data, suffix) {
                var objJSON = JSON.parse(data);

                $hidden = document.createElement('input');
                $hidden.setAttribute('type', 'hidden');
                $hidden.setAttribute('name', 'pagarmetoken' + $this.suffix);
                $hidden.setAttribute('value', objJSON.id);
                $hidden.setAttribute('data-pagarmetoken', $this.suffix);

                $this.$el.append($hidden);

                for (var i = 0; i < notMarkedInputs.length; i += 1) {
                    callbackObj[notMarkedInputs[i]['name']] = notMarkedInputs[i]['value'];
                }

                callbackObj['pagarmetoken'] = objJSON.id;
                cb = $this._onDone.call(null, callbackObj, suffix);

                if (typeof cb === 'boolean' && !cb) {
                    $this.enableFields(markedInputs);
                    return;
                }

                var form = $('form.checkout');
                form.submit();
            },
            function(error, suffix) {
                swal.close();
                if (error.statusCode == 503) {
                    swal({
                        type: 'error',
                        html: 'Não foi possível gerar a transação segura. Serviço indisponível.'
                    });
                } else {
                    $this._onFail(error, suffix);
                }

            }
        );
    };

    Model.fn._onFail = function(error, suffix) {
        $('body').trigger('onPagarmeCheckoutFail', [error]);
    };

    Model.fn._onDone = function(data, suffix) {
        $('body').trigger('onPagarmeCheckoutDone', [data]);

        if ($('input[name=payment_method]').val() == '2_cards') {
            window.Pagarme2Cards = window.Pagarme2Cards + 1;
            if (window.Pagarme2Cards === 2) {
                $('body').trigger('onPagarme2CardsDone');
            }
        }
    };

});;
MONSTER('Pagarme.Components.Wallet', function(Model, $, utils) {

    Model.fn.start = function() {
        this.addEventListener();
    };

    Model.fn.addEventListener = function() {
        this.click('remove-card');
    };

    Model.fn._onClickRemoveCard = function(event) {
        event.preventDefault();

        swal({
            title: this.data.swal.confirm_title,
            text: this.data.swal.confirm_text,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: this.data.swal.confirm_color,
            cancelButtonColor: this.data.swal.cancel_color,
            confirmButtonText: this.data.swal.confirm_button,
            cancelButtonText: this.data.swal.cancel_button,
            allowOutsideClick: false,
        }).then(this._request.bind(this, event.currentTarget.dataset.value), function() {});
    };

    Model.fn._request = function(cardId) {
        swal.showLoading();

        this.ajax({
            url: this.data.apiRequest,
            data: {
                card_id: cardId
            }
        });
    };

    Model.fn._done = function(response) {
        if (response.success) {
            this.successMessage(response.data);
        } else {
            this.failMessage(response.data);
        }
    };

    Model.fn._fail = function(jqXHR, textStatus, errorThrown) {

    };

    Model.fn.failMessage = function(message) {
        swal({
            type: 'error',
            html: message
        }).then(function() {});
    };

    Model.fn.successMessage = function(message) {
        swal({
            type: 'success',
            html: message,
            allowOutsideClick: false
        }).then(function() {
            window.location.reload(true);
        });
    };

});;
jQuery(function($) {
    var context = $('body');

    Pagarme.vars = {
        body: context
    };

    Pagarme.Application.init.apply(null, [context]);
});

jQuery(function($) {
    $('#billing_cpf').change(function() {
        $('input[name="voucher-document-holder"]').empty();
        $('input[name="voucher-document-holder"]').val($('#billing_cpf').val()).mask("999.999.999-99").trigger('input');
    });
    $('input[name="voucher-document-holder"]').val($('#billing_cpf').val()).mask("999.999.999-99").trigger('input');
});