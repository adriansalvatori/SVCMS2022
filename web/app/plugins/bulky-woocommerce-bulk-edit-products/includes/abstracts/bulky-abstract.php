<?php

namespace WCBEditor\Includes\Abstracts;

use WCBEditor\Admin\History;
use WCBEditor\Includes\Helper;

defined( 'ABSPATH' ) || exit;

class Bulky_Abstract {
	protected static $instance = null;
	public $type;
	public $settings;
	public $default_settings;
	public $filter_saved;

	public function __construct() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === "vi_wbe_edit_{$this->type}" ) {
			add_action( 'admin_notices', [ $this, 'remove_notice' ], 999 );
			add_filter( 'admin_body_class', [ $this, 'full_screen_option' ] );
		}
	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function remove_notice() {
		remove_all_actions( 'admin_notices' );
	}

	public function full_screen_option( $body_class ) {
		$full_screen = get_option( 'vi_wbe_full_screen_option' ) ? ' vi-wbe-full-screen ' : '';

		return $body_class . $full_screen;
	}

	public function get_settings() {
		if ( ! $this->settings ) {
			$option_name = $this->type == 'products' ? "vi_wbe_settings" : "vi_wbe_{$this->type}_settings";

			$this->settings = wp_parse_args( get_option( $option_name, [] ), $this->default_settings );
		}

		return $this->settings;
	}

	public function get_setting( $key ) {
		$all_settings = $this->get_settings();

		return $all_settings[ $key ] ?? '';
	}

	public function editor() {
		$user_id        = get_current_user_id();
		$transient_name = $this->type == 'products' ? "vi_wbe_filter_data_{$user_id}" : "vi_wbe_filter_{$this->type}_data_{$user_id}";

		if ( ! $this->get_setting( 'save_filter' ) ) {
			delete_transient( $transient_name );
		}

		$this->filter_saved = get_transient( $transient_name );


		$full_screen_icon  = get_option( 'vi_wbe_full_screen_option' ) ? 'window close outline' : 'external alternate';
		$full_screen_title = get_option( 'vi_wbe_full_screen_option' ) ? esc_html__( 'Exit full screen', 'bulky-woocommerce-bulk-edit-products' ) : esc_html__( 'Full screen', 'bulky-woocommerce-bulk-edit-products' );

		$extra_product_taxonomies = Helper::get_extra_product_taxonomies();
		?>

        <div id="vi-wbe-container">
            <div id="vi-wbe-wrapper">

                <div id="vi-wbe-menu-bar">
                    <div class="vi-ui menu">

                        <a class="item vi-wbe-open-sidebar" data-menu_tab="filter" title="<?php esc_html_e( 'Filter', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                            <i class="filter icon"> </i>
                        </a>

                        <a class="item vi-wbe-open-sidebar" data-menu_tab="settings" title="<?php esc_html_e( 'Settings', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                            <i class="cog icon"> </i> <!--sliders horizontal-->
                        </a>

                        <a class="item vi-wbe-open-sidebar" data-menu_tab="meta_field" title="<?php esc_html_e( 'Meta fields', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                            <i class="server icon"> </i>
                        </a>
						<?php
						if ( $this->type === 'products' && ! empty( $extra_product_taxonomies ) ) {
							?>
                            <a class="item vi-wbe-open-sidebar" data-menu_tab="taxonomy" title="<?php esc_html_e( 'Taxonomy', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                                <i class="server icon"> </i>
                            </a>
							<?php
						}
						?>
                        <a class="item vi-wbe-open-sidebar" data-menu_tab="history" title="<?php esc_html_e( 'History', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                            <i class="history icon"> </i>
                        </a>

                        <a class="item vi-wbe-new-<?php echo esc_attr( $this->type ) ?>" title="<?php esc_html_e( 'Add new', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                            <i class="plus icon"> </i>
                        </a>
                        <a class="item vi-wbe-save-button" title="<?php esc_html_e( 'Save', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                            <i class="save icon"> </i>
                        </a>

                        <a class="item vi-wbe-get-product" title="<?php esc_html_e( 'Reload this page', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                            <i class="sync alternate icon"> </i>
                        </a>

                        <a class="item vi-wbe-full-screen-btn" title="<?php echo esc_attr( $full_screen_title ) ?>">
                            <i class="<?php echo esc_attr( $full_screen_icon ) ?> icon"> </i>
                        </a>

                        <div class="vi-wbe-menu-bar-center">

                        </div>

                        <div class="vi-wbe-pagination">
                        </div>
                    </div>
                </div>

                <div id="vi-wbe-sidebar" class="vi-ui form small">
                    <div class="vi-wbe-sidebar-wrapper">
                        <span class="vi-wbe-close-sidebar"><i class="dashicons dashicons-no-alt"></i></span>
                        <div class="vi-wbe-sidebar-inner">

                            <div class="vi-ui top attached tabular menu">
                                <a class="active item" data-tab="filter"><?php esc_html_e( 'Filter', 'bulky-woocommerce-bulk-edit-products' ); ?></a>
                                <a class="item" data-tab="settings"><?php esc_html_e( 'Settings', 'bulky-woocommerce-bulk-edit-products' ); ?></a>
                                <a class="item" data-tab="meta_field"><?php esc_html_e( 'Meta fields', 'bulky-woocommerce-bulk-edit-products' ); ?></a>
								<?php
								if ( $this->type === 'products' && ! empty( $extra_product_taxonomies ) ) {
									?>
                                    <a class="item" data-tab="taxonomy"><?php esc_html_e( 'Taxonomies', 'bulky-woocommerce-bulk-edit-products' ); ?></a>
									<?php
								}
								?>
                                <a class="item" data-tab="history"><?php esc_html_e( 'History', 'bulky-woocommerce-bulk-edit-products' ); ?></a>
                            </div>

                            <div class="vi-ui bottom attached active tab segment" data-tab="filter">
                                <form class="" id="vi-wbe-products-filter">
									<?php $this->filter_tab(); ?>
                                </form>

                                <div class="vi-wbe-sidebar-footer">
                                        <span class="vi-ui button small vi-wbe-apply-filter">
                                            <?php esc_html_e( 'Filter', 'bulky-woocommerce-bulk-edit-products' ); ?>
                                        </span>
                                    <span class="vi-ui button small vi-wbe-clear-filter">
                                            <?php esc_html_e( 'Clear', 'bulky-woocommerce-bulk-edit-products' ); ?>
                                        </span>
                                </div>

                            </div>

                            <div class="vi-ui bottom attached tab segment" data-tab="settings">
                                <form class="vi-wbe-settings-tab ">

									<?php $this->settings_tab(); ?>

                                    <div class="vi-ui yellow message">
                                        <div class="header"><?php esc_html_e( 'System status', 'bulky-woocommerce-bulk-edit-products' ); ?></div>
                                        <div>
                                            <b>memory_limit : </b>
											<?php echo esc_html( ini_get( 'memory_limit' ) ); ?>
                                        </div>
                                        <div>
                                            <b>max_input_vars : </b>
											<?php
											$max_input_vars = ini_get( 'max_input_vars' );
											echo esc_html( $max_input_vars );
											if ( $max_input_vars < 3000 ) {
												printf( "<small> %s</small>", esc_html__( '(Recommend at least 3000 or higher)', 'bulky-woocommerce-bulk-edit-products' ) );
											}
											?>
                                        </div>
                                        <div>
                                            <b>max_execution_time : </b>
											<?php echo esc_html( ini_get( 'max_execution_time' ) . 's' ); ?>
                                        </div>
                                    </div>
                                </form>

                                <div class="vi-wbe-sidebar-footer">
                                    <span class="vi-ui button small vi-wbe-save-settings">
                                        <?php esc_html_e( 'Save', 'bulky-woocommerce-bulk-edit-products' ); ?>
                                    </span>
                                </div>

                            </div>

                            <div class="vi-ui bottom attached tab segment" data-tab="meta_field">
                                <div class="field">
                                    <div class="vi-ui fluid icon input">
                                        <input type="text" placeholder="Search meta key" class="vi-wbe-search-metakey">
                                        <i class="search icon"></i>
                                    </div>
                                </div>
                                <table class="vi-ui celled table vi-wbe-meta-fields-container form mini">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Meta key', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                        <th><?php esc_html_e( 'Column name', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                        <th><?php esc_html_e( 'Value format', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                        <th><?php esc_html_e( 'Column type', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                        <th><?php esc_html_e( 'Active', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                        <th><?php esc_html_e( 'Actions', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>

                                </table>

                                <div class="vi-wbe-sidebar-footer">
                                    <button class="vi-ui button small vi-wbe-get-meta-fields">
										<?php esc_html_e( 'Get meta fields', 'bulky-woocommerce-bulk-edit-products' ); ?>
                                    </button>
                                    <button class="vi-ui button small vi-wbe-save-meta-fields" title="<?php esc_html_e( 'This action will reload editor', 'bulky-woocommerce-bulk-edit-products' ); ?>">
										<?php esc_html_e( 'Save', 'bulky-woocommerce-bulk-edit-products' ); ?>
                                    </button>
                                    <div class="vi-ui action input">
                                        <input type="text" placeholder="<?php esc_html_e( 'new_meta_key', 'bulky-woocommerce-bulk-edit-products' ); ?>">
                                        <button class="vi-ui button small vi-wbe-add-new-meta-field">
                                            <i class="icon plus"> </i>
                                        </button>
                                    </div>

                                </div>
                            </div>

							<?php
							if ( $this->type === 'products' && ! empty( $extra_product_taxonomies ) ) {
								$taxonomy_fields = get_option( 'vi_wbe_product_taxonomy_fields' );

								?>
                                <div class="vi-ui bottom attached tab segment" data-tab="taxonomy">
                                    <table class="vi-ui celled table form mini vi-wbe-taxonomy-fields">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Name', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                            <th><?php esc_html_e( 'Key', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                            <th><?php esc_html_e( 'Active', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php
										foreach ( $extra_product_taxonomies as $key => $name ) {
											$checked = in_array( $key, $taxonomy_fields ) ? 'checked' : '';
											?>
                                            <tr>
                                                <td>
													<?php echo esc_html( $name ); ?>
                                                </td>
                                                <td>
                                                    <span class="vi-wbe-taxonomy-key"><?php echo esc_html( $key ) ?></span>
                                                </td>
                                                <td>
                                                    <input type="checkbox" class="vi-wbe-taxonomy-active" <?php echo esc_attr( $checked ) ?>/>
                                                </td>
                                            </tr>
											<?php
										}
										?>
                                        </tbody>
                                    </table>

                                    <div class="vi-wbe-sidebar-footer">
                                        <button class="vi-ui button small vi-wbe-save-taxonomy-fields" title="<?php esc_html_e( 'This action will reload editor', 'bulky-woocommerce-bulk-edit-products' ); ?>">
											<?php esc_html_e( 'Save', 'bulky-woocommerce-bulk-edit-products' ); ?>
                                        </button>
                                    </div>
                                </div>
								<?php
							}
							?>

                            <div class="vi-ui bottom attached tab segment" data-tab="history">
                                <div class="vi-ui form mini">

                                    <div>
                                        <div class="vi-wbe-history-menu-left">
                                        </div>
                                        <div class="vi-wbe-history-menu-right">
                                            <div class="vi-wbe-pagination">
                                                <div class="vi-ui pagination menu">
                                                    <a class="item disabled" data-page="0"><i class="icon angle left"> </i></a>
                                                    <a class="item active" data-page="1">1</a><a class="item " data-page="2">2</a>
                                                    <a class="item disabled">...</a>
                                                    <a class="item " data-page="7">7</a>
                                                    <a class="item " data-page="2"><i class="icon angle right"> </i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <table id="vi-wbe-history-points-list" class="vi-ui celled table">
                                        <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Date', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                            <th><?php esc_html_e( 'User', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                            <th class=""><?php esc_html_e( 'Action', 'bulky-woocommerce-bulk-edit-products' ); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php $this->get_history_page() ?>
                                        </tbody>
                                    </table>

                                </div>

                                <div class="vi-wbe-history-review vi-ui form mini">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="vi-wbe-editor" class="vi-ui segment">
                    <div class="wvps-scroll">

                        <div id="vi-wbe-spreadsheet">

                        </div>
                    </div>
                </div>

            </div>

            <!------------------- Modal ---------------------->
            <div class="vi-ui modal">
                <i class="close icon"></i>


                <div class="scrolling content vi-wbe-editing" style="box-sizing: border-box;height: 5000px">

                    <div>
                        <textarea id="vi-wbe-text-editor"></textarea>
                    </div>

                </div>

                <div class="actions vi-wbe-editing">
                    <div class="vi-ui button tiny vi-wbe-text-editor-save">
						<?php esc_html_e( 'Save', 'bulky-woocommerce-bulk-edit-products' ); ?>
                    </div>
                    <div class="vi-ui button tiny vi-wbe-text-editor-save vi-wbe-close">
						<?php esc_html_e( 'Save & Close', 'bulky-woocommerce-bulk-edit-products' ); ?>
                    </div>
                </div>

            </div>

            <div class=" vi-ui segment form vi-wbe-context-popup"></div>

        </div>
		<?php

	}

	public function filter_input_element( $args = [] ) {
		$args = wp_parse_args( $args, [
			'type'         => '',
			'id'           => '',
			'label'        => '',
			'behavior'     => '',
			'operator'     => '',
			'name_prefix'  => '',
			'class'        => '',
			'placeholder'  => '',
			'label_class'  => 'vi-wbe-filter-label',
			'input_class'  => 'vi-wbe-filter-input',
			'select_class' => 'vi-wbe-filter-select',
			'more_content' => '',
			'unit'         => ''
		] );

		if ( in_array( $args['type'], [ 'text', 'number', 'date' ] ) ) {
			$args['class'] .= 'vi-wbe-filter-input-scope';
		}

		if ( $args['behavior'] ) {
			$args['more_content'] = $this->behavior_ui( $args['id'] );
			$args['action_class'] = 'action';
		}

		if ( $args['operator'] ) {
			$args['more_content'] = $this->operator_ui( $args['id'] );
			$args['action_class'] = 'action';
		}

		if ( $args['unit'] ) {
			$args['more_content'] = sprintf( "<div class='vi-ui basic label'>%s</div>", esc_html( $args['unit'] ) );
		}

		if ( $args['name_prefix'] ) {
			$value = $this->filter_saved[ $args['name_prefix'] ][ $args['id'] ] ?? ( $this->filter_saved[ $args['id'] ] ?? '' );
		} else {
			$value = $this->filter_saved[ $args['id'] ] ?? '';
		}

		$this->core_elements( $args, $value );
	}

	public function setting_input_element( $args ) {
		$args = wp_parse_args( $args, [
			'type'         => '',
			'id'           => '',
			'default'      => '',
			'label'        => '',
			'behavior'     => '',
			'operator'     => '',
			'name_prefix'  => '',
			'class'        => '',
			'label_class'  => '',
			'input_class'  => '',
			'select_class' => '',
			'more_content' => '',
			'unit'         => '',
			'clear_button' => '',
			'placeholder'  => '',
		] );

		if ( $args['unit'] ) {
			$args['more_content'] = sprintf( "<div class='vi-ui basic label'>%s</div>", esc_html( $args['unit'] ) );
			$args['action_class'] = 'right labeled';
		}

		$data  = $this->get_settings();
		$value = $data[ $args['id'] ] ?? '';
		$this->core_elements( $args, $value );
	}

	public function core_elements( $args, $value ) {

		$allowed_html = Helper::allowed_html();
		?>
        <div class="field <?php echo esc_attr( $args['class'] ) ?>">
			<?php
			switch ( $args['type'] ) {
				case 'text':
				case 'number':
				case 'date':
					$min = isset( $args['min'] ) ? " min={$args['min']}" : '';
					$max = isset( $args['max'] ) ? " max={$args['max']}" : '';
					?>
                    <label class="<?php echo esc_attr( $args['label_class'] ) ?>">
						<?php echo esc_attr( $args['label'] ) ?>
                    </label>
                    <div class="vi-ui input small <?php echo esc_attr( $args['action_class'] ?? '' ); ?>">
                        <input type="<?php echo esc_attr( $args['type'] ) ?>" placeholder="<?php echo esc_attr( $args['placeholder'] ) ?>"
                               name="<?php echo esc_attr( $args['id'] ) ?>"
                               value="<?php echo esc_attr( $value ) ?>"
                               class="<?php echo esc_attr( $args['input_class'] ) ?>" <?php echo esc_attr( $min . $max ) ?>>
						<?php echo wp_kses( $args['more_content'], $allowed_html ); ?>
                    </div>
					<?php
					break;

				case 'select':
				case 'multi-select':
					$multiple = $args['type'] == 'multi-select' ? 'multiple' : '';
					$name = $args['name_prefix'] ? $args['name_prefix'] . "[{$args['id']}]" : $args['id'];
					$name = $multiple ? $name . '[]' : $name;

					if ( ! empty( $args['label'] ) ) {
						?>
                        <label class="<?php echo esc_attr( $args['label_class'] ) ?>">
							<?php echo esc_attr( $args['label'] ) ?>
                        </label>
						<?php
					}
					?>

                    <div class="vi-ui input small <?php echo esc_attr( $args['action_class'] ?? '' ); ?>">
                        <select id="vi-wbe-<?php echo esc_attr( $args['id'] ?? '' ) ?>"
                                name="<?php echo esc_attr( $name ) ?>"
                                class="vi-wbe vi-ui fluid dropdown <?php echo esc_attr( $args['select_class'] ) ?>"
                                data-placeholder="" <?php echo esc_attr( $multiple ) ?> >
							<?php
							if ( ! empty( $args['options'] ) && is_array( $args['options'] ) ) {
								foreach ( $args['options'] as $key => $label ) {
									if ( $multiple && is_array( $value ) ) {
										$selected = in_array( $key, $value ) ? 'selected' : '';
									} else {
										$selected = $key == $value ? 'selected' : '';
									}
									printf( "<option value='%s' %s>%s</option>", esc_attr( $key ), esc_attr( $selected ), esc_html( $label ) );
								}
							}
							?>
                        </select>
						<?php
						if ( ! empty( $args['clear_button'] ) ) {
							?>
                            <span class="vi-wbe-multi-select-clear"><i class="dashicons dashicons-no-alt"> </i></span>
							<?php
						}
						?>
						<?php echo wp_kses( $args['more_content'], $allowed_html ); ?>
                    </div>
					<?php
					break;

				case 'checkbox':
					?>
                    <label class="<?php echo esc_attr( $args['label_class'] ) ?>">
						<?php echo esc_attr( $args['label'] ) ?>
                    </label>
                    <div class="vi-ui toggle checkbox small <?php echo esc_attr( $args['action_class'] ?? '' ); ?>">
                        <input type="checkbox"
                               name="<?php echo esc_attr( $args['id'] ) ?>"
                               value="1" <?php checked( $value, 1 ) ?>
                               class="<?php echo esc_attr( $args['input_class'] ) ?>">
                        <label> </label>
						<?php echo wp_kses( $args['more_content'], $allowed_html ); ?>
                    </div>
					<?php
					break;
			}
			?>
        </div>
		<?php

	}

	public function behavior_ui( $id ) {
		$behaviors = [
			'like'  => esc_html__( 'Like', 'bulky-woocommerce-bulk-edit-products' ),
			'exact' => esc_html__( 'Exact', 'bulky-woocommerce-bulk-edit-products' ),
			'not'   => esc_html__( 'Not', 'bulky-woocommerce-bulk-edit-products' ),
			'begin' => esc_html__( 'Begin', 'bulky-woocommerce-bulk-edit-products' ),
			'end'   => esc_html__( 'End', 'bulky-woocommerce-bulk-edit-products' ),
			'empty' => esc_html__( 'Empty', 'bulky-woocommerce-bulk-edit-products' ),
		];

		$behaviors = apply_filters( 'bulky_filter_behaviors_list', $behaviors, $id );

		$saved_behavior = $this->filter_saved['behavior'][ $id ] ?? '';
		ob_start();
		?>
        <select class="vi-ui compact selection dropdown" name="behavior[<?php echo esc_attr( $id ) ?>]">
			<?php
			foreach ( $behaviors as $behavior => $show ) {
				printf( '<option value="%s" %s>%s</option>', esc_attr( $behavior ), selected( $behavior, $saved_behavior, false ), esc_html( $show ) );
			}
			?>
        </select>
		<?php
		return ob_get_clean();
	}

	public function operator_ui( $id ) {
		$operators = [
			'or'     => esc_html__( 'Or', 'bulky-woocommerce-bulk-edit-products' ),
			'and'    => esc_html__( 'And', 'bulky-woocommerce-bulk-edit-products' ),
			'not_in' => esc_html__( 'Not in', 'bulky-woocommerce-bulk-edit-products' ),
		];

		$operators = apply_filters( 'bulky_filter_operators_list', $operators, $id );

		$saved_operator = $this->filter_saved['operator'][ $id ] ?? '';
		ob_start();
		?>
        <select class="vi-ui compact selection dropdown" name="operator[<?php echo esc_attr( $id ) ?>]">
			<?php
			foreach ( $operators as $operator => $show ) {
				printf( '<option value="%s" %s>%s</option>',
					esc_attr( $operator ), selected( $operator, $saved_operator, false ), esc_html( $show ) );
			}
			?>
        </select>
		<?php
		return ob_get_clean();
	}

	public function filter_tab() {

	}

	public function settings_tab() {

	}

	public function get_column_titles() {
		$columns = wp_list_pluck( $this->define_columns(), 'title' );
		unset( $columns['id'] );

		return $columns;
	}

	public function get_columns() {
		$columns          = $this->define_columns();
		$accepted_columns = [];
		$patterns         = $this->filter_fields();

		if ( ! empty( $patterns ) ) {
			foreach ( $columns as $key => $column ) {
				if ( in_array( $key, $patterns ) ) {
					$column['id']             = $key;
					$accepted_columns[ $key ] = $column;
				}
			}
		} else {
			foreach ( $columns as $key => $column ) {
				$column['id']             = $key;
				$accepted_columns[ $key ] = $column;
			}
		}

		return $accepted_columns;
	}

	public function define_columns() {
		return [];
	}

	public function filter_fields() {
		return [];
	}

	public function parse_to_dropdown_source( $options ) {
		$r = [];
		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $id => $name ) {
				$r[] = [ 'id' => $id, 'name' => $name ];
			}
		}

		return $r;
	}

	public function parse_to_select2_source( $options ) {
		$r = [];
		if ( ! empty( $options ) && is_array( $options ) ) {
			foreach ( $options as $id => $name ) {
				$r[] = [ 'id' => $id, 'text' => $name ];
			}
		}

		return $r;
	}

	public function get_history_page() {

	}
}
