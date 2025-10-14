
# Universal OID4VP — WordPress Plugin

Request and display **verifiable presentations** (OpenID for Verifiable Presentations) inside WordPress using Gutenberg blocks. The plugin renders a QR (or link) to start the presentation flow, polls the wallet’s status, redirects on success, and lets you show attributes from the verified data on your pages.

---

## Requirements

* WordPress **6.6+**, PHP **7.2+** (see plugin header).
* Ability for the server to make outbound HTTP calls (token + OID4VP API).
* Sessions: the plugin starts a PHP session when needed.

---

## Installation

**From source (recommended for dev):**

1. Clone the repo and install deps:

   ```bash
   npm install
   npm run build        # or: npm run start  (watch mode)
   npm run plugin-zip   # optional: build a distributable zip
   ```

   Scripts are provided by `@wordpress/scripts`.
2. Copy the plugin folder (or the built zip) into `wp-content/plugins/`.
3. Activate **Universal OID4VP** in **WP Admin → Plugins**.

---

## Admin settings (global defaults)

**WP Admin → Settings → Universal OID4VP**

Fields (used as defaults for blocks; per-block overrides are supported):

* **OpenID4VP Endpoint** – Base URL of your OID4VP service. (The /oid4vp path section is appended by the plugin)
* **Token Endpoint** – OAuth2 token endpoint used for **client_credentials**.
* **API client id / secret** – Client used to call the OID4VP backend.
* **Login url** *(optional)* – Page URL that shows a **login with wallet** button on the WP login form.
* **Username attribute** *(optional)* – Dot path to the username inside the verified data (used for optional auto-login).
* **Redirect user to original page** – When logging in, return to the page the user came from.

> The settings page and option storage are implemented in `adminSettings/openid4vp-admin-settings.php` and `...-options.php`.

---

## Blocks (Gutenberg)

This plugin registers **three** blocks you can add to any page/post. All block properties are edited in the right-hand **Settings** sidebar when the block is selected.

### 1) OID4VP – Request data from **personal** wallet

**Block name:** `universal-openid4vp-plugin/openid4vp-exchange`
**What it does:** Renders a QR code (and link) that starts a personal-wallet presentation flow, then polls for completion and redirects to your **Success URL**.

**Key properties (sidebar):**

* **Query id** *(required)* – The query identifier your backend expects.
* **Success url** *(required)* – Where to go after a successful verification. The plugin appends `?oid4vp_cid=<correlation_id>`.
* **Advanced (optional):** `OpenID4VP Endpoint`, `Token endpoint`, `API client id/secret`, `Client id`, `Request URI method`, `Response type`, `Response mode`.
* **QR options:** enable/disable + `qrSize`, `qrColorDark`, `qrColorLight`, `qrPadding`.

**How the request URL is built:**
When the block renders, the server obtains an access token (client credentials) and calls:
`<OpenID4VP Endpoint>/oid4vp/backend/auth/requests`
The path segment is **automatically appended** to the base endpoint you configure. If your deployment uses a different prefix, set the base endpoint accordingly.

**Same-device vs cross-device:**

* On **mobile/same-device**, the plugin includes `direct_post_response_redirect_uri` so the **wallet** redirects straight to your Success URL.
* On **desktop/cross-device**, the plugin stores the Success URL temporarily and the browser page **polls** for status; when verified, it redirects to your Success URL (with the `oid4vp_cid` query parameter).

### 2) OID4VP – Request data from **organizational** wallet

**Block name:** `universal-openid4vp-plugin/openid4vp-exchange-org-wallet`
**What it does:** Renders a small form to enter an **organization wallet URL**, then starts the presentation flow for that wallet. On success, you end up at the Success URL (same `oid4vp_cid` behavior).

**Key properties:** same as the personal wallet block, minus the QR styling options. A small client-side script posts the wallet URL and opens the `request_uri`.

### 3) OID4VP – Display data

**Block name:** `universal-openid4vp-plugin/openid4vp-attribute`
**What it does:** Displays a **single attribute** from the verified presentation on your **Success** page. It reads the correlation ID from the `oid4vp_cid` query parameter, fetches the stored data, and prints the value (or an `<img>` if it’s a base64 data-URI).

**Properties:**

* **Credential query id** – The credential’s `id` you want to read from (e.g., `clubcard-v1`).
* **VP attribute label** – A friendly label rendered before the value.
* **VP attribute name** – **Dot-path** to the field inside that credential (e.g., `claims.personData.name`).

> The Display block renders server-side via `render.php` and traverses the dot-path inside the stored credential map `[credential.id] → ...`. If the value looks like a base64 image data-URI, it outputs an `<img>` tag.

---

## End-to-end flow

1. **Editor:** Create a **Start** page and insert the **Personal Wallet** (or **Org Wallet**) block. Set **Query id** and **Success url**. Optionally tweak advanced/QR settings.
2. **Success page:** Insert one or more **Display data** blocks. For each, set `Credential query id` and an attribute path (e.g., `claims.personData.name`).
3. **Runtime:**

    * On render, the server calls your token endpoint (client credentials) and then posts to `<OpenID4VP Endpoint>/oid4vp/backend/auth/requests` to obtain `correlation_id`, `status_uri`, `request_uri`, and (optionally) a QR. These are kept in the PHP session for the active flow.
    * The front-end script polls the plugin’s AJAX action every 2 seconds. When the OID4VP backend reports **`authorization_response_verified`**, the plugin stores the **verified credential claims** in a transient keyed by the correlation ID and redirects to **Success URL** with `?oid4vp_cid=<id>`.
    * The Success page’s Display blocks read the transient by that ID and render the configured attributes.

---

## How it works (under the hood)

* **Block registration:** All three blocks are registered on `init`.
* **Creating the request:** `universal_openid4vp_sendVpRequest($attributes)` obtains a client access token, detects mobile vs desktop, adds QR options, and calls `.../oid4vp/backend/auth/requests`. It stores `correlationId`, `statusUri`, and the token in the PHP session. For cross-device flows it stores the Success URL in a **transient** keyed by the correlation ID.
* **Polling:** `pollStatus.js` hits the `universal_openid4vp_poll_status_ajax` action. The handler calls the saved **status URI** server-side; when verified, it writes **credential_claims** into a transient `oid4vp_presentation_<cid>`, retrieves the Success URL, appends `oid4vp_cid=<cid>`, and returns it to the browser to redirect.
* **Displaying data:** `presentationAttribute/render.php` extracts `oid4vp_cid` from the URL, fetches `oid4vp_presentation_<cid>`, walks the dot-path, and renders either text or `<img>`.

**AJAX hooks exposed:**

* `universal_openid4vp_poll_status_ajax` – Poll presentation status + redirect URL.
* `universal_openid4vp_presentation_exchange_ajax` – Start org-wallet flow and return `request_uri`.

---

## Optional: Wallet-based login

If **Login url** in settings matches the **current page** being polled, the plugin can auto-log a user in: it reads the **Username attribute** from the verified payload, finds that WP user, and sets auth cookies; logged-in users get redirected to the admin dashboard. (This is entirely optional.)

---

## Security & privacy

* **Server-side requests:** Token + OID4VP API calls are made from the server. Secrets are not exposed to the browser.
* **Ephemeral storage:** Verified data and Success URL are kept in **WordPress transients** (default TTL ~10 minutes). Sessions only hold short-lived IDs/tokens for the active flow. Clear-down happens after success.

---

## Troubleshooting

* **No redirect after scan:** Confirm **Success URL** is set and reachable; check that the page includes the polling script (`viewScript`), and that your OID4VP backend returns `authorization_response_verified`. Also verify your server can reach the **status URI**.
* **Nothing displayed on Success page:** Ensure the URL contains `oid4vp_cid=...` and that your **Display data** block uses the correct **Credential query id** and attribute **dot-path**.
* **Different backend path:** The plugin calls `<OpenID4VP Endpoint>/oid4vp/backend/auth/requests`. If your deployment uses a different prefix, adjust the base endpoint accordingly.

---

## Development

* Main loader: `universal-openid4vp-plugin.php` (hooks, block registration).
* Core class: `src/OpenID4VP.php` (includes + defaults).
* Blocks under `build/`:

    * `presentationExchange` (personal wallet) – server render + `pollStatus.js`.
    * `presentationExchangeOrgWallet` (org wallet) – server render + `submitPresentationRequest.js`.
    * `presentationAttribute` (display).

Package scripts: `build`, `start`, `plugin-zip`, `wp-env`.

---

## License

GPL-2.0-or-later. See plugin header.

---

### Notes on terminology

This plugin implements **OID4VP** (presentations). The request is posted to `.../oid4vp/backend/auth/requests` derived from your **OpenID4VP Endpoint**. 
