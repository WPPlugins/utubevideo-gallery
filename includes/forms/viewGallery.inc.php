<?php

    $id = sanitize_key($_GET['id']);
    $gallery = $wpdb->get_results('SELECT DATA_NAME, DATA_ALBCOUNT	 FROM ' . $wpdb->prefix . 'utubevideo_dataset WHERE DATA_ID = "' . $id . '"', ARRAY_A);

    if(!isset($gallery[0])){

        _e('Invalid Gallery ID', 'utvg');
        return;

    }

    $gallery = $gallery[0];

    require_once(dirname(__FILE__) . '/../../class/utvAlbumListTable.class.php');

    $albums = new utvAlbumListTable($id);
    $albums->prepare_items();

?>

<div id="utv-view-gallery" class="utv-formbox utv-top-formbox">
    <form method="post">
        <p class="submit utv-actionbar">
            <a href="?page=utubevideo&view=albumcreate&id=<?php echo $id; ?>" class="utv-link-submit-button"><?php _e('Create New Album', 'utvg'); ?></a>
            <a href="?page=utubevideo&view=gallery&id=<?php echo $id; ?>" class="utv-ok"><?php _e('Clear Sort', 'utvg'); ?></a>
            <a href="?page=utubevideo" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
        </p>
    </form>
    <h3 class="utv-h3"><?php _e('Video Albums for Gallery', 'utvg'); ?></h3>
    <span class="utv-sub-h3"> ( <?php echo $gallery['DATA_NAME']; ?> ) - <span id="utv-album-count"><?php echo $gallery['DATA_ALBCOUNT']; ?></span> <?php _e('albums', 'utvg'); ?></span>
    <form method="post">

        <?php $albums->display(); ?>

    </form>
</div>