<?php

    $id = sanitize_key($_GET['id']);
	$gallery = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'utubevideo_dataset WHERE DATA_ID = ' . $id, ARRAY_A);

    if(!isset($gallery[0])){

        _e('Invalid Gallery ID', 'utvg');
        return;

    }

	$gallery = $gallery[0];

?>


<div class="utv-left-column">
	<div class="utv-formbox utv-top-formbox card">
		<form method="post">
			<h3><?php _e('Edit Gallery', 'utvg'); ?></h3>
			<p>
				<label><?php _e('Gallery Name:', 'utvg'); ?></label>
				<input type="text" name="galname" class="utv-required" value="<?php echo $gallery['DATA_NAME']; ?>"/>
				<span class="utv-hint"><?php _e('ex: name of gallery', 'utvg'); ?></span>
			</p>
			<p>
				<label for="albumSort"><?php _e('Album Sorting:', 'utvg'); ?></label>
				<select name="albumSort">
	
					<?php
	
					$opts = array(array('text' => __('Top to Bottom', 'utvg'), 'value' => 'asc'), array('text' => __('Bottom to Top', 'utvg'), 'value' => 'desc'));
	
					foreach($opts as $val)
					{
	
						if($val['value'] == $gallery['DATA_SORT'])
							echo '<option value="' . $val['value'] . '" selected>' . $val['text'] . '</option>';
						else
							echo '<option value="' . $val['value'] . '">' . $val['text'] . '</option>';
	
					}
	
					?>
	
				</select>
				<span class="utv-hint"><?php _e('ex: the order that albums will be displayed', 'utvg'); ?></span>
			</p>
			<p>
				<label><?php _e('Thumbnail Type:', 'utvg'); ?></label>
				<select name="thumbType"/>
				
				<?php
	
				$opts = array(array('text' => __('Rectangle', 'utvg'), 'value' => 'rectangle'), array('text' => __('Square', 'utvg'), 'value' => 'square'));
	
				foreach($opts as $val)
				{
	
					if($val['value'] == $gallery['DATA_THUMBTYPE'])
						echo '<option value="' . $val['value'] . '" selected>' . $val['text'] . '</option>';
					else
						echo '<option value="' . $val['value'] . '">' . $val['text'] . '</option>';
	
				}
	
				?>
	
				</select>
				<span class="utv-hint"><?php _e('ex: the type of thumbnail', 'utvg'); ?></span>
			</p>
			<p>
				<label for="displayType"><?php _e('Display Type:', 'utvg'); ?></label>
				<select name="displayType">
	
					<?php
	
					$opts = array(array('text' => __('Albums', 'utvg'), 'value' => 'album'), array('text' => __('Just Videos', 'utvg'), 'value' => 'video'));
	
					foreach($opts as $val)
					{
	
						if($val['value'] == $gallery['DATA_DISPLAYTYPE'])
							echo '<option value="' . $val['value'] . '" selected>' . $val['text'] . '</option>';
						else
							echo '<option value="' . $val['value'] . '">' . $val['text'] . '</option>';
	
					}
	
					?>
	
				</select>
				<span class="utv-hint"><?php _e('ex: display albums or just videos in gallery', 'utvg'); ?></span>
			</p>
			<p class="submit">
				<input type="hidden" name="key" value="<?php echo $id; ?>"/>
				<input type="hidden" name="oldThumbWidth" value="<?php echo $gallery['DATA_THUMBWIDTH']; ?>"/>
				<input type="submit" id="saveGalleryEdit" name="saveGalleryEdit" value="<?php _e('Save Changes', 'utvg') ?>" class="button-primary"/>
				<?php wp_nonce_field('utubevideo_edit_gallery'); ?>
				<a href="?page=utubevideo" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
			</p>
		</form>
	</div>
</div>