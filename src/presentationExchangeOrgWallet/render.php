<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

universal_openid4vp_session_set( 'queryAttributes', $attributes );

if ( array_key_exists( 'successUrl', $attributes ) ) {
    universal_openid4vp_session_set( 'successUrl', wp_sanitize_redirect( $attributes['successUrl'] ) );
}

universal_openid4vp_enqueue_org_wallet_scripts();

// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes() returns core-sanitized HTML attributes.
echo '<div ' . get_block_wrapper_attributes() . '>';
?>
<form id="org-wallet-form">
    <label for="org-wallet-url"><?php esc_html_e( 'Wallet URL', 'universal-openid4vp' ); ?></label>
    <input type="url" id="org-wallet-url" name="walletUrl" placeholder="https://wallet.example.com" required />
    <button type="button" id="org-wallet-submit"><?php esc_html_e( 'Connect to wallet', 'universal-openid4vp' ); ?></button>
</form>
<?php
echo '</div>';
