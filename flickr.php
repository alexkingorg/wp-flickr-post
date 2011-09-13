<?php
/*
Plugin Name: alexking.org Flickr Posting
Version: 1.0
Description: Create posts from Flickr images.
*/

@define('AKV3_FLICKR_CAT', '5');
@define('AKV3_FLICKR_TIMEZONE', 'America/Denver');
@define('AKV3_FLICKR_DRYRUN', false);
@define('AKV3_FLICKR_API', 'json');

function akv3_flickr_cron() {
	set_time_limit(0);
	switch (AKV3_FLICKR_API) {
		case 'html':
			$feeds = akv3_flickr_feeds_html();
			$parse = 'akv3_flickr_process_feed_html';
		break;
		case 'json':
			$feeds = akv3_flickr_feeds_json();
			$parse = 'akv3_flickr_process_feed_json';
		break;
	}
	if (is_array($feeds) && count($feeds)) {
		foreach ($feeds as $feed) {
			$photos = $parse($feed);
			akv3_flickr_insert_posts($photos);
		}
	}
	if (AKV3_FLICKR_DRYRUN) {
		die();
	}
}
add_action('social_cron_15', 'akv3_flickr_cron');

function akv3_flickr_guid($guid) {
	return $guid;
}

function akv3_flickr_feeds_json() {
// NOTE: these Flickr APIs update very slowsly and omit various images for reasons unknown - they are not to be trusted. 
	return array(
		'http://api.flickr.com/services/feeds/photos_public.gne?id=25977117@N00&tags=instagramapp&lang=en-us&format=json&nojsoncallback=1',
		'http://api.flickr.com/services/feeds/photos_public.gne?id=25977117@N00&tags=toblog&lang=en-us&format=json&nojsoncallback=1',
	);
}

function akv3_flickr_process_feed_json($url) {
// get JSON
	$response = wp_remote_get($url);
	if (is_wp_error($response)) {
		return;
	}
	$data = json_decode($response['body']);
	$items = $data->items;

	$photos = array();
	if (count($data->items)) {
		$tz = date_default_timezone_get();
		date_default_timezone_set(AKV3_FLICKR_TIMEZONE);
		foreach ($data->items as $photo) {
			$guid = akv3_flickr_guid($photo->link);
			$title = $photo->title;
			if (empty($title)) {
				$title = 'Untitled Photo';
			}
			$photos[$guid] = array(
				'url' => str_replace('_m.jpg', '_b.jpg', $photo->media->m), // get the large size
				'guid' => $guid,
				'title' => $title,
				'description' => $photo->description,
				'date' => strtotime($photo->published), // timestamp
				'tags' => $photo->tags,
			);
		}
// restore timezone
		date_default_timezone_set($tz);
	}
	return $photos;
}

function akv3_flickr_feeds_html() {
	return array(
		'http://www.flickr.com/photos/alexkingorg/tags/instagramapp/',
		'http://www.flickr.com/photos/alexkingorg/tags/toblog/',
	);
}

function akv3_flickr_process_feed_html($url) {
	die('TODO');
}

function akv3_flickr_insert_posts($photos = array()) {
/* format for inbound photo data
$photo = array(
	'url' => ,
	'guid' => ,
	'title' => ,
	'description' => ,
	'date' => , // timestamp
	'tags' => , // flickr tags, stored as meta
);
*/

	if (!count($photos)) {
		return;
	}
	global $wpdb;
// grab item hashes, look for existing items (use GUID)
	$guids = array_keys($photos);
	$existing = $wpdb->get_col("
		SELECT guid
		FROM $wpdb->posts
		WHERE guid IN ('".implode("', '", array_map(array($wpdb, 'escape'), $guids))."')
	");
	if (!AKV3_FLICKR_DRYRUN) {
		foreach ($existing as $guid) {
			unset($photos[$guid]);
		}
	}
	if (!count($photos)) {
		return;
	}

	$tz = date_default_timezone_get();
	date_default_timezone_set(AKV3_FLICKR_TIMEZONE);

// create new posts
	include_once(ABSPATH.'/wp-admin/includes/file.php');
	include_once(ABSPATH.'/wp-admin/includes/image.php');
	include_once(ABSPATH.'/wp-admin/includes/media.php');
	foreach ($photos as $guid => $photo) {
		// hack to not bring in older photos
		if ($photo['date'] < strtotime('2011-08-14 00:00:00')) {

			continue;
		}

		if (AKV3_FLICKR_DRYRUN) {
			echo '<pre>'.print_r($photo, true).'</pre>';
			continue;
		}

// set image as post, as draft
		$post_id = wp_insert_post(array(
			'guid' => $guid,
			'post_status' => 'draft',
			'post_author' => 1,
			'post_category' => array(AKV3_FLICKR_CAT),
			'post_title' => $photo['title'],
			'post_date' => date('Y-m-d H:i:s', $photo['date'])
		));
		set_post_format($post_id, 'image');
		update_post_meta($post_id, '_flickr_tags', $photo['tags']);

// sideload image
		$file_array = array();

		// Download file to temp location
		$tmp = download_url($photo['url']);

		// Set variables for storage
		// fix file filename for query strings
		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $photo['url'], $matches);
		$file_array['name'] = basename($matches[0]);
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			wp_delete_post($post_id);
			continue;
		}

		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, $post_id, $title );
		// If error storing permanently, unlink
		if ( is_wp_error($id) ) {
			@unlink($file_array['tmp_name']);
			wp_delete_post($post_id);
			continue;
		}
		update_post_meta($post_id, '_thumbnail_id', $id);
// publish post
		wp_publish_post($post_id);
	}
// restore timezone
	date_default_timezone_set($tz);
}

// test run
if ($_GET['ak_action'] == 'flickr') {
 	add_action('admin_init', 'akv3_flickr_cron');
}