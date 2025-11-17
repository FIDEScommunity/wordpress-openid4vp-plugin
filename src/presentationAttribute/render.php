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
// do a session a start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Retrieve the presentation response
$presentationResponse = isset($_SESSION['presentationResponse']) ? $_SESSION['presentationResponse'] : null;
$presentationStatusUri = isset($_SESSION['presentationStatusUri']) ? $_SESSION['presentationStatusUri'] : null;

if (!empty($_SESSION['successUrl']) && !empty($presentationStatusUri)) {
    $headers = array('Content-Type' => 'application/json');
    if (isset($_SESSION['accessToken'])) {
        $headers['Authorization'] = 'Bearer ' . $_SESSION['accessToken'];
    }

    $response = wp_remote_get( $presentationStatusUri, array(
        'headers' => $headers,
        'timeout'     => 45,
        'redirection' => 5,
        'blocking'    => true
    ));

    $body = wp_remote_retrieve_body($response);

    error_log('Result: '. $body);

    $successUrl = null;

    $statusResponse = json_decode( $body, true);

    if ( $statusResponse['status'] === 'authorization_response_verified'  ) {
        $credentialClaims = $statusResponse['verified_data']['credential_claims'];
        foreach ($credentialClaims as $credential) {
            if (empty($_SESSION['presentationResponse'])) {
                $_SESSION['presentationResponse'] = [];
            }
            $_SESSION['presentationResponse'][$credential['id']] = $credential;
        }
        $presentationResponse = isset($_SESSION['presentationResponse']) ? $_SESSION['presentationResponse'] : null;

        $_SESSION['accessToken'] = null;
        $_SESSION['successUrl'] = null;
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
