<?php
// phpcs:ignoreFile

namespace AutomateWoo\Referrals;

/**
 * @view Referrals Page
 *
 * @var $controller Admin\Controllers\Referrals
 * @var $table Referrals_List_Table
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wrap automatewoo-page automatewoo-page--referrals">

	<h1><?php echo esc_html( $controller->get_heading() ); ?></h1>

	<?php
	$controller->output_messages();

	$table->prepare_items();
	$table->display_section_nav();
	$table->display();

	?>
</div>
