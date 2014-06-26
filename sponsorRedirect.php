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

define("SWBA_L10N_DOMAIN", "swba-mmn-redirect");
define("SWBA_REDIRECT_OPTIONS_SLUG", "swba-mmn-redirect-options");

class SponsorRedirect
{
    public function __construct()
    {
        $plugin = plugin_basename(__FILE__);

        register_activation_hook(__FILE__, array($this, 'activatePlugin'));
        add_filter('template_redirect', array($this, 'redirect'));

        if (is_admin()) {
            add_action('admin_menu', array($this, 'pluginMenu'));
            add_action('admin_init', array($this, 'registerSettings'));
            add_filter("plugin_action_links_$plugin", array($this, 'pluginSettingsLink'));
        }

        register_deactivation_hook(__FILE__, array($this, 'deactivatePlugin'));
    }

    /**
     * Setup the plugin required settings on wordpress.
     * Called when the plugin is activated.
     */
    function activatePlugin()
    {
        $this->setupPermalink();
        $this->disableMenus();
    }

    /**
     * Undo setup when deactivating plugin.
     */
    function deactivatePlugin()
    {
        $this->undoPermalink();
        $this->enableMenus();
    }

    /**
     * Setup the administration menu to access Options Page for plugin configuration
     */
    function pluginMenu()
    {
        add_options_page('Sawabona SGMMN Redirect Options', 'Sawabona SGMMN',
            'manage_options', SWBA_REDIRECT_OPTIONS_SLUG, array($this, 'optionsPage'));
    }

    /**
     * Whitelist which options can be saved in this page
     */
    function registerSettings()
    {
        register_setting( 'swba_options', 'swba_options', array($this, 'validateOptions') );

        add_settings_section('plugin_main', __('Main Settings', SWBA_L10N_DOMAIN),
            array($this, 'sectionText'), 'swba_options');

        add_settings_field('swba_redirect_options', __('BackOffice URL', SWBA_L10N_DOMAIN),
            array($this, 'sectionText'), 'swba_options', 'plugin_main');
    }

    // Add settings link on plugin page
    function pluginSettingsLink($links)
    {
        $settings_link = '<a href="options-general.php?page=' . SWBA_REDIRECT_OPTIONS_SLUG . '.php">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Text for the Options Section
     */
    function sectionText()
    {
        echo '<p>Configure Sponsor Redirection here.</p>';
    }

    /**
     * Display the Options Page for plugin configuration
     */
    function optionsPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', SWBA_L10N_DOMAIN));
        }

        ?>
        <div class="wrap">
        <h2>Sawabona SGMMN Redirect Options</h2>
        <form method="post" action="options.php">

        <?php
            settings_fields( SWBA_OPTIONS_GROUP );
            do_settings_sections( 'swba_options' );
            submit_button(__('Save Options', SWBA_L10N_DOMAIN));
        ?>
        </form>
        </div>
<?php
    }

    /*
     * Get sponsor username and redirect to back office
     */
    function redirect()
    {
        global $wp_query;

        if (is_404()) {
            // get back office URL from config (such as https://office.dev.institutosawabona.com)
            $backOfficeUrl = get_option('swba_backoffice_url');
            if ($backOfficeUrl === false) {
                // ops! Plugin was not configured - will not work...
                wp_die(__('Configuration Error: BackOffice URL not found. Please setup Sawabona Sponsor Redirect plugin.', SWBA_L10N_DOMAIN));
            }

            // get the sponsor username
            $url = parse_url($_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
            $username = $url[1];
            $sponsorUrl = $backOfficeUrl . '/#/sponsor/' . $username;

            // redirect to back office
            status_header(301); // moved permanently
            $wp_query->is_404 = false;
            wp_redirect($sponsorUrl, 301);
        }
    }

    /*
     * Make sure the permalink structure is set to custom "/%year%/%monthnum%/%day%/%postname%/"
     */
    function setupPermalink()
    {
        // store the old permalink options
        $currentPermalinkStructure = get_option('permalink_structure');

        if (get_option('swba_old_permalink_structure') === false) {
            add_option('swba_old_permalink_structure', $currentPermalinkStructure);
        } else {
            update_option('swba_old_permalink_structure', $currentPermalinkStructure);
        }

        // update permalink to "Date and Postname" option
        update_option('permalink_structure', '/%year%/%monthnum%/%day%/%postname%/');
    }

    /**
     * Change Permalink Structure to the same value before installing the plugin
     */
    private function undoPermalink()
    {
        $permalink = get_option('swba_old_permalink_structure');
        if ($permalink === false) {
            // old setting not found, set to default
            $permalink = "";
        }

        update_option('permalink_structure', $permalink);
    }

    /*
     * Disable administration menus that should not be available to customer administrators;
     */
    function disableMenus()
    {
        //TODO: Disable File Editor
    }

    /*
     * Enable administration menus that were disabled from customers;
     */
    private function enableMenus()
    {
        //TODO: $this->enableMenus();
    }

    /*
     * Add hooks to detect a username and redirect to back office
     */
    function setupRedirect()
    {
        //TODO: include hook
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
