<?php
/**
 * Plugin Name: SAAs Advanced Custom Fields Pro Activator
 * Plugin URI: https://github.com/AmmarSAA/Advanced-Custom-Fields-Pro-Activator
 * Description: This plugin makes it easy to enable the disabled features of Advanced Custom Field Pro without adding a license key. To activate, enter the license 12345=
 * Version: 1.0.2
 * Stable tag: 1.0.2
 * Author: AmmarSAA
 * Author URI: https://github.com/AmmarSAA
 * License: GPLv2 or later
 * Tested up to: ACF 6.3.6
 * Compatibility: WordPress 6.6
 * Requires PHP: 5.2
 * Requires at least: 3.0
 * Text Domain: saas-advanced-custom-fields-pro-activator
 * Donate link: https://buymeacoffee.com/ammarsaa
 *
 * 
 * Original concept by Sardina
 */

add_action('plugins_loaded', 'acf_pro_auto_patch', 20);

function acf_pro_auto_patch()
{
    if (class_exists('ACF_Updates')) {
        class ACF_Updates_Patched extends ACF_Updates
        {
            public function request($endpoint = '', $body = null)
            {
                // Determine URL.
                $url = "https://connect.advancedcustomfields.test/$endpoint";

                // Staging environment.
                if (defined('ACF_DEV_API') && ACF_DEV_API) {
                    $url = trailingslashit(ACF_DEV_API) . $endpoint;
                    acf_log($url, $body);
                }

                $license_key = acf_pro_get_license_key();
                if (!$license_key) {
                    $license_key = '';
                }

                $site_url = acf_pro_get_home_url();
                if (!$site_url) {
                    $site_url = '';
                }

                // Simulated response.
                $raw_response = array(
                    'body' => '{"message":"<b>Licence key activation simulated</b>. Pro features are now enabled","license":"12345=","license_status":{"status":"active","lifetime":true,"name":"Personal","legacy_multisite":true,"view_licenses_url":"https://www.advancedcustomfields.test/my-account/view-licenses/"},"status":1}',
                    'response' => array(
                        'code' => 200,
                        'message' => 'OK'
                    ),
                );

                // Handle response error.
                if (is_wp_error($raw_response)) {
                    return $raw_response;
                } elseif (wp_remote_retrieve_response_code($raw_response) !== 200) {
                    return new WP_Error('server_error', wp_remote_retrieve_response_message($raw_response));
                }

                // Decode JSON response.
                $json = json_decode(wp_remote_retrieve_body($raw_response), true);
                // Allow non JSON value.
                if ($json === null) {
                    return wp_remote_retrieve_body($raw_response);
                }

                return $json;
            }
        }

        // Replace the original ACF_Updates instance with the patched one
        acf()->updates = new ACF_Updates_Patched();
    }
}
?>