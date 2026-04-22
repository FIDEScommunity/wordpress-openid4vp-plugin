<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'UNIVERSAL_OPENID4VP_COOKIE_NAME' ) ) {
    define( 'UNIVERSAL_OPENID4VP_COOKIE_NAME', 'universal_openid4vp_session' );
}
if ( ! defined( 'UNIVERSAL_OPENID4VP_TRANSIENT_PREFIX' ) ) {
    define( 'UNIVERSAL_OPENID4VP_TRANSIENT_PREFIX', 'uo4vp_' );
}
if ( ! defined( 'UNIVERSAL_OPENID4VP_SESSION_TTL' ) ) {
    define( 'UNIVERSAL_OPENID4VP_SESSION_TTL', HOUR_IN_SECONDS );
}

function universal_openid4vp_session_bootstrap() {
    if ( ! empty( $_COOKIE[ UNIVERSAL_OPENID4VP_COOKIE_NAME ] ) ) {
        return;
    }

    if ( headers_sent() ) {
        return;
    }

    try {
        $token = bin2hex( random_bytes( 16 ) );
    } catch ( Exception $e ) {
        $token = wp_generate_password( 32, false, false );
    }

    $_COOKIE[ UNIVERSAL_OPENID4VP_COOKIE_NAME ] = $token;

    setcookie(
        UNIVERSAL_OPENID4VP_COOKIE_NAME,
        $token,
        array(
            'expires'  => 0,
            'path'     => defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/',
            'domain'   => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        )
    );
}
add_action( 'init', 'universal_openid4vp_session_bootstrap', 0 );

function universal_openid4vp_session_token() {
    if ( empty( $_COOKIE[ UNIVERSAL_OPENID4VP_COOKIE_NAME ] ) ) {
        return '';
    }
    $token = sanitize_text_field( wp_unslash( $_COOKIE[ UNIVERSAL_OPENID4VP_COOKIE_NAME ] ) );
    if ( ! preg_match( '/^[A-Za-z0-9]{16,64}$/', $token ) ) {
        return '';
    }
    return $token;
}

function universal_openid4vp_session_key() {
    $token = universal_openid4vp_session_token();
    return $token ? UNIVERSAL_OPENID4VP_TRANSIENT_PREFIX . $token : '';
}

function universal_openid4vp_session_get_all() {
    $key = universal_openid4vp_session_key();
    if ( empty( $key ) ) {
        return array();
    }
    $data = get_transient( $key );
    return is_array( $data ) ? $data : array();
}

function universal_openid4vp_session_get( $name, $default_value = null ) {
    $data = universal_openid4vp_session_get_all();
    return isset( $data[ $name ] ) ? $data[ $name ] : $default_value;
}

function universal_openid4vp_session_set( $name, $value ) {
    $key = universal_openid4vp_session_key();
    if ( empty( $key ) ) {
        return false;
    }
    $data = universal_openid4vp_session_get_all();
    $data[ $name ] = $value;
    return set_transient( $key, $data, UNIVERSAL_OPENID4VP_SESSION_TTL );
}

function universal_openid4vp_session_delete( $name ) {
    $key = universal_openid4vp_session_key();
    if ( empty( $key ) ) {
        return false;
    }
    $data = universal_openid4vp_session_get_all();
    unset( $data[ $name ] );
    return set_transient( $key, $data, UNIVERSAL_OPENID4VP_SESSION_TTL );
}
