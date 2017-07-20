<?php

    $id = sanitize_key($_GET['id']);
    $pid = sanitize_key($_GET['pid']);
	$pid = explode('-', $pid);

	if(!isset($id) || !isset($pid) || count($pid) < 2){

		_e('Invalid Video data', 'utvg');
        return;

	}

    $dir = wp_upload_dir()['baseurl'];
    $video = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_video WHERE VID_ID = ' . $id, ARRAY_A);
	$albums = $wpdb->get_results('SELECT ALB_ID, ALB_NAME FROM ' . $wpdb->prefix . 'utubevideo_album WHERE DATA_ID = ' . $pid[1] . ' ORDER BY ALB_NAME', ARRAY_A);

    if(!isset($video[0])){

        _e('Invalid Video ID', 'utvg');
        return;

    }

    $video = $video[0];

    if($video['VID_SOURCE'] == 'youtube')
        $iframeurl = 'https://www.youtube.com/embed/' . $video['VID_URL'] . '?modestbranding=1&rel=0&showinfo=0&autohide=0&iv_load_policy=3&color=white&theme=dark&autoplay=0&start=' . $video['VID_STARTTIME'] . '&end=' . $video['VID_ENDTIME'];
    elseif($video['VID_SOURCE'] == 'vimeo')
        $iframeurl = 'https://player.vimeo.com/video/' . $video['VID_URL'] . '?&title=0&portrait=0&byline=0badge=0&autoplay=0#t=' . $video['VID_STARTTIME'];
    else
        $iframeurl = '';

?>

<div class="utv-left-column">
    <div class="utv-formbox utv-top-formbox card">
        <form method="post">
            <h3><?php _e('Edit Video', 'utvg'); ?></h3>
            <p>
                <img src="<?php echo $dir . '/utubevideo-cache/' . $video['VID_URL'] . $video['VID_ID'] . '.jpg';?>" class="utv-preview-thumb"/>
            </p>

            <p>
                <label><?php _e('Source:', 'utvg'); ?></label>
                <input type="text" value="<?php echo ucwords($video['VID_SOURCE']); ?>" tabindex="-1" readonly/>
            </p>
            <p>
                <label><?php _e('Name:', 'utvg'); ?></label>
                <input type="text" name="vidname" value="<?php echo stripslashes($video['VID_NAME']); ?>"/>
                <span class="utv-hint"><?php _e('ex: name of video', 'utvg'); ?></span>
            </p>
			<p>
				<label><?php _e('Album:', 'utvg'); ?></label>
                <select name="videoAlbum"/>

				<?php

                foreach($albums as $album)
                {

                    if($album['ALB_ID'] == $video['ALB_ID'])
                        echo '<option value="' . $album['ALB_ID'] . '" selected>' . $album['ALB_NAME'] . '</option>';
                    else
                        echo '<option value="' . $album['ALB_ID'] . '">' . $album['ALB_NAME'] . '</option>';

                }

                ?>

				</select>
				<span class="utv-hint"><?php _e('ex: the album containing this video', 'utvg'); ?></span>
			</p>
            <p <?php echo ($video['VID_SOURCE'] != 'youtube' ? 'class="utv-hide"' : ''); ?>>
                <label><?php _e('Quality:', 'utvg'); ?></label>
                <select name="videoQuality"/>

                <?php

                $opts = array(array('text' => '480p', 'value' => 'large'), array('text' => '720p', 'value' => 'hd720'), array('text' => '1080p', 'value' => 'hd1080'));

                foreach($opts as $val)
                {

                    if($val['value'] == $video['VID_QUALITY'])
                        echo '<option value="' . $val['value'] . '" selected>' . $val['text'] . '</option>';
                    else
                        echo '<option value="' . $val['value'] . '">' . $val['text'] . '</option>';

                }

                ?>

                </select>
                <span class="utv-hint"><?php _e('ex: the starting quality of the video', 'utvg'); ?></span>
            </p>
            <p <?php echo ($video['VID_SOURCE'] != 'youtube' ? 'class="utv-hide"' : ''); ?>>
                <label><?php _e('Chromeless:', 'utvg'); ?></label>
                <input type="checkbox" name="videoChrome"  <?php echo ($video['VID_CHROME'] == '0' ? 'checked' : ''); ?>/>
                <span class="utv-hint"><?php _e('ex: hide the playback controls of the video', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Start Time:', 'utvg'); ?></label>
                <input type="text" name="startTime" id="utv-videoadd-starttime" value="<?php echo $video['VID_STARTTIME']; ?>"/>
                <span class="utv-hint"><?php _e('ex: start time of video (in seconds)', 'utvg'); ?></span>
            </p>
            <p <?php echo ($video['VID_SOURCE'] != 'youtube' ? 'class="utv-hide"' : ''); ?>>
                <label><?php _e('End Time:', 'utvg'); ?></label>
                <input type="text" name="endTime" id="utv-videoadd-endtime" value="<?php echo $video['VID_ENDTIME']; ?>"/>
                <span class="utv-hint"><?php _e('ex: end time of video (in seconds)', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Refresh Thumbnail:', 'utvg'); ?></label>
                <input type="checkbox" name="thumbnailRefresh" value="on"/>
                <span class="utv-hint"><?php _e('ex: force thumbnail refresh for video', 'utvg'); ?></span>
            </p>
            <p class="submit">
                <input type="hidden" name="key" value="<?php echo $id; ?>"/>
                <input type="hidden" name="vidData" value="<?php echo $video['VID_URL'] . ':' . $video['VID_SOURCE']; ?>" id="utv-vid-data"/>
                <input type="submit" name="saveVideoEdit" id="saveVideoEdit" value="<?php _e('Save Changes', 'utvg') ?>" class="button-primary"/>
                <?php wp_nonce_field('utubevideo_edit_video'); ?>
                <a href="?page=utubevideo&view=album&id=<?php echo $pid[0]; ?>&pid=<?php echo $pid[1]; ?>" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
            </p>
        </form>
    </div>
</div>
<div class="utv-right-column">
    <div class="utv-formbox utv-top-formbox card">
        <div class="utv-flexvideo utv-flexvideo-16x9">
            <iframe id="utv-video-preview" src="<?php echo $iframeurl; ?>" allowfullscreen></iframe>
        </div>
    </div>
</div>
