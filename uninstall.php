<?php

if(defined('WP_UNINSTALL_PLUGIN'))
{

	//multisite call
	/*if(function_exists('is_multisite') && is_multisite()){

		global $wpdb;
		$old_blog =  $wpdb->blogid;

		//Get all blog ids
		$blogids =  $wpdb->get_col('SELECT blog_id FROM ' .  $wpdb->blogs);

		foreach($blogids as $blog_id){

			switch_to_blog($blog_id);
			removePlugin();

		}

		switch_to_blog($old_blog);

	}*/

	//regular call
	removePlugin();

	//remove file directories
	$uploaddir = wp_upload_dir();

	rrmdir($uploaddir['basedir'] . '/utubevideo-cache');

}

function removePlugin() {

	global $wpdb;

	$wpdb->query('DROP TABLE ' . $wpdb->prefix . 'utubevideo_video');
	$wpdb->query('DROP TABLE ' . $wpdb->prefix . 'utubevideo_album');
	$wpdb->query('DROP TABLE ' . $wpdb->prefix . 'utubevideo_dataset');

	delete_option('utubevideo_main_opts');

}

function rrmdir($dir) {

	foreach(glob($dir . '/*') as $file)
	{

		if(is_dir($file))
			rrmdir($file);
		else
			unlink($file);

	}

	rmdir($dir);

}

?>
