(function (g) {

    'use strict';

    var N = 'Vintage';
    var n = 'v';

    if (g && !g[N]) {

    var V = g[N] = {};

    V.C = {};

    V.C.JS = {};

    V.C.JS.cast = function (v) {
        return V.C.Util.invade(v, function (v) {
            return V.C.JS.String.cast(v);
        }, true);
    };

    V.C.JS.escape = function (v) {
        return V.C.Util.invade(v, function (v) {
            return V.C.JS.String.is(v) ? encodeURIComponent(v) : v;
        }, true);
    };

    V.C.JS.unescape = function (v) {
        return V.C.Util.invade(v, function (v) {
            return V.C.JS.String.is(v) ? decodeURIComponent(v) : v;
        }, true);
    };

    V.C.JS.String = {

        is : function (v) {
            if (v instanceof String ) return true;
            if (typeof v == "string") return true;
            return false;
        },

        like_integer : function (v) {
            if (!this.is(v)) return null;
            return v.match(/^\d+$/) ? true : false;
        },

        cast : function (v, strict) {
            var t = this;
            if (!t.is(v)) return strict ? null : v;
            switch (true) {
                case t.like_integer(v) : return parseInt(v); break;
                default                : return v;           break;
            }
        }
    };

    V.C.Html = {};

    V.C.Util = {};

    V.C.Util.invade = function (v, f, m) {
        var t = this;
        var r = null;
        if (!(v instanceof Object)) {
            r = f(v);
            m && (v = r);
        } else if (v instanceof Array) {
            for (var i = 0; i < v.length; i++) {
                r = t.invade(v[i], f, m);
                m && (v[i] = r);
            }
        } else {
            for (var k in v) {
                r = t.invade(v[k], f, m);
                m && (v[k] = r);
            }
        }
        return v;
    };

    V.C.Util.tree = function (path, root) {
        var paths = path.split(/\./);
        var node  = root || g;
        for (var i = 0; i < paths.length; i++) {
            if (!Object.prototype.hasOwnProperty.call(node, paths[i])) {
                node[paths[i]] = {};
            }
            node = node[paths[i]];
        }
        return node;
    };

        V.VString = {};

        V.VString.money = function (string) {
            var regexp = /(\d+)(\d{3})/;
            while (regexp.test(string)) {
                string = string.replace(regexp, '$1,$2');
            }
            return string;
        };

        V.Html = {};

        V.Html.page = function (a) {

            var page = function (number) {

                var options = [];
                var anchor  = null;
                var method  = null;

                if (a.option) {
                    for (var k in a.option) {
                        var v = a.option[k];
                        if (a.anchor) {
                            options.push(k + '=' + v);
                        } else if (a.method) {
                            options.push(k + ":'" + v + "'");
                        }
                    }
                }

                if (a.anchor) {
                    options.push('pageC=' + number);
                    anchor = a.anchor + '?'  + options.join('&');
                } else if (a.method) {
                    options.push('pageC:' + number);
                    method = a.method + '({' + options.join(',') + '});';
                }

                return {
                    number  : number,
                    anchor  : anchor,
                    method  : method,
                    isPageC : (number === a.pageC),
                    isPageF : (number === r.pageF),
                    isPageL : (number === r.pageL)
                };
            };

            var r     = {};
            r.pages   = [];
            r.itemT   = a.itemT;
            r.itemB   = 1 + (a.pageC - 1) * a.itemP;
            r.itemE   = Math.min((r.itemB + a.itemP - 1), a.itemT);
            r.pageT   = Math.ceil(a.itemT / a.itemP);
            r.pageF   = page(1);
            r.pageL   = page(r.pageT);
            r.isPageF = (a.pageC == 1);
            r.isPageL = (a.pageC == r.pageT);

            !r.isPageF && (r.pageP = page(a.pageC - 1));
            !r.isPageL && (r.pageN = page(a.pageC + 1));

            var pageP = a.pageP || 5;
            var pageS = Math.floor(pageP / 2);
            var pageB = Math.max((a.pageC - pageS),         1);
            var pageE = Math.min((a.pageC + pageS),   r.pageT);
                pageB = Math.max((pageE - pageP + 1),       1);
                pageE = Math.min((pageP + pageB - 1), r.pageT);

            for (var i = pageB; i <= pageE; i++) {
                r.pages.push(page(i));
            }

            return r;
        };

    } else {}

})(this);
