<?php
/**
 * Shortcodes for website projects
 */
function ph_custom_add_websites_list( $output, $atts ) {
	if ( ! $atts['websites'] ) {
		return $output;
	}

	// title.
	$title = $atts['mockups'] ? '<h1>' . __( 'Websites', 'project-huddle' ) . '</h1>' : '';

	// get current user.
	$current_user = wp_get_current_user();

	// must be logged in or don't do anything.
	if ( 0 === $current_user->ID ) {
		return false;
	}

	if ( $atts['multisite'] && is_multisite() && defined( 'BLOG_ID_CURRENT_SITE' ) ) {
		$blog_id = get_current_blog_id();
		// phpcs:ignore
		switch_to_blog( BLOG_ID_CURRENT_SITE );
	}

	// use custom pagination.
	$website_page = isset( $_GET['website_page'] ) ? (int) $_GET['website_page'] : 1;

	$website_query = ph_query_users_projects(
		array(
			'types'          => array( 'website' ),
			'posts_per_page' => $atts['limit'],
			'paged'          => $website_page,
		)
	);

	if ( $website_query->have_posts() ) :
		if ( $atts['format'] == 'gallery' && apply_filters( 'ph_enable_websites_gallery', true,  get_the_ID() ) )  {
			while ( $website_query->have_posts() ) {
				$website_query->the_post();

				if ( $atts['project_title'] ) {

					// filter title tag.
					$title_tag = apply_filters( 'ph_subscribed_projects_shortcode_title_tag', 'h3', get_the_ID() );

					// title tag.
					$output .= "<$title_tag>";

					// title.
					$output .= apply_filters( 'ph_user_projects_shortcode_title', esc_html( ph_get_the_title( get_the_ID() ) ) );

					$output .= "</$title_tag>";
				}

				// start shortcode with id.
				$shortcode = '[project_huddle id=' . get_the_ID() . ' ';

				// add attributes.
				foreach ( $atts as $att => $value ) {
					if ( isset( $value ) ) {
						$shortcode .= $att . '=' . $value . ' ';
					}
				}

				// end shortcode.
				$shortcode .= ']';

				// output shortcode.
				$output .= do_shortcode( esc_html( $shortcode ) );
			}
		} else {
			$body = '<ul class="ph-subscribed-projects-list ph-subscribed-websites">';

			while ( $website_query->have_posts() ) {
				$website_query->the_post();
				$body .= '<li><a target="_blank" href="' . esc_url( get_the_permalink() ) . '">';
				$body .= apply_filters( 'ph_subscribed_website_list_title', esc_html( ph_get_the_title( get_the_ID() ) ), get_the_ID() );
				$body .= '</a></li>';
			}

			$body .= '</ul>';

			$big = 999999999; // need an unlikely integer

			// default permalinks
			$base = str_replace( 'paged=' . $big, 'website_page=%#%', esc_url( get_pagenum_link( $big ) ) );
			// pretty permalinks
			$base = str_replace( 'page/' . $big, '?website_page=%#%', $base );
			$base = preg_replace( '/\?\bwebsite_page=(.*)\b/', '?website_page=%#%', $base );

			$body .= paginate_links(
				array(
					'base'     => $base,
					'current'  => max( 1, $website_page ),
					'total'    => $website_query->max_num_pages,
					'add_args' => false,
				)
			);

			$mock_title = $atts['mockups'] ? apply_filters( 'ph_subscribed_shortcode_mockup_heading', '<h1>' . __( 'Mockups', 'project-huddle' ) . '</h1>' ) : false;
			$output     = $mock_title . $output . $title . $body;
		}
	endif;

	if ( is_multisite() && isset( $blog_id ) ) {
		switch_to_blog( $blog_id );
	}

	wp_reset_postdata();
	wp_reset_query();

	return $output;
}

add_filter( 'ph_subscribed_projects_output', 'ph_custom_add_websites_list', 40, 2 );

/**
 * Add websites attribute to ph_subscribed_projects
 *
 * @param $out
 * @param $pairs
 * @param $atts
 *
 * @return mixed
 */
function ph_subscribed_websites_atts( $out, $pairs, $atts ) {
	if ( ! isset( $atts['websites'] ) ) {
		$out['websites'] = 1;
	} else {
		$out['websites'] = filter_var( $atts['websites'], FILTER_VALIDATE_BOOLEAN );
	}
	return $out;
}
add_filter( 'shortcode_atts_ph_subscribed_projects', 'ph_subscribed_websites_atts', 10, 3 );
add_filter( 'shortcode_atts_project_huddle', 'ph_subscribed_websites_atts', 10, 3 );
