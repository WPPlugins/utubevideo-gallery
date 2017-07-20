<?php

    $id = sanitize_key($_GET['id']);
    $pid = sanitize_key($_GET['pid']);

    $album = $wpdb->get_results('SELECT ALB_ID, ALB_NAME, ALB_VIDCOUNT FROM ' . $wpdb->prefix . 'utubevideo_album WHERE ALB_ID = "' . $id . '"', ARRAY_A);

    if(!isset($album[0])){

        _e('Invalid Album ID', 'utvg');
        return;

    }

    $album = $album[0];

    require_once(dirname(__FILE__) . '/../../class/utvVideoListTable.class.php');

    $videos = new utvVideoListTable($id);
    $videos->prepare_items();

?>

<div id="utv-view-album" class="utv-formbox utv-top-formbox">
    <form method="post">
        <p class="submit utv-actionbar">
            <a href="?page=utubevideo&view=videoadd&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>" class="utv-link-submit-button"><?php _e('Add Video', 'utvg'); ?></a>
            <a href="?page=utubevideo&view=playlistadd&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>" class="utv-link-submit-button"><?php _e('Add Playlist', 'utvg'); ?></a>
            <a href="?page=utubevideo&view=album&id=<?php echo $id; ?>&pid=<?php echo $pid; ?>" class="utv-ok"><?php _e('Clear Sort', 'utvg'); ?></a>
            <a href="?page=utubevideo&view=gallery&id=<?php echo $pid; ?>" class="utv-cancel"><?php _e('Go Back', 'utvg'); ?></a>
        </p>
    </form>
    <h3 class="utv-h3"><?php _e('Videos for Album', 'utvg'); ?></h3>
    <span class="utv-sub-h3"> ( <?php echo stripslashes($album['ALB_NAME']); ?> ) - <span id="utv-video-count"><?php echo $album['ALB_VIDCOUNT']; ?></span> <?php _e('videos', 'utvg'); ?></span>
    <form method="post">

        <?php $videos->display(); ?>

    </form>
</div>