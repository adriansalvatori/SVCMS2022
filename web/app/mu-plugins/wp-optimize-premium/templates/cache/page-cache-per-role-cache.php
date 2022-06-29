<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<div class="wpo-fieldgroup__subgroup">
	<label for="enable_per_role_cache">
		<input name="enable_per_role_cache" id="enable_per_role_cache" class="cache-settings wpo-select-group" type="checkbox" value="true" <?php checked($wpo_cache_options['enable_per_role_cache']); ?>>
		<?php _e('Enable user per role cache', 'wp-optimize'); ?>
	</label><span tabindex="0" data-tooltip="<?php _e('Enable this option if you have user-specific content for different roles on your website.', 'wp-optimize');?>"><span class="dashicons dashicons-editor-help"></span> </span>

	<ul id="wpo_per_role_cache_roles_list" class="<?php echo $wpo_cache_options['enable_per_role_cache'] ? '' : 'wpo_hidden'; ?>">
	<?php
		foreach ($user_roles as $role) {
			$checked = !empty($wpo_cache_options['per_role_cache']) && in_array($role['role'], $wpo_cache_options['per_role_cache']);
	?>
		<li><label><input type="checkbox" class="cache-settings-array" name="per_role_cache" value="<?php echo esc_attr($role['role']); ?>" data-saveas="value" <?php checked($checked); ?>><?php echo htmlentities($role['label']); ?></label></li>
	<?php
		}
	?>
	</ul>

	
</div>