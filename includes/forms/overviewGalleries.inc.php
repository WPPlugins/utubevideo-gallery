<?php

    require_once(dirname(__FILE__) . '/../../class/utvGalleryListTable.class.php');

    $galleries = new utvGalleryListTable();
    $galleries->prepare_items();

?>

<div class="utv-formbox">
    <p class="utv-actionbar">
        <a href="?page=utubevideo&view=gallerycreate" class="utv-link-submit-button"><?php _e('Create New Gallery', 'utvg'); ?></a>
    </p>
    <h3>Galleries</h3>

    <form method="post">

        <?php $galleries->display(); ?>

    </form>
</div>
<div class="postbox">
    <h3 class="hndle utv-postbox"><span><?php _e('FAQ\'s', 'utvg'); ?></span></h3>
    <div class="inside">
        <div class="utv-formbox">
            <ul>
                <li>For extra help with using uTubeVideo Gallery visit the <a href="http://www.codeclouds.net/utubevideo-gallery-documentation/" target="_blank">documentation page</a>.</li>
                <li>For any additional help or issues you may also contact me at <a href="mailto:dustin@codeclouds.net">dustin@codeclouds.net</a> or <a href="http://codeclouds.net/contact/">via my website</a>.</li>
            </ul>
        </div>
    </div>
</div>