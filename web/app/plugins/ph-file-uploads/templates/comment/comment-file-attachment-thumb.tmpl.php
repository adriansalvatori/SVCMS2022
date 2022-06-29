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

<script type="text/template" id="tmpl-comment-file-attachment-thumb">
	<div class="thumb-icon" style="
	<# if (data.thumbnail) {  #>
		background-image: url({{data.thumbnail}});
	<# } #>
	">
		<# if ( ! data.id ) { #>
			<div class="ph-loading-image light"><div class="ph-loading-image-dots"></div></div>
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

			<a href="{{ data.source_url }}" download="{{ data.title.rendered }}" class="ph-download" target="_blank">
				<svg viewBox="0 0 512 512" id="ion-android-download" width="18" height="18"><path d="M403.002 217.001C388.998 148.002 328.998 96 256 96c-57.998 0-107.998 32.998-132.998 81.001C63.002 183.002 16 233.998 16 296c0 65.996 53.999 120 120 120h260c55 0 100-45 100-100 0-52.998-40.996-96.001-92.998-98.999zM224 268v-76h64v76h68L256 368 156 268h68z"></path></svg>
			</a>

			<# if ( _.isEmpty(data.thumbnail) && data.extension ) { #>
				<div class="ph-generic-attachment-icon">
					{{ data.extension }}
				</div>
			<# } #>
		<# } #>
	</div>
	<div class="ph-attachment-title" data-text="<# if (data.title) { #>{{data.title.rendered}}<# } #>">
		<# if (data.title) { #>
			{{ data.title.rendered }}
		<# } #>
	</div>
</script>
