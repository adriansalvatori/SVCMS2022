<?php
/**
 * Template for displaying the group field
 *
 * @var array $field The field.
 *
 * @package XT_Framework_Settings\Fields
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if(!empty($field['fields'])) {
	$cols = !empty($field['cols']) ? $field['cols'] : 1;
	$group_classes = array('forminp', 'forminp-'.sanitize_title( $field['type'] ));

	foreach($field['fields'] as $row => $sfield) {
		if (!empty($sfield['group_cols'])) {
			$group_classes[] = "row-".($row + 1)."-col-".$sfield['group_cols'];
		}
	}

	$group_classes = implode(" ", $group_classes);
	?>
    <tr>
        <th scope="row" class="titledesc">
			<?php $this->render_field_label($field); ?>
        </th>
        <td class="<?php echo esc_attr( $group_classes ); ?>" data-cols="<?php echo esc_attr($cols);?>">
			<?php $this->render_settings($field['fields'], false); ?>
            <input id="<?php echo esc_attr( $field['id'] ); ?>" type="hidden" />
        </td>
    </tr>
<?php
}