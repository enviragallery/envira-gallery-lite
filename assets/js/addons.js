/* ==========================================================
 * addons.js
 * http://enviragallery.com/
 * ==========================================================
 * Copyright 2016 David Bisset.
 *
 * Licensed under the GPL License, Version 2.0 or later (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
;(function($){
    $(function(){

        // Addons Search
        var addon_search_timeout;
        $( 'form#add-on-search input#add-on-searchbox' ).on( 'keyup', function() {

            // Clear timeout
            clearTimeout( addon_search_timeout );

            // Get the search input, heading, results and cancel elements
            var search          = $( this ),
                search_terms    = $( search ).val().toLowerCase(),
                search_heading  = $( search ).data( 'heading' ),
                search_results  = $( search ).data( 'results' ),
                search_cancel   = $( search ).data( 'cancel' );

            // Show the Spinner
            $( 'form#add-on-search .spinner' ).css( 'visibility', 'visible' );

            // If the search terms is less than 3 characters, show all Addons
            if ( search_terms.length < 3 ) {
                $( 'div.envira-addon' ).fadeIn( 'fast', function() {
                    // Hide the Spinner
                    $( 'form#add-on-search .spinner' ).css( 'visibility', 'hidden' );
                } );
                return;
            }

            // Iterate through the Addons, showing or hiding them depending on whether they 
            // match the given search terms.
            $( 'div.envira-addon' ).each( function() {
                if ( $( 'h3.envira-addon-title', $( this ) ).text().toLowerCase().search( search_terms ) >= 0 ) {
                    // This Addon's title does match the search terms
                    // Show
                    $( this ).fadeIn();
                } else {
                    // This Addon's title does not match the search terms
                    // Hide
                    $( this ).fadeOut();
                }
            } );

            // Hide the Spinner
            $( 'form#add-on-search .spinner' ).css( 'visibility', 'hidden' );

        } );

        //Sort Filter for addons
        $('#envira-filter-select').on('change', function () {

            var $select = $(this),
                $value = $select.val(),
                $container = $('#envira-addons-unlicensed'),
                container_data = $container.data('envira-filter'),
                $addon = $('#envira-addons-unlicensed .envira-addon');

            //Make sure the addons are visible.
            $addon.show();

            switch ($value) {

                case 'asc':

                    $addon.sort(function (a, b) {

                        return $(a).data('addon-title').localeCompare($(b).data('addon-title'));

                    }).each(function (_, addon) {

                        $(addon).removeClass('last');

                        $container.append(addon).hide().fadeIn(100);

                    });

                    $("#envira-addons-unlicensed .envira-addon:nth-child(3n)").addClass('last');

                    break;
                case 'desc':

                    $addon.sort(function (a, b) {

                        return $(b).data('addon-title').localeCompare($(a).data('addon-title'));

                    }).each(function (_, addon) {

                        $(addon).removeClass('last');
                        $container.append(addon).hide().fadeIn(100);

                    });

                    $("#envira-addons-unlicensed .envira-addon:nth-child(3n)").addClass('last');

                    break;
                case 'sort-order':

                    $addon.sort(function (a, b) {

                        return $(b).data('sort-order') - $(a).data('sort-order');

                    }).each(function (_, addon) {

                        $(addon).removeClass('last');
                        $container.append(addon).hide().fadeIn(100);

                    });

                    $("#envira-addons-unlicensed .envira-addon:nth-child(3n)").addClass('last');

                    break;
            }

        });

        $('#envira-filter-select').on('change', function () {

            var $select = $(this),
                $value = $select.val(),
                $container = $('#envira-addons-licensed'),
                container_data = $container.data('envira-filter'),
                $addon = $('#envira-addons-licensed .envira-addon');

            //Make sure the addons are visible.
            $addon.show();

            switch ($value) {

                case 'asc':

                    $addon.sort(function (a, b) {

                        return $(a).data('addon-title').localeCompare($(b).data('addon-title'));

                    }).each(function (_, addon) {

                        $(addon).removeClass('last');

                        $container.append(addon).hide().fadeIn(100);

                    });

                    $("#envira-addons-licensed .envira-addon:nth-child(3n)").addClass('last');

                    break;
                case 'desc':

                    $addon.sort(function (a, b) {

                        return $(b).data('addon-title').localeCompare($(a).data('addon-title'));

                    }).each(function (_, addon) {

                        $(addon).removeClass('last');
                        $container.append(addon).hide().fadeIn(100);

                    });

                    $("#envira-addons-licensed .envira-addon:nth-child(3n)").addClass('last');

                    break;
                case 'sort-order':

                    $addon.sort(function (a, b) {

                        return $(b).data('sort-order') - $(a).data('sort-order');

                    }).each(function (_, addon) {

                        $(addon).removeClass('last');
                        $container.append(addon).hide().fadeIn(100);

                    });

                    $("#envira-addons-licensed .envira-addon:nth-child(3n)").addClass('last');

                    break;
            }

        });

        // Re-enable install button if user clicks on it, needs creds but tries to install another addon instead.
        $('#envira-addons').on('click.refreshInstallAddon', '.envira-addon-action-button', function(e) {
            var el      = $(this);
            var buttons = $('#envira-addons').find('.envira-addon-action-button');
            $.each(buttons, function(i, element) {
                if ( el == element )
                    return true;

                enviraAddonRefresh(element);
            });
        });

        // Activate Addon
        $('#envira-addons').on('click.activateAddon', '.envira-activate-addon', function(e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.envira-addon-error').remove();
            $(this).html('<i class="envira-toggle-on"></i> ' + envira_gallery_addons.activating);
            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});
            var button  = $(this);
            var plugin  = $(this).attr('rel');
            var el      = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url:      ajaxurl,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action: 'envira_gallery_activate_addon',
                    nonce:  envira_gallery_addons.activate_nonce,
                    plugin: plugin
                },
                success: function(response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if ( response && true !== response ) {
                        $(el).slideDown('normal', function() {
                            $(this).after('<div class="envira-addon-error"><strong>' + response.error + '</strong></div>');
                            $this.next().hide();
                            $('.envira-addon-error').delay(3000).slideUp();
                        });
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).html('<i class="envira-toggle-on"></i> ' + envira_gallery_addons.deactivate).removeClass('envira-activate-addon').addClass('envira-deactivate-addon');
                    $(message).text(envira_gallery_addons.active);
                    // Trick here to wrap a span around he last word of the status
                    var heading = $(message), word_array, last_word, first_part;

                    word_array = heading.html().split(/\s+/); // split on spaces
                    last_word = word_array.pop();             // pop the last word
                    first_part = word_array.join(' ');        // rejoin the first words together

                    heading.html([first_part, ' <span>', last_word, '</span>'].join(''));
                    // Proceed with CSS changes
                    $(el).removeClass('envira-addon-inactive').addClass('envira-addon-active');
                    $this.next().hide();
                },
                error: function(xhr, textStatus ,e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Deactivate Addon
        $('#envira-addons').on('click.deactivateAddon', '.envira-deactivate-addon', function(e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.envira-addon-error').remove();
            $(this).html('<i class="envira-toggle-on"></i> ' + envira_gallery_addons.deactivating);
            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});
            var button  = $(this);
            var plugin  = $(this).attr('rel');
            var el      = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url:      ajaxurl,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action: 'envira_gallery_deactivate_addon',
                    nonce:  envira_gallery_addons.deactivate_nonce,
                    plugin: plugin
                },
                success: function(response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if ( response && true !== response ) {
                        $(el).slideDown('normal', function() {
                            $(this).after('<div class="envira-addon-error"><strong>' + response.error + '</strong></div>');
                            $this.next().hide();
                            $('.envira-addon-error').delay(3000).slideUp();
                        });
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).html('<i class="envira-toggle-on"></i> ' + envira_gallery_addons.activate).removeClass('envira-deactivate-addon').addClass('envira-activate-addon');
                    $(message).text(envira_gallery_addons.inactive);
                    // Trick here to wrap a span around he last word of the status
                    var heading = $(message), word_array, last_word, first_part;

                    word_array = heading.html().split(/\s+/); // split on spaces
                    last_word = word_array.pop();             // pop the last word
                    first_part = word_array.join(' ');        // rejoin the first words together

                    heading.html([first_part, ' <span>', last_word, '</span>'].join(''));
                    // Proceed with CSS changes
                    $(el).removeClass('envira-addon-active').addClass('envira-addon-inactive');
                    $this.next().hide();
                },
                error: function(xhr, textStatus ,e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Install Addon
        $('#envira-addons').on('click.installAddon', '.envira-install-addon', function(e) {
            e.preventDefault();
            var $this = $(this);

            // Remove any leftover error messages, output an icon and get the plugin basename that needs to be activated.
            $('.envira-addon-error').remove();
            $(this).html('<i class="envira-cloud-download"></i> ' + envira_gallery_addons.installing);
            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});
            var button  = $(this);
            var plugin  = $(this).attr('rel');
            var el      = $(this).parent().parent();
            var message = $(this).parent().parent().find('.addon-status');

            // Process the Ajax to perform the activation.
            var opts = {
                url:      ajaxurl,
                type:     'post',
                async:    true,
                cache:    false,
                dataType: 'json',
                data: {
                    action: 'envira_gallery_install_addon',
                    nonce:  envira_gallery_addons.install_nonce,
                    plugin: plugin
                },
                success: function(response) {
                    // If there is a WP Error instance, output it here and quit the script.
                    if ( response.error ) {
                        $(el).slideDown('normal', function() {
                            $(button).parent().parent().after('<div class="envira-addon-error"><div class="xinterior"><p><strong>' + response.error + '</strong></p></div></div>');
                            $(button).html('<i class="envira-cloud-download"></i> ' + envira_gallery_addons.install);
                            $this.next().hide();
                            $('.envira-addon-error').delay(4000).slideUp();
                        });
                        return;
                    }

                    // If we need more credentials, output the form sent back to us.
                    if ( response.form ) {
                        // Display the form to gather the users credentials.
                        $(el).slideDown('normal', function() {
                            $(this).after('<div class="envira-addon-error">' + response.form + '</div>');
                            $this.next().hide();
                        });

                        // Add a disabled attribute the install button if the creds are needed.
                        $(button).attr('disabled', true);

                        $('#envira-addons').on('click.installCredsAddon', '#upgrade', function(e) {
                            // Prevent the default action, let the user know we are attempting to install again and go with it.
                            e.preventDefault();
                            $this.next().hide();
                            $(this).html('<i class="envira-cloud-download"></i> ' + envira_gallery_addons.installing);
                            $(this).next().css({'display' : 'inline-block', 'margin-top' : '0px'});

                            // Now let's make another Ajax request once the user has submitted their credentials.
                            var hostname  = $(this).parent().parent().find('#hostname').val();
                            var username  = $(this).parent().parent().find('#username').val();
                            var password  = $(this).parent().parent().find('#password').val();
                            var proceed   = $(this);
                            var connect   = $(this).parent().parent().parent().parent();
                            var cred_opts = {
                                url:      ajaxurl,
                                type:     'post',
                                async:    true,
                                cache:    false,
                                dataType: 'json',
                                data: {
                                    action:   'envira_gallery_install_addon',
                                    nonce:    envira_gallery_addons.install_nonce,
                                    plugin:   plugin,
                                    hostname: hostname,
                                    username: username,
                                    password: password
                                },
                                success: function(response) {
                                    // If there is a WP Error instance, output it here and quit the script.
                                    if ( response.error ) {
                                        $(el).slideDown('normal', function() {
                                            $(button).parent().parent().after('<div class="envira-addon-error"><strong>' + response.error + '</strong></div>');
                                            $(button).html('<i class="envira-cloud-download"></i> ' + envira_gallery_addons.install);
                                            $this.next().hide();
                                            $('.envira-addon-error').delay(4000).slideUp();
                                        });
                                        return;
                                    }

                                    if ( response.form ) {
                                        $this.next().hide();
                                        $('.envira-inline-error').remove();
                                        $(proceed).val(envira_gallery_addons.proceed);
                                        $(proceed).after('<span class="envira-inline-error">' + envira_gallery_addons.connect_error + '</span>');
                                        return;
                                    }

                                    // The Ajax request was successful, so let's update the output.
                                    $(connect).remove();
                                    $(button).show();
                                    $(button).text(envira_gallery_addons.activate).removeClass('envira-install-addon').addClass('envira-activate-addon');
                                    $(button).attr('rel', response.plugin);
                                    $(button).removeAttr('disabled');
                                    $(message).text(envira_gallery_addons.inactive);
                                    // Trick here to wrap a span around he last word of the status
                                    var heading = $(message), word_array, last_word, first_part;

                                    word_array = heading.html().split(/\s+/); // split on spaces
                                    last_word = word_array.pop();             // pop the last word
                                    first_part = word_array.join(' ');        // rejoin the first words together

                                    heading.html([first_part, ' <span>', last_word, '</span>'].join(''));
                                    // Proceed with CSS changes
                                    $(el).removeClass('envira-addon-not-installed').addClass('envira-addon-inactive');
                                    $this.next().hide();
                                },
                                error: function(xhr, textStatus ,e) {
                                    $this.next().hide();
                                    return;
                                }
                            }
                            $.ajax(cred_opts);
                        });

                        // No need to move further if we need to enter our creds.
                        return;
                    }

                    // The Ajax request was successful, so let's update the output.
                    $(button).html('<i class="envira-toggle-on"></i> ' + envira_gallery_addons.activate).removeClass('envira-install-addon').addClass('envira-activate-addon');
                    $(button).attr('rel', response.plugin);
                    $(message).text(envira_gallery_addons.inactive);
                    // Trick here to wrap a span around he last word of the status
                    var heading = $(message), word_array, last_word, first_part;

                    word_array = heading.html().split(/\s+/); // split on spaces
                    last_word = word_array.pop();             // pop the last word
                    first_part = word_array.join(' ');        // rejoin the first words together

                    heading.html([first_part, ' <span>', last_word, '</span>'].join(''));
                    // Proceed with CSS changes
                    $(el).removeClass('envira-addon-not-installed').addClass('envira-addon-inactive');
                    $this.next().hide();
                },
                error: function(xhr, textStatus ,e) {
                    $this.next().hide();
                    return;
                }
            }
            $.ajax(opts);
        });

        // Function to clear any disabled buttons and extra text if the user needs to add creds but instead tries to install a different addon.
        function enviraAddonRefresh(element) {
            if ( $(element).attr('disabled') )
                $(element).removeAttr('disabled');

            if ( $(element).parent().parent().hasClass('envira-addon-not-installed') )
                $(element).text(envira_gallery_addons.install);
        }



    });
}(jQuery));