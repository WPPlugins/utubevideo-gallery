<?php

class utvAdminAjax
{

    private $_options;

    public function __construct($options)
	{
        
        require_once(dirname(__FILE__) . '/../class/utvAdminGen.class.php');
        
        $this->_options = $options;
		utvAdminGen::initialize($this->_options);

        add_action('wp_ajax_utv_videoorderupdate', array($this, 'updateVideoOrder'));
		add_action('wp_ajax_utv_albumorderupdate', array($this, 'updateAlbumOrder'));
		add_action('wp_ajax_ut_deletevideo', array($this, 'deleteVideo'));
		add_action('wp_ajax_ut_deletealbum', array($this, 'deleteAlbum'));
		add_action('wp_ajax_ut_deletegallery', array($this, 'deleteGallery'));
		add_action('wp_ajax_ut_publishvideo', array($this, 'toggleVideoPublish'));
		add_action('wp_ajax_ut_publishalbum', array($this, 'toggleAlbumPublish'));
		add_action('wp_ajax_utv_fetchyoutubeplaylist', array($this, 'fetchYoutubePlaylist'));
        add_action('wp_ajax_utv_fetchvimeoplaylist', array($this, 'fetchVimeoPlaylist'));

    }
    
    public function updateVideoOrder()
	{
		
		
		global $wpdb;
		$data = explode(',', $_POST['order']);

		$cnt = count($data);

		for($i = 0; $i < $cnt; $i++)
		{

			$wpdb->update(
				$wpdb->prefix . 'utubevideo_video',
				array(
					'VID_POS' => $i
				),
				array('VID_ID' => $data[$i])
			);

		}

		die();

	}

	public function updateAlbumOrder()
	{
		
		global $wpdb;
		$data = explode(',', $_POST['order']);

		$cnt = count($data);

		for($i = 0; $i < $cnt; $i++)
		{

			$wpdb->update(
				$wpdb->prefix . 'utubevideo_album',
				array(
					'ALB_POS' => $i
				),
				array('ALB_ID' => $data[$i])
			);
		  
		}

		die();

	}

	//delete a video script//
	public function deleteVideo()
	{

		check_ajax_referer('ut-delete-video', 'nonce');

		$key = array(sanitize_key($_POST['key']));

		global $wpdb;

		if(utvAdminGen::deleteVideos($key, $wpdb))
			echo 1;

		die();

	}

	//delete an album script//
	public function deleteAlbum()
	{

		check_ajax_referer('ut-delete-album', 'nonce');

		$key = array(sanitize_key($_POST['key']));

		global $wpdb;

		if(utvAdminGen::deleteAlbums($key, $wpdb))
			echo 1;

		die();

	}

	//delete a gallery script//
	public function deleteGallery()
	{

		check_ajax_referer('ut-delete-gallery', 'nonce');

		$key = array(sanitize_key($_POST['key']));

		global $wpdb;
		
		if(utvAdminGen::deleteGalleries($key, $wpdb))
			echo 1;
		else
			echo 0;

		die();

	}

	public function toggleVideoPublish()
    {

		check_ajax_referer('ut-publish-video', 'nonce');

		$key = array(sanitize_key($_POST['key']));
		$changeTo = sanitize_text_field($_POST['changeTo']);

		global $wpdb;

		if(utvAdminGen::toggleVideosPublish($key, $changeTo, $wpdb))
			echo 1;

		die();

	}

	public function toggleAlbumPublish()
    {

		check_ajax_referer('ut-publish-album', 'nonce');

		$key = array(sanitize_key($_POST['key']));
		$changeTo = sanitize_text_field($_POST['changeTo']);

		global $wpdb;
		
		if(utvAdminGen::toggleAlbumsPublish($key, $changeTo, $wpdb))
			echo 1;

		die();

	}
	
	public function fetchYoutubePlaylist()
    {
		
		check_ajax_referer('ut-retrieve-playlist', 'nonce');

		$url = sanitize_text_field($_POST['url']);
		
		$return = array('valid' => true, 'message' => '', 'data' => array());

		//check for a possibly valid api key before continuing
		if($this->_options['youtubeApiKey'] != ''){

			//parse video url to get video id//
			if(!$listID = utvAdminGen::parseURL($url, 'youtube', 'playlist')){

				$return['valid'] = false;
				$return['message'] = __('Invalid URL.', 'utvg');

			}
			
			//get base data from youtube api
			$nextPageToken = true;
			$baseData = array();	

			while($nextPageToken == true){
				
				if(!$data = utvAdminGen::queryAPI('https://www.googleapis.com/youtube/v3/playlistItems?key=' . $this->_options['youtubeApiKey'] . '&part=snippet,status,id,contentDetails&maxResults=50&playlistId=' . $listID . (strlen($nextPageToken) > 1 ? '&pageToken=' . $nextPageToken : ''))){

					$return['valid'] = false;
					$return['message'] = __('Oops... something went wrong. Try again.', 'utvg');

				}
				
				$baseData = array_merge($baseData, $data['items']);
				
				if(isset($data['nextPageToken']))			
					$nextPageToken = $data['nextPageToken'];
				else
					$nextPageToken = false;

			}
			
			//generate video id strings to get additonal details needed to filter out deleted and private videos
			$videoIds = array();
			$idString = '';
			$idCount = 0;

			foreach($baseData as $item){

				$idString .= $item['snippet']['resourceId']['videoId'] . ',';
				$idCount++;

				if($idCount == 50){

					array_push($videoIds, trim($idString, ','));
					$idString = '';
					$idCount = 0;

				}

			}

			if($idCount > 0)
				array_push($videoIds, trim($idString, ','));
				
			$finalData = array();

			//get final video data to filter with
			foreach($videoIds as $list){

				if(!$data = utvAdminGen::queryAPI('https://www.googleapis.com/youtube/v3/videos?key=' . $this->_options['youtubeApiKey'] . '&part=contentDetails,snippet,status&id=' . $list)){

					$return['valid'] = false;
					$return['message'] = __('Oops... something went wrong. Try again.', 'utvg');
	
				}
				
				$finalData = array_merge($finalData, $data['items']);

			}
			
			//filter video and if passed add it to album dataset
			foreach($finalData as $video)
			{

				if(isset($video['status']['uploadStatus']) && $video['status']['uploadStatus'] != 'rejected' && isset($video['status']['embeddable']) && $video['status']['embeddable'] == true && isset($video['status']['privacyStatus']) && $video['status']['privacyStatus'] == 'public')
				{

					$duration = new DateTime('@0');
					$duration->add(new DateInterval($video['contentDetails']['duration']));
					$duration = $duration->format('H:i:s');
					
					array_push($return['data'], array('title' => $video['snippet']['title'], 'videoId' => $video['id'], 'thumbURL' => 'http://img.youtube.com/vi/' . $video['id'] . '/0.jpg', 'duration' => $duration));
	
				}

			}
	
		}else{
			
			$return['valid'] = false;
			$return['message'] = __('You must have a valid API key set in the settings menu.', 'utvg');
						
		}
		
		wp_send_json($return);
		
	}
	
	public function fetchVimeoPlaylist()
    {
		
		check_ajax_referer('ut-retrieve-playlist', 'nonce');

		$url = sanitize_text_field($_POST['url']);
    
		$return = array('valid' => true, 'message' => '', 'data' => array());
        
		if(!$albumID = utvAdminGen::parseURL($url, 'vimeo', 'playlist')){

			$return['valid'] = false;
			$return['message'] = __('Invalid URL.', 'utvg');
			wp_send_json($return);
			
        }
		
        $data = Array();

		if(!$albumData = utvAdminGen::queryAPI('https://vimeo.com/api/v2/album/' . $albumID . '/info.json')){
			
			$return['valid'] = false;
			$return['message'] = __('Oops... something went wrong. Try again.', 'utvg');
			wp_send_json($return);
			
		}
		
		if($albumData['total_videos'] >= 60)
			$pages = 3;
		else
			$pages = ceil($albumData['total_videos'] / 20);

		for($i = 1; $i <= $pages; $i++)
		{

			if(!$ndata = utvAdminGen::queryAPI('https://vimeo.com/api/v2/album/' . $albumID . '/videos.json?page=' . $i)){
				
				$return['valid'] = false;
				$return['message'] = __('Oops... something went wrong. Try again.', 'utvg');
				wp_send_json($return);
			
			}

			$data = array_merge($data, $ndata);

		}
		
		foreach($data as $val)
		{
			
			$duration = gmdate('H:i:s', $val['duration']);

			array_push($return['data'], array('title' => $val['title'], 'videoId' => $val['id'], 'thumbURL' => $val['thumbnail_large'], 'duration' => $duration));

		}
		
		wp_send_json($return);

	}

}

?>