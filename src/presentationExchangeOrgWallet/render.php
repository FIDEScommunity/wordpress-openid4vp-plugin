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
$options = new Universal_OpenID4VP_Admin_Options();
$openidEndpoint = $options->openidEndpoint;
$authenticationHeaderName = $options->authenticationHeaderName;
$authenticationToken = $options->authenticationToken;
if (!empty($attributes['openidEndpoint'])) {
    $openidEndpoint = $attributes['openidEndpoint'];
    $authenticationHeaderName = $attributes['authenticationHeaderName'];
    $authenticationToken = $attributes['authenticationToken'];
}

universal_openid4vp_session_set( 'openidEndpoint', $openidEndpoint );
universal_openid4vp_session_set( 'authenticationHeaderName', $authenticationHeaderName );
universal_openid4vp_session_set( 'authenticationToken', $authenticationToken );
universal_openid4vp_session_set( 'queryAttributes', $attributes );

if (array_key_exists('successUrl', $attributes)) {
    universal_openid4vp_session_set( 'successUrl', wp_sanitize_redirect( $attributes['successUrl'] ) );
}

do_action( 'submitPresentationRequest' );


// Add JavaScript to handle the form submission
universal_openid4vp_enqueue_org_wallet_scripts('jquery');

$block_content = '<div ' . get_block_wrapper_attributes() . '>
    <form id="org-wallet-form">
        <input type="text" id="org-wallet-url" name="walletUrl" placeholder="Enter wallet URL" />
        <button type="button" id="org-wallet-submit">Connect to wallet</button>
    </form>
</div>';

echo $block_content;
