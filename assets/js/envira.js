/**
* envira.js is a placeholder, which CodeKit attaches the following JS files to, before compiling as min/envira-min.js:
*/
// @codekit-append "lib/jquery.justifiedGallery.js";
// @codekit-append "lib/enviraJustifiedGallery-extensions.js";
// @codekit-append "lib/touchsupport.js";
// @codekit-append "lib/touchswipe.js";
// @codekit-append "lib/mousewheel.js";
// @codekit-append "lib/imagesloaded.js";
// @codekit-append "lib/isotope.js";
// @codekit-append "lib/fancybox.js";
// @codekit-append "lib/fancybox-buttons.js";
// @codekit-append "lib/fancybox-media.js";
// @codekit-append "lib/fancybox-thumbs.js";
// @codekit-append "lib/fancybox-video.js";
// @codekit-append "lib/responsivelyLazy.js";
/**
* To load more JS resources:
* - Add them to the lib subfolder
* - Add the to the imports directive of this file in CodeKit
*/

/**
* If a lightbox caption's link is an anchor, close the lightbox
*/
jQuery( document ).ready( function( $ ) {

	$( 'body' ).on( 'click', 'div.envirabox-title a[href*="#"]:not([href="#"])', function( e ) {

		if ( location.pathname.replace( /^\//, '' ) == this.pathname.replace( /^\//, '' ) && location.hostname == this.hostname ) {
      		$.envirabox.close();
      		return false;
      	}

	} );

    /* setup lazy load event */
    $( document ).on( "envira_image_lazy_load_complete", function( event ) {
        if ( event !== undefined && event.image_id !== undefined && event.image_id !== null ) {

            var envira_container = $('div.envira-gallery-public').find('img#' + event.image_id);

            if ( $('div.envira-gallery-public').hasClass('envira-gallery-0-columns') ) {
                /* this is an automatic gallery */
                $( envira_container ).closest('div.envira-gallery-item-inner').find( 'div.envira-gallery-position-overlay' ).delay( 100 ).show();
            } else {
                /* this is a legacy gallery */
                $( envira_container ).closest('div.envira-gallery-item-inner').find( 'div.envira-gallery-position-overlay' ).delay( 100 ).show();

                /* re-do the padding bottom */
                /* $padding_bottom = ( $output_height / $output_width ) * 100; */

                var envira_lazy_width = $( envira_container ).closest('div.envira-gallery-item-inner').find('.envira-lazy').width();
                var ratio1 = ( event.naturalHeight / event.naturalWidth );
                var ratio2 = ( event.naturalHeight / envira_lazy_width );

                if ( ratio2 < ratio1 ) {
                    var ratio = ratio2;
                } else {
                    var ratio = ratio1;
                }
                
                var padding_bottom = ratio * 100;
                
                $( envira_container ).closest('div.envira-gallery-item-inner').find('.envira-lazy').css('padding-bottom', padding_bottom + '%');
                $( envira_container ).closest('div.envira-gallery-item-inner').find('.envira-lazy').data('envira-changed', 'true');

                if ( window["envira_container_" + event.gallery_id] !== undefined ) {

                    window["envira_container_" + event.gallery_id].on( 'layoutComplete',
                      function( event, laidOutItems ) {
                        
                        $( envira_container ).closest('div.envira-gallery-item-inner').find( 'span.envira-title' ).delay( 1000 ).css('visibility', 'visible');
                        $( envira_container ).closest('div.envira-gallery-item-inner').find( 'span.envira-caption' ).delay( 1000 ).css('visibility', 'visible');


                      }
                    );

                }

                
                $('#envira-gallery-' + event.gallery_id).enviratope('layout');

            }

        }
    });

} );

function jg_effect_desaturate(src) {
    var supportsCanvas = !!document.createElement('canvas').getContext;
    if (supportsCanvas) {
        var canvas = document.createElement('canvas'),
        context = canvas.getContext('2d'),
        imageData, px, length, i = 0, gray,
        img = new Image();

        img.src = src;
        canvas.width = img.width;
        canvas.height = img.height;
        context.drawImage(img, 0, 0);

        imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        px = imageData.data;
        length = px.length;

        for (; i < length; i += 4) {
            gray = px[i] * .3 + px[i + 1] * .59 + px[i + 2] * .11;
            px[i] = px[i + 1] = px[i + 2] = gray;
        }

        context.putImageData(imageData, 0, 0);
        return canvas.toDataURL();
    } else {
        return src;
    }
}

function jg_effect_threshold(src) {
    var supportsCanvas = !!document.createElement('canvas').getContext;
    if (supportsCanvas) {
        var canvas = document.createElement('canvas'),
        context = canvas.getContext('2d'),
        imageData, px, length, i = 0, gray,
        img = new Image();

        img.src = src;
        canvas.width = img.width;
        canvas.height = img.height;
        context.drawImage(img, 0, 0);

        imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        px = imageData.data;
        length = px.length;

        threshold = 120;

        for (var i=0; i<length; i+=4) {
            var r = px[i];
            var g = px[i+1];
            var b = px[i+2];
            var v = (0.2126*r + 0.7152*g + 0.0722*b >= threshold) ? 255 : 0;
            px[i] = px[i+1] = px[i+2] = v
        }

        context.putImageData(imageData, 0, 0);
        return canvas.toDataURL();
    } else {
        return src;
    }
}

function jg_effect_blur(src) {
    var supportsCanvas = !!document.createElement('canvas').getContext;
    if (supportsCanvas) {
        var canvas = document.createElement('canvas'),
        context = canvas.getContext('2d'),
        imageData, px, length, i = 0, gray, top_x = 0, top_y = 0, radius = 30, iterations = 1
        img = new Image();

        img.src = src;
        canvas.width = img.width;
        canvas.height = img.height;
        context.drawImage(img, 0, 0);

        var imageData;
        var width = img.width;
        var height = img.height;

        imageData = context.getImageData( top_x, top_y, width, height );
        var pixels = imageData.data;

        var rsum,gsum,bsum,asum,x,y,i,p,p1,p2,yp,yi,yw,idx;
        var wm = width - 1;
        var hm = height - 1;
        var wh = width * height;
        var rad1 = radius + 1;

        var r = [];
        var g = [];
        var b = [];

        var mul_sum = mul_table[radius];
        var shg_sum = shg_table[radius];

        var vmin = [];
        var vmax = [];

        while ( iterations-- > 0 ){
            yw = yi = 0;

            for ( y=0; y < height; y++ ){
                rsum = pixels[yw]   * rad1;
                gsum = pixels[yw+1] * rad1;
                bsum = pixels[yw+2] * rad1;

                for( i = 1; i <= radius; i++ ){
                    p = yw + (((i > wm ? wm : i )) << 2 );
                    rsum += pixels[p++];
                    gsum += pixels[p++];
                    bsum += pixels[p++];
                }

                for ( x = 0; x < width; x++ ){
                    r[yi] = rsum;
                    g[yi] = gsum;
                    b[yi] = bsum;

                    if( y==0) {
                        vmin[x] = ( ( p = x + rad1) < wm ? p : wm ) << 2;
                        vmax[x] = ( ( p = x - radius) > 0 ? p << 2 : 0 );
                    }

                    p1 = yw + vmin[x];
                    p2 = yw + vmax[x];

                    rsum += pixels[p1++] - pixels[p2++];
                    gsum += pixels[p1++] - pixels[p2++];
                    bsum += pixels[p1++] - pixels[p2++];

                    yi++;
                }
                yw += ( width << 2 );
            }

            for ( x = 0; x < width; x++ ){
                yp = x;
                rsum = r[yp] * rad1;
                gsum = g[yp] * rad1;
                bsum = b[yp] * rad1;

                for( i = 1; i <= radius; i++ ){
                  yp += ( i > hm ? 0 : width );
                  rsum += r[yp];
                  gsum += g[yp];
                  bsum += b[yp];
                }

                yi = x << 2;
                for ( y = 0; y < height; y++){
                    pixels[yi]   = (rsum * mul_sum) >>> shg_sum;
                    pixels[yi+1] = (gsum * mul_sum) >>> shg_sum;
                    pixels[yi+2] = (bsum * mul_sum) >>> shg_sum;

                    if( x == 0 ) {
                        vmin[y] = ( ( p = y + rad1) < hm ? p : hm ) * width;
                        vmax[y] = ( ( p = y - radius) > 0 ? p * width : 0 );
                    }

                    p1 = x + vmin[y];
                    p2 = x + vmax[y];

                    rsum += r[p1] - r[p2];
                    gsum += g[p1] - g[p2];
                    bsum += b[p1] - b[p2];

                    yi += width << 2;
                }
            }
        }
        context.putImageData( imageData, top_x, top_y );

        return canvas.toDataURL();

    } else {
        return src;
    }
}

function jg_effect_vintage( img ) {
    var options = {
        onError: function() {
            alert('ERROR');
        }
    };
    var effect = {
        vignette: 1,
        sepia: true,
        noise: 50,
        desaturate: .2,
        lighten: .1

    };
    new VintageJS(img, options, effect);
}