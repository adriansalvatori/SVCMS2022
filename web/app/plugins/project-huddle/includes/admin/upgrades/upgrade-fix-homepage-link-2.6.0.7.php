<?php if (!defined('ABSPATH')) {
	exit;
}

final class PH_Upgrade_Fix_Homepage_Link_2_6_0_7 extends PH_Upgrade
{
	/**
	 * Unique Identifier for upgrade routine
	 * @var string
	 */
	public $name = 'hompage-2-6-0-7';

	/**
	 * User-Facing identifier for upgrade routine
	 * @var string
	 */
	public $nice_name = '2.6.0.7 Homepage Link Fix';

	/**
	 * The priority determines the oder in which the upgrades are run.
	 * Use a version for this one.
	 *
	 * @var string
	 */
	public $priority = '2.6.0.7';

	/**
	 * Upgrade user-facing description
	 * @var string
	 */
	public $description = 'This update fixes missing homepage link page data between versions after 2.6.0.6 and prior to 2.6.0.7';

	/**
	 * The loading method is used to setup the upgrade and is called by the Upgrade Handler.
	 * @return array
	 */
	public function loading()
	{
		// Get all our forms
		$threads = $this->get_threads();

		$x = 1;
		if (is_array($threads)) {
			foreach ($threads as $thread) {
				$this->args['threads'][$x] = $thread;
				$x++;
			}
		}

		if (empty($this->total_steps) || $this->total_steps <= 1) {
			$this->total_steps = count($threads);
		}

		$args = array(
			'total_steps' 	=> $this->total_steps,
			'step' 			=> 1,
		);

		return $args;
	}

	public function _beforeStep($step)
	{
	}

	public function step($step)
	{
		// Get our form ID
		$thread = $this->args['sites'][$step];

		if (!$thread || !$thread->parent_id || !$thread->page_url) {
			return;
		}

		$page_url = get_post_meta($thread->parent_id, 'page_url', true);
		if (!$page_url) {
			return;
		}

		// normalize for now.
		$thread_url = str_replace('https', 'http', $thread->page_url);
		$page_url   = str_replace('https', 'http', $page_url);

		if ($thread_url !== $page_url) {
			// find existing page.
			$items = PH()->page->rest->fetch(
				array(
					'page_url' => $thread->page_url,
				)
			);

			// if page already exists and website project is published.
			if (isset($items[0]) && get_post_status($items[0]['parent_id']) === 'publish') {
				update_post_meta($thread->ID, 'parent_id', $items[0]['id']);
			} else {
				// create page.
				$page = PH()->page->create_item(
					array(
						'title'     => $thread->page_url,
						'page_url'  => $thread->page_url,
						'parent_id' => $thread->website_id,
					)
				);

				// update thread parent.
				update_post_meta($thread->ID, 'parent_id', $page['id']);
			}
		}
	}

	public function complete()
	{
		ph_log('Fix Homepage Link 2.6.0.7 Update Completed');
		update_site_option('ph_fix_homepage_link_2_6_0_7', true);
	}

	public function isComplete()
	{
		// if newer than 2.6.0.5, it's complete
		if (version_compare(get_site_option('ph_db_version'), '2.6.0.7', '>')) {
			return true;
		}

		// otherwise we should upgrade
		return get_site_option('ph_fix_homepage_link_2_6_0_7', false);
	}

	public function get_threads()
	{
		// phpcs:ignore
		$threads = get_posts(
			array(
				'post_type'      => 'phw_comment_loc',
				'posts_per_page' => -1,
				'status'         => 'publish',
				'date_query'     => array(
					'after'  => date('2017-10-6', strtotime('-10 days')),
					'before' => date('2017-10-27'),
				),
				array(
					'key'     => 'website_id',
					'compare' => 'EXISTS',
				),
			)
		);

		return $threads;
	}
}

function ph_register_upgrade_fix_homepage_link_2_6_0_7($upgrades)
{
	$upgrades[] = new PH_Upgrade_Fix_Homepage_Link_2_6_0_7();

	return $upgrades;
}

add_action('ph_upgrade_handler_register', 'ph_register_upgrade_fix_homepage_link_2_6_0_7');
