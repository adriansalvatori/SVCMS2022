<?php

$post_type    = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'uo-recipe';
$form_action  = admin_url( 'edit.php' ) . '?post_type=uo-recipe';
$search_query = isset( $_GET['search_key'] ) ? sanitize_text_field( $_GET['search_key'] ) : '';

?>
<div class="uap">

	<div class="uap-report">

		<form class="uap-report-filters" method="GET" action="<?php echo $form_action; ?>">

			<input type="hidden" name="page" value="uncanny-automator-<?php echo $tab; ?>">

			<input type="hidden" name="post_type" value="uo-recipe">

			<div class="uap-report-filters-content">

				<div class="uap-report-filters-left">
					<?php
					/**
					 * Filter by Recipe Name
					 * This is one is going to be global, we're going to show it in all the logs
					 */
					?>
					<div class="uap-report-filters-filter">

						<select name="recipe_id" id="recipe_id_filter">

							<option value=""><?php _e( 'All recipes', 'uncanny-automator-pro' ); ?></option>

							<?php

							if ( $recipes ) {

								foreach ( $recipes as $recipe ) {

									if ( isset( $_GET['recipe_id'] ) && $_GET['recipe_id'] == $recipe['id'] ) {
										?>

										<option value="<?php echo $recipe['id']; ?>" selected="selected">
											<?php echo ! empty( $recipe['recipe_title'] ) ? $recipe['recipe_title'] : sprintf( __( 'ID: %1$s (no title)', 'uncanny-automator' ), $recipe['id'] ); ?>
										</option>

										<?php
									} else {
										?>

										<option value="<?php echo $recipe['id']; ?>">
											<?php echo ! empty( $recipe['recipe_title'] ) ? $recipe['recipe_title'] : sprintf( __( 'ID: %1$s (no title)', 'uncanny-automator' ), $recipe['id'] ); ?>
										</option>

										<?php
									}
								}
							}

							?>
						</select>
					</div>

					<?php

					/**
					 * Triggers-only filter
					 * Filter by Trigger Name
					 */
					if ( $tab == 'trigger-log' ) {
						?>

						<div class="uap-report-filters-filter">
							<select name="trigger_id" id="trigger_id_filter">
								<option value=""><?php _e( 'All triggers', 'uncanny-automator-pro' ); ?></option>

								<?php

								if ( $triggers ) {
									foreach ( $triggers as $trigger ) {
										if ( isset( $_GET['trigger_id'] ) && $_GET['trigger_id'] == $trigger['id'] ) {
											?>

											<option value="<?php echo $trigger['id']; ?>" selected="selected">
												<?php echo ! empty( $trigger['trigger_title'] ) ? $trigger['trigger_title'] : sprintf( __( 'Trigger deleted: %1$s', 'uncanny-automator' ), $trigger['id'] ); ?>
											</option>

											<?php
										} else {
											?>

											<option value="<?php echo $trigger['id']; ?>">
												<?php echo ! empty( $trigger['trigger_title'] ) ? $trigger['trigger_title'] : sprintf( __( 'Trigger deleted: %1$s', 'uncanny-automator' ), $trigger['id'] ); ?>
											</option>

											<?php
										}
									}
								}

								?>
							</select>
						</div>

						<?php
					}

					?>

					<?php

					/**
					 * Actions-only filter
					 * Filter by Action name
					 */

					if ( $tab == 'action-log' ) {
						?>

						<div class="uap-report-filters-filter">
							<select name="action_id" id="action_id_filter">
								<option value=""><?php _e( 'All actions', 'uncanny-automator-pro' ); ?></option>

								<?php

								if ( $actions ) {
									foreach ( $actions as $action ) {
										if ( isset( $_GET['action_id'] ) && $_GET['action_id'] == $action['id'] ) {
											?>

											<option value="<?php echo $action['id']; ?>" selected="selected">
												<?php echo ! empty( $action['action_title'] ) ? $action['action_title'] : sprintf( __( 'Action deleted: %1$s', 'uncanny-automator' ), $action['id'] ); ?>
											</option>

											<?php
										} else {
											?>

											<option value="<?php echo $action['id']; ?>">
												<?php echo ! empty( $action['action_title'] ) ? $action['action_title'] : sprintf( __( 'Action deleted: %1$s', 'uncanny-automator' ), $action['id'] ); ?>
											</option>

											<?php
										}
									}
								}

								?>
							</select>
						</div>

						<?php
					}

					?>

					<?php

					/**
					 * Filter by Recipe Creator
					 * This is one is going to be global, we're going to show it in all the logs
					 */

					?>

					<div class="uap-report-filters-filter">
						<select name="user_id">
							<option value=""><?php _e( 'All users', 'uncanny-automator-pro' ); ?></option>

							<?php

							if ( $users ) {
								foreach ( $users as $user ) {
									if ( isset( $_GET['user_id'] ) && $_GET['user_id'] == $user['id'] ) {
										?>

										<option value="<?php echo $user['id']; ?>" selected="selected">
											<?php echo ! empty( $user['title'] ) ? $user['title'] : sprintf( __( 'No display name: %1$s', 'uncanny-automator-pro' ), $user['id'] ); ?>
										</option>

										<?php
									} else {
										?>

										<option value="<?php echo $user['id']; ?>">
											<?php echo ! empty( $user['title'] ) ? $user['title'] : sprintf( __( 'No display name: %1$s', 'uncanny-automator-pro' ), $user['id'] ); ?>
										</option>

										<?php
									}
								}
							}

							?>
						</select>
					</div>

					<?php

					/**
					 * Filter by Recipe's completion date
					 * This is one is going to be global, we're going to show it in all the logs
					 */

					?>

					<div class="uap-report-filters-filter">
						<input type="text" name="daterange"
							   placeholder="<?php _e( 'Recipe completion date', 'uncanny-automator-pro' ); ?>"
							   class="daterange"
							   value="<?php echo isset( $_GET['daterange'] ) ? $_GET['daterange'] : ''; ?>">
					</div>

					<?php

					/**
					 * Triggers-only filter
					 * Filter by Trigger's completion date
					 */

					if ( $tab == 'trigger-log' ) {
						?>

						<div class="uap-report-filters-filter">
							<input type="text" name="trigger_daterange"
								   placeholder="<?php _e( 'Trigger completion date', 'uncanny-automator-pro' ); ?>"
								   class="daterange"
								   value="<?php echo isset( $_GET['trigger_daterange'] ) ? $_GET['trigger_daterange'] : ''; ?>">
						</div>

						<?php
					}

					?>

					<?php

					/**
					 * Actions-only filter
					 * Filter by Action's completion date
					 */

					if ( $tab == 'action-log' ) {
						?>

						<div class="uap-report-filters-filter">
							<input type="text" name="action_daterange"
								   placeholder="<?php _e( 'Action completion date', 'uncanny-automator-pro' ); ?>"
								   class="daterange"
								   value="<?php echo isset( $_GET['action_daterange'] ) ? $_GET['action_daterange'] : ''; ?>">
						</div>

						<?php if ( ! empty( $action_statuses ) ) { ?>
							<div class="uap-report-filters-filter">
								<select name="action_completed">
									<option value=""><?php echo esc_html__( 'All statuses', 'uncanny_automator' ); ?></option>
									<?php foreach ( $action_statuses as $status ) { ?>
										<?php $action_completed = $status['action_completed']; ?>
										<?php if ( '0' === $action_completed ) { ?>
											<?php // Do make exception for zero type because it evaluate to empty. ?>
											<?php $action_completed = 'not_completed'; ?>
										<?php } ?>
										<option <?php selected( automator_filter_input( 'action_completed' ), $action_completed ); ?> value="<?php echo esc_attr( $action_completed ); ?>">
											<?php echo esc_html( \Uncanny_Automator_Pro\Utilities::get_action_completed_label( $status['action_completed'] ) ); // Use the actual status_completed value in array. ?>
										</option>
									<?php } ?>
								</select>
							</div>
						<?php } ?>

						<?php
					}

					?>

					<input type="submit" name="filter_action" class="button"
						   value="<?php _e( 'Filter', 'uncanny-automator-pro' ); ?>">
				</div>
				<div class="uap-report-filters-right">
					<div class="uap-report-filters-search">
						<input type="text" name="search_key" value="<?php echo $search_query; ?>"
							   class="uap-report-filters-search__field"/>
						<input type="submit" name="filter_action" value="
						<?php
						/* Translators: Non-personal infinitive verb */
						_e( 'Search', 'uncanny-automator-pro' );
						?>
						" class="button uap-report-filters-search__submit">
					</div>
				</div>
			</div>

		</form>
	</div>
</div>
