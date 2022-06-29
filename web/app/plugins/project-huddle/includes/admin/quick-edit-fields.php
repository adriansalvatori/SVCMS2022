<?php

class PH_Quick_Edit_Fields
{
    public function __construct()
    {
        add_action('quick_edit_custom_box', [$this, 'page_url_field'], 10, 2);
        add_action('quick_edit_custom_box', [$this, 'parent_id_field'], 10, 2);
        add_action('save_post', [$this, 'quicksave_post'], 10, 2);
        add_action('admin_print_footer_scripts-edit.php', [$this, 'quickedit_javascript']);
        add_filter('post_row_actions', [$this, 'quickedit_set_data'], 10, 2);
    }

    public function quickedit_set_data($actions, $post)
    {
        $found_value = get_post_meta($post->ID, 'page-url', true);

        if ($found_value) {
            if (isset($actions['inline hide-if-no-js'])) {
                $new_attribute = sprintf('data-page-url="%s"', esc_attr($found_value));
                $actions['inline hide-if-no-js'] = str_replace('class=', "$new_attribute class=", $actions['inline hide-if-no-js']);
            }
        }

        $found_value = esc_url(get_post_meta($post->ID, 'parent-id', true));

        if ($found_value) {
            if (isset($actions['inline hide-if-no-js'])) {
                $new_attribute = sprintf('data-parent-id="%s"', esc_attr($found_value));
                $actions['inline hide-if-no-js'] = str_replace('class=', "$new_attribute class=", $actions['inline hide-if-no-js']);
            }
        }

        return $actions;

        return $actions;
    }

    public function quickedit_javascript()
    {
        $current_screen = get_current_screen();
        if ($current_screen->id != 'edit-phw_comment_loc' || $current_screen->post_type != 'phw_comment_loc')
            return;

        // Ensure jQuery library loads
        wp_enqueue_script('jquery');
?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                // we create a copy of the WP inline edit post function
                var $wp_inline_edit = inlineEditPost.edit;

                // and then we overwrite the function with our own code
                inlineEditPost.edit = function(id) {
                    // "call" the original WP edit function
                    // we don't want to leave WordPress hanging
                    $wp_inline_edit.apply(this, arguments);

                    // now we take care of our business
                    // get the post ID
                    var $post_id = 0;
                    if (typeof(id) == 'object') {
                        $post_id = parseInt(this.getId(id));
                    }

                    if ($post_id > 0) {
                        // define the edit row
                        var $edit_row = $('#edit-' + $post_id);
                        var $post_row = $('#post-' + $post_id);

                        // get the data
                        var $page_url = $('.column-url', $post_row).text();
                        var $parent_id = $('.column-page a', $post_row).data('id');

                        // populate the data
                        $(':input[name="ph_parent_id"]', $edit_row).val($parent_id);
                        $(':input[name="ph_page_url"]', $edit_row).val($page_url);
                    }
                };
            });
        </script>
    <?php
    }

    public function quicksave_post($post_id, $post)
    {
        // if called by autosave, then bail here
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        // if this "post" post type?
        if ($post->post_type != 'phw_comment_loc')
            return;

        // does this user have permissions?
        if (!current_user_can('edit_post', $post_id))
            return;

        // update!
        if (isset($_POST['ph_page_url'])) {
            update_post_meta($post_id, 'page_url', esc_url_raw($_POST['ph_page_url']));
        }
        if (isset($_POST['ph_parent_id'])) {
            update_post_meta($post_id, 'parent_id', (int) $_POST['ph_parent_id']);
        }
    }

    public function page_url_field($column_name, $post_type)
    {
        if ('url' != $column_name)
            return; ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php esc_html_e('Page Url', 'generatewp'); ?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="ph_page_url" class="ph-page-url" value="">
                    </span>
                </label>
            </div>
        </fieldset>

    <?php
    }

    public function parent_id_field($column_name)
    {
        if ('page' != $column_name)
            return;
    ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php esc_html_e('Page ID', 'generatewp'); ?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="ph_parent_id" class="ph-parent-id" value="">
                    </span>
                </label>
            </div>
        </fieldset>
<?php
    }
}
new PH_Quick_Edit_Fields();
