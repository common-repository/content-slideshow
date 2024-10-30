/**
 * Content slideshow script.
 */

var cs = {};
( function( $, wp ) {
	cs = {
		currentId : -1,
		allData : {},
		width : 0,
		height : 0,
		template : {},
		container : {},
		t : 0,
		reloop : false,
		init : function() {
			cs.allData = contentSlideshowData;
			cs.width = window.innerWidth;
			cs.height = window.innerHeight;
			cs.template = wp.template( 'single-image-figure' );
			cs.container = $( 'body' );

			// Extra initial call to pre-load the first image.
			cs.nextImage(); // "pre" load the first image.
			cs.nextImage(); // pre-load the second image and show the first image.
			cs.t = setInterval( cs.nextImage, 3210 ); // Incrementally load then show additional images.
		},

		// Pre-load the next image, then show the previous image.
		// This function generates the HTML for the image, so the img tag is automatically lazy-loaded.
		nextImage : function() {
			if ( cs.currentId < cs.allData.length - 1 ) {

				// Increment to the next image in the slideshow.
				cs.currentId = cs.currentId + 1;

			} else {

				// This is the end of the slideshow.
				if ( 500 === cs.allData.length ) {
					// We loaded 500 images randomly. 
					// There may be more than 500 images in the collection, so reload from PHP to get a new batch.
					location.reload(); 
				} else {
					// There are less than 500 total images in the collection, so they're all loaded now.
					// Hide the last image and reloop to the beginning of the slideshow.
					var fig2 = document.getElementById( 'figure-' + cs.allData[cs.currentId - 1].id );
					fig2.style.top = '100%';
					fig2.style.opacity = '0';
					cs.currentId = 0;
					cs.reloop = true;
				}
			}
			if ( ! cs.reloop ) {
				// Generate the html markup for the "next" image.
				var figure = cs.template( cs.allData[cs.currentId] );
				
				// Add the html markup for the "next" image to the DOM.
				cs.container.append( figure );

				
			}
			
			// Display the previous image.
			cs.showPrevImage();
		},

		// Display the previous image, already loaded.
		showPrevImage: function() {
			if ( 0 === cs.currentId ) {
				// We're showing the previous image, so do nothing if rhis is the first image.
				return;
			}

			// For overscaled images, set the image size markup based on the loaded image file.
			// @todo: the markup needs to know the width before the image loads to switch to srcset.
			var img = document.getElementById( 'image-' + cs.allData[cs.currentId - 1].id );
			var ratio = img.naturalWidth / img.naturalHeight;
			if ( img.width > cs.width ) {
				img.style.width = cs.width + 'px';
				img.style.height = cs.width / ratio + 'px';
			} else if ( img.height > cs.height ) {
				img.style.height = cs.height + 'px';
				img.style.width = cs.height * ratio + 'px';
			}
			
			// Fade in the next image (via CSS transition).
			var fig = document.getElementById( 'figure-' + cs.allData[cs.currentId - 1].id );
			fig.style.top = '0';
			fig.style.opacity = '1';
			fig.getElementsByTagName('a')[0].tabIndex = 0;
			if ( 1 === cs.currentId ) {
				return;
			}

			// Hide the image that was previously shown. 
			var fig2 = document.getElementById( 'figure-' + cs.allData[cs.currentId - 2].id );
			fig2.style.top = '100%';
			fig2.style.opacity = '0';
			fig2.getElementsByTagName('a')[0].tabIndex = -1;
		}
	}
	
	$(document).ready( function() { cs.init(); } );

} )( jQuery, window.wp );