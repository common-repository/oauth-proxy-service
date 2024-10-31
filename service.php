<?php
/**
 * Created by PhpStorm.
 * User: adrian
 * Date: 2018-03-06
 * Time: 4:39 PM
 */

class iop_infusionsoft_oauth_token_proxy_service {
	static $client_secret = ""; //client secret goes here
	static $client_id = ""; //client ID goes here
	static $redirect_uri = "";

	const GRANT_TYPE_AUTHORIZATION_CODE = "authorization_code";
	const TOKEN_REQUEST_URI = "https://api.infusionsoft.com/token";
	const AUTHORIZATION_URI = "https://signin.infusionsoft.com/app/oauth/authorize";

	public function __construct() {
		/* client will pass the settings page URL the action to perform oauth*/
		$options = get_option( 'inf_oauth_settings' );

		static::$client_id     = $options['client_id'];
		static::$client_secret = $options['client_secret'];
		static::$redirect_uri  = $options['redirect_uri'];
	}

	public static function connect() {
		/* client will pass the settings page URL the action to perform oauth*/
		if ( ! isset( $_GET['redirectUri'] ) || ! isset( $_GET['OauthConnect'] ) ) {
			return;
		}

		$uniqueId = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

		set_transient( $uniqueId, array(
			'redirectUri'     => sanitize_text_field( urldecode( $_GET['redirectUri'] ) ),
			'OauthClientPass' => sanitize_text_field( $_GET['OauthClientPass'] )
		) );

		$authorizeUrl = static::AUTHORIZATION_URI;

		$args = array(
			'client_id'     => static::$client_id,
			'client_secret' => static::$client_secret,
			'redirect_uri'  => static::$redirect_uri,
			'response_type' => 'code',
			'scope'         => 'full'
		);

		wp_redirect( $authorizeUrl . '?' . http_build_query( $args ) );

		die();
	}

	public static function finish() {
		//var_dump( $_SERVER['HTTP_REFERER'] );

		if ( ! isset( $_GET['scope'] ) || ! isset( $_GET['code'] ) || ! preg_match( '/^(https:\/\/accounts\.infusionsoft\.com\/app\/oauth\/authorize).*/', $_SERVER['HTTP_REFERER'] ) ) {
			return;
		}

		$uniqueId = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );

		$options = get_transient( $uniqueId );

		delete_transient( $uniqueId );

		$redirectUri     = $options['redirectUri'];
		$OauthClientPass = $options['OauthClientPass'];

		/**
		 * @var $response WP_Error
		 */

		$parts     = explode( "|", $_GET['scope'] );
		$scope     = array_shift( $parts ); // the only scope at this moment is FULL scope, so we don't really need it
		$appDomain = array_shift( $parts );

		$response = static::request_token( $_GET['code'] );

		if ( is_wp_error( $response ) ) {
			self::fail( $response->get_error_message() );
		} else if ( isset( $response['error'] ) ) {
			self::fail( $response['error_description'] );
		}

		$params = array(
			'access_token'    => $response['access_token'],
			'refresh_token'   => $response['refresh_token'],
			'expires_in'      => $response['expires_in'],
			'appDomain'       => $appDomain,
			'OauthClientPass' => $OauthClientPass,
		);

		//send back to site with URL encoded args.
		if ( strchr( $redirectUri, '?' ) ) {
			wp_redirect( $redirectUri . "&" . http_build_query( $params ) );
		} else {
			wp_redirect( $redirectUri . "?" . http_build_query( $params ) );
		}

		die();
	}

	public static function request_token( $code ) {
		$params = array(
			'client_id'     => static::$client_id,
			'client_secret' => static::$client_secret,
			'code'          => $code,
			'grant_type'    => self::GRANT_TYPE_AUTHORIZATION_CODE,
			'redirect_uri'  => static::$redirect_uri
		);

		$response = wp_remote_post( static::TOKEN_REQUEST_URI, array(
			'sslverify' => true,
			'body'      => $params,
			'timeout'   => '20'
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$decodedResponse = json_decode( $response['body'], true );

		return $decodedResponse;
	}

	public function refresh_token() {
		if ( ! isset( $_POST['OauthRefresh'] ) ) {
			return;
		}

		$params = array(
			'grant_type'    => 'refresh_token',
			'refresh_token' => base64_decode( $_REQUEST['OauthToken'] )
		);

		$authorization = 'Basic ' . base64_encode( static::$client_id . ':' . static::$client_secret );

		$response = wp_remote_post( static::TOKEN_REQUEST_URI, array(
			'sslverify' => true,
			'body'      => $params,
			'timeout'   => '20',
			'headers'   => array(
				'Authorization' => $authorization
			)
		) );

		if ( is_wp_error( $response ) ) {
			echo json_encode( array( 'error' => $response->get_error_message() ) );
			die();
		}

		//just pass the already encoded string back to the client server.
		echo $response['body'];
		die();
	}

	public static function fail( $error ) {
		$email = get_option( 'admin_email' );
		wp_die( "Something went wrong authenticating your application. Please try authenticating again, if the problem persists please email <a href=\"mailto:$email\">$email</a> and explain the issue.</br> Error Message: " . $error );
	}
}

$iop_infusionsoft_oauth_token_proxy_service = new iop_infusionsoft_oauth_token_proxy_service();

add_action( 'plugins_loaded', array( 'iop_infusionsoft_oauth_token_proxy_service', 'connect' ) );
add_action( 'plugins_loaded', array( 'iop_infusionsoft_oauth_token_proxy_service', 'finish' ) );
add_action( 'plugins_loaded', array( 'iop_infusionsoft_oauth_token_proxy_service', 'refresh_token' ) );
