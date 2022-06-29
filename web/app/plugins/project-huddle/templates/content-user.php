<div class="ph-user-form-wrapper">
    <div class="container">
        <div class="columns">
            <div class="column col-4 col-lg-6 col-md-8 col-sm-10 col-mx-auto">
                <h1 class="text-center">
                    <?php if ($logo = apply_filters('ph_login_logo_id', get_option('ph_login_logo'))) : ?>

                        <?php
                        // get logo image
                        $logo_image = wp_get_attachment_image_src($logo, 'full');

                        // check retina option
                        if (apply_filters('ph_login_logo_retina', get_option('ph_login_logo_retina'))) :
                            $logo_image[1] = $logo_image[1] / 2;
                            $logo_image[2] = $logo_image[2] / 2;
                        endif;
                        ?>

                        <a href="<?php echo home_url(); ?>">
                            <img src="<?php echo esc_url($logo_image[0]); ?>" width="<?php echo (float) $logo_image[1]; ?>" height="<?php echo (float) $logo_image[2]; ?>" />
                        </a>

                    <?php endif; ?>
                </h1>
                <?php $current_user = wp_get_current_user(); ?>
                <h5 class="text-center user-form-title"><?php echo sprintf(__('Hi, %s! You can update your email settings below.', 'project-huddle'), $current_user->display_name); ?></h5>
                <?php ph_render_message(); ?>
                <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                    <?php
                    // get emails
                    $options = PH()->emails->options(); ?>

                    <?php foreach ($options as $id => $option) : if (!$option['when']) continue; ?>

                        <?php $value = get_user_meta($current_user->ID, 'ph_' . $id, true); ?>

                        <!-- form switch control -->
                        <div class="form-group">
                            <label class="form-switch" data-cy="<?php echo esc_attr('toggle-' . $id); ?>">
                                <input data-cy="<?php echo esc_attr('check-' . $id); ?>" name="<?php echo esc_attr('ph_' . $id); ?>" type="checkbox" <?php echo $value !== 'off' ? 'checked' : ''; ?>>
                                <i class="form-icon"></i>
                                <span class="form-switch-text">
                                    <strong><?php echo esc_html($option['label']) ?></strong>
                                    <span class="text-gray setting-description"><?php echo esc_html($option['description']) ?></span>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <input type="hidden" name="action" value="ph_update_user_preferences">
                    <?php wp_nonce_field('ph_update_email_preferences', 'ph_user_preferences'); ?>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg"><?php _e('Update Preferences', 'project-huddle'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>