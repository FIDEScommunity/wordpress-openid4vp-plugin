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


// Get correlation_id from URL parameter
$correlationId = isset($_GET['oid4vp_cid']) ? sanitize_text_field($_GET['oid4vp_cid']) : null;


// Retrieve presentation data from transient
$presentationResponse = null;
if ($correlationId) {
    $presentationResponse = get_transient('oid4vp_presentation_' . $correlationId);
} else {
    error_log('OID4VP render.php: No correlation_id in URL, cannot retrieve data');
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

        // Check if result is a base64 image data URI
        if (is_string($result) && preg_match('/^data:image\/(jpeg|jpg|png|gif|webp);base64,/', $result)) {
            $block_content = '<div ' . get_block_wrapper_attributes() . '><img src="' . esc_attr($result) . '" alt="' . esc_attr($attributes['attributeLabel']) . '" /></div>';
        } else {
            $block_content = '<p ' . get_block_wrapper_attributes() . '>' . $attributes['attributeLabel'] . ': ' . $result . '</p>';
        }

        echo $block_content;
    } else {
        error_log('OID4VP render.php: credentialQueryId=' . ($attributes['credentialQueryId'] ?? 'NOT SET') . ' not found in presentation data');
    }
}
