 <?php

    $id = sanitize_key($_GET['id']);
    $pid = sanitize_key($_GET['pid']);

	if(!isset($id) || !isset($pid)){

		_e('Invalid Album data', 'utvg');
        return;

	}

    $dir = wp_upload_dir()['baseurl'];

    $album = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_ID = ' . $id, ARRAY_A);
	$galleries = $wpdb->get_results('SELECT DATA_ID, DATA_NAME FROM ' . $wpdb->prefix . 'utubevideo_dataset ORDER BY DATA_NAME', ARRAY_A);

    if(!isset($album[0])){

        _e('Invalid Album ID', 'utvg');
        return;

    }

    $album = $album[0];

    $thumbs = $wpdb->get_results('SELECT VID_ID, VID_URL FROM ' . $wpdb->prefix . 'utubevideo_video WHERE ALB_ID = ' . $id, ARRAY_A);

?>

<div class="utv-left-column">
    <div class="utv-formbox utv-top-formbox card">
        <form method="post">
            <h3><?php _e('Edit Video Album', 'utvg'); ?></h3>
            <p>
                <img src="<?php echo $dir . '/utubevideo-cache/' . $album['ALB_THUMB'] . '.jpg';?>" class="utv-preview-thumb" id="utv-album-preview-thumb"/>
            </p>
            <p>
                <label><?php _e('Album Name:', 'utvg'); ?></label>
                <input type="text" name="alname" class="utv-required" value="<?php echo stripslashes($album['ALB_NAME']); ?>"/>
                <span class="utv-hint"><?php _e('ex: name of album', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Slug:', 'utvg'); ?></label>
                <input type="text" name="slug" class="utv-required" value="<?php echo stripslashes($album['ALB_SLUG']); ?>"/>
                <span class="utv-hint"><?php _e('ex: permalink slug for album', 'utvg'); ?></span>
            </p>
			<p>
	   			<label><?php _e('Album Gallery:', 'utvg'); ?></label>
	   	 		<select name="albumGallery">

		   	 	<?php

		   	 	foreach($galleries as $gallery)
		   	 	{

		   		 	if($gallery['DATA_ID'] == $album['DATA_ID'])
		   			 	echo '<option value="' . $gallery['DATA_ID'] . '" selected>' . $gallery['DATA_NAME'] . '</option>';
		   		 	else
		   			 	echo '<option value="' . $gallery['DATA_ID'] . '">' . $gallery['DATA_NAME'] . '</option>';

		   	 	}

		   	 	?>

		   	 	</select>
		   	 	<span class="utv-hint"><?php _e('ex: the gallery containing this album', 'utvg'); ?></span>
    		</p>
            <p>
                <label><?php _e('Video Sorting:', 'utvg'); ?></label>
                <select name="vidSort">

                <?php

                $opts = array(array('text' => __('Top to Bottom', 'utvg'), 'value' => 'asc'), array('text' => __('Bottom to Top', 'utvg'), 'value' => 'desc'));

                foreach($opts as $val)
                {

                    if($val['value'] == $album['ALB_SORT'])
                        echo '<option value="' . $val['value'] . '" selected>' . $val['text'] . '</option>';
                    else
                        echo '<option value="' . $val['value'] . '">' . $val['text'] . '</option>';

                }

                ?>

                </select>
                <span class="utv-hint"><?php _e('ex: the order that videos will be displayed', 'utvg'); ?></span>
            </p>
            <p class="submit">
                <input type="hidden" name="key" value="<?php echo $id; ?>"/>
                <input type="hidden" name="prevSlug" value="<?php echo $album['ALB_SLUG']; ?>"/>
                <input type="hidden" name="albumThumbSelect" id="utv-album-selected-thumb" value="<?php echo $album['ALB_THUMB']; ?>"/>
                <input type="submit" name="saveAlbumEdit" id="saveAlbumEdit" value="<?php _e('Save Changes', 'utvg') ?>" class="button-primary"/>
                <?php wp_nonce_field('utubevideo_edit_album'); ?>
                <a href="?page=utubevideo&view=gallery&id=<?php echo $pid; ?>" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
            </p>
        </form>
    </div>
</div>
<div class="utv-right-column">
    <div class="utv-formbox utv-top-formbox card">
        <p>
            <label style="width: auto; margin-right: 10px;"><?php _e('Album Thumbnail:', 'utvg'); ?></label>
            <span class="utv-hint" style="display: inline-block; margin: 0;"><?php _e('ex: choose the thumbnail for the album', 'utvg'); ?></span>
            <div id="utv-album-thumb-select">

            <?php

            if(!empty($thumbs))
            {

                foreach($thumbs as $val)
                {

                ?>

                    <div class="utv-album-thumb-choice <?php echo ($album['ALB_THUMB'] == $val['VID_URL'] . $val['VID_ID'] ? 'utv-album-thumb-choice-active' : ''); ?>">
                        <img src="<?php echo $dir . '/utubevideo-cache/' . $val['VID_URL'] . $val['VID_ID'] . '.jpg';?>" class="utv-preview-thumb"/>
                        <span class="utv-album-thumb-overlay"></span>
                    </div>

                <?php

                }

            }
            else
                echo '<span class="utv-admin-error">' . __('Oops, you have not added any videos to this album yet', 'utvg') . '</span>';

            ?>

            </div>
        </p>
    </div>
</div>
