<?php
/**
 * Template part for displaying pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy
 */

namespace YOOtheme;

global $multipage, $numpages, $page; ?>

<article id="post-<?php the_ID(); ?>" <?php post_class("uk-article"); ?> typeof="Article">

	<div class="admin-page-content">
			<?php the_content(); ?>
	</div>

</article>
