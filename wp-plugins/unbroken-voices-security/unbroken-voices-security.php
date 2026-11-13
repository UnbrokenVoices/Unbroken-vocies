<?php
/**
 * Plugin Name:       Unbroken Voices Security Hardening
 * Description:       Adds supplemental Content-Security-Policy directives (base-uri, object-src) and removes generator metadata from public pages.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Unbroken Voices
 * License:           GPL-2.0-or-later
 * Text Domain:       unbroken-voices-security
 *
 * @package UnbrokenVoicesSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Send a second Content-Security-Policy header on public (front-end) responses.
 *
 * WordPress.com already sends its own policy (frame-ancestors). Browsers enforce
 * every Content-Security-Policy header they receive, so this header adds
 * restrictions without replacing or weakening the platform policy.
 *
 * - base-uri 'self'   blocks an injected <base> element from re-pointing relative URLs.
 * - object-src 'none' blocks <object>/<embed> plugin content.
 *
 * Note: object-src 'none' also blocks the inline PDF preview of the core File
 * block (the download link still works). To allow same-origin previews:
 *
 *     add_filter( 'uv_security_csp_directives', function ( $d ) {
 *         $d['object-src'] = "'self'";
 *         return $d;
 *     } );
 */
function uv_security_send_csp_header() {
	if ( headers_sent() ) {
		return;
	}

	$directives = apply_filters(
		'uv_security_csp_directives',
		array(
			'base-uri'   => "'self'",
			'object-src' => "'none'",
		)
	);

	if ( ! is_array( $directives ) || empty( $directives ) ) {
		return;
	}

	$parts = array();
	foreach ( $directives as $name => $value ) {
		// Directive names: letters and hyphens only. Values: no control characters or separators.
		if ( ! is_string( $name ) || ! preg_match( '/^[a-z-]+$/', $name ) ) {
			continue;
		}
		if ( ! is_string( $value ) || preg_match( '/[\x00-\x1F\x7F;,]/', $value ) ) {
			continue;
		}
		$parts[] = $name . ' ' . trim( $value );
	}

	if ( $parts ) {
		// false = append, so the platform's existing CSP header is preserved.
		header( 'Content-Security-Policy: ' . implode( '; ', $parts ), false );
	}
}
add_action( 'send_headers', 'uv_security_send_csp_header' );

/*
 * Remove generator metadata so exact product versions are not advertised.
 */
add_filter( 'googlesitekit_generator', '__return_empty_string' ); // Site Kit by Google.
add_filter( 'the_generator', '__return_empty_string' );           // WordPress core (HTML, feeds).
remove_action( 'wp_head', 'wp_generator' );
