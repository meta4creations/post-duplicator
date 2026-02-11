<?php
/**
 * Verify the protected meta injection vulnerability is fixed.
 * Run via: wp eval-file verify-vuln-fix.php
 * Or: php -r "require 'wp-load.php'; require 'verify-vuln-fix.php';"
 *
 * Must be run from WordPress root or with correct path.
 */
if ( ! defined( 'ABSPATH' ) ) {
	// Load WordPress
	$wp_load = dirname( __DIR__, 2 ) . '/wp-load.php';
	if ( ! file_exists( $wp_load ) ) {
		$wp_load = dirname( __DIR__, 3 ) . '/wp-load.php';
	}
	if ( ! file_exists( $wp_load ) ) {
		echo "Could not find wp-load.php\n";
		exit( 1 );
	}
	require $wp_load;
}

// Get a post to duplicate
$posts = get_posts( array( 'post_type' => array( 'post', 'page' ), 'posts_per_page' => 1, 'post_status' => 'any' ) );
if ( empty( $posts ) ) {
	echo "No posts found to use as original.\n";
	exit( 1 );
}
$original_id = $posts[0]->ID;
echo "Using original post ID: {$original_id}\n";

// Check original does NOT have the injected keys
$orig_meta = get_post_custom( $original_id );
$orig_has_template = isset( $orig_meta['_wp_page_template'] );
$orig_has_attached = isset( $orig_meta['_wp_attached_file'] );
echo "Original has _wp_page_template: " . ( $orig_has_template ? 'yes' : 'no' ) . "\n";
echo "Original has _wp_attached_file: " . ( $orig_has_attached ? 'yes' : 'no' ) . "\n";

// Simulate the malicious REST request payload
$request_data = array(
	'original_id'      => $original_id,
	'status'            => 'draft',
	'includeCustomMeta' => true,
	'customMetaData'    => array(
		array( 'key' => '_wp_page_template', 'value' => 'INJECTED_VALUE' ),
		array( 'key' => '_wp_attached_file', 'value' => '../../wp-config.php' ),
	),
);

// Create mock WP_REST_Request
$request = new WP_REST_Request( 'POST' );
$request->set_header( 'Content-Type', 'application/json' );
$request->set_body( wp_json_encode( $request_data ) );

// Call the duplicate_post function
$duplicate_post = \Mtphr\PostDuplicator\duplicate_post( $request );

if ( is_wp_error( $duplicate_post ) ) {
	echo "Duplicate failed: " . $duplicate_post->get_error_message() . "\n";
	exit( 1 );
}

$duplicate_id = $duplicate_post->data['duplicate_id'];
echo "Duplicate created: {$duplicate_id}\n";

// Check if injection succeeded (vulnerability) or failed (fixed)
$dup_meta = get_post_custom( $duplicate_id );
$injected_template = isset( $dup_meta['_wp_page_template'] ) && in_array( 'INJECTED_VALUE', $dup_meta['_wp_page_template'], true );
$injected_file = isset( $dup_meta['_wp_attached_file'] ) && in_array( '../../wp-config.php', $dup_meta['_wp_attached_file'], true );

echo "\n--- RESULT ---\n";
echo "_wp_page_template = INJECTED_VALUE on duplicate: " . ( $injected_template ? 'YES (VULNERABLE!)' : 'no' ) . "\n";
echo "_wp_attached_file = ../../wp-config.php on duplicate: " . ( $injected_file ? 'YES (VULNERABLE!)' : 'no' ) . "\n";

if ( $injected_template || $injected_file ) {
	echo "\n*** VULNERABLE: Injection succeeded! ***\n";
	exit( 1 );
} else {
	echo "\n*** SECURE: Injection blocked - vulnerability is fixed ***\n";
	exit( 0 );
}
