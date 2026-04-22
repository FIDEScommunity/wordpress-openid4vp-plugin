<?php
/**
 * Plugin Name:       Universal OID4VP
 * Description:       Retrieve verifiable presentations
 * Version:           0.7.0
 * Requires at least: 6.6
 * Requires PHP:      7.3
 * Author:            Credenco
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       openid4vp-exchange
 *
 * @package           create-block
 */

if ( ! defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly.
}

if ( ! defined( 'UNIVERSAL_OPENID4VP_PLUGIN_URL' ) ) {
   define( 'UNIVERSAL_OPENID4VP_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}
if (!defined('UNIVERSAL_OPENID4VP_PLUGIN_DIR')) {
    define('UNIVERSAL_OPENID4VP_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
}

require_once(UNIVERSAL_OPENID4VP_PLUGIN_DIR . 'build/OpenID4VP.php');
require_once(UNIVERSAL_OPENID4VP_PLUGIN_DIR . 'build/openid4vp-session.php');

$openid4vp = new Universal_OpenID4VP();

add_action('admin_menu', [$openid4vp, 'plugin_init']);
add_action('wp_logout', [$openid4vp, 'logout']);

register_activation_hook(__FILE__, [$openid4vp, 'setup']);
register_activation_hook(__FILE__, [$openid4vp, 'upgrade']);

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function universal_openid4vp_create_block_init() {
   register_block_type( __DIR__ . '/build/presentationExchange' );
   register_block_type( __DIR__ . '/build/presentationExchangeOrgWallet' );
   register_block_type( __DIR__ . '/build/presentationAttribute' );
}

function universal_openid4vp_login_form_button() {
    $options = new Universal_OpenID4VP_Admin_Options();
    if ($options->loginUrl !== '') {
        $login_with_wallet_button = sprintf('
            <div>
                <a style="margin:1em auto;" rel="nofollow" class="button" href="%s">Login with Personal Wallet</a>
                <div style="clear:both;"></div>
            </div>',
            esc_url($options->loginUrl)
        );

        echo wp_kses_post($login_with_wallet_button);
    }
}

/**
 * Enqueues our scripts
 */
function universal_openid4vp_enqueue_personal_wallet_scripts() {
    // Enqueue our script, using the jQuery dependency
    wp_enqueue_script( 'pollStatus', UNIVERSAL_OPENID4VP_PLUGIN_URL . '/build/presentationExchange/dummy.js', array( 'jquery' ));
    wp_localize_script(
        'pollStatus',
        'my_ajax_obj',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'universal_openid4vp_ajax' ),
        )
    );
}

function universal_openid4vp_enqueue_org_wallet_scripts() {
    // Enqueue our script, using the jQuery dependency
    wp_enqueue_script( 'submitPresentationRequest', UNIVERSAL_OPENID4VP_PLUGIN_URL . '/build/presentationExchange/dummy.js', array( 'jquery' ));
    wp_localize_script(
        'submitPresentationRequest',
        'my_ajax_obj',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'universal_openid4vp_ajax' ),
        )
    );
}

add_action( 'init', 'universal_openid4vp_create_block_init' );
// Display the Login button at the top of the WP Login form
add_action('login_message', 'universal_openid4vp_login_form_button');
// Add an action to call our script enqueuing function
add_action( 'wp_enqueue_script', 'universal_openid4vp_enqueue_personal_wallet_scripts' );

add_action( 'wp_ajax_nopriv_universal_openid4vp_poll_status_ajax', 'universal_openid4vp_ajax_poll_status' );
add_action( 'wp_ajax_universal_openid4vp_poll_status_ajax', 'universal_openid4vp_ajax_poll_status' );

add_action( 'wp_ajax_nopriv_universal_openid4vp_presentation_exchange_ajax', 'universal_openid4vp_ajax_org_wallet_presentation_exchange');
add_action( 'wp_ajax_universal_openid4vp_presentation_exchange_ajax', 'universal_openid4vp_ajax_org_wallet_presentation_exchange');

/**
 * Gets the number of votes from the database, and sends it
 * back to the client script as JSON.
 */
function universal_openid4vp_ajax_poll_status() {
    check_ajax_referer( 'universal_openid4vp_ajax', 'nonce' );

    $current = isset( $_POST['current'] )
        ? esc_url_raw( wp_unslash( $_POST['current'] ) )
        : '';

    $options = new Universal_OpenID4VP_Admin_Options();

    $presentationStatusUri = universal_openid4vp_session_get( 'presentationStatusUri' );
    $accessToken           = universal_openid4vp_session_get( 'accessToken' );

    $response = wp_remote_get( $presentationStatusUri, array(
        'headers' => array('Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken ),
        'timeout'     => 45,
        'redirection' => 5,
        'blocking'    => true
    ));

    $body = wp_remote_retrieve_body($response);
    //$result = json_decode( $body );
    $successUrl = null;
    $presentationResponse = json_decode( $body, true);

    if ( $presentationResponse['status'] === 'authorization_response_verified'  ) {
        $successUrl = universal_openid4vp_session_get( 'successUrl' );

        $credentialClaims = $presentationResponse['verified_data']['credential_claims'];
        $storedPresentationResponse = universal_openid4vp_session_get( 'presentationResponse', array() );
        if ( ! is_array( $storedPresentationResponse ) ) {
            $storedPresentationResponse = array();
        }
        foreach ($credentialClaims as $credential) {
            $storedPresentationResponse[ $credential['id'] ] = $credential;
        }
        universal_openid4vp_session_set( 'presentationResponse', $storedPresentationResponse );

        if ($options->loginUrl == $current) {
            $jsonAttributeNames = explode(".", $options->usernameAttribute);

            $result = $storedPresentationResponse;
            foreach ($jsonAttributeNames as &$name) {
                $result = $result[$name];
            }
            // $arr is now array(2, 4, 6, 8)
            unset($name);

            if (username_exists($result) == true) {
                $user = get_user_by('login', $result);
                $user_id = $user->ID;
            }

            if (!empty($user_id)) {
                // set current user session
                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                if (is_user_logged_in()) {
                    $successUrl = admin_url();
                }
            }
        }

        universal_openid4vp_session_delete( 'accessToken' );
        universal_openid4vp_session_delete( 'successUrl' );
    }

    // Prepare the data to sent back to Javascript
    $data = array(
        'presentationStatusUri'    =>    $presentationStatusUri,
        'configuredSuccessUrl' => universal_openid4vp_session_get( 'successUrl' ),
        'successUrl' => $successUrl
    );

    // Encode it as JSON and send it back
    echo wp_json_encode( $data );
    die();
}

/**
 * Gets the number of votes from the database, and sends it
 * back to the client script as JSON.
 */
function universal_openid4vp_ajax_org_wallet_presentation_exchange() {
   check_ajax_referer( 'universal_openid4vp_ajax', 'nonce' );

   $attributes = universal_openid4vp_session_get( 'queryAttributes', array() );

   $response = universal_openid4vp_sendVpRequest($attributes);

   if ($response["success"] === false) {
      echo wp_kses_post( $response["error"] );
      return;
   }

   echo wp_json_encode( $response["result"] );

   die();
}

function universal_openid4vp_sendVpRequest($attributes) {
    $options = new Universal_OpenID4VP_Admin_Options();
    $openidEndpoint = $options->openidEndpoint;
    $tokenEndpoint = $options->tokenEndpoint;
    $apiClientId = $options->apiClientId;
    $apiClientSecret = $options->apiClientSecret;
    if (!empty($attributes['openidEndpoint'])) {
        $openidEndpoint = $attributes['openidEndpoint'];
        $tokenEndpoint = $attributes['tokenEndpoint'];
        $apiClientId = $attributes['apiClientId'];
        $apiClientSecret = $attributes['apiClientSecret'];
    }

    $tokenRequest = array(
        'grant_type' => 'client_credentials',
        'client_id' => $apiClientId,
        'client_secret' => $apiClientSecret
    );

    $response = wp_remote_post( $tokenEndpoint, array(
        'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        'timeout'     => 45,
        'redirection' => 5,
        'blocking'    => true,
        'body'        => $tokenRequest
    ));

    if (is_wp_error($response)) {
        $block_content = '<div ' . get_block_wrapper_attributes() . '><p>Error getting client access token</p></div>';
        return ["success" => false, "error" => $block_content];
    }
    $authenticationResult = json_decode( wp_remote_retrieve_body($response) );

    $body = array('query_id' => $attributes['queryId']);
    if ( isset( $_POST['walletUrl'] ) ) {
        $body['request_uri_base'] = esc_url_raw( wp_unslash( $_POST['walletUrl'] ) );
    }
    if (array_key_exists('requestUriMethod', $attributes)) {
        $body['request_uri_method'] = $attributes['requestUriMethod'];
    }
    if (array_key_exists('clientId', $attributes)) {
        $body['client_id'] = $attributes['clientId'];
    }
    if (array_key_exists('responseType', $attributes)) {
        $body['response_type'] = $attributes['responseType'];
    }
    if (array_key_exists('responseMode', $attributes)) {
        $body['response_mode'] = $attributes['responseMode'];
    }
    if (array_key_exists('successUrl', $attributes) && !(isset($attributes['qrCodeEnabled']) && $attributes['qrCodeEnabled'])) {
        $body['direct_post_response_redirect_uri'] = $attributes['successUrl'];
    }
    if (isset($attributes['qrCodeEnabled']) && $attributes['qrCodeEnabled']) {
        $qrCode = (object)[];
        if (array_key_exists('qrSize', $attributes) && !empty($attributes['qrSize'])) {
            $qrCode->size = $attributes['qrSize'];
        }
        if (array_key_exists('qrColorDark', $attributes) && !empty($attributes['qrColorDark'])) {
            $qrCode->color_dark = $attributes['qrColorDark'];
        }
        if (array_key_exists('qrColorLight', $attributes) && !empty($attributes['qrColorLight'])) {
            $qrCode->color_light = $attributes['qrColorLight'];
        }
        if (array_key_exists('qrPadding', $attributes) && !empty($attributes['qrPadding'])) {
            $qrCode->padding = $attributes['qrPadding'];
        }
        $body['qr_code'] = $qrCode;
    }

    $response = wp_remote_post( $openidEndpoint . '/oid4vp/backend/auth/requests', array(
        'headers' => array('Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $authenticationResult->access_token),
        'timeout'     => 45,
        'redirection' => 5,
        'blocking'    => true,
        'body'        => wp_json_encode($body)
    ));

    if (is_wp_error($response)) {
        $block_content = '<div ' . get_block_wrapper_attributes() . '><p>Error fetching data</p></div>';
        return ["success" => false, "error" => $block_content];
    }

    $body = wp_remote_retrieve_body($response);
    $result = json_decode( $body );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        $block_content = '<div ' . get_block_wrapper_attributes() . '><p>JSON decode fout: ' . esc_html( json_last_error_msg() ) . '</p></div>';
        return ["success" => false, "error" => $block_content];
    }

    // Controleer op fout in de API response zelf (bijv. foutcode of foutbericht)
    if ( isset( $result->status ) && isset( $result->detail ) ) {
        $block_content = '<div ' . get_block_wrapper_attributes() . '><p>API fout: ' . esc_html( $result->detail ) . '</p></div>';
        return ["success" => false, "error" => $block_content];
    }

    // store the correlation id in the session store
    universal_openid4vp_session_set( 'correlationId', $result->correlation_id );
    universal_openid4vp_session_set( 'presentationStatusUri', $result->status_uri );
    universal_openid4vp_session_set( 'accessToken', $authenticationResult->access_token );
    if (array_key_exists('successUrl', $attributes)) {
        universal_openid4vp_session_set( 'successUrl', wp_sanitize_redirect( $attributes['successUrl'] ) );
    }

   return ["success" => true, "result" => $result];
}
