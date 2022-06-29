<?php

namespace Uncanny_Automator_Pro;

/**
 * Pro license
 * Settings > General > License > Pro license
 *
 * @since   3.7
 * @version 3.7
 * @package Uncanny_Automator
 * @author  Daniela R. & Agustin B.
 *
 * Variables:
 * $license               Object with data about the license
 * $remove_license_url    URL to remove the license
 * $renew_license_url     URL to renew an expired or disabled license
 * $automator_account_url URL to the "My account" page in automatorplugin.com
 * $buy_new_license_url   URL to buy a new license
 */

?>

<form method="POST">

	<div class="uap-settings-panel">

		<div class="uap-settings-panel-top">

			<div class="uap-settings-panel-title">
				<?php esc_html_e( 'License', 'uncanny-automator' ); ?><uo-pro-tag></uo-pro-tag>
			</div>

			<div class="uap-settings-panel-content">

				<?php

				// Add nonce
				wp_nonce_field( 'uapro_nonce', 'uapro_nonce' );

				?>

				<?php 

				// Check if we have to show a notice
				if ( ! empty( $license->notice->title ) || ! empty( $license->notice->content ) ) {

					?>

					<uo-alert
						type="<?php echo esc_attr( $license->notice->type ); ?>"
						heading="<?php echo esc_attr( $license->notice->title ); ?>"
						class="uap-spacing-bottom"
					>

						<?php if ( ! empty( $license->notice->content ) ) { ?>
						
							<?php echo $license->notice->content; ?>

						<?php } ?>

					</uo-alert>

					<?php

				}

				?>

				<div class="uap-field">

					<label for="uap_automator_pro_license_key">
						<?php esc_html_e( 'Uncanny Automator Pro license key', 'uncanny-automator-pro' ); ?>
					</label>

					<?php if ( $license->success ) { ?>

						<input
							value="<?php echo esc_attr( md5( $license->key ) ); ?>"
							name="uap_automator_pro_license_key"
							id="uap_automator_pro_license_key"
							type="password"
							disabled
							required

							class="uap-field-text"
						>

					<?php } else { ?> 

						<input
							value="<?php echo esc_attr( $license->key ); ?>"
							name="uap_automator_pro_license_key"
							id="uap_automator_pro_license_key"
							type="password"

							class="uap-field-text"
						>

					<?php } ?>

					<div class="uap-field-description">

						<?php

						printf(
							/* Translators: Both 1 and 2 are links. */
							esc_html__( 'Find the license key in %1$s. Expired or don\'t have one? %2$s.', 'uncanny-automator-pro' ),

							// "your Automator account" link
							sprintf(
								'<a href="%s" target="_blank">%s <uo-icon id="external-link"></uo-icon></a>',
								esc_url(
									add_query_arg(
										array(
											'utm_content' => 'license_field_description'
										),
										$automator_account_url
									)
								),
								esc_html__( 'your Automator account', 'uncanny-automator-pro' )
							),

							// "Buy a new license" link
							sprintf(
								'<a href="%s" target="_blank">%s <uo-icon id="external-link"></uo-icon></a>',
								esc_url(
									add_query_arg(
										array(
											'utm_content' => 'license_field_description'
										),
										$buy_new_license_url
									)
								),
								esc_html__( 'Buy a new license', 'uncanny-automator-pro' )
							)
						);

						?>

					</div>

				</div>

				<?php if ( $license->success && ! empty( $license->key ) ) { ?> 

					<uo-button
						href="<?php echo esc_url( $remove_license_url ); ?>"
						color="secondary"
						size="small"
						class="uap-spacing-top"
					>
						<?php esc_html_e( 'Change license key', 'uncanny-automator-pro' ); ?>
					</uo-button>

				<?php } ?>

				<?php 

				// Add license content
				do_action( 'automator_settings_general_license_content' );

				?>

			</div>

		</div>

		<div class="uap-settings-panel-bottom">
			
			<?php

			// Check if the license is valid
			if ( $license->success ) {

				?>

				<input type="hidden" name="uapro_license_deactivate">

				<uo-button
					type="submit"
					color="danger"
				>
					<?php esc_html_e( 'Deactivate license', 'uncanny-automator-pro' ); ?>
				</uo-button>

				<?php

			} else {

				?>

				<input type="hidden" name="uapro_license_activate">

				<uo-button
					type="submit"
				>
					<?php esc_html_e( 'Activate license', 'uncanny-automator-pro' ); ?>
				</uo-button>

				<?php

			}

			?>

		</div>

	</div>

</form>