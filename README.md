# Unbroken Voices website

Code for [unbroken-voices.org](https://unbroken-voices.org/), a WordPress.com (Atomic) site using the Kadence theme.

## `wp-plugins/unbroken-voices-security`

A small, dependency-free plugin that addresses three findings from the November 12, 2026 security header audit:

| Finding | Severity | What the plugin does |
| --- | --- | --- |
| CSP does not define `base-uri` | Medium | Sends an additional `Content-Security-Policy: base-uri 'self'; object-src 'none'` header on public pages. The WordPress.com platform policy (`frame-ancestors`) is left in place; browsers enforce both. |
| CSP does not define `object-src` | Low | Same header as above. |
| Generator metadata exposed | Low | Removes the Site Kit by Google and WordPress core generator tags. |

### Known side effect

`object-src 'none'` blocks the inline PDF preview of the core File block. The download link continues to work. To allow same-origin previews, change the directive through the `uv_security_csp_directives` filter (see the comment in the plugin file).

### Install

1. Zip the `unbroken-voices-security` directory (the ZIP must contain that single directory).
2. Upload at **Plugins → Add New → Upload Plugin** and activate.
3. Purge the Cloudflare and WordPress.com caches, then confirm with `curl -sI https://unbroken-voices.org/ | grep -i content-security-policy` (two headers are expected) and rerun the audit.

### Rollback

Deactivate the plugin. It stores no settings and makes no database changes.

## Audit findings not addressed in code

- **HSTS `includeSubDomains`**: set at Cloudflare (SSL/TLS → Edge Certificates → HSTS), and only after confirming every subdomain serves valid HTTPS.
- **postMessage handler without origin check**: the handler is not in the homepage content authored in this project; it is injected by a plugin or the hosting platform and has to be identified from the rendered page source before any change is made.
- **Scripts without Subresource Integrity**: Google Tag, AdSense, and WordPress.com statistics scripts are unversioned and change without notice, so a pinned hash would break them. The practical control is to load fewer third-party scripts.
