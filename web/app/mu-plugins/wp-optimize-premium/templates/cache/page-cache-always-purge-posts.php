<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<h3 class="wpo-first-child"><?php _e('Always purge this page when saving a post with selected post type', 'wp-optimize'); ?></h3>

<div class="wpo-fieldgroup">

	<p>
		<table class="wpo-simple-table" id="wpo_always_purge_table">
			<thead>
				<tr>
					<th><?php _e('When saving a:', 'wp-optimize'); ?></th>
					<th><?php _e('Purge this post:', 'wp-optimize'); ?></th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<?php
					if (!empty($always_purge_post_type)) {
						foreach ($always_purge_post_type as $i => $post_type) {
							$post_id = $always_purge_post_id[$i];
							$current_post_type = get_post_type($post_id);
							$post_type_label = (isset($post_types[$current_post_type]) ? $post_types[$current_post_type] : $current_post_type);
							echo '<tr><td><input type="hidden" class="cache-settings-array" name="always_purge_post_type" value="'.esc_attr($post_type).'">'.$always_purge_post_type_str[$i].'</td><td><input type="hidden" class="cache-settings-array" name="always_purge_post_id" value="'.$post_id.'">['.$post_type_label.'] '.get_the_title($post_id).'</td><td><a class="wpo-always-purge-delete wpo-delete" href="javascript: ;">'.__('Delete', 'wp-optimize').'</a></td></tr>';
						}
					}
				?>	
				<tr>
					<td>
						<select id="wpo_always_purge_post_types_select" multiple>
							<?php foreach ($post_types as $type => $name) { ?>
								<option value="<?php echo esc_attr($type); ?>"><?php echo $name; ?></option>
							<?php } ?>	
						</select>
					</td>
					<td><select id="wpo_always_purge_post_id_select"></select></td>
					<td><a id="wpo_add_always_purge_btn" href="javascript: ;" class="button"><?php _e('Add', 'wp-optimize'); ?></a></td>
				</tr>
			</tbody>
		</table>		
	</p>

</div>