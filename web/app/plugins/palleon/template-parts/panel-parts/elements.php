<div id="palleon-elements" class="palleon-icon-panel-content panel-hide">
    <div class="palleon-tabs">
        <ul class="palleon-tabs-menu">
            <li id="palleon-all-elements-open" class="active" data-target="#palleon-all-elements"><?php echo esc_html__('All', 'palleon'); ?></li>
            <?php if (is_admin()) { ?>
            <li data-target="#palleon-my-favorites"><?php echo esc_html__('My Favorites', 'palleon'); ?></li>
            <?php } ?>
            <li id="palleon-custom-element-open" data-target="#palleon-custom-element"><?php echo esc_html__('Settings', 'palleon'); ?></li>
        </ul>
        <div id="palleon-all-elements" class="palleon-tab active">
            <div class="palleon-search-wrap">
                <input id="palleon-element-search" type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Search Category...', 'palleon'); ?>" autocomplete="off" />
                <span id="palleon-element-search-icon" class="material-icons">search</span>
            </div>
            <ul id="palleon-elements-wrap" class="palleon-accordion">
                <?php
                $elementTags = palleon_get_element_tags();
                $user_fav = get_user_meta(get_current_user_id(), 'palleon_element_fav',true);
                $perpage =  PalleonSettings::get_option('el_pagination',12);
                if (empty($user_fav)) {
                    $user_fav = array();
                }
                foreach($elementTags as $slug => $data) {
                    echo '<li data-keyword="' . $slug . '"><a>' . $data[0] . '<span class="data-count">' . $data[1] . '</span><span class="material-icons arrow">keyboard_arrow_down</span></a><div><div id="palleon-elements-grid-' . $slug . '" class="palleon-grid palleon-elements-grid four-column paginated" data-perpage="' . $perpage . '">';
                    for ($i = 1; $i <= $data[1]; ++$i) {
                        $elementid = $slug . '/' . $i;
                        $btn_class = '';
                        $icon = 'star_border';
                        if (in_array($elementid, $user_fav)) {
                            $btn_class = 'favorited';
                            $icon = 'star';
                        }
                        echo '<div class="palleon-element ' . $data[2] . '" data-elsource="' . PALLEON_SOURCE_URL . 'elements/' . $elementid . '.svg" data-loader="' . $data[3] . '">';
                        echo '<img class="lazy" data-src="' . PALLEON_SOURCE_URL . 'elements/' . $elementid . '.svg" />';
                        echo '<div class="element-favorite"><button type="button" class="palleon-btn-simple star ' . $btn_class . '" data-elementid="' . $elementid . '"><span class="material-icons">' . $icon . '</span></button></div>';
                        echo '</div>';
                    }
                    echo '</div></div></li>';
                }
                ?>
            </ul>
        </div>
        <?php if (is_admin()) { ?>
        <div id="palleon-my-favorites" class="palleon-tab">
            <div class="palleon-grid palleon-elements-grid five-column">
                <?php
                $elementTags = palleon_get_element_tags();
                $user_fav = get_user_meta(get_current_user_id(), 'palleon_element_fav',true);
                if (!empty($user_fav)) {
                    foreach($user_fav as $slug) {
                        $tag = strtok($slug, '/');
                        echo '<div class="palleon-element ' . $elementTags[$tag][2] . '" data-elsource="' . PALLEON_SOURCE_URL . 'elements/' . $slug . '.svg" data-loader="' . $elementTags[$tag][3] . '">';
                        echo '<img class="lazy" data-src="' . PALLEON_SOURCE_URL . 'elements/' . $slug . '.svg" />';
                        echo '<div class="element-favorite"><button type="button" class="palleon-btn-simple star favorited" data-elementid="' . $slug . '"><span class="material-icons">star</span></button></div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="notice notice-info"><h6>' . esc_html__( 'No favorites yet', 'palleon' ) . '</h6>' . esc_html__('Click the star icon on any element, and you will see it here next time you visit.', 'palleon') . '</div>';
                }
                ?>
            </div>
        </div>
        <?php } ?>
        <div id="palleon-custom-element" class="palleon-tab">
            <div id="palleon-custom-element-options-info" class="notice notice-info">
                <h6><?php echo esc_html__( 'No element is selected', 'palleon' ); ?></h6>
                <?php echo esc_html__('Select an element to adjust the settings.', 'palleon'); ?>
            </div>
            <div id="palleon-custom-element-options">
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Fill Style', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <select id="palleon-element-gradient" class="palleon-select" autocomplete="off">
                            <option value="none" selected><?php echo esc_html__('Solid Color', 'palleon'); ?></option>
                            <option value="vertical"><?php echo esc_html__('Vertical Gradient', 'palleon'); ?></option>
                            <option value="horizontal"><?php echo esc_html__('Horizontal Gradient', 'palleon'); ?></option>
                        </select>
                    </div>
                </div>
                <div id="element-gradient-settings">
                    <div class="palleon-control-wrap control-text-color">
                        <label class="palleon-control-label"><?php echo esc_html__('Color 1', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-gradient-color-1" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#9C27B0" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap control-text-color">
                        <label class="palleon-control-label"><?php echo esc_html__('Color 2', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-gradient-color-2" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000000" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap control-text-color">
                        <label class="palleon-control-label"><?php echo esc_html__('Color 3', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-gradient-color-3" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap control-text-color">
                        <label class="palleon-control-label"><?php echo esc_html__('Color 4', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-gradient-color-4" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                        </div>
                    </div>
                </div>
                <div id="element-fill-color" class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Fill Color', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="palleon-element-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="" />
                    </div>
                </div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Shadow', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-element-shadow" class="palleon-toggle-checkbox" data-conditional="#element-shadow-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="element-shadow-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Shadow Color', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-shadow-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Shadow Blur', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-shadow-blur" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Offset X', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-shadow-offset-x" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Offset Y', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="element-shadow-offset-y" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="palleon-control-wrap label-block">
                    <div class="palleon-control">
                        <div class="palleon-btn-group icon-group">
                            <button id="element-flip-horizontal" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip X', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                            <button id="element-flip-vertical" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip Y', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                            <button type="button" class="palleon-horizontal-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('H-Align Center', 'palleon'); ?>"><span class="material-icons">align_horizontal_center</span></button>
                            <button type="button" class="palleon-vertical-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('V-Align Center', 'palleon'); ?>"><span class="material-icons">vertical_align_center</span></button>
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Opacity', 'palleon'); ?><span>1</span></label>
                    <div class="palleon-control">
                        <input id="element-opacity" type="range" min="0" max="1" value="1" step="0.1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew X', 'palleon'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="element-skew-x" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew Y', 'palleon'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="element-skew-y" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap label-block">
                    <label class="palleon-control-label slider-label"><?php echo esc_html__('Rotate', 'palleon'); ?><span>0</span></label>
                    <div class="palleon-control">
                        <input id="element-rotate" type="range" min="0" max="360" value="0" step="1" class="palleon-slider" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="palleon-noelements" class="notice notice-warning"><?php echo esc_html__('Nothing found.', 'palleon'); ?></div>
</div>