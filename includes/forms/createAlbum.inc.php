<?php

    $id = sanitize_key($_GET['id']);

?>

<div class="utv-left-column">	
    <div class="utv-formbox utv-top-formbox card">
        <form method="post">
            <h3><?php _e('Create Video Album', 'utvg'); ?></h3>
            <p>
                <label for="alname"><?php _e('Album Name:', 'utvg'); ?></label>
                <input type="text" name="alname" class="utv-required"/>
                <span class="utv-hint"><?php _e('ex: name of video album', 'utvg'); ?></span>
            </p>
            <p>
                <label for="vidsort"><?php _e('Video Sorting:', 'utvg'); ?></label>
                <select name="vidSort">
                    <option value="asc"><?php _e('Top to Bottom', 'utvg'); ?></option>
                    <option value="desc"><?php _e('Bottom to Top', 'utvg'); ?></option>
                </select>
                <span class="utv-hint"><?php _e('ex: the order that videos will be displayed', 'utvg'); ?></span>
            </p>
            <p class="submit">
                <input type="submit" id="saveAlbum" name="saveAlbum" value="<?php _e('Save New Album','utvg') ?>" class="button-primary"/>
                <input type="hidden" name="key" value="<?php echo $id; ?>"/>
                <?php wp_nonce_field('utubevideo_save_album'); ?>
                <a href="?page=utubevideo&view=gallery&id=<?php echo $id; ?>" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
            </p>
        </form>
    </div>
</div>