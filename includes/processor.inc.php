<?php

if(!empty($_POST))
{

    //declare globals
    global $wpdb;

    //require helper classes
    require_once(dirname(__FILE__) . '/../class/utvAdminGen.class.php');
    utvAdminGen::initialize($this->_options);

    //save main options script//
    if(isset($_POST['utSaveOpts']))
    {

        if(check_admin_referer('utubevideo_update_options'))
        {

            $opts['skipMagnificPopup'] = (isset($_POST['skipMagnificPopup']) ? 'yes' : 'no');
            $opts['skipSlugs'] = (isset($_POST['skipSlugs']) ? 'yes' : 'no');
            $opts['playerControlTheme'] = sanitize_text_field($_POST['playerControlTheme']);
            $opts['playerProgressColor'] = sanitize_text_field($_POST['playerProgressColor']);
            $opts['fancyboxOverlayColor'] = (isset($_POST['fancyboxOverlayColor']) ? sanitize_text_field($_POST['fancyboxOverlayColor']) : '#000');
            $opts['fancyboxOverlayOpacity'] = (isset($_POST['fancyboxOverlayOpacity']) ? sanitize_text_field($_POST['fancyboxOverlayOpacity']) : '0.85');
            $opts['thumbnailWidth'] = (isset($_POST['thumbnailWidth']) ? sanitize_text_field($_POST['thumbnailWidth']) : '150');
            $opts['thumbnailPadding'] = (isset($_POST['thumbnailPadding']) ? sanitize_text_field($_POST['thumbnailPadding']) : '10');
            $opts['thumbnailBorderRadius'] = (isset($_POST['thumbnailBorderRadius']) ? sanitize_text_field($_POST['thumbnailBorderRadius']) : '3');
            $opts['youtubeApiKey'] = (isset($_POST['youtubeApiKey']) ? sanitize_text_field($_POST['youtubeApiKey']) : '');

            if(!empty($_POST['playerWidth']) && !empty($_POST['playerHeight']))
            {

                $opts['playerWidth'] = sanitize_text_field($_POST['playerWidth']);
                $opts['playerHeight'] = sanitize_text_field($_POST['playerHeight']);

            }
            else
            {

                $opts['playerWidth'] = 950;
                $opts['playerHeight'] = 537;

            }

            if(preg_match("/[^0-9]/", $opts['thumbnailWidth']) || preg_match("/[^0-9]/", $opts['thumbnailPadding']) || preg_match("/[^0-9]/", $opts['thumbnailBorderRadius']))
            {

                echo '<div class="error e-message"><p>' . __('Oops... thumbnail width, padding, and radius must contain only numbers.', 'utvg') . '</p></div>';
                return;

            }

            if($opts['thumbnailWidth'] != $this->_options['thumbnailWidth'])
            {

                //override admin helper object with new options
                utvAdminGen::initialize($opts);

                $videoData = $wpdb->get_results('SELECT VID_ID, VID_URL, VID_SOURCE, VID_THUMBTYPE, DATA_THUMBTYPE FROM ' . $wpdb->prefix . 'utubevideo_video v JOIN ' . $wpdb->prefix . 'utubevideo_album a ON (v.ALB_ID = a.ALB_ID) JOIN ' . $wpdb->prefix . 'utubevideo_dataset d ON (a.DATA_ID = d.DATA_ID)', ARRAY_A);

                foreach($videoData as $video)
                {

                    if($video['VID_SOURCE'] == 'vimeo')
                    {

                        $data = utvAdminGen::queryAPI('https://vimeo.com/api/v2/video/' . $video['VID_URL'] . '.json')[0];
                        $sourceURL = $data['thumbnail_large'];

                    }
                    else
                        $sourceURL = 'http://img.youtube.com/vi/' . $video['VID_URL'] . '/0.jpg';

                    //resave thumbnail
                    utvAdminGen::saveThumbnail($sourceURL, $video['VID_URL'] . $video['VID_ID'], $video['DATA_THUMBTYPE'], true);

                    //update thumbnail type in database if needed - compatibilty
                    if($video['VID_THUMBTYPE'] != $video['DATA_THUMBTYPE'])
                    {

                        $wpdb->update(
                            $wpdb->prefix . 'utubevideo_video',
                            array(
                                'VID_THUMBTYPE' => $video['DATA_THUMBTYPE']
                            ),
                            array('VID_ID' => $video['VID_ID'])
                        );

                    }

                }

            }

            if(update_option('utubevideo_main_opts', $opts))
                echo '<div class="updated"><p>Settings saved</p></div>';
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong or there were no changes needed', 'utvg') . '</p></div>';

        }

    }
    //save new gallery script//
    elseif(isset($_POST['createGallery']))
    {

        if(check_admin_referer('utubevideo_save_gallery'))
        {

            $shortname = sanitize_text_field($_POST['galleryName']);
            $albumsort = sanitize_text_field($_POST['albumSort']);
            $thumbType = sanitize_text_field($_POST['thumbType']);
            $displaytype = sanitize_text_field($_POST['displayType']);
            $time = current_time('timestamp');

            if(empty($shortname) || empty($albumsort) || empty($displaytype) || empty($thumbType))
            {

                echo '<div class="error e-message"><p>' . __('Oops... all form fields must have a value', 'utvg') . '</p></div>';
                return;

            }

            if($wpdb->insert(
                $wpdb->prefix . 'utubevideo_dataset',
                array(
                    'DATA_NAME' => $shortname,
                    'DATA_SORT' => $albumsort,
                    'DATA_THUMBTYPE' => $thumbType,
                    'DATA_DISPLAYTYPE' => $displaytype,
                    'DATA_UPDATEDATE' => $time
                )
            ))
                echo '<div class="updated"><p>' . __('Gallery created', 'utvg') . '</p></div>';
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong', 'utvg') . '</p></div>';

        }

    }
    //save a gallery edit script//
    elseif(isset($_POST['saveGalleryEdit']))
    {

        if(check_admin_referer('utubevideo_edit_gallery'))
        {

            $galleryName = sanitize_text_field($_POST['galname']);
            $albumSort = sanitize_text_field($_POST['albumSort']);
            $thumbType = sanitize_text_field($_POST['thumbType']);
            $displayType = sanitize_text_field($_POST['displayType']);
            $key = sanitize_key($_POST['key']);

            if(empty($galleryName) || !isset($key) || empty($albumSort) || empty($displayType) || empty($thumbType))
            {

                echo '<div class="error e-message"><p>' . __('Oops... all form fields must have a value', 'utvg') . '</p></div>';
                return;

            }

            if($wpdb->update(
                $wpdb->prefix . 'utubevideo_dataset',
                array(
                    'DATA_NAME' => $galleryName,
                    'DATA_SORT' => $albumSort,
                    'DATA_THUMBTYPE' => $thumbType,
                    'DATA_DISPLAYTYPE' => $displayType
                ),
                array('DATA_ID' => $key)
            ) >= 0)
                echo '<div class="updated"><p>' . __('Gallery updated', 'utvg') . '</p></div>';
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong', 'utvg') . '</p></div>';

        }

    }
    //save a new album script//
    elseif(isset($_POST['saveAlbum']))
    {

        if(check_admin_referer('utubevideo_save_album'))
        {

            $albumName = sanitize_text_field($_POST['alname']);
            $videoSort = ($_POST['vidSort'] == 'desc' ? 'desc' : 'asc');
            $key = sanitize_key($_POST['key']);
            $time = current_time('timestamp');

            if(empty($albumName) || empty($videoSort) || !isset($key))
            {

                echo '<div class="error e-message"><p>' . __('Oops... all form fields must have a value', 'utvg') . '</p></div>';
                return;

            }

            $slug = utvAdminGen::generateSlug($albumName, $wpdb);

            //get current album count for gallery//
            $gallery = $wpdb->get_results('SELECT DATA_ALBCOUNT FROM ' . $wpdb->prefix . 'utubevideo_dataset WHERE DATA_ID = ' . $key, ARRAY_A)[0];
            $nextSortPos = $gallery['DATA_ALBCOUNT'];
            $albcnt = $gallery['DATA_ALBCOUNT'] + 1;

            if($wpdb->insert(
                $wpdb->prefix . 'utubevideo_album',
                array(
                    'ALB_NAME' => $albumName,
                    'ALB_SLUG' => $slug,
                    'ALB_THUMB' => 'missing',
                    'ALB_SORT' => $videoSort,
                    'ALB_UPDATEDATE' => $time,
                    'ALB_POS' => $nextSortPos,
                    'DATA_ID' => $key
                )
            ) && $wpdb->update(
                $wpdb->prefix . 'utubevideo_dataset',
                array(
                    'DATA_ALBCOUNT' => $albcnt
                ),
                array('DATA_ID' => $key)
            ) >= 0)
                echo '<div class="updated"><p>' . __('Video album created', 'utvg') . '</p></div>';
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong', 'utvg') . '</p></div>';

        }

    }
    //save a new video script//
    elseif(isset($_POST['addVideo']))
    {

        if(check_admin_referer('utubevideo_add_video'))
        {

            $url = sanitize_text_field($_POST['url']);
            $videoName = sanitize_text_field($_POST['vidname']);
            $quality = sanitize_text_field($_POST['videoQuality']);
            $chrome = (isset($_POST['videoChrome']) ? 0 : 1);
            $videoSource = sanitize_text_field($_POST['videoSource']);
            $startTime = sanitize_text_field($_POST['startTime']);
            $endTime = sanitize_text_field($_POST['endTime']);
            $key = sanitize_key($_POST['key']);
            $time = current_time('timestamp');

            if(empty($url) || empty($quality) || !isset($chrome) || empty($videoSource) || !isset($key))
            {

                echo '<div class="error e-message"><p>' . __('Oops... all required fields must have a value.', 'utvg') . '</p></div>';
                return;

            }

            //get current video count for album//
            $album = $wpdb->get_results('SELECT ALB_VIDCOUNT, DATA_ID FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_ID = ' . $key, ARRAY_A)[0];
            $vidcnt = $album['ALB_VIDCOUNT'] + 1;
            $nextSortPos = $album['ALB_VIDCOUNT'];

            $gallery = $wpdb->get_results('SELECT DATA_THUMBTYPE FROM ' . $wpdb->prefix . 'utubevideo_dataset WHERE DATA_ID = ' . $album['DATA_ID'], ARRAY_A)[0];

            if(!$vID = utvAdminGen::parseURL($url, $videoSource, 'video')){

                echo '<div class="error e-message"><p>' . __('Invalid URL.', 'utvg') . '</p></div>';
                return;

            }

            if($videoSource == 'youtube'){

                $sourceURL = 'http://img.youtube.com/vi/' . $vID . '/0.jpg';

            }elseif($videoSource == 'vimeo'){

                $data = utvAdminGen::queryAPI('https://vimeo.com/api/v2/video/' . $vID . '.json');
                $sourceURL = $data[0]['thumbnail_large'];

            }

            //insert new video
            if($wpdb->insert(
              $wpdb->prefix . 'utubevideo_video',
                array(
                    'VID_SOURCE' => $videoSource,
                    'VID_NAME' => $videoName,
                    'VID_URL' => $vID,
                    'VID_THUMBTYPE' => $gallery['DATA_THUMBTYPE'],
                    'VID_QUALITY' => $quality,
                    'VID_CHROME' => $chrome,
                    'VID_STARTTIME' => $startTime,
                    'VID_ENDTIME' => $endTime,
                    'VID_POS' => $nextSortPos,
                    'VID_UPDATEDATE' => $time,
                    'ALB_ID' => $key
                )
            )){

                //get last insert id and save thumbnail
                $idnum = $wpdb->insert_id;

                if(!utvAdminGen::saveThumbnail($sourceURL, $vID . $idnum, $gallery['DATA_THUMBTYPE'])){

                    $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'utubevideo_video WHERE VID_ID ="' . $idnum . '"');
                    return;

                }

                //update album video count
                if($wpdb->update(
                    $wpdb->prefix . 'utubevideo_album',
                    array(
                        'ALB_VIDCOUNT' => $vidcnt
                    ),
                    array('ALB_ID' => $key)
                ) >= 0)
                    echo '<div class="updated"><p>' . __('Video added to album.', 'utvg') . '</p></div>';
                else
                    echo '<div class="error e-message"><p>' . __('Oops... something went wrong. Try again.', 'utvg') . '</p></div>';

            }
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong. Try again.', 'utvg') . '</p></div>';

        }

    }
    //save an playlist script//
    elseif(isset($_POST['addPlaylist']))
    {

        if(check_admin_referer('utubevideo_add_playlist'))
        {

            $playlistSource = sanitize_text_field($_POST['playlistSource']);
            $url = sanitize_text_field($_POST['url']);
            $quality = sanitize_text_field($_POST['videoQuality']);
            $chrome = isset($_POST['videoChrome']) ? 0 : 1;
            $videolistIds = sanitize_text_field($_POST['videolistIds']);
            $videolistTitles = sanitize_text_field($_POST['videolistTitles']);
            $videolistThumbURLs = sanitize_text_field($_POST['videolistThumbURLs']);
            $key = sanitize_key($_POST['key']);
            $time = current_time('timestamp');

            if(empty($url) || empty($quality) || empty($playlistSource) || empty($videolistIds) || empty($videolistTitles) || empty($videolistThumbURLs) || !isset($key))
            {

                echo '<div class="error e-message"><p>' . __('Oops... all form fields must have a value', 'utvg') . '</p></div>';
                return;

            }

            //get current video count for album//
            $album = $wpdb->get_results('SELECT ALB_VIDCOUNT, DATA_ID FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_ID = ' . $key, ARRAY_A)[0];
            $gallery = $wpdb->get_results('SELECT DATA_THUMBTYPE FROM ' . $wpdb->prefix . 'utubevideo_dataset WHERE DATA_ID = ' . $album['DATA_ID'], ARRAY_A)[0];
            $addedcount = 0;
            $nextSortPos = $album['ALB_VIDCOUNT'];
            $thumbType = $gallery['DATA_THUMBTYPE'];

            $idarray = explode('||', $videolistIds);
            $titlearray = explode('||', $videolistTitles);
            $thumbarray = explode('||', $videolistThumbURLs);
            $totalvideos = count($idarray);

            if($totalvideos != count($titlearray) || $totalvideos != count($thumbarray))
            {

                echo '<div class="error e-message"><p>' . __('Oops... an internal error has occurred.', 'utvg') . '</p></div>';
                return;

            }

            for($i = 0; $i < $totalvideos; $i++){

                //if video insertion successful; save thumbnail and increment new video count++
                if($wpdb->insert(
                    $wpdb->prefix . 'utubevideo_video',
                    array(
                        'VID_SOURCE' => $playlistSource,
                        'VID_NAME' => $titlearray[$i],
                        'VID_URL' => $idarray[$i],
                        'VID_THUMBTYPE' => $thumbType,
                        'VID_QUALITY' => $quality,
                        'VID_CHROME' => $chrome,
                        'VID_POS' => $nextSortPos,
                        'VID_UPDATEDATE' => $time,
                        'ALB_ID' => $key
                    )
                )){

                    $idnum = $wpdb->insert_id;

                    if(utvAdminGen::saveThumbnail($thumbarray[$i], $idarray[$i] . $idnum, $thumbType, true)){

                        $addedcount++;
                        $nextSortPos++;

                    }else{

                        $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'utubevideo_video WHERE VID_ID ="' . $idnum . '"');

                    }

                }

            }

            if($wpdb->update(
                $wpdb->prefix . 'utubevideo_album',
                array(
                    'ALB_VIDCOUNT' => $album['ALB_VIDCOUNT'] + $addedcount
                ),
                array('ALB_ID' => $key)
            ) >= 0)
                echo '<div class="updated"><p>' . __('Playlist added to album', 'utvg') . '</p></div>';
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong', 'utvg') . '</p></div>';

        }

    }
    //save an album edit script//
    elseif(isset($_POST['saveAlbumEdit']))
    {

        if(check_admin_referer('utubevideo_edit_album'))
        {

            $albumName = sanitize_text_field($_POST['alname']);
			$albumGallery = sanitize_key($_POST['albumGallery']);
            $videoSort = ($_POST['vidSort'] == 'desc' ? 'desc' : 'asc');
            $thumb = (isset($_POST['albumThumbSelect']) ? $_POST['albumThumbSelect'] : 'missing');
            $prevSlug = sanitize_text_field($_POST['prevSlug']);
            $slug = sanitize_text_field($_POST['slug']);
            $key = sanitize_key($_POST['key']);

            if(empty($albumGallery) || empty($albumName) || empty($videoSort) || empty($thumb) || empty($prevSlug) || empty($slug) || !isset($key))
            {

                echo '<div class="error e-message"><p>' . __('Oops... all form fields must have a value', 'utvg') . '</p></div>';
                return;

            }

            if($slug != $prevSlug)
            {

                $slug = utvAdminGen::generateSlug($albumName, $wpdb);

            }

            if($wpdb->update(
                $wpdb->prefix . 'utubevideo_album',
                array(
                    'ALB_NAME' => $albumName,
                    'ALB_SLUG' => $slug,
                    'ALB_THUMB' => $thumb,
                    'ALB_SORT' => $videoSort,
					'DATA_ID' => $albumGallery
                ),
                array('ALB_ID' => $key)
            ) >= 0)
                echo '<div class="updated"><p>' . __('Video album updated', 'utvg') . '</p></div>';
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong', 'utvg') . '</p></div>';

        }

    }
    //save a video edit script//
    elseif(isset($_POST['saveVideoEdit']))
    {

        if(check_admin_referer('utubevideo_edit_video'))
        {

            $videoName = sanitize_text_field($_POST['vidname']);
			$videoAlbum = sanitize_key($_POST['videoAlbum']);
            $quality = sanitize_text_field($_POST['videoQuality']);
            $chrome = isset($_POST['videoChrome']) ? 0 : 1;
            $startTime = sanitize_text_field($_POST['startTime']);
            $endTime = sanitize_text_field($_POST['endTime']);
            $thumbnailRefresh = isset($_POST['thumbnailRefresh']) ? true : false;

            $key = sanitize_key($_POST['key']);

            if(empty($videoAlbum) || empty($quality) || !isset($chrome) || !isset($key))
            {

                echo '<div class="error e-message"><p>' . __('Oops... all required fields must have a value', 'utvg') . '</p></div>';
                return;

            }

            $video = $wpdb->get_results('SELECT VID_ID, VID_SOURCE, VID_URL, VID_THUMBTYPE, ALB_ID FROM ' . $wpdb->prefix . 'utubevideo_video WHERE VID_ID = ' . $key, ARRAY_A)[0];
            $album = $wpdb->get_results('SELECT DATA_ID FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_ID = ' . $video['ALB_ID'], ARRAY_A)[0];
            $gallery = $wpdb->get_results('SELECT DATA_THUMBTYPE FROM ' . $wpdb->prefix . 'utubevideo_dataset WHERE DATA_ID = ' . $album['DATA_ID'], ARRAY_A)[0];

            //resave thumbnail if difference detected or forced
            if($gallery['DATA_THUMBTYPE'] != $video['VID_THUMBTYPE'] || $thumbnailRefresh)
            {

                if($video['VID_SOURCE'] == 'youtube'){

                    $sourceURL = 'http://img.youtube.com/vi/' . $video['VID_URL'] . '/0.jpg';

                }elseif($video['VID_SOURCE'] == 'vimeo'){

                    $data = utvAdminGen::queryAPI('https://vimeo.com/api/v2/video/' . $video['VID_URL'] . '.json')[0];
                    $sourceURL = $data['thumbnail_large'];

                }

                if(!utvAdminGen::saveThumbnail($sourceURL, $video['VID_URL'] . $video['VID_ID'], $gallery['DATA_THUMBTYPE']))
                    return;

            }

            //update database entry
            if($wpdb->update(
                $wpdb->prefix . 'utubevideo_video',
                array(
                    'VID_NAME' => $videoName,
                    'VID_THUMBTYPE' => $gallery['DATA_THUMBTYPE'],
                    'VID_QUALITY' => $quality,
                    'VID_CHROME' => $chrome,
                    'VID_STARTTIME' => $startTime,
                    'VID_ENDTIME' => $endTime,
					'ALB_ID' => $videoAlbum
                ),
                array('VID_ID' => $key)
            ) >= 0)
                echo '<div class="updated"><p>' . __('Video updated', 'utvg') . '</p></div>';
            else
                echo '<div class="error e-message"><p>' . __('Oops... something went wrong', 'utvg') . '</p></div>';

        }

    }
    elseif(isset($_POST['resetPermalinks']))
    {

        //setup rewrite rule for video albums
        add_rewrite_rule('([^/]+)/album/([^/]+)$', 'index.php?pagename=$matches[1]&albumid=$matches[2]', 'top');

        global $wp_rewrite;
        $wp_rewrite->flush_rules(false);

        echo '<div class="updated"><p>' . __('Permalinks updated', 'utvg') . '</p></div>';

    }

}

?>
