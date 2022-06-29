<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */
?>

<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo("charset"); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<?php wp_head(); ?>
	</head>
	
	<body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
		
		<?php the_post(); ?>
		
		
		

			<div class="admin-page-content">
					<?php the_content(); ?>
			</div>
			
			
		<?php wp_footer(); ?>
		
		
	</body>
</html>
