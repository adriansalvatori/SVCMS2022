<?php 
$allowSVG =  PalleonSettings::get_option('allow_svg', 'enable');
if ($allowSVG == 'enable') {
    $perpage = PalleonSettings::get_option('ml_pagination',18); 
    $other_images = PalleonSettings::get_option('other_images','enable');    
?>
<div id="modal-svg-library" class="palleon-modal">
    <div class="palleon-modal-close" data-target="#modal-svg-library"><span class="material-icons">close</span></div>
    <div class="palleon-modal-wrap">
        <div class="palleon-modal-inner">
            <div class="palleon-tabs">
                <ul class="palleon-tabs-menu">
                    <li class="active" data-target="#svg-library-my-images"><span class="material-icons">photo_library</span><?php echo esc_html__('My SVGs', 'palleon'); ?></li>
                    <?php if ($other_images != 'disable') { ?>
                    <li data-target="#svg-library-all-images"><span class="material-icons">photo_library</span><?php echo esc_html__('Other SVGs', 'palleon'); ?></li>
                    <?php } ?>
                </ul>
                <div id="svg-library-my-images" class="palleon-tab active">
                    <div id="palleon-svg-library-my-menu">
                        <div>
                        <form class="uploadSVGToLibrary" enctype="multipart/form-data">
                            <div class="palleon-file-field">
                                <input type="file" name="palleon-svg-library-upload-img" id="palleon-svg-library-upload-img" class="palleon-hidden-file" accept="image/svg+xml" />
                                <label for="palleon-svg-library-upload-img" class="palleon-btn primary"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload Image', 'palleon'); ?></span></label>
                            </div>
                            </form>
                            <button id="palleon-svg-library-my-refresh" type="button" class="palleon-btn primary"><span class="material-icons">refresh</span><?php echo esc_html__('Refresh', 'palleon'); ?></button>
                        </div>
                        <div class="palleon-search-box">
                            <input type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Search by title...', 'palleon'); ?>" autocomplete="off" />
                            <button id="palleon-svg-library-my-search" type="button" class="palleon-btn primary"><span class="material-icons">search</span></button>
                        </div>
                    </div>
                    <div id="palleon-svg-library-my" class="palleon-grid svg-library-grid paginated" data-perpage="<?php echo esc_attr($perpage); ?>">
                        <?php 
                        $my_images_args = array(
                            'post_type'      => 'attachment',
                            'post_mime_type' => 'image/svg+xml',
                            'post_status'    => 'inherit',
                            'posts_per_page' => - 1,
                            'author' => get_current_user_id()
                        );  
                        $my_images = new WP_Query( $my_images_args );
                        if($my_images->have_posts()) {
                        foreach ( $my_images->posts as $image ) { 
                            $id = $image->ID;
                            $thumb = wp_get_attachment_image_url($id, 'thumbnail', false);
                            $full = wp_get_attachment_image_url($id, 'full', false);
                            $title = get_the_title($id);
                        ?>
                        <div class="palleon-masonry-item" data-keyword="<?php echo esc_attr($title); ?>">
                            <div class="palleon-svg-library-delete" data-target="<?php echo esc_attr($id); ?>"><span class="material-icons">remove</span></div>
                            <div class="palleon-masonry-item-inner">
                                <img class="lazy" data-src="<?php echo esc_url($thumb); ?>" data-full="<?php echo esc_url($full); ?>" data-id="<?php echo esc_attr($id); ?>" data-filename="<?php echo esc_attr($title); ?>" alt="<?php echo esc_attr($title); ?>" />
                                <?php if (!empty($title)) { ?>
                                <div class="palleon-masonry-item-desc">
                                    <?php echo esc_html($title); ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php }} ?>
                    </div>
                    <div id="palleon-svg-library-my-noimg" class="notice notice-warning <?php if($my_images->have_posts()) { echo 'd-none'; } ?>"><?php echo esc_html__('Nothing found.', 'palleon'); ?></div>
                    <script>
                    /* <![CDATA[ */
                    var SVGlibraryMyRefresh = {
                        'posts':'<?php echo json_encode( $my_images->query_vars ); ?>'
                    };
                    /* ]]> */
                    </script>
                    <?php wp_reset_postdata(); ?>
                </div>
                <?php if ($other_images != 'disable') { ?>
                <div id="svg-library-all-images" class="palleon-tab">
                    <div id="palleon-svg-library-all-menu">
                        <div>
                            <button id="palleon-svg-library-all-refresh" type="button" class="palleon-btn primary"><span class="material-icons">refresh</span><?php echo esc_html__('Refresh', 'palleon'); ?></button>
                        </div>
                        <div class="palleon-search-box">
                            <input type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Search by title...', 'palleon'); ?>" autocomplete="off" />
                            <button id="palleon-svg-library-all-search" type="button" class="palleon-btn primary"><span class="material-icons">search</span></button>
                        </div>
                    </div>
                    <div id="palleon-svg-library-all" class="palleon-grid svg-library-grid paginated" data-perpage="<?php echo esc_attr($perpage); ?>">
                        <?php 
                        $query_images_args = array(
                            'post_type'      => 'attachment',
                            'post_mime_type' => 'image/svg+xml',
                            'post_status'    => 'inherit',
                            'posts_per_page' => - 1,
                            'author__not_in' => get_current_user_id()
                        );  
                        $query_images = new WP_Query( $query_images_args );
                        if($query_images->have_posts()) {
                        foreach ( $query_images->posts as $image ) { 
                            $id = $image->ID;
                            $thumb = wp_get_attachment_image_url($id, 'thumbnail', false);
                            $full = wp_get_attachment_image_url($id, 'full', false);
                            $title = get_the_title($id);
                        ?>
                        <div class="palleon-masonry-item" data-keyword="<?php echo esc_attr($title); ?>">
                            <div class="palleon-masonry-item-inner">
                                <img class="lazy" data-src="<?php echo esc_url($thumb); ?>" data-full="<?php echo esc_url($full); ?>" data-id="<?php echo esc_attr($id); ?>" data-filename="<?php echo esc_attr($title); ?>" alt="<?php echo esc_attr($title); ?>" />
                                <?php if (!empty($title)) { ?>
                                <div class="palleon-masonry-item-desc">
                                    <?php echo esc_html($title); ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php }}?>
                    </div>
                    <div id="palleon-svg-library-all-noimg" class="notice notice-warning <?php if($query_images->have_posts()) { echo 'd-none'; } ?>"><strong><?php echo esc_html__('Nothing found.', 'palleon'); ?></strong></div>
                    <script>
                    /* <![CDATA[ */
                    var SVGlibraryAllRefresh = {
                        'posts':'<?php echo json_encode( $query_images->query_vars ); ?>'
                    };
                    /* ]]> */
                    </script>
                    <?php wp_reset_postdata(); ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>