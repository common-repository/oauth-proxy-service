<?php
/**
 * Created by PhpStorm.
 * User: adria
 * Date: 2018-03-08
 * Time: 2:35 PM
 *
 * This is a boiler plate template for your integration. There will also be one made available in the I2SDK provided by
 * david bullock and the Memberium team.
 */

class infusionsoft_manager_example
{
    /**
     * @var $app Infusionsoft_App
     */
    static $app;

    const AUTH_URI = 'https://yoursite.com/oauth';

    public static function app_init()
    {
        $hostname = get_option( 'oauth_app_domain', "" );

        static::$app = new Infusionsoft_App( $hostname );

        if( ! static::$app->hasTokens() ){
            //todo add error telling user no tokens are found and hence APP is not connected.
        }
    }

    public static function connect()
    {
        if ( isset( $_POST[ 'activate_OAuth'] ) )
        {
            $pass = wp_generate_password( 8, false );

            set_transient( 'oauth_password', $pass, 60*5 );

            $params = array(
                'redirectUri' => get_site_url(null, '/wp-admin/?client_return_url' ), //todo edit return URL
                'OauthConnect' => true,
                'OauthClientPass' => $pass
            );

            $query = http_build_query( $params );

            wp_redirect( static::AUTH_URI.'?'.$query ); //Send To OAuth Page...
            die();
        }
    }

    public static function refresh_oauth()
    {
        if ( isset( $_POST[ 'refresh_OAuth'] ) )
        {
            static::$app->refreshTokens();
        }
    }

    public static function listen_for_tokens()
    {
        if ( isset( $_REQUEST['OauthClientPass'] ) ){

            $pass = get_transient( 'oauth_password' );

            if ( empty( $pass ) ){
                wp_die( 'Could not verify server authorization for '. site_url() . '. Please Try Again.');
            }

            elseif ( $pass != base64_decode( $_REQUEST['OauthClientPass'] ) ){
                wp_die( 'Incorrect password. Please try again...' );
            }

            static::$app = new Infusionsoft_App(  base64_decode( $_REQUEST['appDomain'] ) );
            static::$app->updateAndSaveTokens(  base64_decode( $_REQUEST['access_token'] ),  base64_decode( $_REQUEST['refresh_token'] ),  base64_decode( $_REQUEST['expires_in'] ) );

            update_option( 'Oauth_App_Domain',  base64_decode( $_REQUEST['appDomain'] ) );

            update_option( 'oauth_last_status', 'Authorized token at ' . date('Y/m/d H:i:s') . ' for app ' . static::$app->getHostname() );

            delete_transient( 'oauth_password' );

            wp_redirect( get_site_url(null, '/wp-admin/?client_return_url' ) ); //todo edit return URL
            die();

        }
    }

    public static function refreshTokens( $token )
    {
        $params = array(
            'OauthToken' => base64_encode( $token ),
            'OauthRefresh' => 'refresh_token'
        );

        $response = wp_remote_post( static::AUTH_URI, array(
            'timeout' => 20,
            'sslverify' => true,
            'body' => $params
        ));

        if ( is_wp_error( $response ) ) {

            //todo give the user an error when authentication fails=

            return array(
                'access_token' => '',
                'refresh_token' => '',
                'expires_in' => ''
            );
        }

        $decodedResponse = json_decode($response, true);

        if ( isset( $decodedResponse['error'] ) ){

            //todo give the user an error when authentication fails

            //return a blank array
            return array(
                'access_token' => '',
                'refresh_token' => '',
                'expires_in' => ''
            );

        } else if ( !isset( $decodedResponse['access_token'] ) ) {

            //todo give the user an error when authentication fails

            return array(
                'access_token' => '',
                'refresh_token' => '',
                'expires_in' => ''
            );
        }

        return $decodedResponse;
    }

    public static function disconnect_app()
    {
        if ( isset( $_POST['disconnect_Oauth'] ) && is_user_logged_in() && is_admin() ){
            if ( static::$app->hasTokens() ){

                static::$app->deleteTokens();

                delete_option('oauth_app_domain');
            }
        }
    }
}

add_action( 'plugins_loaded', array('infusionsoft_manager_example', 'app_init' ) );
add_action( 'plugins_loaded', array('infusionsoft_manager_example', 'connect' ) );
add_action( 'plugins_loaded', array('infusionsoft_manager_example', 'listen_for_tokens' ) );
add_action( 'plugins_loaded', array('infusionsoft_manager_example', 'refresh_oauth' ) );
add_action( 'plugins_loaded', array('infusionsoft_manager_example', 'disconnect_app') );
