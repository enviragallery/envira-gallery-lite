/**
 * Handles tabbed interfaces within Envira:
 * - Settings Screen
 * - Add/Edit Screen: Native/External
 * - Add/Edit Screen: Configuration Tabs
 */

;( function( $ ) {
    $( function() {

        // Define some general vars
        var envira_tabs_nav         = '.envira-tabs-nav',  // Container of tab navigation items (typically an unordered list)
            envira_tabs_hash        = window.location.hash,
            envira_tabs_current_tab = window.location.hash.replace( '!', '' );

        // If the URL contains a hash beginning with envira-tab, mark that tab as open
        // and display that tab's panel.
        if ( envira_tabs_hash && envira_tabs_hash.indexOf( 'envira-tab-' ) >= 0 ) {
            // Find the tab panel that the tab corresponds to
            var envira_tabs_section = $( envira_tabs_current_tab ).parent(),
                envira_tab_nav      = $( envira_tabs_section ).data( 'navigation' );

            // Remove the active class from everything in this tab navigation and section
            $( envira_tab_nav ).find( '.envira-active' ).removeClass( 'envira-active' );
            $( envira_tabs_section ).find( '.envira-active' ).removeClass( 'envira-active' );

            // Add the active class to the chosen tab and section
            $( envira_tab_nav ).find( 'a[href="' + envira_tabs_current_tab + '"]').addClass( 'envira-active' );
            $( envira_tabs_current_tab ).addClass( 'envira-active' );

            // Update the form action to contain the selected tab as a hash in the URL
            // This means when the user saves their Gallery, they'll see the last selected
            // tab 'open' on reload
            var envira_post_action = $( '#post' ).attr( 'action' );
            if ( envira_post_action ) {
                // Remove any existing hash from the post action
                envira_post_action = envira_post_action.split( '#' )[0];

                // Append the selected tab as a hash to the post action
                $( '#post' ).attr( 'action', envira_post_action + window.location.hash );
            } 
        }

        // Change tabs on click.
        // Tabs should be clickable elements, such as an anchor or label.
        $( envira_tabs_nav ).on( 'click', '.nav-tab, a', function( e ) {

            // Prevent the default action
            e.preventDefault();

            // Destroy all instances of Envira Video iframes
            $( 'div.envira-video-help' ).remove();

            // Get the clicked element and the nav tabs
            var envira_tabs                 = $( this ).closest( envira_tabs_nav ),
                envira_tabs_section         = $( envira_tabs ).data( 'container' ),
                envira_tabs_update_hashbang = $( envira_tabs ).data( 'update-hashbang' ),
                envira_tab                  = ( ( typeof $( this ).attr( 'href' ) !== 'undefined' ) ? $( this ).attr( 'href' ) : $( this ).data( 'tab' ) );

            // Don't do anything if we're clicking the already active tab.
            if ( $( this ).hasClass( 'envira-active' ) ) {
                return;
            }

            // If the tab that was clicked is a label, check its corresponding input element, if it isn't already checked
            if ( typeof $( this ).attr( 'for' ) !== 'undefined' ) {
                if ( ! $( 'input#' + $( this ).attr( 'for' ) ).prop( 'checked' ) ) {
                    $( 'input#' + $( this ).attr( 'for' ) ).prop( 'checked', true ).trigger( 'change' );
                }
            }

            // Remove the active class from everything in this tab navigation and section
            $( envira_tabs ).find( '.envira-active' ).removeClass( 'envira-active' );
            $( envira_tabs_section ).find( '.envira-active' ).removeClass( 'envira-active' );

            // Add the active class to the chosen tab and section
            $( this ).addClass( 'envira-active' );
            $( envira_tabs_section ).find( envira_tab ).addClass( 'envira-active' );

            // Update the window URL to contain the selected tab as a hash in the URL.
            if ( envira_tabs_update_hashbang == '1' ) {
                window.location.hash = envira_tab.split( '#' ).join( '#!' );

                // Update the form action to contain the selected tab as a hash in the URL
                // This means when the user saves their Gallery, they'll see the last selected
                // tab 'open' on reload
                var envira_post_action = $( '#post' ).attr( 'action' );
                if ( envira_post_action ) {
                    // Remove any existing hash from the post action
                    envira_post_action = envira_post_action.split( '#' )[0];

                    // Append the selected tab as a hash to the post action
                    $( '#post' ).attr( 'action', envira_post_action + window.location.hash );
                }  
            }      

        } );
    } );
} ( jQuery ) );