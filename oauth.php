<?php
/*
Plugin Name: Oauth Proxy Service
Plugin URI: https://oauth.formlift.net
Description: Provides a proxy service for WordPress based applications to connect to Infusionsoft.
Version: 1.0.1
Author: Adrian Tobey
Author URI: https://formlift.net

Copyright (c) Training Business Pros 2016
25 Lesmill Road, Toronto, Ontario, July 2016
License: GPLv2

For Support Please send emails to info@formlift.net or visit https://formlift.net/contact-us.
*/

include ( plugin_dir_path( __FILE__ ) . 'service.php' );

class iop_Infusionsoft_Oauth_Proxy_Settings_Page
{
    static $admin_page;
    static $client_secret;
    static $client_id;

    public static function add_page()
    {
        static::$admin_page = add_submenu_page(
            'options-general.php',
            'Infusionsoft Oauth Proxy Settings',
            'Oauth Proxy',
            'manage_options',
            'infusionsoft_oauth_settings',
            array('iop_Infusionsoft_Oauth_Proxy_Settings_Page', 'create_page')
        );
    }

    public static function create_page()
    {
        $defaults = array(
            'client_secret' => "",
            'client_id' => "",
            'redirect_uri' => ""
        );

        $oauth_settings = get_option( 'inf_oauth_settings',  $defaults );
        $client_id = $oauth_settings[ 'client_id' ];
        $client_secret = $oauth_settings[ 'client_secret' ];
        $redirect_uri = $oauth_settings[ 'redirect_uri' ];

        ?>
            <form action="" method="post">
                <h1>Infusionsoft Oauth Proxy Settings</h1>
                <?php wp_nonce_field( 'save_oauth_settings', 'oauth_nonce_field' ); ?>
                <table>
                    <tr>
                        <td>
                            <label for="client_secret">Client Secret key</label>
                        </td>
                        <td>
                            <input id="client_secret" type="text" name="inf_oauth_settings[client_secret]" value="<?php echo esc_textarea( $client_secret ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="client_id">Client ID</label>
                        </td>
                        <td>
                            <input id="client_id" type="text" name="inf_oauth_settings[client_id]" value="<?php echo esc_textarea( $client_id ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="redirect_uri">Redirect URI</label>
                        </td>
                        <td>
                            <input id="redirect_uri" type="text" name="inf_oauth_settings[redirect_uri]" value="<?php echo esc_url( $redirect_uri ); ?>">
                            <div>This should be the same Callback URL as set in your Infusionsoft Developer App Settings</div>
                        </td>
                    </tr>
                </table>
                <div>
                    <?php submit_button( "save changes" ) ?>
                </div>
            </form>
            <div>
                <h2>Credits:</h2>
                <p>
                    This plugin is brought to you by Adrian Tobey, developer of FormLift and the rest of the Training Business Pros team. Give them a thumbs up and a good review if you found this really helpful.
                </p>
            </div>
        <?php
    }

    public static function save_settings()
    {
        if ( !isset( $_POST[ 'inf_oauth_settings' ] ) ){
            return;
        }

        if (
            ( ! isset( $_POST['oauth_nonce_field'] ) || ! wp_verify_nonce( $_POST['oauth_nonce_field'], 'save_oauth_settings' ) )
        ) {

            wp_die( 'Sorry, your nonce did not verify.' );

        } else {

            $_POST[ 'inf_oauth_settings' ][ 'client_secret' ] = sanitize_text_field( $_POST[ 'inf_oauth_settings' ][ 'client_secret' ] );
            $_POST[ 'inf_oauth_settings' ][ 'client_id' ] = sanitize_text_field( $_POST[ 'inf_oauth_settings' ][ 'client_id' ] );
            $_POST[ 'inf_oauth_settings' ][ 'redirect_uri' ] = esc_url( sanitize_text_field( $_POST[ 'inf_oauth_settings' ][ 'redirect_uri' ] ) );

            update_option( 'inf_oauth_settings', $_POST[ 'inf_oauth_settings' ] );

        }
    }
}

add_action( 'admin_menu' , array('iop_Infusionsoft_Oauth_Proxy_Settings_Page' , 'add_page') );
add_action( 'plugins_loaded', array('iop_Infusionsoft_Oauth_Proxy_Settings_Page' , 'save_settings') );