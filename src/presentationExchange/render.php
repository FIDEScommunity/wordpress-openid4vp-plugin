<?php
$response = universal_openid4vp_sendVpRequest($attributes);

if ($response["success"] === false) {
  echo wp_kses_post( $response["error"] );
  return;
}

$result = $response["result"];

do_action( 'wp_enqueue_script' );

$qr_content = $attributes['qrCodeEnabled']
    ? '<img id="openid4vp_qrImage" alt="" src="data:' . esc_attr( $result->qr_uri ) . '" />or '
    : '';
$block_content = '<div ' . get_block_wrapper_attributes() . '>' . $qr_content . 'click <a href="' . esc_url( $result->request_uri ) . '">link</a></div>';

echo wp_kses_post( $block_content );

