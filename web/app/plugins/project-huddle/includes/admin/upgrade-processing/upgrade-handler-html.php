<div class="wrap">
    <h2><?php printf( __( '%s Upgrade Processing', 'project-huddle' ), PH_UpgradeHandler()->name ); ?></h2>

    <?php foreach ( PH_UpgradeHandler()->upgrades as $upgrade ): ?>
		<?php if( ! $upgrade->isComplete() ) : ?>
			<div id="ph_upgrade_<?php echo $upgrade->name ?>">
				<dl class="menu-item-bar ph_upgrade">
					<dt class="menu-item-handle">
						<span class="item-title ninja-forms-field-title ph_upgrade__name"><?php echo $upgrade->nice_name; ?></span>
						<span class="item-controls">
                                    <span class="item-type">
                                        <span class="item-type-name ph_upgrade__status">
                                            <!-- TODO: Move inline styles to Stylesheet. -->
	                                        <!-- Status: INCOMPLETE -->
                                            <span class="dashicons dashicons-no" style="color: red; display: none;"></span>
	                                        <!-- Status: PROCESSING -->
                                            <span class="spinner" style="display: none;margin-top: -1.5px;margin-right: -2px;"></span>
	                                        <!-- Status: COMPLETE -->
                                            <span class="dashicons dashicons-yes" style="color: green; display: none;"></span>
                                        </span>
                                    </span>
                                </span>
					</dt>
				</dl>
				<div class="menu-item-settings menu-item-settings--ph-upgrade type-class inside" style="display: none;">
					<div id="progressbar_<?php echo $upgrade->name; ?>" class="progressbar">
						<div class="progress-label">
							<?php _e( 'Processing', 'project-huddle' ); ?>
						</div>
					</div>
					<p><?php echo $upgrade->description; ?></p>
					<div class="ph-upgrade-handler__errors" style="display: none; box-sizing: border-box; border: 1px solid #DEDEDE; padding-left: 5px; margin-right: 10px; border-radius: 3px; background-color: #EDEDED;">
						<h3 class="ph-upgrade-handler__errors__title">
							<?php _e( 'Error', 'project-huddle' ); ?>
						</h3>
						<pre class="ph-upgrade-handler__errors__text" style="padding-left: 10px;">

                        </pre>
						<p>
							<?php _e('Please contact support with the error seen above.', 'project-huddle' ); ?>
						</p>
					</div>
				</div>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>

</div> <!-- /.wrap -->

<div class="ph-upgrade-complete" style="display: none;">
    <p><?php printf( __( '%s has completed all available upgrades!', 'project-huddle' ), PH_UpgradeHandler()->name ); ?></p>
    <p>
        <a class="button-primary" href="<?php echo admin_url();?>">
            <?php _e( 'Dashboard', 'project-huddle' ); ?>
        </a>
    </p>
</div>
