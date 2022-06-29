<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;


/**
 * @class AW_Dashboard_Widget_Chart_Referral_Invites
 */
class AW_Dashboard_Widget_Chart_Referral_Invites extends AutomateWoo\Dashboard_Widget_Chart {

	public $id = 'chart-invites';

	private $_count = 0;

	function load_data() {

		$query = new AutomateWoo\Referrals\Invite_Query();
		$query->where( 'date', $this->date_from, '>' );
		$query->where( 'date', $this->date_to, '<' );

		$data = [];

		foreach ( $query->get_results() as $invite ) {
			$data[] = (object) [
				'date' => get_date_from_gmt( $invite->get_date()->format( AutomateWoo\Format::MYSQL ) )
			];
		}

		$this->_count = count( $data );

		return [ array_values( $this->prepare_chart_data( $data, 'date', false, $this->get_interval(), 'day' ) ) ];
	}


	function output_content() {

		if ( ! $this->date_to || ! $this->date_from )
			return;

		$this->render_js();

		?>

		<div class="automatewoo-dashboard-chart">

			<div class="automatewoo-dashboard-chart__header">

				<div class="automatewoo-dashboard-chart__header-group">
					<div class="automatewoo-dashboard-chart__header-figure"><?php echo esc_html( $this->_count ); ?></div>
					<div class="automatewoo-dashboard-chart__header-text">
						<span class="automatewoo-dashboard-chart__legend automatewoo-dashboard-chart__legend--blue"></span>
						<?php esc_html_e( 'referral invites sent', 'automatewoo-referrals' ); ?>
					</div>
				</div>

				<a href="<?php echo esc_url( AW_Referrals()->admin->page_url( 'invites' ) ); ?>" class="automatewoo-arrow-link"></a>
			</div>

			<div class="automatewoo-dashboard-chart__tooltip"></div>

			<div id="automatewoo-dashboard-<?php echo esc_attr( $this->get_id() ); ?>" class="automatewoo-dashboard-chart__flot"></div>

		</div>

		<?php
	}

}

return new AW_Dashboard_Widget_Chart_Referral_Invites();
