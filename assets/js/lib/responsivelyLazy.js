/*
 * Responsively Lazy
 * http://ivopetkov.com/b/lazy-load-responsive-images/
 * Copyright 2015-2016, Ivo Petkov
 * Free to use under the MIT license.
 */

if ( typeof envira_lazy_load !== 'undefined' && envira_lazy_load == 'true' ) {



var responsivelyLazy = (function () {
    
    var hasWebPSupport = false;
    var windowWidth = null;
    var windowHeight = null;
    var justifiedReady = false;
    var galleryClass = false;
    var hasIntersectionObserverSupport = typeof IntersectionObserver !== 'undefined';

    var isVisible = function (element) {

        if (windowWidth === null) {

            windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;

            if (windowWidth === null) {
                return false;
            }
        }

        var rect = element.getBoundingClientRect();

        var elementTop = rect.top;
        var elementLeft = rect.left;
        var elementWidth = rect.width;
        var elementHeight = rect.height;
        var test = elementTop < windowHeight && elementTop + elementHeight > 0 && elementLeft < windowWidth && elementLeft + elementWidth > 0;
        
        if ( test === false ) { 

        }
        return test;
    

    };

    jQuery.fn.exists = function(){return this.length>0;}

    var updateElement = function (container, element) {
        var options = element.getAttribute('data-envira-srcset');
        if (options !== null) {
            options = options.trim();
            if (options.length > 0) {
                options = options.split(',');
                var temp = [];
                var optionsCount = options.length;
                for (var j = 0; j < optionsCount; j++) {
                    var option = options[j].trim();
                    if (option.length === 0) {
                        continue;
                    }
                    var spaceIndex = option.lastIndexOf(' ');
                    if (spaceIndex === -1) {
                        var optionImage = option;
                        var optionWidth = 999998;
                    } else {
                        var optionImage = option.substr(0, spaceIndex);
                        var optionWidth = parseInt(option.substr(spaceIndex + 1, option.length - spaceIndex - 2), 10);
                    }
                    var add = false;
                    if (optionImage.indexOf('.webp', optionImage.length - 5) !== -1) {
                        if (hasWebPSupport) {
                            add = true;
                        }
                    } else {
                        add = true;
                    }
                    if (add) {
                        temp.push([optionImage, optionWidth]);
                    }
                }
                temp.sort(function (a, b) {
                    if (a[1] < b[1]) {
                        return -1;
                    }
                    if (a[1] > b[1]) {
                        return 1;
                    }
                    if (a[1] === b[1]) {
                        if (b[0].indexOf('.webp', b[0].length - 5) !== -1) {
                            return 1;
                        }
                        if (a[0].indexOf('.webp', a[0].length - 5) !== -1) {
                            return -1;
                        }
                    }
                    return 0;
                });
                options = temp;
            } else {
                options = [];
            }
        } else {
            options = [];
        }

        var containerWidth = container.offsetWidth * window.devicePixelRatio;

        var bestSelectedOption = null;
        var optionsCount = options.length;
        for (var j = 0; j < optionsCount; j++) {
            var optionData = options[j];
            if (optionData[1] >= containerWidth) {
                bestSelectedOption = optionData;
                break;
            } else {
                //console.log( 'bestSelectedOption = optionData - DID NOT HAPPEN' );
            }
        }

        if (bestSelectedOption === null) {
            bestSelectedOption = [element.getAttribute('data-envira-src'), 999999];
        }

        if (typeof container.lastSetOption === 'undefined') {
            container.lastSetOption = ['', 0];
        }
        if (container.lastSetOption[1] < bestSelectedOption[1]) {
            var fireEvent = container.lastSetOption[1] === 0;
            var url = bestSelectedOption[0];
            var image = new Image();
            image.addEventListener('load', function () {
                element.setAttribute('srcset', url);
                element.setAttribute('src', url);
                if (fireEvent) {
                    var handler = container.getAttribute('data-onlazyload');
                    if (handler !== null) {
                        (new Function(handler).bind(container))();
                    }
                }
            }, false);
            image.addEventListener('error', function () {
                container.lastSetOption = ['', 0];
            }, false);

            image.onload = function () {

                if ( container.getAttribute('class') == 'envira-lazy' ) {
                    // this is a legacy layout
                    var the_image = container.firstElementChild;
                    var the_container = container;
                    var image_id = the_image.id;
                    var image_src = the_image.src;
                    var gallery_id = jQuery(the_image).data('envira-gallery-id');
                    var naturalWidth = this.naturalWidth;
                    var naturalHeight = this.naturalHeight;
                } else {
                    // we are going with the automatic
                    var the_image = image;
                    var the_container = container;
                    var image_id = container.id;
                    var image_src = container.src;
                    var gallery_id = jQuery(the_image).data('envira-gallery-id');
                    var naturalWidth = this.naturalWidth;
                    var naturalHeight = this.naturalHeight;
                }

                /* type check */

                if ( gallery_id === undefined || gallery_id === null ) {
                    gallery_id = 0;
                }

                jQuery( document ).trigger( {
                    type:           'envira_image_lazy_load_complete',
                    container:      the_container,
                    image_src:      image_src,
                    image_id:       image_id,
                    gallery_id:     gallery_id,
                    naturalWidth:   naturalWidth,
                    naturalHeight:  naturalHeight,
                } );

            }
            image.onerror = function () {
               /* console.error("Cannot load image"); */
               //do something else...
            }

            image.src = null;
            image.src = url;

            container.lastSetOption = bestSelectedOption;

        }
    };

    var updateWindowSize = function () {

        windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        windowHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
    };

    var setGalleryClass = function ( theClass ) {
        galleryClass = theClass;
    }

    var run = function ( galleryClass ) {

        if ( typeof galleryClass === 'undefined' ) {
            return;
        }

        var update = function (elements, unknownHeight) {
            var elementsCount = elements.length;
            for (var i = 0; i < elementsCount; i++) {
                var element = elements[i];
                var container = unknownHeight ? element : element.parentNode;
                if ( isVisible(container) === true ) {
                    updateElement(container, element);
                }
            }
        };
        if ( galleryClass ) {
            if ( typeof galleryClass !== 'string' ) {
                return;
            }

            if ( envira_lazy_load_delay === 'undefined' || envira_lazy_load_initial === false || envira_lazy_load_initial === 'undefined' ) {
                /* if we can't locate these vars, at least define the delay - there is no delay, super fast */
                envira_lazy_load_delay = 0;
            }


            myVar = setTimeout(function () {
                
                if ( jQuery( galleryClass + ' .envira-lazy > img').exists() ) {
                    //console.log('exists');
                    update(document.querySelectorAll(galleryClass + ' .envira-lazy > img'), false);
                } else if ( jQuery(  galleryClass + ' img.envira-lazy').exists() ) {
                    //console.log('exists');
                    update(document.querySelectorAll(galleryClass + ' img.envira-lazy'), true);
                }

                envira_lazy_load_initial == true; // ok, we did the initial load so now delay can happen

            }, envira_lazy_load_delay);
            

            

        }
        //update(document.querySelectorAll('img.envira-lazy'), true);
    };

    if ('srcset' in document.createElement('img') && typeof window.devicePixelRatio !== 'undefined' && typeof window.addEventListener !== 'undefined' && typeof document.querySelectorAll !== 'undefined') {

        updateWindowSize();

        var image = new Image();
        image.src = 'data:image/webp;base64,UklGRiQAAABXRUJQVlA4IBgAAAAwAQCdASoCAAEADMDOJaQAA3AA/uuuAAA=';
        image.onload = image.onerror = function () {
            hasWebPSupport = image.width === 2;
            if (hasIntersectionObserverSupport) {

                var updateIntersectionObservers = function () {
                    var elements = document.querySelectorAll('.envira-lazy');
                    var elementsCount = elements.length;
                    for (var i = 0; i < elementsCount; i++) {
                        var element = elements[i];
                        if (typeof element.responsivelyLazyObserverAttached === 'undefined') {
                            element.responsivelyLazyObserverAttached = true;
                            intersectionObserver.observe(element);
                        }
                    }
                };

                var intersectionObserver = new IntersectionObserver(function (entries) {
                    for (var i in entries) {
                        var entry = entries[i];
                        if (entry.intersectionRatio > 0) {
                            var target = entry.target;
                            if (target.tagName.toLowerCase() !== 'img') {
                                var img = target.querySelector('img');
                                if (img !== null) {
                                    updateElement(target, img);
                                }
                            } else {
                                updateElement(target, target);
                            }
                        }
                    }
                });

                run();

            } else {

                var requestAnimationFrameFunction = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function (callback) {
                    window.setTimeout(callback, 1000 / 60);
                };

                var hasChange = true;
                var runIfHasChange = function () {
                    if ( hasChange ) {
                        hasChange = false;
                        // run();
                    }
                    requestAnimationFrameFunction.call(null, runIfHasChange);
                };



                var setChanged = function () {
                    hasChange = true;
                    //console.log('setChanged');
                    runIfHasChange();
                };

                var updateParentNodesScrollListeners = function () {
                    var elements = document.querySelectorAll('.envira-lazy');
                    var elementsCount = elements.length;
                    for (var i = 0; i < elementsCount; i++) {
                        var parentNode = elements[i].parentNode;
                        while (parentNode && parentNode.tagName.toLowerCase() !== 'html') {
                            if (typeof parentNode.responsivelyLazyScrollAttached === 'undefined') {
                                parentNode.responsivelyLazyScrollAttached = true;
                                parentNode.addEventListener('scroll', setChanged);
                            }
                            parentNode = parentNode.parentNode;
                        }
                    }
                };

            }

            var attachEvents = function () {
                if (hasIntersectionObserverSupport) {
                    var resizeTimeout = null;
                }
                window.addEventListener('resize', function () {
                    updateWindowSize();
                    if (hasIntersectionObserverSupport) {
                        window.clearTimeout(resizeTimeout);
                        resizeTimeout = window.setTimeout(function () {
                            run();
                        }, 300);
                    } else {
                        setChanged();
                    }
                });
                if (hasIntersectionObserverSupport) {
                    window.addEventListener('load', run);
                    updateIntersectionObservers();
                } else {
                    //console.log('*********attaching scrool and load listeners');
                    window.addEventListener('scroll', setChanged);
                    window.addEventListener('load', setChanged);
                    updateParentNodesScrollListeners();
                }
                if (typeof MutationObserver !== 'undefined') {
                    var observer = new MutationObserver(function () {
                        if (hasIntersectionObserverSupport) {
                            updateIntersectionObservers();
                            run();
                        } else {
                            updateParentNodesScrollListeners();
                            setChanged();
                        }
                    });
                    observer.observe(document.querySelector('body'), {childList: true, subtree: true});
                }
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', attachEvents);
                //console.log('loading');
            } else {
                attachEvents();
                //console.log('attachEvents');
            }
        };
    }

    return {
        'run': run,
        'isVisible': isVisible,
        'setGalleryClass': setGalleryClass
    };

}());

}