/*!
 * Justified Gallery / Envira Extensions and Overrides - v3.6.2
 * Copyright (c) 2016 David Bisset, Benjamin Rojas
 * Licensed under the MIT license.
 */

(function ($) {
  var justifiedGallery =  $.fn.justifiedGallery;
  var EnviraJustifiedGallery = {};

  $.fn.enviraJustifiedGallery = function () {
    var obj = justifiedGallery.apply(this, arguments);
    EnviraJustifiedGallery = obj.data('jg.controller');

    if (EnviraJustifiedGallery !== undefined) {

    EnviraJustifiedGallery.displayEntryCaption = function ($entry) {

        var $image = this.imgFromEntry($entry);
        if ($image !== null && this.settings.captions) {
          var $imgCaption = this.captionFromEntry($entry);

          // Create it if it doesn't exists
          if ($imgCaption === null) {

            var caption = $image.data('envira-caption');
            if ( caption !== undefined && typeof caption === 'string' ) {
                caption = caption.replace('<', '&lt;');
            }

            if (this.isValidCaption(caption)) { // Create only we found something
              caption = $('<textarea />').html(caption).text();
              $imgCaption = $('<div class="caption">' + caption + '</div>');
              $entry.append($imgCaption);
              $entry.data('jg.createdCaption', true);
            }
          }

          // Create events (we check again the $imgCaption because it can be still inexistent)
          if ($imgCaption !== null) {
            if (!this.settings.cssAnimation) $imgCaption.stop().fadeTo(0, this.settings.captionSettings.nonVisibleOpacity);
            this.addCaptionEventsHandlers($entry);
          }
        } else {
          this.removeCaptionEventsHandlers($entry);
        }
    };

    return EnviraJustifiedGallery;

    }

  };
})(jQuery);

/*!
 * vintageJS
 * Add a retro/vintage effect to images using the HTML5 canvas element
 * 
 * @license Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 * @author Robert Fleischmann <rendro87@gmail.com>
 * @version 1.1.5
*/

!function(a,b){"function"==typeof define&&define.amd?define("vintagejs",["jquery"],function(c){return a.VintageJS=b(c)}):"object"==typeof exports?module.exports=b(require("jquery")):a.VintageJS=b(jQuery)}(this,function(a){var b=function(a,b,c){if(!1==a instanceof HTMLImageElement)throw"The element (1st parameter) must be an instance of HTMLImageElement";var d,e,f,g,h,i,j,k,l,m=new Image,n=new Image,o=document.createElement("canvas"),p=o.getContext("2d"),q={onStart:function(){},onStop:function(){},onError:function(){},mime:"image/jpeg"},r={curves:!1,screen:!1,desaturate:!1,vignette:!1,lighten:!1,noise:!1,viewFinder:!1,sepia:!1,brightness:!1,contrast:!1};m.onerror=q.onError,m.onload=function(){i=o.width=m.width,j=o.height=m.height,d()},n.onerror=q.onError,n.onload=function(){p.clearRect(0,0,i,j),p.drawImage(n,0,0,i,j),(window.vjsImageCache||(window.vjsImageCache={}))[l]=p.getImageData(0,0,i,j).data,d()},e=function(a){q.onStart(),k={};for(var b in r)k[b]=a[b]||r[b];g=[],k.viewFinder&&g.push(k.viewFinder),m.src==h?d():m.src=h},d=function(){if(0===g.length)return f();var a=g.pop();return l=[i,j,a].join("-"),window.vjsImageCache&&window.vjsImageCache[l]?d():void(n.src=a)},f=function(){var b,c,d;p.clearRect(0,0,i,j),p.drawImage(m,0,0,i,j),(k.vignette||k.lighten)&&(b=Math.sqrt(Math.pow(i/2,2)+Math.pow(j/2,2))),k.vignette&&(p.globalCompositeOperation="source-over",c=p.createRadialGradient(i/2,j/2,0,i/2,j/2,b),c.addColorStop(0,"rgba(0,0,0,0)"),c.addColorStop(.5,"rgba(0,0,0,0)"),c.addColorStop(1,["rgba(0,0,0,",k.vignette,")"].join("")),p.fillStyle=c,p.fillRect(0,0,i,j)),k.lighten&&(p.globalCompositeOperation="lighter",c=p.createRadialGradient(i/2,j/2,0,i/2,j/2,b),c.addColorStop(0,["rgba(255,255,255,",k.lighten,")"].join("")),c.addColorStop(.5,"rgba(255,255,255,0)"),c.addColorStop(1,"rgba(0,0,0,0)"),p.fillStyle=c,p.fillRect(0,0,i,j)),d=p.getImageData(0,0,i,j);var e,f,g,h,l,n,o,r,s,t=d.data;k.contrast&&(s=259*(k.contrast+255)/(255*(259-k.contrast))),k.viewFinder&&(r=window.vjsImageCache[[i,j,k.viewFinder].join("-")]);for(var u=i*j;u>=0;--u)for(e=u<<2,k.curves&&(t[e]=k.curves.r[t[e]],t[e+1]=k.curves.g[t[e+1]],t[e+2]=k.curves.b[t[e+2]]),k.contrast&&(t[e]=s*(t[e]-128)+128,t[e+1]=s*(t[e+1]-128)+128,t[e+2]=s*(t[e+2]-128)+128),k.brightness&&(t[e]+=k.brightness,t[e+1]+=k.brightness,t[e+2]+=k.brightness),k.screen&&(t[e]=255-(255-t[e])*(255-k.screen.r*k.screen.a)/255,t[e+1]=255-(255-t[e+1])*(255-k.screen.g*k.screen.a)/255,t[e+2]=255-(255-t[e+2])*(255-k.screen.b*k.screen.a)/255),k.noise&&(o=k.noise-Math.random()*k.noise/2,t[e]+=o,t[e+1]+=o,t[e+2]+=o),k.viewFinder&&(t[e]=t[e]*r[e]/255,t[e+1]=t[e+1]*r[e+1]/255,t[e+2]=t[e+2]*r[e+2]/255),k.sepia&&(g=t[e],h=t[e+1],l=t[e+2],t[e]=.393*g+.769*h+.189*l,t[e+1]=.349*g+.686*h+.168*l,t[e+2]=.272*g+.534*h+.131*l),k.desaturate&&(n=(t[e]+t[e+1]+t[e+2])/3,t[e]+=(n-t[e])*k.desaturate,t[e+1]+=(n-t[e+1])*k.desaturate,t[e+2]+=(n-t[e+2])*k.desaturate),f=2;f>=0;--f)t[e+f]=~~(t[e+f]>255?255:t[e+f]<0?0:t[e+f]);p.putImageData(d,0,0),a.src=p.canvas.toDataURL(q.mime),q.onStop()},h=a.src,b=b||{};for(var s in q)q[s]=b[s]||q[s];return c&&e(c),{apply:function(){h=a.src},reset:function(){a.src=h},vintage:e}};return a.fn.vintage=function(c,d){return this.each(function(){a.data(this,"vintageJS")||a.data(this,"vintageJS",new b(this,c,d))})},b});


/*

Superfast Blur - a fast Box Blur For Canvas

Version:    0.5
Author:     Mario Klingemann
Contact:    mario@quasimondo.com
Website:    http://www.quasimondo.com/BoxBlurForCanvas
Twitter:    @quasimondo

In case you find this class useful - especially in commercial projects -
I am not totally unhappy for a small donation to my PayPal account
mario@quasimondo.de

Or support me on flattr:
https://flattr.com/thing/140066/Superfast-Blur-a-pretty-fast-Box-Blur-Effect-for-CanvasJavascript

Copyright (c) 2011 Mario Klingemann

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
var mul_table = [ 1,57,41,21,203,34,97,73,227,91,149,62,105,45,39,137,241,107,3,173,39,71,65,238,219,101,187,87,81,151,141,133,249,117,221,209,197,187,177,169,5,153,73,139,133,127,243,233,223,107,103,99,191,23,177,171,165,159,77,149,9,139,135,131,253,245,119,231,224,109,211,103,25,195,189,23,45,175,171,83,81,79,155,151,147,9,141,137,67,131,129,251,123,30,235,115,113,221,217,53,13,51,50,49,193,189,185,91,179,175,43,169,83,163,5,79,155,19,75,147,145,143,35,69,17,67,33,65,255,251,247,243,239,59,29,229,113,111,219,27,213,105,207,51,201,199,49,193,191,47,93,183,181,179,11,87,43,85,167,165,163,161,159,157,155,77,19,75,37,73,145,143,141,35,138,137,135,67,33,131,129,255,63,250,247,61,121,239,237,117,29,229,227,225,111,55,109,216,213,211,209,207,205,203,201,199,197,195,193,48,190,47,93,185,183,181,179,178,176,175,173,171,85,21,167,165,41,163,161,5,79,157,78,154,153,19,75,149,74,147,73,144,143,71,141,140,139,137,17,135,134,133,66,131,65,129,1];
        
   
var shg_table = [0,9,10,10,14,12,14,14,16,15,16,15,16,15,15,17,18,17,12,18,16,17,17,19,19,18,19,18,18,19,19,19,20,19,20,20,20,20,20,20,15,20,19,20,20,20,21,21,21,20,20,20,21,18,21,21,21,21,20,21,17,21,21,21,22,22,21,22,22,21,22,21,19,22,22,19,20,22,22,21,21,21,22,22,22,18,22,22,21,22,22,23,22,20,23,22,22,23,23,21,19,21,21,21,23,23,23,22,23,23,21,23,22,23,18,22,23,20,22,23,23,23,21,22,20,22,21,22,24,24,24,24,24,22,21,24,23,23,24,21,24,23,24,22,24,24,22,24,24,22,23,24,24,24,20,23,22,23,24,24,24,24,24,24,24,23,21,23,22,23,24,24,24,22,24,24,24,23,22,24,24,25,23,25,25,23,24,25,25,24,22,25,25,25,24,23,24,25,25,25,25,25,25,25,25,25,25,25,25,23,25,23,24,25,25,25,25,25,25,25,25,25,24,22,25,25,23,25,25,20,24,25,24,25,25,22,24,25,24,25,24,25,25,24,25,25,25,25,22,25,25,25,24,25,24,25,18];

        
function boxBlurImage( imageID, canvasID, radius, blurAlphaChannel, iterations ){
            
    var img = document.getElementById( imageID );
    var w = img.naturalWidth;
    var h = img.naturalHeight;
       
    var canvas = document.getElementById( canvasID );
      
    canvas.style.width  = w + "px";
    canvas.style.height = h + "px";
    canvas.width = w;
    canvas.height = h;
    
    var context = canvas.getContext("2d");
    context.clearRect( 0, 0, w, h );
    context.drawImage( img, 0, 0 );

    if ( isNaN(radius) || radius < 1 ) return;
    
    if ( blurAlphaChannel )
    {
        boxBlurCanvasRGBA( canvasID, 0, 0, w, h, radius, iterations );
    } else {
        boxBlurCanvasRGB( canvasID, 0, 0, w, h, radius, iterations );
    }
    
}


function boxBlurCanvasRGBA( id, top_x, top_y, width, height, radius, iterations ){
    if ( isNaN(radius) || radius < 1 ) return;
    
    radius |= 0;
    
    if ( isNaN(iterations) ) iterations = 1;
    iterations |= 0;
    if ( iterations > 3 ) iterations = 3;
    if ( iterations < 1 ) iterations = 1;
    
    var canvas  = document.getElementById( id );
    var context = canvas.getContext("2d");
    var imageData;
    
    try {
      try {
        imageData = context.getImageData( top_x, top_y, width, height );
      } catch(e) {
      
        // NOTE: this part is supposedly only needed if you want to work with local files
        // so it might be okay to remove the whole try/catch block and just use
        // imageData = context.getImageData( top_x, top_y, width, height );
        try {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
            imageData = context.getImageData( top_x, top_y, width, height );
        } catch(e) {
            alert("Cannot access local image");
            throw new Error("unable to access local image data: " + e);
            return;
        }
      }
    } catch(e) {
      alert("Cannot access image");
      throw new Error("unable to access image data: " + e);
      return;
    }
            
    var pixels = imageData.data;
        
    var rsum,gsum,bsum,asum,x,y,i,p,p1,p2,yp,yi,yw,idx,pa;      
    var wm = width - 1;
    var hm = height - 1;
    var wh = width * height;
    var rad1 = radius + 1;
    
    var mul_sum = mul_table[radius];
    var shg_sum = shg_table[radius];

    var r = [];
    var g = [];
    var b = [];
    var a = [];
    
    var vmin = [];
    var vmax = [];
  
    while ( iterations-- > 0 ){
        yw = yi = 0;
     
        for ( y=0; y < height; y++ ){
            rsum = pixels[yw]   * rad1;
            gsum = pixels[yw+1] * rad1;
            bsum = pixels[yw+2] * rad1;
            asum = pixels[yw+3] * rad1;
            
            
            for( i = 1; i <= radius; i++ ){
                p = yw + (((i > wm ? wm : i )) << 2 );
                rsum += pixels[p++];
                gsum += pixels[p++];
                bsum += pixels[p++];
                asum += pixels[p]
            }
            
            for ( x = 0; x < width; x++ ) {
                r[yi] = rsum;
                g[yi] = gsum;
                b[yi] = bsum;
                a[yi] = asum;

                if( y==0) {
                    vmin[x] = ( ( p = x + rad1) < wm ? p : wm ) << 2;
                    vmax[x] = ( ( p = x - radius) > 0 ? p << 2 : 0 );
                } 
                
                p1 = yw + vmin[x];
                p2 = yw + vmax[x];
                  
                rsum += pixels[p1++] - pixels[p2++];
                gsum += pixels[p1++] - pixels[p2++];
                bsum += pixels[p1++] - pixels[p2++];
                asum += pixels[p1]   - pixels[p2];
                     
                yi++;
            }
            yw += ( width << 2 );
        }
      
        for ( x = 0; x < width; x++ ) {
            yp = x;
            rsum = r[yp] * rad1;
            gsum = g[yp] * rad1;
            bsum = b[yp] * rad1;
            asum = a[yp] * rad1;
            
            for( i = 1; i <= radius; i++ ) {
              yp += ( i > hm ? 0 : width );
              rsum += r[yp];
              gsum += g[yp];
              bsum += b[yp];
              asum += a[yp];
            }
            
            yi = x << 2;
            for ( y = 0; y < height; y++) {
                
                pixels[yi+3] = pa = (asum * mul_sum) >>> shg_sum;
                if ( pa > 0 )
                {
                    pa = 255 / pa;
                    pixels[yi]   = ((rsum * mul_sum) >>> shg_sum) * pa;
                    pixels[yi+1] = ((gsum * mul_sum) >>> shg_sum) * pa;
                    pixels[yi+2] = ((bsum * mul_sum) >>> shg_sum) * pa;
                } else {
                    pixels[yi] = pixels[yi+1] = pixels[yi+2] = 0;
                }               
                if( x == 0 ) {
                    vmin[y] = ( ( p = y + rad1) < hm ? p : hm ) * width;
                    vmax[y] = ( ( p = y - radius) > 0 ? p * width : 0 );
                } 
              
                p1 = x + vmin[y];
                p2 = x + vmax[y];

                rsum += r[p1] - r[p2];
                gsum += g[p1] - g[p2];
                bsum += b[p1] - b[p2];
                asum += a[p1] - a[p2];

                yi += width << 2;
            }
        }
    }
    
    context.putImageData( imageData, top_x, top_y );
    
}

function boxBlurCanvasRGB( id, top_x, top_y, width, height, radius, iterations ){
    if ( isNaN(radius) || radius < 1 ) return;
    
    radius |= 0;
    
    if ( isNaN(iterations) ) iterations = 1;
    iterations |= 0;
    if ( iterations > 3 ) iterations = 3;
    if ( iterations < 1 ) iterations = 1;
    
    var canvas  = id;
    var context = canvas.getContext("2d");
    var imageData;
    
    try {
      try {
        imageData = context.getImageData( top_x, top_y, width, height );
      } catch(e) {
      
        // NOTE: this part is supposedly only needed if you want to work with local files
        // so it might be okay to remove the whole try/catch block and just use
        // imageData = context.getImageData( top_x, top_y, width, height );
        try {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
            imageData = context.getImageData( top_x, top_y, width, height );
        } catch(e) {
            alert("Cannot access local image");
            throw new Error("unable to access local image data: " + e);
            return;
        }
      }
    } catch(e) {
      alert("Cannot access image");
      throw new Error("unable to access image data: " + e);
      return;
    }
            
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
    
}