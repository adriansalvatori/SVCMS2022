<?php

/**
 * Functions used in PH PDF Mockups addon
 */

/**
 * Add pdf page field to post type
 */
function ph_pdf_page_field()
{
	register_rest_field(
		'project_image',
		'pdf_page',
		array(
			'get_callback'    => function ($post, $attr, $request, $object_type) {
				$page = get_post_meta($post['id'], $attr, true);

				return (int) $page;
			},
			'update_callback' => function ($value, $post, $attr, $request, $object_type) {
				return update_post_meta($post->ID, $attr, (int) $value);
			},
			'schema'          => array(
				'description' => esc_html__('Page of the pdf document.', 'project-huddle'),
				'type'        => 'integer',
				'default'     => 0,
			),
		)
	);
}

add_action('rest_api_init', 'ph_pdf_page_field');


function ph_pdf_add_width_default($defaults)
{
	$defaults['pdf_width'] = array(
		'default'  => 1180,
		'sanitize' => 'intval',
	);
	return $defaults;
}
add_filter('ph_image_options_defaults', 'ph_pdf_add_width_default');


function ph_pdf_width_defaults($defaults)
{
	$defaults['pdf_width'] = 1180;
	return $defaults;
}
add_filter('ph_mockup_image_defaults', 'ph_pdf_width_defaults');

/**
 * Add a pdf badge to pdf images
 */
function ph_pdf_template_indicator()
{ ?>
	<# if ( data.type=='pdf' ) { #>
		<div class="ph-badge right" style="background: rgba(0,0,0,0.25)">
			<?php _e('pdf', 'project-huddle'); ?>
		</div>
		<# } #>
		<?php
	}
	add_action('ph_image_thumbail_template', 'ph_pdf_template_indicator');

	/**
	 * Admin underscore templates
	 *
	 * @return void
	 */
	function ph_pdf_admin_templates()
	{
		global $typenow, $pagenow, $adminpage;

		// check correct post type
		if ('ph-project' != $typenow) {
			return;
		}

		// check correct page
		if ('post.php' != $pagenow && 'post-new.php' != $pagenow) {
			return;
		}

		ph_get_template('admin/mockup/pdf-options-dialog.tmpl.php', '', '', PH_PDF_PLUGIN_DIR . 'templates/');
		ph_get_template('admin/mockup/pdf-options-dialog-version.tmpl.php', '', '', PH_PDF_PLUGIN_DIR . 'templates/');
	}

	add_action('print_media_templates', 'ph_pdf_admin_templates', 9);
