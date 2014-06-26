<?php
/**
 * Plugin Name: Sawabona SGMMN Redirect
 * Plugin URI: http://institutosawabona.com/swba_sgmmn_redirect
 * Description: Redirects sponsor URLs such as http://mmnwebsite.com/username to https://office.mmnwebsite.com/sponsor/username; Must use custom permalink setting to "/%year%/%monthnum%/%day%/%postname%/"
 * Version: 1.0
 * Author: Instituto Sawabona
 * Author URI: http://institutosawabona.com
 * License: GPL2
 */

/*  Copyright 2014  INSTITUTO SAWABONA  (email : webmaster@institutosawabona.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Redirects to back office's sponsor page if there is only one path after the hostname,
 * where this path is considered the sponsor's username
 */

namespace Sawabona;

class SponsorRedirect
{
    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'activatePlugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivatePlugin'));
    }

    /**
     * Setup the plugin required settings on wordpress.
     * Called when the plugin is activated.
     */
    function activatePlugin()
    {
        $this->setupPermalink();
        // TODO: $this->setupRedirect();
        // TODO: $this->disableMenus();
    }

    /**
     * Undo setup when deactivating plugin.
     */
    function deactivatePlugin()
    {
        $this->undoPermalink();
        // TODO: $this->enableMenus();
    }

    /*
     * Get sponsor username and redirect to back office
     */
    function redirect()
    {
        // get the sponsor username

        // get back office URL from config

        // redirect to back office
    }

    /*
     * Make sure the permalink structure is set to custom "/%year%/%monthnum%/%day%/%postname%/"
     */
    function setupPermalink()
    {
        // store the old permalink options
        $currentPermalinkStructure = get_option( 'permalink_structure' );

        if ( get_option( 'old_permalink_structure' ) === false ) {
            add_option( 'old_permalink_structure', $currentPermalinkStructure );
        } else {
            update_option( 'old_permalink_structure', $currentPermalinkStructure );
        }

        // update permalink to "Date and Postname" option
        update_option( 'permalink_structure', '/%year%/%monthnum%/%day%/%postname%/' );
    }

    /**
     * Change Permalink Structure to the same value before installing the plugin
     */
    private function undoPermalink()
    {
        $permalink = get_option( 'old_permalink_structure' );
        if ( $permalink === false ) {
            // old setting not found, set to default
            $permalink = "";
        }

        update_option( 'permalink_structure', $permalink );
    }

    /*
     * Disable administration menus that should not be available to customer administrators;
     */
    function disableMenus()
    {
        // Disable File Editor
    }

    /*
     * Enable administration menus that were disabled from customers;
     */
    private function enableMenus()
    {
    }

    /*
     * Add hooks to detect a username and redirect to back office
     */
    function setupRedirect()
    {
        // include hook
    }

    /**
     * @param $message
     * @param $type
     */
    public function displayMessage($message, $type)
    {
        add_settings_error(
            'swba-mmn-redirect-permalink',
            esc_attr('settings_updated'),
            $message,
            $type
        );
    }
}

$swbaSponsorRedirect = new SponsorRedirect();
