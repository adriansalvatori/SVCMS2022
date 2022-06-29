<?php 
$perpage = PalleonSettings::get_option('tp_pagination',21);  
$myperpage = PalleonSettings::get_option('mytp_pagination',10);
$templates = PalleonSettings::get_option('module_templates', 'enable'); 
$mytemplates = PalleonSettings::get_option('allow_json','enable');  
?>
<div id="modal-add-new" class="palleon-modal">
    <div class="palleon-modal-close" data-target="#modal-add-new"><span class="material-icons">close</span></div>
    <div class="palleon-modal-wrap">
        <div class="palleon-modal-inner large">
            <div class="palleon-tabs">
                <ul class="palleon-tabs-menu">
                    <li class="active" data-target="#modal-select-img"><span class="material-icons">file_upload</span><?php echo esc_html__('New Image', 'palleon'); ?></li>
                    <?php if ($templates == 'enable') { ?>
                    <li data-target="#modal-template-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Template Library', 'palleon'); ?></li>
                    <?php if ($mytemplates == 'enable' && is_admin()) { ?>
                    <li data-target="#modal-my-templates"><span class="material-icons">photo_library</span><?php echo esc_html__('My Templates', 'palleon'); ?></li>
                    <?php }
                    } ?>
                    <li data-target="#modal-blank-canvas"><span class="material-icons">crop_original</span><?php echo esc_html__('Blank Canvas', 'palleon'); ?></li>
                </ul>
                <div id="modal-select-img" class="palleon-tab active">
                    <div class="palleon-select-btn-block">
                        <div>
                            <div class="palleon-btn-set">
                                <div class="palleon-file-field">
                                    <input type="file" name="palleon-image-upload" id="palleon-image-upload" class="palleon-hidden-file" accept="image/png, image/jpeg" />
                                    <label for="palleon-image-upload" class="palleon-btn primary palleon-lg-btn"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload Image', 'palleon'); ?></span></label>
                                </div>
                                <?php if (is_admin()) { ?>
                                <button id="palleon-media-library" type="button" class="palleon-btn primary palleon-lg-btn palleon-modal-open" data-target="#modal-media-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Select From Media Library', 'palleon'); ?></button>
                                <?php } ?>
                            </div>
                            <div class="palleon-keep">
                                <label class="palleon-checkbox">
                                    <input id="keep-data" type="checkbox" autocomplete="off">
                                    <span class="palleon-checkmark"></span>
                                </label>
                                <div><?php echo esc_html__('Keep current objects and filters', 'palleon'); ?></div>
                            </div>
                        </div>
                        <div>
                            <?php if ($templates == 'enable') { ?>
                            <div class="palleon-file-field">
                                <input type="file" name="palleon-json-upload" id="palleon-json-upload" class="palleon-hidden-file" accept=".json,application/JSON" />
                                <label for="palleon-json-upload" class="palleon-btn primary palleon-lg-btn"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload Template', 'palleon'); ?></span></label>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php if ($templates == 'enable') { ?>
                <div id="modal-template-library" class="palleon-tab">
                    <div class="palleon-templates-wrap">
                        <div class="palleon-tabs">
                            <ul class="palleon-tabs-menu">
                                <li class="active" data-target="#palleon-all-templates"><?php echo esc_html__('All', 'palleon'); ?></li>
                                <?php if (is_admin()) { ?>
                                <li data-target="#palleon-templates-favorites"><?php echo esc_html__('My Favorites', 'palleon'); ?></li>
                                <?php } ?>
                            </ul>
                            <div id="palleon-all-templates" class="palleon-tab active">
                                <div class="palleon-templates-menu-wrap">
                                    <input id="palleon-template-search-keyword" type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Search by keyword...', 'palleon'); ?>" autocomplete="off" />
                                    <select id="palleon-templates-menu" class="palleon-select palleon-select2" autocomplete="off">
                                        <option value="all" selected><?php echo esc_html__('All Categories', 'palleon') . ' (' . palleon_get_template_count() . ')'; ?></option>
                                        <?php 
                                        $getTags = palleon_get_template_tags();
                                        foreach($getTags as $slug => $name) {
                                            echo '<option value="' . esc_attr($slug) . '">' . esc_attr($name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <button id="palleon-template-search" type="button" class="palleon-btn primary"><span class="material-icons">search</span></button>
                                </div>
                                <div class="palleon-templates-content">
                                    <div class="palleon-grid-wrap">
                                        <div id="palleon-templates-grid" class="palleon-grid template-grid template-selection paginated" data-perpage="<?php echo esc_attr($perpage); ?>">
                                            <?php 
                                            $user_fav = get_user_meta(get_current_user_id(), 'palleon_template_fav',true);
                                            if (empty($user_fav)) {
                                                $user_fav = array();
                                            }
                                            $getTemplates = palleon_get_templates(false);
                                            foreach($getTemplates as $template) { 
                                            $btn_class = '';
                                            $icon = 'star_border';
                                            if (in_array($template[0], $user_fav)) {
                                                $btn_class = 'favorited';
                                                $icon = 'star';
                                            }
                                            ?>
                                            <div class="grid-item">
                                                <div class="template-favorite"><button type="button" class="palleon-btn-simple star <?php echo esc_attr($btn_class); ?>" data-templateid="<?php echo esc_attr($template[0]); ?>"><span class="material-icons"><?php echo esc_html($icon); ?></span></button></div>
                                                <div class="palleon-masonry-item-inner palleon-select-template" data-json="<?php echo esc_url($template[3]); ?>">
                                                    <div class="palleon-img-wrap">
                                                        <div class="palleon-img-loader"></div>
                                                        <img class="lazy" data-src="<?php echo esc_url($template[2]); ?>" title="<?php echo esc_attr($template[1]); ?>" />
                                                    </div>
                                                    <div class="palleon-masonry-item-desc">
                                                    <?php echo esc_html($template[1]); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if (is_admin()) { ?>
                            <div id="palleon-templates-favorites" class="palleon-tab">
                                <div id="palleon-templates-favorites-grid" class="palleon-grid template-grid template-selection paginated" data-perpage="<?php echo esc_attr($perpage); ?>">
                                    <?php 
                                    $templates = palleon_get_templates(false);
                                    $user_fav = get_user_meta(get_current_user_id(), 'palleon_template_fav',true);
                                    if (!empty($user_fav)) {
                                        foreach($templates as $template) { 
                                            if (in_array($template[0], $user_fav)) { ?>
                                            <div class="grid-item">
                                                <div class="template-favorite"><button type="button" class="palleon-btn-simple star favorited" data-templateid="<?php echo esc_url($template[0]); ?>"><span class="material-icons">star</span></button></div>
                                                <div class="palleon-masonry-item-inner palleon-select-template" data-json="<?php echo esc_url($template[3]); ?>">
                                                    <div class="palleon-img-wrap">
                                                        <div class="palleon-img-loader"></div>
                                                        <img class="lazy" data-src="<?php echo esc_url($template[2]); ?>" title="<?php echo esc_attr($template[1]); ?>" />
                                                    </div>
                                                    <div class="palleon-masonry-item-desc">
                                                    <?php echo esc_html($template[1]); ?>
                                                    </div>
                                                </div>
                                            </div>  
                                        <?php }
                                        }
                                    } else {
                                        echo '<div class="notice notice-info"><h6>' . esc_html__( 'No favorites yet', 'palleon' ) . '</h6>' . esc_html__('Click the star icon on any template, and you will see it here next time you visit.', 'palleon') . '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php if ($mytemplates == 'enable' && is_admin()) { ?>
                <div id="modal-my-templates" class="palleon-tab">
                    <div id="palleon-my-templates-menu">
                        <div>
                            <button id="palleon-my-templates-refresh" type="button" class="palleon-btn primary"><span class="material-icons">refresh</span><?php echo esc_html__('Refresh', 'palleon'); ?></button>
                        </div>
                        <div class="palleon-search-box">
                            <input type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Search by title...', 'palleon'); ?>" autocomplete="off" />
                            <button id="palleon-my-templates-search" type="button" class="palleon-btn primary"><span class="material-icons">search</span></button>
                        </div>
                    </div>
                <?php
                    echo '<ul id="palleon-my-templates" class="palleon-template-list template-selection paginated" data-perpage="' . esc_attr($myperpage) . '">';
                    $my_temps_args = array(
                        'post_type'      => 'attachment',
                        'post_mime_type' => 'application/json',
                        'post_status'    => 'inherit',
                        'posts_per_page' => - 1,
                        'author' => get_current_user_id()
                    );  
                    $my_temps = new WP_Query( $my_temps_args );
                    if($my_temps->have_posts()) {
                        foreach ( $my_temps->posts as $temp ) {
                            $temp_ID = $temp->ID;
                            $temp_url = wp_get_attachment_url($temp_ID);
                            $temp_title = get_the_title($temp_ID);
                            ?>
                            <li data-keyword="<?php echo esc_attr($temp_title); ?>">
                                <div><?php echo esc_html($temp_title); ?></div>
                                <div>
                                    <button type="button" class="palleon-btn primary palleon-select-template" data-json="<?php echo esc_url($temp_url); ?>"><span class="material-icons">check</span><?php echo esc_html__('Select', 'palleon'); ?></button>
                                    <button type="button" class="palleon-btn danger palleon-template-delete" data-target="<?php echo esc_attr($temp_ID); ?>"><span class="material-icons">clear</span><?php echo esc_html__('Delete', 'palleon'); ?></button>
                                </div>
                            </li>
                            <?php
                        }
                    }
                    echo '</ul>';
                ?>
                <div id="palleon-my-templates-noimg" class="notice notice-warning <?php if($my_temps->have_posts()) { echo 'd-none'; } ?>"><?php echo esc_html__('Nothing found.', 'palleon'); ?></div>
                <script>
                    /* <![CDATA[ */
                    var templatesMyRefresh = {
                        'posts':'<?php echo json_encode( $my_temps->query_vars ); ?>'
                    };
                    /* ]]> */
                </script>
                <?php wp_reset_postdata(); ?>
                </div>    
                <?php }
                } ?>
                <div id="modal-blank-canvas" class="palleon-tab">
                    <div class="palleon-control-group">
                        <div>
                            <label><?php echo esc_html__('Size', 'palleon'); ?></label>
                            <select id="palleon-canvas-size-select" class="palleon-select" autocomplete="off">
                                <option selected value="custom"><?php echo esc_html__('Custom', 'palleon'); ?></option>
                                <option value="blog-banner"><?php echo esc_html__('Blog Banner', 'palleon'); ?></option>
                                <option value="fb-cover"><?php echo esc_html__('Facebook Cover', 'palleon'); ?></option>
                                <option value="fb-ad"><?php echo esc_html__('Facebook Ad', 'palleon'); ?></option>
                                <option value="instagram"><?php echo esc_html__('Instagram Post', 'palleon'); ?></option>
                                <option value="pinterest"><?php echo esc_html__('Pinterest Post', 'palleon'); ?></option>
                                <option value="fb-post"><?php echo esc_html__('Facebook Post', 'palleon'); ?></option>
                                <option value="twitter-post"><?php echo esc_html__('Twitter Post', 'palleon'); ?></option>
                                <option value="youtube"><?php echo esc_html__('Youtube Thumbnail', 'palleon'); ?></option>
                                <option value="wallpaper"><?php echo esc_html__('Wallpaper', 'palleon'); ?></option>
                            </select>
                        </div>
                        <div>
                            <label><?php echo esc_html__('Width', 'palleon'); ?></label>
                            <input id="palleon-canvas-width" class="palleon-form-field" type="number" value="800" data-min="1" data-max="10000" autocomplete="off">
                        </div>
                        <div>
                            <label><?php echo esc_html__('Height', 'palleon'); ?></label>
                            <input id="palleon-canvas-height" class="palleon-form-field" type="number" value="800" data-min="1" data-max="10000" autocomplete="off">
                        </div>
                        <div>
                            <label><?php echo esc_html__('Background', 'palleon'); ?></label>
                            <input id="palleon-canvas-color" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                        </div>
                        <div>
                            <button id="palleon-canvas-create" type="button" class="palleon-btn primary"><span class="material-icons">done</span><?php echo esc_html__('Create', 'palleon'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>