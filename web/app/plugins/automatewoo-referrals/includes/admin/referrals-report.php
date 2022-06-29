<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Referrals_Report
 */
class AW_Referrals_Report extends AW_Report_Abstract_Graph {

	/** @var array  */
	public $chart_colours = [
		'value' => '#3498db',
		'number' => '#DBE1E3',
	];

	/** @var array  */
	public $referral_orders = [];

	/** @var int  */
	public $referrals_value = 0;

	/** @var int  */
	public $referrals_count = 0;


	/**
	 * Get referred orders
	 */
	function load_chart_data() {

		$start_date = new DateTime();
		$start_date->setTimestamp( $this->start_date );

		$end_date = new DateTime();
		$end_date->setTimestamp( $this->end_date );
		$end_date->modify( '+1 days' );


		// Get referred orders
		$orders = new WP_Query(
			[
				'post_type'      => 'shop_order',
				'post_status'    => [ 'wc-processing', 'wc-completed' ],
				'posts_per_page' => - 1,
				'fields'         => 'ids',
				'meta_query'     => [
					[
						'key'     => '_aw_referral_id',
						'compare' => 'EXISTS',
					]
				],
				'date_query'     => [
					[
						'column' => 'post_date',
						'after'  => $start_date->format( AutomateWoo\Format::MYSQL )
					],
					[
						'column' => 'post_date',
						'before' => $end_date->format( AutomateWoo\Format::MYSQL )
					]
				]
			]
		);

		foreach ( $orders->posts as $order_id ) {

			$order                  = wc_get_order( $order_id );
			$this->referrals_value += $order->get_total();
			$order_created_date     = aw_normalize_date( $order->get_date_created() );

			if ( ! $order_created_date ) {
				continue;
			}

			$order_created_date->convert_to_site_time();

			$order_obj        = new stdClass();
			$order_obj->date  = $order_created_date->to_mysql_string();
			$order_obj->total = $order->get_total();

			$this->referral_orders[] = $order_obj;
		}

		$this->referrals_count = $orders->post_count;
	}


	/**
	 * Get the legend for the main chart sidebar
	 * @return array
	 */
	function get_chart_legend() {

		$this->load_chart_data();

		$legend = [];

		$legend[] = [
			'title' => sprintf( __( '%s referred orders value', 'automatewoo-referrals' ), '<strong>' . wc_price( $this->referrals_value ) . '</strong>' ),
			'color' => $this->chart_colours['value'],
			'highlight_series' => 1
		];

		$legend[] = [
			'title' => sprintf( __( '%s referred orders', 'automatewoo-referrals' ), '<strong>' . $this->referrals_count . '</strong>' ),
			'color' => $this->chart_colours['number'],
			'highlight_series' => 0
		];

		return $legend;
	}



	/**
	 * Get the main chart
	 *
	 * @return string
	 */
	function get_main_chart() {

		global $wp_locale;

		// Prepare data for report
		$referral_value = $this->prepare_chart_data( $this->referral_orders, 'date', 'total', $this->chart_interval, $this->start_date, $this->chart_groupby );
		$referral_count = $this->prepare_chart_data( $this->referral_orders, 'date', false, $this->chart_interval, $this->start_date, $this->chart_groupby );

		// Encode in json format
		$chart_data = wp_json_encode(
			[
				'value'  => array_values( $referral_value ),
				'number' => array_values( $referral_count ),
			]
		);

		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">
			var main_chart;

			jQuery(function(){

				var order_data = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $chart_data ); ?>' ) );

				var drawGraph = function( highlight ) {

					var series = [
						{
							label: "<?php echo esc_js( __( 'Referrals Count', 'automatewoo-referrals' ) ); ?>",
							data: order_data.number,
							yaxis: 1,
							color: '<?php echo esc_js( $this->chart_colours['number'] ); ?>',
							bars: { fillColor: '<?php echo esc_js( $this->chart_colours['number'] ); ?>', fill: true, show: true, lineWidth: 0, barWidth: 60 * 60 * 24 * 1000, align: 'center' },
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Referrals Value', 'automatewoo-referrals' ) ); ?>",
							data: order_data.value,
							yaxis: 2,
							color: '<?php echo esc_js( $this->chart_colours['value'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 4, fill: false },
							shadowSize: 0
						},
					];

				if ( highlight !== 'undefined' && series[ highlight ] ) {
					highlight_series = series[ highlight ];

					highlight_series.color = '#9c5d90';

					if ( highlight_series.bars )
						highlight_series.bars.fillColor = '#9c5d90';

					if ( highlight_series.lines ) {
						highlight_series.lines.lineWidth = 5;
					}
				}

			main_chart = jQuery.plot(
				jQuery('.chart-placeholder.main'),
				series,
				{
					legend: {
						show: false
					},
					grid: {
						color: '#aaa',
						borderColor: 'transparent',
						borderWidth: 0,
						hoverable: true
					},
					xaxes: [ {
						color: '#aaa',
						position: "bottom",
						tickColor: 'transparent',
						mode: "time",
						timeformat: "<?php if ( $this->chart_groupby == 'day' ) echo '%d %b'; else echo '%b'; ?>",
						monthNames: JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->month_abbrev ) ) ); ?>' ) ),
						tickLength: 1,
						minTickSize: [1, "<?php echo esc_js( $this->chart_groupby ); ?>"],
						font: {
							color: "#aaa"
						}
					} ],
					yaxes: [
						{
							min: 0,
							minTickSize: 1,
							tickDecimals: 0,
							color: '#d4d9dc',
							font: { color: "#aaa" }
						},
						{
							position: "right",
							min: 0,
							tickDecimals: 0,
							alignTicksWithAxis: 0,
							color: '#eee',
							font: { color: "#aaa" }
						}
					]
				}
			);

			jQuery('.chart-placeholder').resize();
			}

			drawGraph();

			jQuery('.highlight_series').hover(
				function() {
					drawGraph( jQuery(this).data('series') );
				},
				function() {
					drawGraph();
				}
			);
			});
		</script>
	<?php

	}

}
