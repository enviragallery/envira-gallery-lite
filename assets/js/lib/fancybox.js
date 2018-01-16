// envirabox v1.3.4
(function(B) {
    var L, T, Q, M, d, m, J, A, O, z, C = 0,
        H = {}, j = [],
        e = 0,
        G = {}, y = [],
        f = null,
        o = new Image(),
        i = /\.(jpg|gif|png|bmp|jpeg)(.*)?$/i,
        k = /[^\.]\.(swf)\s*$/i,
        p, N = 1,
        h = 0,
        t = "",
        b, c, P = false,
        s = B.extend(B("<div/>")[0], {
            prop: 0
        }),
        S = false,
        r = function() {
            T.hide();
            o.onerror = o.onload = null;
            if (f) {
                f.abort()
            }
            L.empty()
        }, x = function() {
            if (false === H.onError(j, C, H)) {
                T.hide();
                P = false;
                return
            }
            H.titleShow = false;
            H.width = "auto";
            H.height = "auto";
            L.html('<p id="envirabox-error">The requested content cannot be loaded.<br />Please try again later.</p>');
            n()
        }, w = function() {
            var Z = j[C],
                W, Y, ab, aa, V, X;
            r();
            H = B.extend({}, B.fn.envirabox.defaults, (typeof B(Z).data("envirabox") == "undefined" ? H : B(Z).data("envirabox")));
            X = H.onStart(j, C, H);
            if (X === false) {
                P = false;
                return
            } else {
                if (typeof X == "object") {
                    H = B.extend(H, X)
                }
            }
            ab = H.title || (Z.nodeName ? B(Z).attr("title") : Z.title) || "";
            if (Z.nodeName && !H.orig) {
                H.orig = B(Z).children("img:first").length ? B(Z).children("img:first") : B(Z)
            }
            if (ab === "" && H.orig && H.titleFromAlt) {
                ab = H.orig.attr("alt")
            }
            W = H.href || (Z.nodeName ? B(Z).attr("href") : Z.href) || null;
            if ((/^(?:javascript)/i).test(W) || W == "#") {
                W = null
            }
            /* is href/W a link? */
            if ( typeof W === 'string' && B.envirabox.isUrl(W) && !B.envirabox.isImage(W) ) {
                document.getElementById("envirabox-loading").style.visibility = "hidden";
                window.location.href = W;
            }
            if (H.type) {
                Y = H.type;
                if (!W) {
                    W = H.content
                }
            } else {
                if (H.content) {
                    Y = "html"
                } else {
                    if (W) {
                        if (W.match(i)) {
                            Y = "image"
                        } else {
                            if (W.match(k)) {
                                Y = "swf"
                            } else {
                                if (B(Z).hasClass("iframe")) {
                                    Y = "iframe"
                                } else {
                                    if (W.indexOf("#") === 0) {
                                        Y = "inline"
                                    } else {
                                        Y = "ajax"
                                    }
                                }
                            }
                        }
                    }
                }
            } if (!Y) {
                x();
                return
            }
            if (Y == "inline") {
                Z = W.substr(W.indexOf("#"));
                Y = B(Z).length > 0 ? "inline" : "ajax"
            }
            H.type = Y;
            H.href = W;
            H.title = ab;
            if (H.autoDimensions) {
                if (H.type == "html" || H.type == "inline" || H.type == "ajax") {
                    H.width = "auto";
                    H.height = "auto"
                } else {
                    H.autoDimensions = false
                }
            }
            if (H.modal) {
                H.overlayShow = true;
                H.hideOnOverlayClick = false;
                H.hideOnContentClick = false;
                H.enableEscapeButton = false;
                H.showCloseButton = false
            }
            H.padding = parseInt(H.padding, 10);
            H.margin = parseInt(H.margin, 10);
            L.css("padding", (H.padding + H.margin));
            B(".envirabox-inline-tmp").unbind("envirabox-cancel").bind("envirabox-change", function() {
                B(this).replaceWith(m.children())
            });
            switch (Y) {
                case "html":
                    L.html(H.content);
                    n();
                    break;
                case "inline":
                    if (B(Z).parent().is("#envirabox-content") === true) {
                        P = false;
                        return
                    }
                    B('<div class="envirabox-inline-tmp" />').hide().insertBefore(B(Z)).bind("envirabox-cleanup", function() {
                        B(this).replaceWith(m.children())
                    }).bind("envirabox-cancel", function() {
                        B(this).replaceWith(L.children())
                    });
                    B(Z).appendTo(L);
                    n();
                    break;
                case "image":
                    P = false;
                    B.envirabox.showActivity();
                    o = new Image();
                    o.onerror = function() {
                        x()
                    };
                    o.onload = function() {
                        P = true;
                        o.onerror = o.onload = null;
                        F()
                    };
                    o.src = W;
                    break;
                case "swf":
                    H.scrolling = "no";
                    aa = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="' + H.width + '" height="' + H.height + '"><param name="movie" value="' + W + '"></param>';
                    V = "";
                    B.each(H.swf, function(ac, ad) {
                        aa += '<param name="' + ac + '" value="' + ad + '"></param>';
                        V += " " + ac + '="' + ad + '"'
                    });
                    aa += '<embed src="' + W + '" type="application/x-shockwave-flash" width="' + H.width + '" height="' + H.height + '"' + V + "></embed></object>";
                    L.html(aa);
                    n();
                    break;
                case "ajax":
                    P = false;
                    B.envirabox.showActivity();
                    H.ajax.win = H.ajax.success;
                    f = B.ajax(B.extend({}, H.ajax, {
                        url: W,
                        data: H.ajax.data || {},
                        error: function(ac, ae, ad) {
                            if (ac.status > 0) {
                                x()
                            }
                        },
                        success: function(ad, af, ac) {
                            var ae = typeof ac == "object" ? ac : f;
                            if (ae.status == 200) {
                                if (typeof H.ajax.win == "function") {
                                    X = H.ajax.win(W, ad, af, ac);
                                    if (X === false) {
                                        T.hide();
                                        return
                                    } else {
                                        if (typeof X == "string" || typeof X == "object") {
                                            ad = X
                                        }
                                    }
                                }
                                L.html(ad);
                                n()
                            }
                        }
                    }));
                    break;
                case "iframe":
                    E();
                    break
            }
        }, n = function() {
            var V = H.width,
                W = H.height;
            if (V.toString().indexOf("%") > -1) {
                V = parseInt((B(window).width() - (H.margin * 2)) * parseFloat(V) / 100, 10) + "px"
            } else {
                V = V == "auto" ? "auto" : V + "px"
            } if (W.toString().indexOf("%") > -1) {
                W = parseInt((B(window).height() - (H.margin * 2)) * parseFloat(W) / 100, 10) + "px"
            } else {
                W = W == "auto" ? "auto" : W + "px"
            }
            L.wrapInner('<div style="width:' + V + ";height:" + W + ";overflow: " + (H.scrolling == "auto" ? "auto" : (H.scrolling == "yes" ? "scroll" : "hidden")) + ';position:relative;"></div>');
            H.width = L.width();
            H.height = L.height();
            E()
        }, F = function() {
            H.width = o.width;
            H.height = o.height;
            B("<img />").attr({
                id: "envirabox-img",
                src: o.src,
                alt: H.title
            }).appendTo(L);
            E()
        }, E = function() {
            var W, V;
            T.hide();
            if (M.is(":visible") && false === G.onCleanup(y, e, G)) {
                B.event.trigger("envirabox-cancel");
                P = false;
                return
            }
            P = true;
            B(m.add(Q)).unbind();
            B(window).unbind("resize.fb scroll.fb");
            B(document).unbind("keydown.fb");
            if (M.is(":visible") && G.titlePosition !== "outside") {
                M.css("height", M.height())
            }
            y = j;
            e = C;
            G = H;
            if (G.overlayShow) {
                Q.css({
                    "background-color": G.overlayColor,
                    opacity: G.overlayOpacity,
                    cursor: G.hideOnOverlayClick ? "pointer" : "auto",
                    height: B(document).height()
                });
                if (!Q.is(":visible")) {
                    if (S) {
                        B("select:not(#envirabox-tmp select)").filter(function() {
                            return this.style.visibility !== "hidden"
                        }).css({
                            visibility: "hidden"
                        }).one("envirabox-cleanup", function() {
                            this.style.visibility = "inherit"
                        })
                    }
                    Q.show()
                }
            } else {
                Q.hide()
            }
            c = R();
            l();
            if (M.is(":visible")) {
                B(J.add(O).add(z)).hide();
                W = M.position(), b = {
                    top: W.top,
                    left: W.left,
                    width: M.width(),
                    height: M.height()
                };
                V = (b.width == c.width && b.height == c.height);
                m.fadeTo(G.changeFade, 0.3, function() {
                    var X = function() {
                        m.html(L.contents()).fadeTo(G.changeFade, 1, v)
                    };
                    B.event.trigger("envirabox-change");
                    m.empty().removeAttr("filter").css({
                        "border-width": G.padding,
                        width: c.width - G.padding * 2,
                        height: H.autoDimensions ? "auto" : c.height - h - G.padding * 2
                    });
                    if (V) {
                        X()
                    } else {
                        s.prop = 0;
                        B(s).animate({
                            prop: 1
                        }, {
                            duration: G.changeSpeed,
                            easing: G.easingChange,
                            step: U,
                            complete: X
                        })
                    }
                });
                return
            }
            M.removeAttr("style");
            m.css("border-width", G.padding);
            if (G.transitionIn == "elastic") {
                b = I();
                m.html(L.contents());
                M.show();
                if (G.opacity) {
                    c.opacity = 0
                }
                s.prop = 0;
                B(s).animate({
                    prop: 1
                }, {
                    duration: G.speedIn,
                    easing: G.easingIn,
                    step: U,
                    complete: v
                });
                return
            }
            if (G.titlePosition == "inside" && h > 0) {
                A.show()
            }
            m.css({
                width: c.width - G.padding * 2,
                height: H.autoDimensions ? "auto" : c.height - h - G.padding * 2
            }).html(L.contents());
            M.css(c).fadeIn(G.transitionIn == "none" ? 0 : G.speedIn, v)
        }, D = function(V) {
            if (V && V.length) {
                if (G.titlePosition == "float") {
                    return '<table id="envirabox-title-float-wrap" cellpadding="0" cellspacing="0"><tr><td id="envirabox-title-float-left"></td><td id="envirabox-title-float-main">' + V + '</td><td id="envirabox-title-float-right"></td></tr></table>'
                }
                return '<div id="envirabox-title-' + G.titlePosition + '">' + V + "</div>"
            }
            return false
        }, l = function() {
            t = G.title || "";
            h = 0;
            A.empty().removeAttr("style").removeClass();
            if (G.titleShow === false) {
                A.hide();
                return
            }
            t = B.isFunction(G.titleFormat) ? G.titleFormat(t, y, e, G) : D(t);
            if (!t || t === "") {
                A.hide();
                return
            }
            A.addClass("envirabox-title-" + G.titlePosition).html(t).appendTo("body").show();
            switch (G.titlePosition) {
                case "inside":
                    A.css({
                        width: c.width - (G.padding * 2),
                        marginLeft: G.padding,
                        marginRight: G.padding
                    });
                    h = A.outerHeight(true);
                    A.appendTo(d);
                    c.height += h;
                    break;
                case "over":
                    A.css({
                        marginLeft: G.padding,
                        width: c.width - (G.padding * 2),
                        bottom: G.padding
                    }).appendTo(d);
                    break;
                case "float":
                    A.css("left", parseInt((A.width() - c.width - 40) / 2, 10) * -1).appendTo(M);
                    break;
                default:
                    A.css({
                        width: c.width - (G.padding * 2),
                        paddingLeft: G.padding,
                        paddingRight: G.padding
                    }).appendTo(M);
                    break
            }
            A.hide()
        }, g = function() {
            if (G.enableEscapeButton || G.enableKeyboardNav) {
                B(document).bind("keydown.fb", function(V) {
                    if (V.keyCode == 27 && G.enableEscapeButton) {
                        V.preventDefault();
                        B.envirabox.close()
                    } else {
                        if ((V.keyCode == 37 || V.keyCode == 39) && G.enableKeyboardNav && V.target.tagName !== "INPUT" && V.target.tagName !== "TEXTAREA" && V.target.tagName !== "SELECT") {
                            V.preventDefault();
                            B.envirabox[V.keyCode == 37 ? "prev" : "next"]()
                        }
                    }
                })
            }
            if (!G.showNavArrows) {
                O.hide();
                z.hide();
                return
            }
            if ((G.cyclic && y.length > 1) || e !== 0) {
                O.show()
            }
            if ((G.cyclic && y.length > 1) || e != (y.length - 1)) {
                z.show()
            }
        }, v = function() {
            if (!B.support.opacity) {
                m.get(0).style.removeAttribute("filter");
                M.get(0).style.removeAttribute("filter")
            }
            if (H.autoDimensions) {
                m.css("height", "auto")
            }
            M.css("height", "auto");
            if (t && t.length) {
                A.show()
            }
            if (G.showCloseButton) {
                J.show()
            }
            g();
            if (G.hideOnContentClick) {
                m.bind("click", B.envirabox.close)
            }
            if (G.hideOnOverlayClick) {
                Q.bind("click", B.envirabox.close)
            }
            B(window).bind("resize.fb", B.envirabox.resize);
            if (G.centerOnScroll) {
                B(window).bind("scroll.fb", B.envirabox.center)
            }
            if (G.type == "iframe") {
                B('<iframe id="envirabox-frame" name="envirabox-frame' + new Date().getTime() + '" frameborder="0" hspace="0" scrolling="' + H.scrolling + '" src="' + G.href + '"></iframe>').appendTo(m)
            }
            M.show();
            P = false;
            B.envirabox.center();
            G.onComplete(y, e, G);
            K()
        }, K = function() {
            var V, W;
            if ((y.length - 1) > e) {
                V = y[e + 1].href;
                if (typeof V !== "undefined" && V.match(i)) {
                    W = new Image();
                    W.src = V
                }
            }
            if (e > 0) {
                V = y[e - 1].href;
                if (typeof V !== "undefined" && V.match(i)) {
                    W = new Image();
                    W.src = V
                }
            }
        }, U = function(W) {
            var V = {
                width: parseInt(b.width + (c.width - b.width) * W, 10),
                height: parseInt(b.height + (c.height - b.height) * W, 10),
                top: parseInt(b.top + (c.top - b.top) * W, 10),
                left: parseInt(b.left + (c.left - b.left) * W, 10)
            };
            if (typeof c.opacity !== "undefined") {
                V.opacity = W < 0.5 ? 0.5 : W
            }
            M.css(V);
            m.css({
                width: V.width - G.padding * 2,
                height: V.height - (h * W) - G.padding * 2
            })
        }, u = function() {
            return [B(window).width() - (G.margin * 2), B(window).height() - (G.margin * 2), B(document).scrollLeft() + G.margin, B(document).scrollTop() + G.margin]
        }, R = function() {
            var V = u(),
                Z = {}, W = G.autoScale,
                X = G.padding * 2,
                Y;
            if (G.width.toString().indexOf("%") > -1) {
                Z.width = parseInt((V[0] * parseFloat(G.width)) / 100, 10)
            } else {
                Z.width = G.width + X
            } if (G.height.toString().indexOf("%") > -1) {
                Z.height = parseInt((V[1] * parseFloat(G.height)) / 100, 10)
            } else {
                Z.height = G.height + X
            } if (W && (Z.width > V[0] || Z.height > V[1])) {
                if (H.type == "image" || H.type == "swf") {
                    Y = (G.width) / (G.height);
                    if ((Z.width) > V[0]) {
                        Z.width = V[0];
                        Z.height = parseInt(((Z.width - X) / Y) + X, 10)
                    }
                    if ((Z.height) > V[1]) {
                        Z.height = V[1];
                        Z.width = parseInt(((Z.height - X) * Y) + X, 10)
                    }
                } else {
                    Z.width = Math.min(Z.width, V[0]);
                    Z.height = Math.min(Z.height, V[1])
                }
            }
            Z.top = parseInt(Math.max(V[3] - 20, V[3] + ((V[1] - Z.height - 40) * 0.5)), 10);
            Z.left = parseInt(Math.max(V[2] - 20, V[2] + ((V[0] - Z.width - 40) * 0.5)), 10);
            return Z
        }, q = function(V) {
            var W = V.offset();
            W.top += parseInt(V.css("paddingTop"), 10) || 0;
            W.left += parseInt(V.css("paddingLeft"), 10) || 0;
            W.top += parseInt(V.css("border-top-width"), 10) || 0;
            W.left += parseInt(V.css("border-left-width"), 10) || 0;
            W.width = V.width();
            W.height = V.height();
            return W
        }, I = function() {
            var Y = H.orig ? B(H.orig) : false,
                X = {}, W, V;
            if (Y && Y.length) {
                W = q(Y);
                X = {
                    width: W.width + (G.padding * 2),
                    height: W.height + (G.padding * 2),
                    top: W.top - G.padding - 20,
                    left: W.left - G.padding - 20
                }
            } else {
                V = u();
                X = {
                    width: G.padding * 2,
                    height: G.padding * 2,
                    top: parseInt(V[3] + V[1] * 0.5, 10),
                    left: parseInt(V[2] + V[0] * 0.5, 10)
                }
            }
            return X
        }, a = function() {
            if (!T.is(":visible")) {
                clearInterval(p);
                return
            }
            B("div", T).css("top", (N * -40) + "px");
            N = (N + 1) % 12
        };
    B.fn.envirabox = function(V) {
        if (!B(this).length) {
            return this
        }
        B(this).data("envirabox", B.extend({}, V, (B.metadata ? B(this).metadata() : {}))).unbind("click.fb").bind("click.fb", function(X) {
            X.preventDefault();
            if (P) {
                return
            }
            P = true;
            B(this).blur();
            j = [];
            C = 0;
            var W = B(this).attr("rel") || "";
            if (!W || W == "" || W === "nofollow") {
                j.push(this)
            } else {
                j = B("a[rel=" + W + "], area[rel=" + W + "]");
                C = j.index(this)
            }
            w();
            return
        });
        return this
    };
    B.envirabox = function(Y) {
        var X;
        if (P) {
            return
        }
        P = true;
        X = typeof arguments[1] !== "undefined" ? arguments[1] : {};
        j = [];
        C = parseInt(X.index, 10) || 0;
        if (B.isArray(Y)) {
            for (var W = 0, V = Y.length; W < V; W++) {
                if (typeof Y[W] == "object") {
                    B(Y[W]).data("envirabox", B.extend({}, X, Y[W]))
                } else {
                    Y[W] = B({}).data("envirabox", B.extend({
                        content: Y[W]
                    }, X))
                }
            }
            j = jQuery.merge(j, Y)
        } else {
            if (typeof Y == "object") {
                B(Y).data("envirabox", B.extend({}, X, Y))
            } else {
                Y = B({}).data("envirabox", B.extend({
                    content: Y
                }, X))
            }
            j.push(Y)
        } if (C > j.length || C < 0) {
            C = 0
        }
        w()
    };
    B.envirabox.showActivity = function() {
        clearInterval(p);
        T.show();
        p = setInterval(a, 66)
    };
    B.envirabox.hideActivity = function() {
        T.hide()
    };
    B.envirabox.next = function() {
        return B.envirabox.pos(e + 1)
    };
    B.envirabox.prev = function() {
        return B.envirabox.pos(e - 1)
    };
    B.envirabox.pos = function(V) {
        if (P) {
            return
        }
        V = parseInt(V);
        j = y;
        if (V > -1 && V < y.length) {
            C = V;
            w()
        } else {
            if (G.cyclic && y.length > 1) {
                C = V >= y.length ? 0 : y.length - 1;
                w()
            }
        }
        return
    };
    B.envirabox.cancel = function() {
        if (P) {
            return
        }
        P = true;
        B.event.trigger("envirabox-cancel");
        r();
        H.onCancel(j, C, H);
        P = false
    };
    B.envirabox.close = function() {
        if (P || M.is(":hidden")) {
            return
        }
        P = true;
        if (G && false === G.onCleanup(y, e, G)) {
            P = false;
            return
        }
        r();
        B(J.add(O).add(z)).hide();
        B(m.add(Q)).unbind();
        B(window).unbind("resize.fb scroll.fb");
        B(document).unbind("keydown.fb");
        m.find("iframe").attr("src", S && /^https/i.test(window.location.href || "") ? "javascript:void(false)" : "about:blank");
        if (G.titlePosition !== "inside") {
            A.empty()
        }
        M.stop();

        function V() {
            Q.fadeOut("fast");
            A.empty().hide();
            M.hide();
            B.event.trigger("envirabox-cleanup");
            m.empty();
            G.onClosed(y, e, G);
            y = H = [];
            e = C = 0;
            G = H = {};
            P = false
        }
        if (G.transitionOut == "elastic") {
            b = I();
            var W = M.position();
            c = {
                top: W.top,
                left: W.left,
                width: M.width(),
                height: M.height()
            };
            if (G.opacity) {
                c.opacity = 1
            }
            A.empty().hide();
            s.prop = 1;
            B(s).animate({
                prop: 0
            }, {
                duration: G.speedOut,
                easing: G.easingOut,
                step: U,
                complete: V
            })
        } else {
            M.fadeOut(G.transitionOut == "none" ? 0 : G.speedOut, V)
        }
    };
    B.envirabox.resize = function() {
        if (Q.is(":visible")) {
            Q.css("height", B(document).height())
        }
        var W, V;
        c = R();
        l();
        A.show();
        W = M.position(), b = {
            top: W.top,
            left: W.left,
            width: M.width(),
            height: M.height()
        };
        V = (b.width == c.width && b.height == c.height);
        if (V) {} else {
            s.prop = 0;
            B(s).animate({
                prop: 1
            }, {
                duration: G.changeSpeed,
                easing: G.easingChange,
                step: U
            })
        }
        m.css({
            width: c.width - G.padding * 2,
            height: H.autoDimensions ? "auto" : c.height - h - G.padding * 2
        });
        M.css(c);
        B.envirabox.center(true)
    };
    B.envirabox.center = function() {
        var V, W;
        if (P) {
            return
        }
        W = arguments[0] === true ? 1 : 0;
        V = u();
        if (!W && (M.width() > V[0] || M.height() > V[1])) {
            return
        }
        M.stop().animate({
            top: parseInt(Math.max(V[3] - 20, V[3] + ((V[1] - m.height() - 40) * 0.5) - G.padding)),
            left: parseInt(Math.max(V[2] - 20, V[2] + ((V[0] - m.width() - 40) * 0.5) - G.padding))
        }, typeof arguments[0] == "number" ? arguments[0] : 200)
    };
    B.envirabox.init = function() {
        if (B("#envirabox-wrap").length) {
            return
        }
        B("body").append(L = B('<div id="envirabox-tmp"></div>'), T = B('<div id="envirabox-loading"><div></div></div>'), Q = B('<div id="envirabox-overlay"></div>'), M = B('<div id="envirabox-wrap"></div>'));
        d = B('<div id="envirabox-outer"></div>').append('<div class="envirabox-bg" id="envirabox-bg-n"></div><div class="envirabox-bg" id="envirabox-bg-ne"></div><div class="envirabox-bg" id="envirabox-bg-e"></div><div class="envirabox-bg" id="envirabox-bg-se"></div><div class="envirabox-bg" id="envirabox-bg-s"></div><div class="envirabox-bg" id="envirabox-bg-sw"></div><div class="envirabox-bg" id="envirabox-bg-w"></div><div class="envirabox-bg" id="envirabox-bg-nw"></div>').appendTo(M);
        d.append(m = B('<div id="envirabox-content"></div>'), J = B('<a id="envirabox-close"></a>'), A = B('<div id="envirabox-title"></div>'), O = B('<a href="javascript:;" id="envirabox-left"><span class="fancy-ico" id="envirabox-left-ico"></span></a>'), z = B('<a href="javascript:;" id="envirabox-right"><span class="fancy-ico" id="envirabox-right-ico"></span></a>'));
        J.click(B.envirabox.close);
        T.click(B.envirabox.cancel);
        O.click(function(V) {
            V.preventDefault();
            B.envirabox.prev()
        });
        z.click(function(V) {
            V.preventDefault();
            B.envirabox.next()
        });
        if (B.fn.mousewheel) {
            M.bind("mousewheel.fb", function(V, W) {
                if (P) {
                    V.preventDefault()
                } else {
                    if (B(V.target).get(0).clientHeight == 0 || B(V.target).get(0).scrollHeight === B(V.target).get(0).clientHeight) {
                        V.preventDefault();
                        B.envirabox[W > 0 ? "prev" : "next"]()
                    }
                }
            })
        }
        if (!B.support.opacity) {
            M.addClass("envirabox-ie")
        }
        if (S) {
            T.addClass("envirabox-ie6");
            M.addClass("envirabox-ie6");
            B('<iframe id="envirabox-hide-sel-frame" src="' + (/^https/i.test(window.location.href || "") ? "javascript:void(false)" : "about:blank") + '" scrolling="no" border="0" frameborder="0" tabindex="-1"></iframe>').prependTo(d)
        }
    };
    B.envirabox.isUrl = function(str) {
        return str.match(/^(https?):\/\/((?:[a-z0-9.-]|%[0-9A-F]{2}){3,})(?::(\d+))?((?:\/(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9A-F]{2})*)*)(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?$/i);
    };
    B.envirabox.isImage = function(str) {
        return str.match(/(^data:image\/.*,)|(\.(jp(e|g|eg)|gif|png|bmp|webp|svg)((\?|#).*)?$)/i);
    };
    B.fn.envirabox.defaults = {
        padding: 10,
        margin: 40,
        opacity: false,
        modal: false,
        cyclic: false,
        scrolling: "auto",
        width: 560,
        height: 340,
        autoScale: true,
        autoDimensions: true,
        centerOnScroll: false,
        ajax: {},
        swf: {
            wmode: "transparent"
        },
        hideOnOverlayClick: true,
        hideOnContentClick: false,
        overlayShow: true,
        overlayOpacity: 0.9, /* was 0.7 but upped to remove CSS !important */
        overlayColor: "#777",
        titleShow: true,
        titlePosition: "float",
        titleFormat: null,
        titleFromAlt: false,
        transitionIn: "fade",
        transitionOut: "fade",
        speedIn: 300,
        speedOut: 300,
        changeSpeed: 300,
        changeFade: "fast",
        easingIn: "swing",
        easingOut: "swing",
        showCloseButton: true,
        showNavArrows: true,
        enableEscapeButton: true,
        enableKeyboardNav: true,
        onStart: function() {},
        onCancel: function() {},
        onComplete: function() {},
        onCleanup: function() {},
        onClosed: function() {},
        onError: function() {}
    };
    B(document).ready(function() {
        B.envirabox.init()
    })
})(jQuery);