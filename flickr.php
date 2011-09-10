<?php
/*
Plugin Name: alexking.org Flickr Posting
Version: 1.0
Description: Create posts from Flickr images.
*/

@define('AKV3_FLICKR_CAT', '5');
@define('AKV3_FLICKR_TIMEZONE', 'America/Denver');
@define('AKV3_FLICKR_DRYRUN', false);

function akv3_flickr_feeds() {
// NOTE: these Flickr APIs update very slowsly and omit various images for reasons unknown - they are not to be trusted. 
	return array(
		'http://api.flickr.com/services/feeds/photos_public.gne?id=25977117@N00&tags=instagramapp&lang=en-us&format=json&nojsoncallback=1',
		'http://api.flickr.com/services/feeds/photos_public.gne?id=25977117@N00&tags=toblog&lang=en-us&format=json&nojsoncallback=1',
	);
}

function akv3_flickr_cron() {
	$feeds = akv3_flickr_feeds();
	if (is_array($feeds) && count($feeds)) {
		foreach ($feeds as $feed) {
			akv3_flickr_process_feed($feed);
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

function akv3_flickr_process_feed($url) {
	set_time_limit(0);
	$tz = date_default_timezone_get();
	date_default_timezone_set(AKV3_FLICKR_TIMEZONE);
	global $wpdb;

// get JSON
	$response = wp_remote_get($url);
	if (is_wp_error($response)) {
		return;
	}
	$data = json_decode($response['body']);
	$items = $data->items;

	$photos = array();
	if (count($data->items)) {
		foreach ($data->items as $item) {
			$photos[akv3_flickr_guid($item->link)] = $item;
		}
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
	}
	if (!count($photos)) {
		return;
	}

// create new posts
	include_once(ABSPATH.'/wp-admin/includes/file.php');
	include_once(ABSPATH.'/wp-admin/includes/image.php');
	include_once(ABSPATH.'/wp-admin/includes/media.php');
	foreach ($photos as $guid => $photo) {
		$date = strtotime($photo->published);
		// hack to not bring in older photos
		if ($date < strtotime('2011-08-14 00:00:00')) {
			continue;
		}
		// get the large size
		$source = str_replace('_m.jpg', '_b.jpg', $photo->media->m);
		$title = $photo->title;
		if (empty($title)) {
			$title = 'Untitled Photo';
		}

		if (AKV3_FLICKR_DRYRUN) {
			echo '<pre>';
			print_r(array(
				'url' => $source,
				'guid' => $guid,
				'post_status' => 'publish',
				'post_author' => 1,
				'post_category' => array(AKV3_FLICKR_CAT),
				'post_title' => $title,
				'post_date' => date('Y-m-d H:i:s', $date),
				'cats' => $photo->tags,
			));
			echo '</pre>';
			continue;
		}

// set image as post, as draft
		$post_id = wp_insert_post(array(
			'guid' => $guid,
			'post_status' => 'draft',
			'post_author' => 1,
			'post_category' => array(AKV3_FLICKR_CAT),
			'post_title' => $title,
			'post_date' => date('Y-m-d H:i:s', $date)
		));
		set_post_format($post_id, 'image');
		update_post_meta($post_id, '_flickr_tags', $photo->tags);

// sideload image
		$file_array = array();

		// Download file to temp location
		$tmp = download_url($source);

		// Set variables for storage
		// fix file filename for query strings
		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $source, $matches);
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
		wp_update_post(array(
			'ID' => $post_id,
			'post_status' => 'publish'
		));
	}
// restore timezone
	date_default_timezone_set($tz);
}

// test run
if ($_GET['ak_action'] == 'flickr') {
 	add_action('admin_init', 'akv3_flickr_cron');
}