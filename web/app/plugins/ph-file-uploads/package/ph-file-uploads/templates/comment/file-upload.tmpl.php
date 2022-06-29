<?php
/**
 * The Template for displaying the file upload button
 *
 * This template can be overridden by copying it to yourtheme/project-huddle/comment/file-upload.tmpl.php.
 *
 * HOWEVER, on occasion ProjectHuddle will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.projecthuddle.io/article/15-overriding-templates-via-a-theme
 * @author      Projecthuddle
 * @package     ProjectHuddle/Templates
 * @version     2.7.6
 */

?>

<script type="text/template" id="tmpl-file-upload">
	<div class="ph-tooltip-wrap ph-add-file">
        <svg xmlns="http://www.w3.org/2000/svg" style="fill:none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
		<div class="ph-tooltip">
			<?php esc_html_e( 'Attach a File', 'ph-file-uploads' ); ?>
		</div>
	</div>
	<input type="file" class="ph-hidden ph-file-input" accept="<?php echo ph_file_input_types(); ?>"/>
</script>
