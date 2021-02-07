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
            return ( window.PagarmeGlobalVars || {} )[name];
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
