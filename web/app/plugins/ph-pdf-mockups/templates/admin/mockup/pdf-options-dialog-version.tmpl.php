<?php
/**
 * The Template for displaying pdf options dialog
 *
 * This template can be overridden by copying it to yourtheme/project-huddle/admin/mockup/pdf-options-dialog-version.tmpl.php.
 *
 * HOWEVER, on occasion ProjectHuddle will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.projecthuddle.io/article/15-overriding-templates-via-a-theme
 * @author 		Projecthuddle
 * @package 	ProjectHuddle/Templates
 * @version     3.1.0
 */
?>

<!-- The modal / dialog box, hidden somewhere near the footer -->
<div id="ph-pdf-dialog-version" class="hidden" style="max-width:800px" title="<?php esc_attr_e('New PDF Version Options', 'ph-pdf-mockups' ); ?>">
    <p>
        <label for="pdf-width-number">
            <?php _e('Set the width of your pdf pages in pixels. This width ignores the retina option.', 'ph-pdf-mockups' ); ?>
        </label>
    </p>
    <p>
        <input id="pdf-width-number" class="widefat" type="number" value="1180" />
    </p>
    <p>
        <button id="pdf-width-default" class="button">
            <?php _e('Set to default', 'ph-pdf-mockups' ); ?>
        </button>
    </p>

    <p>
        <input id="ph-pdf-resolve-comments" type="checkbox" name="ph-pdf-resolve-comments">
        <label for="ph-pdf-resolve-comments">
            <?php _e('Resolve all comments in all pdf pages.', 'ph-pdf-mockups' ); ?>
        </label>
    </p>
    <div id="pdf-custom-width"></div>
</div>