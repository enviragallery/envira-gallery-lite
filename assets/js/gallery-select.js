/* ==========================================================
 * gallery-select.js
 *
 * Defines a Backbone Modal for Gallery or Album selection.
 *
 * To use:
 *  EnviraGalleryModalWindow.content( new EnviraGallerySelectionView( {
 *      action:                 'gallery',                  // gallery|album
 *      multiple:               true,                       // Support multiple Gallery / Album selection
 *      sidebar_view:           'envira-selection-sidebar'  // WordPress media view to render into the sidebar
 *      modal_title:            'Insert Gallery',           // Title for the Backbone Modal
 *      insert_button_label:    'Insert',                   // Label for the Insert Button
 *      onInsert:               function() {},              // Callback function when the 'Insert' button in the modal is pressed. 
 *                                                          // You'll have access to this.selection, containing the chosen Galleries / Albums.
 *  } ).open();
 *
 * Only the 'action' and 'multiple' arguments are required; defaults will be appled for the other arguments if they're not specified.
 *
 * You can also attach a global event once the user has chosen Galleries / Albums, clicked the Insert button and the item(s)
 * have been inserted:
 *  jQuery( document ).on( 'enviraGalleryModalData', function( e ) { 
 *      console.log( e.action );            // 'gallery' or 'album'
 *      console.log( e.multiple );          // Whether the user could select multiple Galleries / Albums (true|false)
 *      console.log( e.items );             // An array of Galleries or Albums
 *      console.log( e.insert_options );    // An object of the Insert Options the user chose
 *  } );
 */

/**
* Controller: Modal Window
* - Used by most Envira Backbone views to display information e.g. bulk edit, edit single image etc.
*/
if ( typeof EnviraGalleryModalWindow == 'undefined' ) {

    var EnviraGalleryModalWindow = new wp.media.view.Modal( {
        controller: {
            trigger: function() {
            }
        }
    } );

}

/**
* View: Error
* - Renders a WordPress style error message when something goes wrong.
*
* @since 1.4.3.0
*/
wp.media.view.EnviraGalleryError = wp.Backbone.View.extend( {

    // The outer tag and class name to use. The item is wrapped in this
    tagName   : 'div',
    className : 'notice error envira-gallery-error',

    render: function() {

        // Load the template to render
        // See includes/admin/media-views.php
        this.template = wp.media.template( 'envira-gallery-error' );

        // Define the HTML for the template
        this.$el.html( this.template( this.model ) );

        // Return the template
        return this;

    }

} );

/**
* View: Single Item (Gallery or Album)
* - Renders an <li> element within the "Choose your Gallery / Album" view
*
* @since 1.5.0
*/
var EnviraGallerySelectionItemView = wp.Backbone.View.extend( {
    
    /**
    * The Tag Name and Tag's Class(es)
    */
    tagName:    'li',
    className:  'attachment',

    /**
    * Template
    * - The template to load inside the above tagName element
    */
    template:   wp.template( 'envira-selection-item' ),

    /**
    * Initialize
    *
    * @param object model   EnviraGalleryImage Backbone Model
    */
    initialize: function( args ) {

        // Assign the model to this view
        this.model = args.model;

    },

    /**
    * Render
    * - Binds the model to the view, so we populate the view's fields and data
    */
    render: function() {

        // Get HTML
        this.$el.html( this.template( this.model.attributes ) );
        return this;

    }

} );

/**
* View: Sidebar
* - Renders the Helpful Tips when selecting a Gallery or Album for insertion into a Post.
*
* @since 1.5.0.3
*/
var EnviraGallerySelectionSidebarView = wp.Backbone.View.extend( {
    
    /**
    * The Tag Name and Tag's Class(es)
    */
    tagName:    'div',
    className:  'sidebar',

    /**
    * Initialize
    *
    * @param    string     view    View to render
    */
    initialize: function( view ) {

        // Define the sidebar view to render
        // This must be a WordPress media view using e.g. <script type="text/html" id="tmpl-my-sidebar-view-name"> 
        this.view       = ( ( typeof view == 'undefined' ) ? 'envira-selection-sidebar' : view );
        
    },    

    /**
    * Render the view
    */
    render: function() {

        // Get HTML
        this.$el.html( wp.template( this.view ) );
        return this;

    }

} );

/**
* Gallery Selection View
*/
var EnviraGallerySelectionView = wp.Backbone.View.extend( {

    /**
    * The Tag Name and Tag's Class(es)
    */
    tagName:    'div',
    className:  'media-frame mode-select wp-core-ui hide-router hide-menu',

    /**
    * Template
    * - The template to load inside the above tagName element
    */
    template:   wp.template( 'envira-selection' ),

    /**
    * Events
    * - Functions to call when specific events occur
    */
    events: {
        // Clicked a gallery
        'click .attachment':                'click',

        // Used the search input
        'keyup':                            'search',
        'search':                           'search',

        // Display Options
        'change select':                     'updateInsertOption',

        // Insert Button
        'click button.media-button-insert': 'insert',
    },

    /**
    * Initialize
    *
    * @param    object  args:
    * - action:                 gallery|album (required)
    * - multiple:               true|false (required)
    * - sidebar_view:           A custom WordPress media view to render into the sidebar (optional)
    * - modal_title:            The modal title (optional)
    * - insert_button_label:    The 'Insert' button label (optional)
    * - prepend_ids:            Optional array of Galleries or Albums to always prepend to the resultset
    * - onInsert:               function() {} (optional)
    */
    initialize: function( args ) {

        // Whether we're inserting galleries or albums
        this.action             = args.action;

        // Whether multiple galleries or albums can be selected
        this.multiple           = args.multiple;

        // Define the sidebar view to render into this Modal
        // This must be a WordPress media view using e.g. <script type="text/html" id="tmpl-my-sidebar-view-name"> 
        this.sidebar_view       = ( ( typeof args.sidebar_view == 'undefined' ) ? 'envira-selection-sidebar' : args.sidebar_view );
        
        // Store the onInsert function provided by the calling class
        this.onInsert           = args.onInsert;

        // Whether we're prepending galleries / albums to the collection
        this.prepend_ids        = ( ( typeof args.prepend_ids == 'undefined' ) ? false : args.prepend_ids );

        // Whether we're preselecting galleries / albums
        this.select_ids         = ( ( typeof args.select_ids == 'undefined' ) ? false : args.select_ids );

        // Define a collection, which will store the Galleries
        this.selection          = new Backbone.Collection(); // The galleries / albums the user has selected
        this.collection         = new Backbone.Collection(); // The available galleries / albums

        // Define a model to store Insert Options
        this.insert_options = new Backbone.Model( {
            'modal_title':          ( ( typeof args.modal_title == 'undefined' ) ? envira_gallery_select.modal_title : args.modal_title ),
            'title':                0,
            'insert_button_label':  ( ( typeof args.modal_title == 'undefined' ) ? envira_gallery_select.insert_button_label : args.insert_button_label )
        } );
        
        // Define some other flags.
        this.is_loading         = false;
        this.search_timeout     = false;

        // Define loading and loaded events
        this.on( 'loading', this.loading, this );
        this.on( 'loaded',  this.loaded, this );

        // Get Galleries
        this.getItems( false, '' );

    },

    /**
     * Called when a Gallery is clicked
     *
     * @param object    event   Event
     */
    click: function( event ) {

        // Get the target element, whether it's a directory and its ID
        var target  = jQuery( event.currentTarget ),
            id      = jQuery( 'div.attachment-preview', target ).attr( 'data-id' );

        // Add or remove item from the selection, depending on its current state
        if ( target.hasClass( 'selected' ) ) {
            // Remove
            this.removeFromSelection( target, id );
        } else {
            // If multiple selection isn't supported, clear the selection first
            if ( ! this.multiple ) {
                this.clearSelection();
            }

            // Add
            this.addToSelection( target, id );
        }
        
    },

    /**
     * Called when the search event is fired (the user types into the search field)
     *
     * @param object    event   Event
     */
    search: function( event ) {

        // If we're already loading something, bail
        if ( this.is_loading ) {
            return;
        }

        // Clear any existing timeout
        clearTimeout( this.search_timeout );

        // Check if a search term exists, and is at least 3 characters
        var search = event.target.value;

        // If search is empty, return the entire folder's contents
        if ( search.length == 0 ) {
            this.getItems( false, '' );
            return;
        }

        // If search isn't empty but less than 3 characters, don't do anything
        if ( search.length < 3 ) {
            return;
        }

        // Set a small timeout before we perform the search. If the user keeps typing,
        // this ensures we don't return the wrong results too early.
        var that = this;
        this.search_timeout = setTimeout( function() {
            that.getItems( true, search );  
        }, 1000 );

    },

    /**
    * Gets galleries by sending an AJAX request
    *
    * @param    bool    is_search       Is a search request
    * @param    string  search_terms    Search Terms
    */
    getItems: function( is_search, search_terms ) {

        // If we're already loading something, bail
        if ( this.is_loading ) {
            return;
        }

        // Clear the existing collection
        this.clearSelection();
        this.$el.find( 'ul.attachments' ).empty();
        this.$el.find( 'div.envira-gallery-error' ).remove();

        // Update the loading flag
        this.trigger( 'loading' );

        // Determine whether we're going to retrieve Galleries or Albums.
        var action = '';
        switch ( this.action ) {
            case 'gallery':
                action = 'envira_gallery_editor_get_galleries';
                break;
            case 'album':
                action = 'envira_albums_editor_get_albums';
                break;
        }

        // Perform AJAX request to get Galleries or Albums.
        wp.media.ajax( action, {
            context: this,
            data: {
                nonce:          envira_gallery_select.get_galleries_nonce,
                search:         is_search,
                search_terms:   search_terms,
                prepend_ids:    this.prepend_ids
            },
            success: function( items ) {

                // Define a collection
                var collection = new Backbone.Collection( items );

                // Reset the collection
                this.collection.reset();

                // Add the collection's models (items) to this class' collection
                this.collection.add( collection.models );

                // Render each item in the collection
                this.collection.each( function( model ) {

                    // Init with model
                    var child_view = new EnviraGallerySelectionItemView( {
                        model: model
                    } );

                    // Render view within our main view
                    this.$el.find( 'ul.attachments' ).append( child_view.render().el );

                    // If we're selecting specific item IDs, check now if this item should be selected
                    if ( this.select_ids !== false ) {
                        var select_model = jQuery.inArray( parseInt( model.get( 'id' ) ), this.select_ids );
                        if ( select_model > -1 ) {
                            // Select this item
                            this.addToSelection( jQuery( child_view.render().el ), model.get( 'id' ) );
                        }
                    }

                }, this );

                // Tell wp.media we've finished loading items
                this.trigger( 'loaded' );

            },
            error: function( error_message ) {

                // Tell wp.media we've finished loading items, and send the error message
                // for output
                this.trigger( 'loaded', error_message );

            }
        } );

    },

    /**
    * Updates an Insert Option (displayed in the sidebar) when changed.
    */
    updateInsertOption: function( event ) {

        // Check if the target has a name. If not, it's not a model value we want to store
        if ( event.target.name == '' ) {
            return;
        }

        // Update the model's value, depending on the input type
        if ( event.target.type == 'checkbox' ) {
            value = ( event.target.checked ? 1 : 0 );
        } else {
            value = event.target.value;
        }

        // Update the model
        this.insert_options.set( event.target.name, value );

    },

    /**
    * Render
    * - Binds the collection to the view, so we populate the view's attachments grid
    */
    render: function() {

        // Get HTML
        this.$el.html( this.template( this.insert_options.attributes ) );

        // Render the Sidebar View
        var sidebar = new EnviraGallerySelectionSidebarView( this.sidebar_view );
        this.$el.find( 'div.media-sidebar' ).append( sidebar.render().el );

        // Return
        return this;
        
    },

    /**
    * Renders an error using
    * wp.media.view.EnviraGalleryError
    */
    renderError: function( error ) {

        // Define model
        var model = {};
        model.error = error;

        // Define view
        var view = new wp.media.view.EnviraGalleryError( {
            model: model
        } );

        // Return rendered view
        return view.render().el;

    },

    /**
    * Tells the view we're loading by displaying a spinner
    */
    loading: function() {

        // Set a flag so we know we're loading data
        this.is_loading = true;

        // Show the spinner
        this.$el.find( '.spinner' ).css( 'visibility', 'visible' );

    },

    /**
    * Hides the loading spinner
    */
    loaded: function( response ) {

        // Set a flag so we know we're not loading anything now
        this.is_loading = false;

        // Hide the spinner
        this.$el.find( '.spinner' ).css( 'visibility', 'hidden' );

        // Display the error message, if it's provided
        if ( typeof response !== 'undefined' ) {
            this.$el.find( 'ul.attachments' ).before( this.renderError( response ) );
        }

    },

    /**
    * Adds the given target to the selection
    *
    * @param    object  target  Selected Element
    * @param    string  id      Unique Identifier (i.e. third party API item's UID)
    */
    addToSelection: function( target, id ) {

        // Trigger the loading event
        this.trigger( 'loading' );

        // Iterate through the current collection of models until we find the model
        // that matches the ID we have
        this.collection.each( function( model ) {
            // If this model matches the model the user selected, add it to the selection
            if ( model.get( 'id' ) == id ) {
                this.selection.add( model );
            }
        }, this );

        // Mark the item as selected in the media view
        target.addClass( 'selected details' );

        // If the selection is not empty, enable the Insert button
        if ( this.selection.length > 0 ) {
            this.$el.find( 'button.media-button-insert' ).attr( 'disabled', false );
        }

        // Trigger the loaded event
        this.trigger( 'loaded' );

    },

    /**
    * Removes the given target from the selection
    *
    * @param    object  target  Deselected Element
    * @param    string  id      Unique Identifier (i.e. third party API item's UID)
    */
    removeFromSelection: function( target, id ) {

        // Trigger the loading event
        this.trigger( 'loading' );

        // Iterate through the current collection of selected models until we find the model
        // that has a path matching the given path
        this.selection.each( function( model ) {
            // remove this model from the collection of selected models
            this.selection.remove([{ cid: model.cid }]);
        }, this );

        // Mark the item as deselected in the media view
        target.removeClass( 'selected details' );

        // If the selection is empty, disable the Insert button
        if ( this.selection.length == 0 ) {
            this.$el.find( 'button.media-button-insert' ).attr( 'disabled', 'disabled' );
        }

        // Trigger the loaded event
        this.trigger( 'loaded' );

    },

    /**
    * Clears all selected items
    */
    clearSelection: function() {

        // Iterate through each item, removing the selected state from the UI
        this.selection.each( function( model ) {
            this.$el.find( 'div[data-id="' + model.get( 'id' ) + '"]' ).parent().removeClass( 'selected details' );
        }, this );

        // Disable the Insert button
        this.$el.find( 'button.media-button-insert' ).attr( 'disabled', 'disabled' );

        // Clear the selected models
        this.selection.reset();

    },

    /**
    * Inserts one or more Galleries or Albums into the Editor
    */
    insert: function() {

        // Tell the View we're loading
        this.trigger( 'loading' );

        // Run the onInsert() function from the calling class
        // If no function given, run the default action, which is to insert
        // into the Visual Editor
        if ( typeof this.onInsert === 'undefined' ) {
            // For each selected item, insert a shortcode into the editor
            var items = [];
            this.selection.forEach( function( item ) {

                // Build array of items, and reset shortcode
                items.push( item.attributes );
                var shortcode = '';

                // Prepend Title to Shortcode
                if ( this.insert_options.get( 'title' ) != 0 ) {
                    shortcode = '<' + this.insert_options.get( 'title' ) + '>' + item.get( 'title' ) + '</' + this.insert_options.get( 'title' ) + '>';
                }

                // Shortcode
                shortcode += '[envira-' + this.action + ' id="' + item.id + '"]';

                // Insert into Editor
                wp.media.editor.insert( shortcode );

            }, this );

            // Trigger the enviraGalleryModalData event, comprising of the chosen Galleries.
            jQuery( document ).trigger( { 
                type:   'enviraGalleryModalData', 
                items:  items,                      // array of galleries or albums
                insert_options: this.insert_options,// Backbone Model comprising of Insert Options the user chose on screen
                action: this.action                 // gallery or album
            } );

            var result = true;
        } else {
            var result = this.onInsert();
        }

        // Trigger the loaded event
        this.trigger( 'loaded' );

        // If the result was successful, close the modal window
        if ( result ) {
            EnviraGalleryModalWindow.close();
        }

    }

} );