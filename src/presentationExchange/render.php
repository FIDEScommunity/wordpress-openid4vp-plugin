<?php
$response = universal_openid4vp_sendVpRequest($attributes);

if ($response["success"] === false) {
  echo wp_kses_post( $response["error"] );
  return;
}

$result = $response["result"];

do_action( 'wp_enqueue_script' );

$allowed_protocols = array( 'http', 'https', 'openid4vp', 'haip', 'mdoc-openid4vp', 'eudi-openid4vp' );
$request_uri = esc_url( $result->request_uri, $allowed_protocols );

echo '<div ' . get_block_wrapper_attributes() . '>';
if ( $attributes['qrCodeEnabled'] ) {
    echo '<img id="openid4vp_qrImage" alt="" src="' . esc_attr( $result->qr_uri ) . '" />or ';
}
echo 'click <a href="' . $request_uri . '">link</a></div>';

