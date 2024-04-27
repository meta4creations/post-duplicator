<?php
namespace Mtphr\PostDuplicator\Hooks;

// Disable WC product review count
add_filter( 'mtphr_post_duplicator_meta__wc_review_count_enabled', '__return_false' );