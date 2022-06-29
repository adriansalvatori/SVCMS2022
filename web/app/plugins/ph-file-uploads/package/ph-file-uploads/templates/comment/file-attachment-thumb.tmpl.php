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

<script type="text/template" id="tmpl-file-attachment-thumb">
	<div class="thumb-icon" style="
	<# if (data.media_details && data.media_details.sizes && data.media_details.sizes.ph_comment_attachment) { #>
		background-image: url({{data.media_details.sizes.ph_comment_attachment.source_url}});
	<# } #>
	">
		<# if ( ! data.id ) { #>
			<div class="ph-loading-image light"><div class="ph-loading-image-dots"></div></div>
			<div class="ph-upload-progress">
				<div class="ph-upload-progress-indicator" style="width: {{data.progress}}%"></div>
			</div>
		<# } else { #>
			<# if ( data.can_delete ) { #>
				<div class="ph-close-icon ph-tooltip-wrap">
					<svg viewBox="0 0 512 512" id="ion-android-close" width="9" height="9">
						<path d="M405 136.798L375.202 107 256 226.202 136.798 107 107 136.798 226.202 256 107 375.202 136.798 405 256 285.798 375.202 405 405 375.202 285.798 256z"></path>
					</svg>
					<div class="ph-tooltip">
						<?php esc_html_e( 'Delete', 'ph-file-uploads' ); ?>
					</div>
				</div>
			<# } #>

			<# if ( (_.isEmpty(data.media_details) || _.isEmpty(data.media_details.sizes.ph_comment_attachment) ) && data.extension ) { #>
				<div class="ph-generic-attachment-icon">
					{{ data.extension }}
				</div>
			<# } #>
		<# } #>
	</div>
	<div class="ph-attachment-title" data-text="<# if (data.title) { #>{{data.title.rendered}}<# } #>">
		<# if (data.title) { #>
			{{data.title.rendered}}
		<# } #>
	</div>
</script>
