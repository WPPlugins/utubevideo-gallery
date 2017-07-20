<div class="utv-left-column">	
    <div class="utv-formbox utv-top-formbox card">
        <form method="post">
            <h3><?php _e('Create Gallery', 'utvg'); ?></h3>
            <p>
                <label for="galleryName"><?php _e('Gallery Name:', 'utvg'); ?></label>
                <input type="text" name="galleryName" class="utv-required"/>
                <span class="utv-hint"><?php _e('ex: name of gallery for your reference', 'utvg'); ?></span>
            </p>
            <p>
                <label for="albumSort"><?php _e('Album Sorting:', 'utvg'); ?></label>
                <select name="albumSort">
                    <option value="asc"><?php _e('Top to Bottom', 'utvg'); ?></option>
                    <option value="desc"><?php _e('Bottom to Top', 'utvg'); ?></option>
                </select>
                <span class="utv-hint"><?php _e('ex: the order that albums will be displayed', 'utvg'); ?></span>
            </p>      
            <p>
                <label><?php _e('Thumbnail Type:', 'utvg'); ?></label>
                <select name="thumbType"/>
                    <option value="rectangle"><?php _e('Rectangle', 'utvg'); ?></option>
                    <option value="square"><?php _e('Square', 'utvg'); ?></option>
                </select>
                <span class="utv-hint"><?php _e('ex: the type of thumbnail', 'utvg'); ?></span>
            </p>
            <p>
                <label for="displayType"><?php _e('Display Type:', 'utvg'); ?></label>
                <select name="displayType">
                    <option value="album"><?php _e('Albums', 'utvg'); ?></option>
                    <option value="video"><?php _e('Just Videos', 'utvg'); ?></option>
                </select>
                <span class="utv-hint"><?php _e('ex: display albums or just videos in gallery', 'utvg'); ?></span>
            </p>
            <p class="submit">
                <input type="submit" id="createGallery" name="createGallery" value="<?php _e('Save New Gallery', 'utvg') ?>" class="button-primary"/>
                <?php wp_nonce_field('utubevideo_save_gallery'); ?>
                <a href="?page=utubevideo" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
                <div style="clear: both;"></div>
            </p>
        </form>
    </div>
</div>