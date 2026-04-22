<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
// Retrieve the presentation response
$presentationResponse  = universal_openid4vp_session_get( 'presentationResponse' );
$presentationStatusUri = universal_openid4vp_session_get( 'presentationStatusUri' );
$storedSuccessUrl      = universal_openid4vp_session_get( 'successUrl' );
$storedAccessToken     = universal_openid4vp_session_get( 'accessToken' );

if ( ! empty( $storedSuccessUrl ) && ! empty( $presentationStatusUri ) ) {
    $headers = array('Content-Type' => 'application/json');
    if ( ! empty( $storedAccessToken ) ) {
        $headers['Authorization'] = 'Bearer ' . $storedAccessToken;
    }

    $response = wp_remote_get( $presentationStatusUri, array(
        'headers' => $headers,
        'timeout'     => 45,
        'redirection' => 5,
        'blocking'    => true
    ));

    $body = wp_remote_retrieve_body($response);

    $successUrl = null;

    $statusResponse = json_decode( $body, true);

    if ( $statusResponse['status'] === 'authorization_response_verified'  ) {
        $credentialClaims = $statusResponse['verified_data']['credential_claims'];
        $storedPresentationResponse = universal_openid4vp_session_get( 'presentationResponse', array() );
        if ( ! is_array( $storedPresentationResponse ) ) {
            $storedPresentationResponse = array();
        }
        foreach ($credentialClaims as $credential) {
            $storedPresentationResponse[ $credential['id'] ] = $credential;
        }
        universal_openid4vp_session_set( 'presentationResponse', $storedPresentationResponse );
        $presentationResponse = $storedPresentationResponse;

        universal_openid4vp_session_delete( 'accessToken' );
        universal_openid4vp_session_delete( 'successUrl' );
    }
}

if (!empty($presentationResponse) && isset($attributes['attributeName'])) {
    $jsonAttributeNames = explode(".", $attributes['attributeName']);

    // Check if the credential type exists in the presentation response
    if (isset($attributes['credentialQueryId']) && isset($presentationResponse[$attributes['credentialQueryId']])) {
        $result = $presentationResponse[$attributes['credentialQueryId']];
        foreach ($jsonAttributeNames as &$name) {
            // Check if the attribute exists before accessing it
            if (isset($result[$name])) {
                $result = $result[$name];
            } else {
                // If the attribute doesn't exist, set result to empty and break the loop
                $result = '';
                break;
            }
        }
        // $arr is now array(2, 4, 6, 8)
        unset($name);

        $label = trim($attributes['attributeLabel']);
        if ($label !== '') {
            $block_content = '<p ' . get_block_wrapper_attributes() . '>' . esc_html($label) . ': ' . esc_html($result) . '</p>';
        } else {
            $block_content = '<p ' . get_block_wrapper_attributes() . '>' . esc_html($result) . '</p>';
        }

        echo $block_content;
    }
}
