<?php
/*
Plugin Name: uTubeVideo Gallery
Plugin URI: http://www.codeclouds.net/
Description: This plugin allows you to create YouTube video galleries to embed in a WordPress site.
Version: 1.9.4
Author: Dustin Scarberry
Author URI: http://www.codeclouds.net/
License: GPL2
*/

/*  2013 Dustin Scarberry dustin@codeclouds.net

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('utvGallery'))
{

	class utvGallery
	{

		private $_utvadmin, $_utvfrontend, $_options, $_dirpath;
		const CURRENT_VERSION = '1.9.4';

		public function __construct()
		{

			//set dirpath
			$this->_dirpath = dirname(__FILE__);

			//load options
			$this->_options = get_option('utubevideo_main_opts');

			//call upgrade check
			$this->upgrade_check();

			//load external files
			$this->load_dependencies();

			//activation hook
			register_activation_hook(__FILE__, array(&$this, 'activate'));

			add_filter('query_vars', array(&$this, 'insert_query_vars'));

			/////////////////////////
			/////////////////////////
			/////////////////////////
			/////////////////////////
			//FORCE UPGRADE DEVELOPER
			//$this->maintenance();
			/////////////////////////
			/////////////////////////
			/////////////////////////
			/////////////////////////

		}

		//activate plugin
		public function activate($network)
		{

			//multisite call
			/*if(function_exists('is_multisite') && is_multisite() && $network){

				global $wpdb;
           		$old_blog =  $wpdb->blogid;

               	//Get all blog ids
               	$blogids =  $wpdb->get_col('SELECT blog_id FROM ' .  $wpdb->blogs);

               	foreach($blogids as $blog_id){

                  	switch_to_blog($blog_id);
       				$this->maintenance();

               	}

               	switch_to_blog($old_blog);

     		}*/

			//regular call
			$this->maintenance();

		}

		//rewrite rules setup function
		public function setup_rewrite_rules()
		{

			//setup rewrite rule for video albums
			add_rewrite_rule('([^/]+)/album/([^/]+)$', 'index.php?pagename=$matches[1]&albumid=$matches[2]', 'top');

			global $wp_rewrite;
			$wp_rewrite->flush_rules();

		}

		//version check for updates
		private function upgrade_check()
		{

			if(!isset($this->_options['version']) || $this->_options['version'] < self::CURRENT_VERSION)
			{

				$this->maintenance();
				$this->_options['version'] = self::CURRENT_VERSION;
				update_option('utubevideo_main_opts', $this->_options);

			}

		}

		//load dependencies for plugin
		private function load_dependencies()
		{

			load_plugin_textdomain('utvg', false, 'utubevideo-gallery/language');

			//load backend or frontend dependencies
			if(is_admin())
			{

				require ($this->_dirpath . '/admin.php');
				$this->_utvadmin = new utvAdmin(self::CURRENT_VERSION);

			}
			else
			{

				require ($this->_dirpath . '/frontend.php');
				$this->_utvfrontend = new utvFrontend(self::CURRENT_VERSION);

			}

		}

		private function maintenance()
		{

			/*//php version check - implement down the road
			$requiredPHPVersion = '5.5';

			if(version_compare(PHP_VERSION, $requiredPHPVersion, '<')){

				deactivate_plugins( basename( __FILE__ ) );
				wp_die('<p><strong>uTubeVideo Gallery</strong> requires PHP version ' . $requiredPHPVersion . ' or greater.</p>', 'Plugin Activation Error');

			}*/

			//set up globals
			global $wpdb;

			//create database tables for plugin
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$tbname[0] = $wpdb->prefix . 'utubevideo_dataset';
			$tbname[1] = $wpdb->prefix . 'utubevideo_album';
			$tbname[2] = $wpdb->prefix . 'utubevideo_video';

			$sql = "CREATE TABLE $tbname[0] (
				DATA_ID int(11) NOT NULL AUTO_INCREMENT,
				DATA_NAME varchar(40) NOT NULL,
				DATA_SORT varchar(4) DEFAULT 'desc' NOT NULL,
				DATA_DISPLAYTYPE varchar(6) DEFAULT 'album' NOT NULL,
				DATA_UPDATEDATE int(11) NOT NULL,
				DATA_ALBCOUNT int(4) DEFAULT '0' NOT NULL,
				DATA_THUMBTYPE varchar(9) DEFAULT 'rectangle' NOT NULL,
				UNIQUE KEY DATA_ID (DATA_ID)
			);
			CREATE TABLE $tbname[1] (
				ALB_ID int(11) NOT NULL AUTO_INCREMENT,
				ALB_NAME varchar(50) NOT NULL,
				ALB_SLUG varchar(50) DEFAULT '--empty--' NOT NULL,
				ALB_THUMB varchar(40) NOT NULL,
				ALB_SORT varchar(4) DEFAULT 'desc' NOT NULL,
				ALB_POS int(11) NOT NULL,
				ALB_PUBLISH int(11) DEFAULT 1 NOT NULL,
				ALB_UPDATEDATE int(11) NOT NULL,
				ALB_VIDCOUNT int(4) DEFAULT '0' NOT NULL,
				DATA_ID int(11) NOT NULL,
				UNIQUE KEY ALB_ID (ALB_ID)
			);
			CREATE TABLE $tbname[2] (
				VID_ID int(11) NOT NULL AUTO_INCREMENT,
				VID_SOURCE varchar(15) DEFAULT 'youtube' NOT NULL,
				VID_NAME varchar(90) NOT NULL,
				VID_URL varchar(40) NOT NULL,
				VID_THUMBTYPE varchar(9) DEFAULT 'rectangle' NOT NULL,
				VID_QUALITY varchar(6) DEFAULT 'large' NOT NULL,
				VID_CHROME tinyint(1) DEFAULT 1 NOT NULL,
				VID_STARTTIME varchar(10) DEFAULT '' NOT NULL,
				VID_ENDTIME varchar(10) DEFAULT '' NOT NULL,
				VID_POS int(11) NOT NULL,
				VID_PUBLISH int(11) DEFAULT 1 NOT NULL,
				VID_UPDATEDATE int(11) NOT NULL,
				ALB_ID int(11) NOT NULL,
				UNIQUE KEY VID_ID (VID_ID)
			);";

			dbDelta($sql);

			//set up main option defaults if needed

			//initalize main if empty
			if(empty($this->_options))
				$this->_options = array();

			//count videos if not done yet
			if(!isset($this->_options['countSet']))
			{

				$galids = $wpdb->get_results('SELECT DATA_ID FROM ' . $wpdb->prefix . 'utubevideo_dataset', ARRAY_A);

				foreach($galids as $value)
				{

					$albs = $wpdb->get_results('SELECT ALB_ID FROM ' . $wpdb->prefix . 'utubevideo_album WHERE DATA_ID = ' . $value['DATA_ID'], ARRAY_A);
					$count = count($albs);

					$wpdb->update($wpdb->prefix . 'utubevideo_dataset',
						array(
							'DATA_ALBCOUNT' => $count
						),
						array('DATA_ID' => $value['DATA_ID'])
					);

				}

				$alids = $wpdb->get_results('SELECT ALB_ID FROM ' . $wpdb->prefix . 'utubevideo_album', ARRAY_A);

				foreach($alids as $value)
				{

					$vids = $wpdb->get_results('SELECT VID_ID FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $value['ALB_ID'], ARRAY_A);
					$count = count($vids);

					$wpdb->update($wpdb->prefix . 'utubevideo_album',
						array(
							'ALB_VIDCOUNT' => $count
						),
						array('ALB_ID' => $value['ALB_ID'])
					);

				}

				$dft['countSet'] = 'ok';

			}

			//fix video sorting if not done yet
			if(!isset($this->_options['sortFix']))
			{

				$albumIds = $wpdb->get_results('SELECT ALB_ID FROM ' . $wpdb->prefix . 'utubevideo_album', ARRAY_A);

				foreach($albumIds as $value)
				{

					$videoIds = $wpdb->get_results('SELECT VID_ID FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $value['ALB_ID'] . ' ORDER BY VID_POS', ARRAY_A);
					$posCounter = 0;

					foreach($videoIds as $video)
					{

						$wpdb->update($wpdb->prefix . 'utubevideo_video',
							array(
								'VID_POS' => $posCounter
							),
							array('VID_ID' => $video['VID_ID'])
						);

						$posCounter++;

					}

				}

				$dft['sortFix'] = 'ok';

			}

			//album sort fix
			if(!isset($this->_options['albumSortFix']))
			{

				$galleryIds = $wpdb->get_results('SELECT DATA_ID FROM ' . $wpdb->prefix . 'utubevideo_dataset', ARRAY_A);

				foreach($galleryIds as $value)
				{

					$albumIds = $wpdb->get_results('SELECT ALB_ID FROM ' . $wpdb->prefix . 'utubevideo_album WHERE DATA_ID = ' . $value['DATA_ID'] . ' ORDER BY ALB_POS', ARRAY_A);
					$posCounter = 0;

					foreach($albumIds as $album)
					{

						$wpdb->update($wpdb->prefix . 'utubevideo_album',
							array(
								'ALB_POS' => $posCounter
							),
							array('ALB_ID' => $album['ALB_ID'])
						);

						$posCounter++;

					}

				}

				$dft['albumSortFix'] = 'ok';

			}

			//set slugs if not set yet
			if(!isset($this->_options['setSlugs']))
			{

				$mark = 1;
				$sluglist = array();

				$data = $wpdb->get_results('SELECT ALB_ID, ALB_NAME FROM ' . $wpdb->prefix . 'utubevideo_album', ARRAY_A);

				foreach($data as $value)
				{

					$slug = strtolower($value['ALB_NAME']);
					$slug = str_replace(' ', '-', $slug);
					$slug = html_entity_decode($slug, ENT_QUOTES, 'UTF-8');
					$slug = preg_replace("/[^a-zA-Z0-9-]+/", "", $slug);

					if(!empty($sluglist))
					{

						$this->checkslug($slug, $sluglist, $mark);

					}

					$sluglist[] = $slug;
					$mark = 1;

					$wpdb->update($wpdb->prefix . 'utubevideo_album',
						array(
							'ALB_SLUG' => $slug
						),
						array('ALB_ID' => $value['ALB_ID'])
					);

				}

				$dft['setSlugs'] = true;

			}

			//set filenames in not set yet
			if(!isset($this->_options['filenameFix']))
			{

				//suppress odd warning messages temporarily
				error_reporting(0);

				//rename thumbnails
				$dir = wp_upload_dir();
				$dir = $dir['basedir'] . '/utubevideo-cache/';
				$data = $wpdb->get_results('SELECT VID_ID, VID_URL FROM ' . $wpdb->prefix . 'utubevideo_video', ARRAY_A);

				foreach($data as $val){

					$old = $dir . $val['VID_URL'] . '.jpg';
					$new = $dir . $val['VID_URL'] . $val['VID_ID'] . '.jpg';

					rename($old, $new);

				}

				//update album thumbnails
				$albumData = $wpdb->get_results('SELECT a.ALB_ID, ALB_THUMB, VID_ID FROM ' . $wpdb->prefix . 'utubevideo_album a LEFT JOIN ' . $wpdb->prefix . 'utubevideo_video v ON (ALB_THUMB=VID_URL) WHERE ALB_THUMB != "missing"', ARRAY_A);

				foreach($albumData as $val){

					$wpdb->update(
						$wpdb->prefix . 'utubevideo_album',
						array(
							'ALB_THUMB' => $val['ALB_THUMB'] . $val['VID_ID'],
						),
						array('ALB_ID' => $val['ALB_ID'])
					);

				}

				$dft['filenameFix'] = true;

			}

			$dft['skipMagnificPopup'] = 'no';
			$dft['skipSlugs'] = 'no';
			$dft['playerWidth'] = 1274;
			$dft['playerHeight'] = 720;
			$dft['playerControlTheme'] = 'dark';
			$dft['playerProgressColor'] = 'red';
			$dft['fancyboxOverlayOpacity'] = '0.85';
			$dft['fancyboxOverlayColor'] = '#000';
			$dft['thumbnailWidth'] = 150;
			$dft['thumbnailPadding'] = 10;
			$dft['thumbnailBorderRadius'] = 3;
			$dft['youtubeApiKey'] = '';
			$dft['version'] = self::CURRENT_VERSION;

			$this->_options = $this->_options + $dft;

			update_option('utubevideo_main_opts', $this->_options);

			//create photo cache directory if needed
			$dir = wp_upload_dir();
			$dir = $dir['basedir'];
			wp_mkdir_p($dir . '/utubevideo-cache');

			//copy 'missing.jpg' into cache directory
			copy(plugins_url('missing.jpg', __FILE__), $dir . '/utubevideo-cache/missing.jpg');

			//add rewrite rules to rewrite engine
			add_action('init', array(&$this, 'setup_rewrite_rules'));

		}

		//recursive function for making sure slugs are unique
		private function checkslug(&$slug, &$sluglist, &$mark)
		{

			if(in_array($slug, $sluglist))
			{

				$slug = $slug . '-' . $mark;
				$mark++;
				$this->checkslug($slug, $sluglist, $mark);

			}
			else
				return;

		}

		//insert custom query vars into array
		public function insert_query_vars($vars)
		{

			array_push($vars, 'albumid');
			return $vars;

		}

	}

	$utvg = new utvGallery();

}
?>
