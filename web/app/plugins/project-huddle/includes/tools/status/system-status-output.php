<div class="wrap">
	<h2 class="sss-title"><?php _e( 'System Status', 'project-huddle' ); ?></h2>
	<div id="template">
		<?php // Form used to download .txt file ?>
		<form action="<?php echo esc_url( self_admin_url( 'admin-ajax.php' ) ); ?>" method="post" enctype="multipart/form-data" >
			<input type="hidden" name="action" value="download_ph_system_status" />
			<div>
					<textarea rows="30" readonly="readonly" onclick="this.focus();this.select()" id="ph-system-status-text" name="ph-system-status-text" title="<?php _e( 'To copy the System Status, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'project-huddle' ); ?>">
<?php echo wp_kses_post( PH()->status->display() ) ?>
					</textarea>
			</div>
			<p class="submit">
				<input type="submit" class="button-secondary" value="<?php _e( 'Download System Info as Text File', 'project-huddle' ) ?>" />
			</p>
		</form>
	</div>
</div>
