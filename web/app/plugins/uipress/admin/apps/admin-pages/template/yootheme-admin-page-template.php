<?php
/**
 * The template for displaying all pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-page
 */

namespace YOOtheme;
//get_header();

$config = app(Config::class);

if ($config("app.isBuilder")) { ?>
			<style>
			html[uip-admin-page="true"] .tm-header-mobile, html[uip-admin-page="true"] .tm-toolbar{
				display: none !important;
			}
			</style>
		
		<?php
  get_header();
  echo get_section("builder");
  get_footer();
  } else { ?>
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
	  
	  <?php }
