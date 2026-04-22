=== Universal OID4VP ===
Contributors:      credenco
Tags:              openid4vp, verifiable-credentials, sso, login, identity
Requires at least: 6.6
Tested up to:      6.9
Requires PHP:      7.4
Stable tag:        0.7.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Retrieve verifiable presentations from a personal or business wallet using the OpenID for Verifiable Presentations (OpenID4VP) flow.

== Description ==

Universal OID4VP lets a WordPress site request and consume verifiable credentials from a user's digital wallet over the OpenID for Verifiable Presentations (OpenID4VP) protocol.

The plugin adds:

* A settings page where the site administrator configures the OpenID4VP and token endpoints, API client credentials, and optional wallet-based login behaviour.
* A block that starts a presentation exchange with a personal wallet (QR code or link).
* A block that starts a presentation exchange with a business wallet.
* A block that displays an attribute obtained from a received presentation.
* An optional "Login with Personal Wallet" button on the standard WordPress login form.

== External services ==

This plugin communicates with an OpenID4VP backend that you configure in the plugin settings (for example `https://wallet.acc.credenco.com`) and with a token endpoint (for example a Keycloak `/protocol/openid-connect/token` URL). Both URLs are chosen by the site administrator. No data is sent to those services until a visitor actively uses a wallet block or the wallet login button.

The data exchanged with the configured endpoints includes the API client credentials entered on the settings page, the query identifier configured on the block, and — for responses — the verifiable presentation returned by the wallet.

== Installation ==

1. Upload the plugin zip via the "Plugins" screen in WordPress, or extract it to `/wp-content/plugins/universal-openid4vp-plugin`.
2. Activate the plugin through the "Plugins" screen.
3. Go to Settings -> Universal OID4VP and fill in the OpenID4VP endpoint, token endpoint, API client id and secret.
4. Add one of the Universal OID4VP blocks to a page to start a presentation exchange.

== Changelog ==

= 0.7.0 =
* Maintenance release: hardening for wordpress.org submission (input sanitization, output escaping, nonces on AJAX endpoints, readme).

= 0.1.0 =
* Initial release.
