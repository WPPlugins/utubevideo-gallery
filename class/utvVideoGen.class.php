<?php

class utvVideoGen
{

	private $_validAlbum = false;
	private $_aid, $_dir, $_atts, $_options, $_gallery, $_content = '';

	public function __construct($atts, &$options, $type = null, $albumId = null)
	{

		global $wpdb;

		//set atts array
		$this->_atts = shortcode_atts(array(

			'id' => null,
			'align' => 'left', //[left, right, center]
			'panelvideocount' => 14,
			'theme' => 'light', //[light, dark, transparent]
			'icon' => 'red', //[default, red, blue]
			'controls' => 'false' //[true, false]

		), $atts, 'utubevideo');

		$this->_gallery = $wpdb->get_results('SELECT DATA_SORT, DATA_DISPLAYTYPE FROM ' . $wpdb->prefix . 'utubevideo_dataset WHERE DATA_ID = "' . $this->_atts['id'] . '"', ARRAY_A)[0];

		//set thumbnail cache folder location
		$this->_dir = wp_upload_dir()['baseurl'];
		$this->_options = $options;
		$albumId = sanitize_text_field($albumId);

		//check for valid album id
		if($type == 'permalink' && $albumId != null)
		{

			$meta = $wpdb->get_results('SELECT ALB_ID, DATA_ID FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_SLUG = "' . $albumId . '"', ARRAY_A)[0];

			if($meta)
			{

				$this->_aid = $meta['ALB_ID'];

				if($meta['DATA_ID'] == $this->_atts['id'] && $this->_gallery['DATA_DISPLAYTYPE'] == 'album')
					$this->_validAlbum = true;

			}

		}
		elseif($type == 'query' && $albumId != null)
		{

			$args = explode('-', $albumId);

			//if valid aid token
			if(count($args) == 2)
			{

				$this->_aid = $args[0];
				$check = $args[1];

				if($check == $this->_atts['id'] && $this->_gallery['DATA_DISPLAYTYPE'] == 'album')
					$this->_validAlbum = true;

			}

		}

	}

	public function printGallery()
	{

		global $wpdb;
		$this->printGalleryOpeningTags();

		if($this->_validAlbum)
		{

			//get name of video album
			$meta = $wpdb->get_results('SELECT ALB_NAME, ALB_SORT, ALB_PUBLISH FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_ID = ' . $this->_aid, ARRAY_A)[0];

			//get videos in album
			if($meta != null && $meta['ALB_PUBLISH'] == 1)
				$data = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $this->_aid . ' && VID_PUBLISH = 1 ORDER BY VID_POS ' . $meta['ALB_SORT'], ARRAY_A);

			global $post;

			//if there are videos in the video album
			if(!empty($data))
			{

				//create html for breadcrumbs
				$this->_content .= '<div class="utv-breadcrumb"><a href="' . get_permalink($post->ID) . '">' . __('Albums', 'utvg') . '</a><span class="utv-albumcrumb"> | ' . stripslashes($meta['ALB_NAME']) . '</span></div>';

				$this->printGalleryOpeningContainer();

				//create html for each video
				foreach($data as $val)
					$this->_content .= $this->printVideo($val);

				$this->printGalleryClosingContainer();

			}
			//if the video album is empty
			else
			{

				$this->_content .= '<div class="utv-breadcrumb"><a href="' . get_permalink($post->ID) . '">' . __('Go Back', 'utvg') . '</a></div>';

				$this->_content .= '<p>' . __('Sorry... there appear to be no videos for this album yet.', 'utvg') . '</p>';

			}

		}
		else
		{

			//get video albums in the gallery
			$data = $wpdb->get_results('SELECT ' . $wpdb->prefix . 'utubevideo_album.ALB_ID, ALB_SLUG, ALB_NAME, ALB_THUMB, VID_SOURCE, VID_THUMBTYPE FROM ' . $wpdb->prefix . 'utubevideo_album LEFT JOIN ' . $wpdb->prefix . 'utubevideo_video ON ALB_THUMB = CONCAT(VID_URL, VID_ID) WHERE DATA_ID = ' . $this->_atts['id'] . ' && ALB_PUBLISH = 1 ORDER BY ' . $wpdb->prefix . 'utubevideo_album.ALB_POS ' . $this->_gallery['DATA_SORT'], ARRAY_A);

			//if there are video albums in the gallery
			if(!empty($data))
			{

				//if skipalbums in set to true
				if($this->_gallery['DATA_DISPLAYTYPE'] == 'video')
				{

					$this->printGalleryOpeningContainer();

					//build array of album ids
					foreach($data as $idval)
						$alids[] = $idval['ALB_ID'];

					//implode ids to string//
					$alids = implode(', ', $alids);
					//get video info for each all albums in gallery//
					$vids = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID IN ('  . $alids . ') && VID_PUBLISH = 1 ORDER BY VID_POS', ARRAY_A);

					//create html for all videos in gallery
					foreach($vids as $val)
						$this->_content .= $this->printVideo($val);

				}
				//if display type is album
				else
				{

					//create html for breadcrumbs
					$this->_content .= '<div class="utv-breadcrumb"><span class="utv-albumcrumb">' . __('Albums', 'utvg') . '</span></div>';

					$this->printGalleryOpeningContainer();

					//create html for each video album
					foreach($data as $val)
					{

						//use permalinks for pages, else use GET parameters
						if(is_page() && $this->_options['skipSlugs'] == 'no')
							$this->_content .= $this->printAlbum($val, 'permalink');
						else
							$this->_content .= $this->printAlbum($val);

					}

				}

				$this->printGalleryClosingContainer();

			}
			//if there are no video albums in the gallery
			else
				$this->_content .= '<p>' . __('Sorry... there appear to be no video albums yet.', 'utvg') . '</p>';

		}

		$this->printGalleryClosingTags();


		return $this->_content;

	}

	public function printPanel()
	{

		global $wpdb;

		//fetch album ids
		$albumIds = $wpdb->get_results('SELECT ALB_ID, ALB_SORT FROM ' . $wpdb->prefix . 'utubevideo_album WHERE DATA_ID = "' . $this->_atts['id'] . '" && ALB_PUBLISH = 1 ORDER BY ALB_POS ' . $this->_gallery['DATA_SORT'], ARRAY_A);

		if(!empty($albumIds))
		{

			//fetch video data for above albums
			$videos = Array();

			foreach($albumIds as $val){

				$temp = $wpdb->get_results('SELECT VID_ID, VID_NAME, VID_URL, VID_THUMBTYPE, VID_QUALITY, VID_CHROME, VID_SOURCE, VID_STARTTIME, VID_ENDTIME FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $val['ALB_ID'] . ' && VID_PUBLISH = 1 ORDER BY VID_POS ' . $val['ALB_SORT'], ARRAY_A);

				$videos = array_merge($videos, $temp);

			}

			if(!empty($videos))
			{

				$this->printPanelOpeningTags();
				$this->printPanelOpeningContainer($videos[0]);

				$index = 0;

				foreach($videos as $video){

					$this->printPanelVideo($video, $index);

					$index++;

				}

				$this->printPanelPaging(count($videos));
				$this->printPanelClosingContainer();
				$this->printPanelClosingTags();

			}
			else
				$this->_content .= '<p>' . __('Sorry... there appear to be no videos yet.', 'utvg') . '</p>';

		}
		else
			$this->_content .= '<p>' . __('Sorry... there appear to be no videos yet.', 'utvg') . '</p>';

		return $this->_content;

	}

	private function printGalleryOpeningTags()
	{

		//get gallery icon type
		if($this->_atts['icon'] == 'blue')
			$iconClass = 'utv-icon-blue';
		elseif($this->_atts['icon'] == 'red')
			$iconClass = 'utv-icon-red';
		else
			$iconClass = 'utv-icon-default';

		$this->_content .= '<div class="utv-container utv-invis ' . $iconClass . '">';

	}

	private function printGalleryClosingTags()
	{
		$this->_content .= '</div>';
	}

	private function printGalleryOpeningContainer()
	{

		if($this->_atts['align'] == 'center')
			$css = 'class="utv-outer-wrapper utv-align-center"';
		elseif($this->_atts['align'] == 'right')
			$css = 'class="utv-outer-wrapper utv-align-right"';
		else
			$css = 'class="utv-outer-wrapper"';

		$this->_content .= '<div ' . $css . '><div class="utv-inner-wrapper">';

	}

	private function printGalleryClosingContainer()
	{
		$this->_content .= '</div></div>';
	}

	private function printPanelOpeningTags()
	{

		//get panel theme
		if($this->_atts['theme'] == 'dark')
			$themeClass = 'utv-panel-dark';
		elseif($this->_atts['theme'] == 'transparent')
			$themeClass = 'utv-panel-transparent';
		else
			$themeClass = 'utv-panel-light';

		//get panel icon type
		if($this->_atts['icon'] == 'blue')
			$iconClass = 'utv-icon-blue';
		elseif($this->_atts['icon'] == 'red')
			$iconClass = 'utv-icon-red';
		else
			$iconClass = 'utv-icon-default';

		$this->_content .= '<div class="utv-panel utv-invis ' . $themeClass . ' ' . $iconClass . '" data-panel-video-count="' . $this->_atts['panelvideocount'] . '" data-visible-controls=' . $this->_atts['controls'] . '>';

	}

	private function printPanelClosingTags()
	{
		$this->_content .= '</div>';
	}

	private function printPanelOpeningContainer($firstvideo)
	{

		//generate url for first video in gallery
		if($firstvideo['VID_SOURCE'] == 'youtube')
			$url = 'https://www.youtube.com/embed/' . $firstvideo['VID_URL'] . '?modestbranding=1&rel=0&showinfo=0&autohide=1&controls=' . ($this->_atts['controls'] == 'true' ? '1' : '0') . '&theme=' . $this->_options['playerControlTheme'] . '&color=' . $this->_options['playerProgressColor'] . '&autoplay=0&iv_load_policy=3&start=' . $firstvideo['VID_STARTTIME'] . '&end=' . $firstvideo['VID_ENDTIME'];
		elseif($firstvideo['VID_SOURCE'] == 'vimeo')
			$url = 'https://player.vimeo.com/video/' . $firstvideo['VID_URL'] . '?autoplay=0&autopause=0&title=0&portrait=0&byline=0&badge=0#t=' . $firstvideo['VID_STARTTIME'];
		else
			$url = '';

		$this->_content .= '<div class="utv-video-panel-wrapper">
					<iframe class="utv-video-panel-iframe" src="' . $url . '" frameborder="0" allowfullscreen></iframe>
				</div>
				<div class="utv-video-panel-controls">
					<i class="fa fa-chevron-left utv-video-panel-bkarrow"></i>
					<span class="utv-video-panel-title">' . $firstvideo['VID_NAME'] . '</span>
					<i class="fa fa-chevron-right utv-video-panel-fwarrow"></i>
					<div class="utv-clear"></div>
				</div>
				<div class="utv-video-panel-thumbnails utv-align-center"><div class="utv-inner-wrapper">';

	}

	private function printPanelClosingContainer()
	{
		$this->_content .= '</div></div>';
	}

	private function printPanelPaging($videoCount)
	{

		$totalpages = ceil($videoCount / $this->_atts['panelvideocount']);

		$this->_content .= '<div class="utv-video-panel-paging">';

		for($i = 1; $i <= $totalpages; $i++)
			$this->_content .= '<span class="utv-panel-paging-handle' . ($i == 1 ? ' utv-panel-paging-active' : '') . '">' . $i . '</span>';

		$this->_content .= '</div>';

	}

	private function printVideo(&$data)
	{

		if($data['VID_THUMBTYPE'] == 'square')
		{

			$style = 'width:' . $this->_options['thumbnailWidth'] . 'px; height:' . $this->_options['thumbnailWidth'] . 'px;';

		}
		else
		{

			if($data['VID_SOURCE'] == 'youtube')
				$ratio = 1.339;
			elseif($data['VID_SOURCE'] == 'vimeo')
				$ratio = 1.785;

			$style = 'width:' . $this->_options['thumbnailWidth'] . 'px; height:' .  round($this->_options['thumbnailWidth'] / $ratio) . 'px;';

		}

		if($data['VID_SOURCE'] == 'youtube')
		{

			$href = '//www.youtube.com/embed/' . $data['VID_URL'] . '?modestbranding=1&rel=0&showinfo=0&autohide=1&autoplay=1&iv_load_policy=3&color=' . $this->_options['playerProgressColor'] . '&vq=' . $data['VID_QUALITY'] . '&theme=' . $this->_options['playerControlTheme'] . '&controls=' . $data['VID_CHROME'] . '&start=' . $data['VID_STARTTIME'] . '&end=' . $data['VID_ENDTIME'];

		}
		elseif($data['VID_SOURCE'] == 'vimeo')
		{

			$href = '//player.vimeo.com/video/' . $data['VID_URL'] . '?autoplay=1&autopause=0&title=0&portrait=0&byline=0&badge=0#t=' . $data['VID_STARTTIME'];

		}

		return '<div class="utv-thumb" style="width:' . $this->_options['thumbnailWidth'] . 'px; margin:' . $this->_options['thumbnailPadding'] . 'px;">
			<a href="' . $href . '" title="' . stripslashes($data['VID_NAME']) . '" class="utv-popup ' . ($data['VID_THUMBTYPE'] == 'square' ? 'utv-square' : 'utv-rect') . '" style="background-image: url(' . $this->_dir . '/utubevideo-cache/' . $data['VID_URL'] . $data['VID_ID'] . '.jpg); ' . $style . '">
				<span class="utv-play-btn"></span>
			</a>
			<span>' . stripslashes($data['VID_NAME']) . '</span>
		</div>';

	}

	private function printAlbum(&$data, $linkType = '')
	{

		if($data['VID_THUMBTYPE'] == 'square')
		{

			$style = 'width:' . $this->_options['thumbnailWidth'] . 'px; height:' . $this->_options['thumbnailWidth'] . 'px;';

		}
		else
		{

			if($data['VID_SOURCE'] == 'vimeo')
				$ratio = 1.785;
			else
				$ratio = 1.339;

			$style = 'width:' . $this->_options['thumbnailWidth'] . 'px; height:' . round($this->_options['thumbnailWidth'] / $ratio) . 'px;';

		}

		if($linkType == 'permalink')
			$link = get_site_url() . '/' . get_query_var('pagename') . '/album/' . $data['ALB_SLUG'] . '/';
		else
			$link = '?aid=' . $data['ALB_ID'] . '-' . $this->_atts['id'];

		return '<div class="utv-thumb utv-album" style="width:' . $this->_options['thumbnailWidth'] . 'px; margin:' . $this->_options['thumbnailPadding'] . 'px;">
			<a href="' . $link . '" class="' . ($data['VID_THUMBTYPE'] == 'square' ? 'utv-square' : 'utv-rect') . '" style="' . $style . '">
				<img src="' . $this->_dir . '/utubevideo-cache/' . $data['ALB_THUMB']  . '.jpg"/>
			</a>
			<span>' . stripslashes($data['ALB_NAME']) . '</span>
		</div>';

	}

	private function printPanelVideo(&$data, $index)
	{

		if($data['VID_THUMBTYPE'] == 'square')
			$style = 'width:' . $this->_options['thumbnailWidth'] . 'px; height:' . $this->_options['thumbnailWidth'] . 'px;';
		else
		{

			if($data['VID_SOURCE'] == 'vimeo')
				$ratio = 1.785;
			else
				$ratio = 1.339;

			$style = 'width:' . $this->_options['thumbnailWidth'] . 'px; height:' .  round($this->_options['thumbnailWidth'] / $ratio) . 'px;';

		}

		$this->_content .= '<div class="utv-thumb ' . ($index == 0 ? 'utv-panel-video-active' : '') . '" style="width:' . $this->_options['thumbnailWidth'] . 'px;margin:' . $this->_options['thumbnailPadding'] . 'px;" data-index="' . $index . '" data-type="' . $data['VID_SOURCE'] . '" data-id="' . $data['VID_URL'] . '" data-name="' . stripslashes($data['VID_NAME']) . '" data-stime="' . $data['VID_STARTTIME'] . '" data-etime="' . $data['VID_ENDTIME'] . '">
			<a class="' . ($data['VID_THUMBTYPE'] == 'square' ? 'utv-square' : 'utv-rect') . '" style="background-image: url(' . $this->_dir . '/utubevideo-cache/' . $data['VID_URL'] . $data['VID_ID'] . '.jpg);' . $style . '">
				<span class="utv-play-btn"></span>
			</a>
			<span>' . stripslashes($data['VID_NAME']) . '</span>
		</div>';

	}

}

?>
