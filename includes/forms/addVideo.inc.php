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
            <h3><?php echo __('Add New Video to', 'utvg') . ' ' . '<span class="utv-sub-h3">( ' . stripslashes($data['ALB_NAME']) . ' )</span>'; ?></h3>
            <p>
                <label><?php _e('Source:', 'utvg'); ?></label>
                <select name="videoSource" id="utv-videoadd-source"/>
                    <option value="youtube">Youtube</option>
                    <option value="vimeo">Vimeo</option>
                </select>
                <span class="utv-hint"><?php _e('ex: the source of the video', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('URL:', 'utvg'); ?></label>
                <input type="text" name="url" id="utv-videoadd-url" class="utv-required"/>
                <span class="utv-hint"><?php _e('ex: video url', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Name:', 'utvg'); ?></label>
                <input type="text" name="vidname" id="utv-videoadd-name"/>
                <span class="utv-hint"><?php _e('ex: the name for the video', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Quality:', 'utvg'); ?></label>
                <select name="videoQuality" id="utv-videoadd-quality"/>
                    <option value="large">480p</option>
                    <option value="hd720">720p</option>
                    <option value="hd1080">1080p</option>
                </select>
                <span class="utv-hint"><?php _e('ex: the starting quality of the video', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Chromeless:', 'utvg'); ?></label>
                <input type="checkbox" name="videoChrome" id="utv-videoadd-chrome"/>
                <span class="utv-hint"><?php _e('ex: hide the playback controls of the video', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('Start Time:', 'utvg'); ?></label>
                <input type="text" name="startTime" id="utv-videoadd-starttime"/>
                <span class="utv-hint"><?php _e('ex: start time of video (in seconds)', 'utvg'); ?></span>
            </p>
            <p>
                <label><?php _e('End Time:', 'utvg'); ?></label>
                <input type="text" name="endTime" id="utv-videoadd-endtime"/>
                <span class="utv-hint"><?php _e('ex: end time of video (in seconds)', 'utvg'); ?></span>
            </p>       
            <p class="submit">
                <input type="hidden" name="key" value="<?php echo $id; ?>"/>
                <input type="submit" name="addVideo" id="utv-videoadd-submit" value="<?php _e('Save New Video', 'utvg') ?>" class="button-primary"/>
                <?php wp_nonce_field('utubevideo_add_video'); ?>
                <a href="?page=utubevideo&view=album&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
            </p>
        </form>
    </div>
</div>
<div class="utv-right-column">
    <div class="utv-formbox utv-top-formbox card">
        <div class="utv-flexvideo utv-flexvideo-16x9">
            <iframe id="utv-video-preview" src="" allowfullscreen></iframe>	
        </div>	
    </div>
</div>