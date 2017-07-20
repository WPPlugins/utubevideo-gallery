<?php

    $id = sanitize_key($_GET['id']);
    $pid = sanitize_key($_GET['pid']);
    $data = $wpdb->get_results('SELECT ALB_NAME FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_ID = ' . $id, ARRAY_A);

    if(!isset($data[0])){

        _e('Invalid Album ID', 'utvg');
        return;

    }

    $data = $data[0];

?>

<div class="utv-left-column">
    <div class="utv-formbox utv-top-formbox card">
        <form method="post">
            <h3><?php echo __('Add New Playlist to', 'utvg') . ' ' . '<span class="utv-sub-h3">( ' . stripslashes($data['ALB_NAME']) . ' )</span>'; ?></h3>
            <p>
                <label><?php _e('Source:', 'utvg'); ?></label>
                <select id="utv-playlistadd-source" name="playlistSource"/>
                    <option value="youtube">Youtube</option>
                    <option value="vimeo">Vimeo</option>
                </select>
                <span class="utv-hint"><?php _e('ex: the source of the video', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('URL:', 'utvg'); ?></label>
                <input type="text" id="utv-playlistadd-url" name="url" class="utv-required"/>
                <span class="utv-hint"><?php _e('ex: YouTube or Vimeo playlist url', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Quality:', 'utvg'); ?></label>
                <select id="utv-playlistadd-quality" name="videoQuality"/>
                    <option value="large">480p</option>
                    <option value="hd720">720p</option>
                    <option value="hd1080">1080p</option>
                </select>
                <span class="utv-hint"><?php _e('ex: the starting quality of the playlist videos', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Chromeless:', 'utvg'); ?></label>
                <input type="checkbox" id="utv-playlistadd-chrome" name="videoChrome" />
                <span class="utv-hint"><?php _e('ex: hide the playback controls of the video', 'utvg'); ?></span>
            </p>
            <p class="submit">
                <input type="hidden" name="key" value="<?php echo $id; ?>"/>
                <input type="hidden" name="videolistIds" id="utv-playlist-add-ids"/>
                <input type="hidden" name="videolistTitles" id="utv-playlist-add-titles"/>
                <input type="hidden" name="videolistThumbURLs" id="utv-playlist-add-thumburls"/>
                <input type="submit" name="addPlaylist" id="utv-add-playlist" value="<?php _e('Save New Playlist', 'utvg') ?>" class="button-primary"/>
                <?php wp_nonce_field('utubevideo_add_playlist'); ?>
                <a href="?page=utubevideo&view=album&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
            </p>
            <p>
              <span id="utv-add-playlist-message" class="utv-js-errors"></span>
            </p>
        </form>
    </div>
</div>
<div class="utv-right-column">
    <div class="utv-formbox utv-top-formbox card">
        <h3><?php echo __('Playlist Items:', 'utvg'); ?><span class="utv-playlist-preview-count">( <span id="utv-playlist-preview-selectedcount">0</span> / <span id="utv-playlist-preview-totalcount">0</span> selected )</span></h3>
        <div id="utv-playlist-preview"></div>
        <img id="utv-playlist-preview-loader" class="utv-hide" src="<?php echo plugins_url('utubevideo-gallery/i/hex-loader.gif'); ?>"/>
        <span id="utv-playlist-preview-empty" class="utv-hide"><?php _e('No videos loaded yet', 'utvg'); ?></span>
    </div>
</div>
