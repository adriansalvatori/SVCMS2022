<?php

namespace Uncanny_Automator_Pro;

use GFAPI;

/**
 * Class GF_CREATEENTRY
 *
 * @package Uncanny_Automator_Pro
 */
class GF_CREATEENTRY {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GF';

	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'GFCREATEENTRY';
		$this->action_meta = 'GFFORMS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/gravity-forms/' ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			/* translators: Action - MailPoet */
			'sentence'           => sprintf( esc_attr__( 'Create an entry for {{a form:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - MailPoet */
			'select_option_name' => esc_attr__( 'Create an entry for {{a form}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'gf_create_entry' ),
			'options_group'      => array(
				$this->action_meta => array(
					$uncanny_automator->helpers->recipe->gravity_forms->options->list_gravity_forms(
						null,
						$this->action_meta,
						array(
							'token'        => false,
							'is_ajax'      => false,
							'target_field' => $this->action_code,
						)
					),
					array(
						'option_code'       => 'GF_FIELDS',
						'input_type'        => 'repeater',
						'label'             => __( 'Row', 'uncanny-automator' ),
						/* translators: 1. Button */
						'description'       => '',
						'required'          => true,
						'fields'            => array(
							array(
								'option_code' => 'GF_COLUMN_NAME',
								'label'       => __( 'Column', 'uncanny-automator' ),
								'input_type'  => 'text',
								'required'    => true,
								'read_only'   => true,
								'options'     => array(),
							),
							Automator()->helpers->recipe->field->text_field( 'GF_COLUMN_VALUE', __( 'Value', 'uncanny-automator' ), true, 'text', '', false ),
						),
						'add_row_button'    => __( 'Add pair', 'uncanny-automator' ),
						'remove_row_button' => __( 'Remove pair', 'uncanny-automator' ),
						'hide_actions'      => true,
					),
				),
			),
			'options'            => array(),
			'buttons'            => array(
				array(
					'show_in'     => $this->action_meta,
					'text'        => __( 'Get fields', 'uncanny-automator' ),
					'css_classes' => 'uap-btn uap-btn--red',
					'on_click'    => $this->get_fields_js(),
					'modules'     => array( 'modal', 'markdown' ),
				),
			),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Anonymous JS function invoked as callback when clicking
	 * the custom button "Send test". The JS function requires
	 * the JS module "modal". Make sure it's included in
	 * the "modules" array
	 *
	 * @return string The JS code, with or without the <script> tags
	 */
	public function get_fields_js() {
		// Start output
		ob_start();

		// It's optional to add the <script> tags
		// This must have only one anonymous function
		?>

		<script>

			// Do when the user clicks on send test
			function ($button, data, modules) {

				// Create a configuration object
				let config = {
					// In milliseconds, the time between each call
					timeBetweenCalls: 1 * 1000,
					// In milliseconds, the time we're going to check for fields
					checkingTime: 60 * 1000,
					// Links
					links: {
						noResultsSupport: 'https://automatorplugin.com/knowledge-base/google-sheets/'
					},
					// i18n
					i18n: {
						checkingHooks: "<?php printf( esc_html__( "We're checking for fields. We'll keep trying for %s seconds.", 'uncanny-automator' ), '{{time}}' ); ?>",
						noResultsTrouble: "<?php esc_html_e( 'We had trouble finding fields.', 'uncanny-automator' ); ?>",
						noResultsSupport: "<?php esc_html_e( 'See more details or get help', 'uncanny-automator' ); ?>",
						fieldsModalTitle: "<?php esc_html_e( "Here is the data we've collected", 'uncanny-automator' ); ?>",
						fieldsModalWarning: "<?php /* translators: 1. Button */ printf( esc_html__( 'Clicking on \"%1$s\" will remove your current fields and will use the ones on the table above instead.', 'uncanny-automator' ), '{{confirmButton}}' ); ?>",
						fieldsTableValueType: "<?php esc_html_e( 'Value type', 'uncanny-automator' ); ?>",
						fieldsTableReceivedData: "<?php esc_html_e( 'Received data', 'uncanny-automator' ); ?>",
						fieldsModalButtonConfirm: "<?php /* translators: Non-personal infinitive verb */ esc_html_e( 'Use these fields', 'uncanny-automator' ); ?>",
						fieldsModalButtonCancel: "<?php /* translators: Non-personal infinitive verb */ esc_html_e( 'Do nothing', 'uncanny-automator' ); ?>",
					}
				}

				// Create the variable we're going to use to know if we have to keep doing calls
				let foundResults = false;

				// Get the date when this function started
				let startDate = new Date();
				// console.log( data );
				// Create array with the data we're going to send
				let dataToBeSent = {
					action: 'get_form_fields_GFFORMS',
					nonce: UncannyAutomator.nonce,
					recipe_id: UncannyAutomator.recipe.id,
					form_id: data.values.GFFORMS,
				};

				// Add notice to the item
				// Create notice
				let $notice = jQuery('<div/>', {
					'class': 'item-options__notice item-options__notice--warning'
				});

				// Add notice message
				$notice.html(config.i18n.checkingHooks.replace('{{time}}', parseInt(config.checkingTime / 1000)));

				// Get the notices container
				let $noticesContainer = jQuery('.item[data-id="' + data.item.id + '"] .item-options__notices');

				// Add notice
				$noticesContainer.html($notice);

				// Create the function we're going to use recursively to
				// do check for the fields
				var getGfFields = function () {
					// Do AJAX call
					jQuery.ajax({
						method: 'POST',
						dataType: 'json',
						url: ajaxurl,
						data: dataToBeSent,

						// Set the checking time as the timeout
						timeout: config.checkingTime,

						success: function (response) {
							// Get new date
							let currentDate = new Date();
							// Define the default value of foundResults
							let foundResults = false;

							// Check if the response was successful
							if (response.success) {

								// Check if we got the rows from a sample
								if (response.data.fields.length > 0) {
									// Update foundResults
									foundResults = true;
								}
							}

							// Check if we have to do another call
							let shouldDoAnotherCall = false;

							// First, check if we don't have results
							if (!foundResults) {
								// Check if we still have time left
								if ((currentDate.getTime() - startDate.getTime()) <= config.checkingTime) {
									// Update result
									shouldDoAnotherCall = true;
								}
							}

							if (shouldDoAnotherCall) {
								// Wait and do another call
								setTimeout(function () {
									// Invoke this function again
									getGfFields();
								}, config.timeBetweenCalls);
							} else {

								// Add loading animation to the button
								$button.removeClass('uap-btn--loading uap-btn--disabled');
								// Iterate fields and create an array with the rows
								let rows = [];
								let keys = {}

								jQuery.each(response.data.fields, function (index, field) {
									// Iterate keys
									jQuery.each(field, function (index, row) {
										if (row.value !== 'undefined') {
											keys[row.value] = rows.push(row);
										}
									});
								});

								// Get the field with the fields (AJAX_DATA)
								let gfFields = data.item.options.GFFORMS.fields[1];

								gfFields.fieldRows = [];

								// Add new rows. Iterate rows from the sample
								jQuery.each(rows, function (index, row) {

									if (typeof row.key !== 'undefined') {
										// Add row
										gfFields.addRow({
											GF_COLUMN_NAME: row.key
										}, false);
									} else {
										// Add row
										gfFields.addRow({
											GF_COLUMN_NAME: row.text
										}, false);
									}

								});

								// Render again
								gfFields.reRender();

								// Check if it has results
								if (foundResults) {
									// Remove notice
									$notice.remove();
								} else {
									// Change the notice type
									$notice.removeClass('item-options__notice--warning').addClass('item-options__notice--error');

									// Create a new notice message
									let noticeMessage = config.i18n.noResultsTrouble;

									// Change the notice message
									$notice.html(noticeMessage + ' ');

									// Add help link
									let $noticeHelpLink = jQuery('<a/>', {
										target: '_blank',
										href: config.links.noResultsSupport
									}).text(config.i18n.noResultsSupport);
									$notice.append($noticeHelpLink);
								}
							}
						},

						statusCode: {
							403: function () {
								location.reload();
							}
						},

						fail: function (response) {
						}
					});
				}

				// Add loading animation to the button
				$button.addClass('uap-btn--loading uap-btn--disabled');

				// Try to get fields
				getGfFields();
			}

		</script>

		<?php

		// Get output
		$output = ob_get_clean();

		// Return output
		return $output;
	}

	/**
	 * Validation function when the action is hit.
	 *
	 * @param string $user_id user id.
	 * @param array $action_data action data.
	 * @param string $recipe_id recipe id.
	 */
	public function gf_create_entry( $user_id, $action_data, $recipe_id, $args ) {
		$form_id = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$fields  = json_decode( $action_data['meta']['GF_FIELDS'] );

		$gfrom_input_values            = array();
		$t_values                      = array();
		$field_id                      = '';
		$gfrom_input_values['form_id'] = absint( $form_id );

		foreach ( $fields as $field ) {
			if ( ! empty( $field->GF_COLUMN_NAME ) ) {
				$t_values                                = explode( '-', $field->GF_COLUMN_NAME );
				$field_id                                = reset( $t_values );
				$gfrom_input_values[ trim( $field_id ) ] = sanitize_text_field( Automator()->parse->text( $field->GF_COLUMN_VALUE, $recipe_id, $user_id, $args ) );
			}
		}

		$entry_id = GFAPI::add_entry( $gfrom_input_values );

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
