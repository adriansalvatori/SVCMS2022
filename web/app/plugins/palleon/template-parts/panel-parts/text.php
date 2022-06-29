<div id="palleon-text" class="palleon-icon-panel-content panel-hide">
    <button id="palleon-add-text" type="button" class="palleon-btn primary palleon-lg-btn btn-full"><span class="material-icons">add_circle</span><?php echo esc_html__('Add Text', 'palleon'); ?></button>
    <div id="palleon-text-settings" class="palleon-sub-settings">
        <div class="palleon-text-wrap">
            <div class="palleon-control-wrap label-block">
                <div class="palleon-control">
                    <div id="palleon-text-format-btns" class="palleon-btn-group icon-group">
                        <button id="format-bold" type="button" class="palleon-btn"><span class="material-icons">format_bold</span></button>
                        <button id="format-italic" type="button" class="palleon-btn"><span class="material-icons">format_italic</span></button>
                        <button id="format-underlined" type="button" class="palleon-btn"><span class="material-icons">format_underlined</span></button>
                        <button id="format-align-left" type="button" class="palleon-btn format-align"><span class="material-icons">format_align_left</span></button>
                        <button id="format-align-center" type="button" class="palleon-btn format-align"><span class="material-icons">format_align_center</span></button>
                        <button id="format-align-right" type="button" class="palleon-btn format-align"><span class="material-icons">format_align_right</span></button>
                        <button id="format-align-justify" type="button" class="palleon-btn format-align"><span class="material-icons">format_align_justify</span></button>
                    </div>
                </div>
            </div>
            <div class="palleon-control-wrap label-block">
                <div class="palleon-control">
                    <textarea id="palleon-text-input" class="palleon-form-field" rows="2" autocomplete="off"><?php echo esc_html__('Enter Your Text Here', 'palleon'); ?></textarea>
                </div>
            </div>
            <hr/>
            <div class="palleon-control-wrap label-block">
                <label class="palleon-control-label"><?php echo esc_html__('Font Family', 'palleon'); ?></label>
                <div class="palleon-control">
                    <select id="palleon-font-family" class="palleon-select palleon-select2" autocomplete="off" data-loadFont="yes">
                        <?php do_action('palleon_fonts'); ?>
                        <optgroup id="websafe-fonts" label="Default Fonts"></optgroup>
                        <optgroup id="google-fonts" label="Google Fonts"></optgroup>
                    </select>
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Font Size', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="palleon-font-size" class="palleon-form-field" type="number" value="60" data-min="10" data-max="1000" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Line Height', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="palleon-line-height" class="palleon-form-field" type="number" value="1.2" data-min="0.1" data-max="10" step="0.1" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Letter Spacing', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="palleon-letter-spacing" class="palleon-form-field" type="number" value="0" data-max="1000" step="100" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Fill Style', 'palleon'); ?></label>
                <div class="palleon-control">
                    <select id="palleon-text-gradient" class="palleon-select" autocomplete="off">
                        <option value="none" selected><?php echo esc_html__('Solid Color', 'palleon'); ?></option>
                        <option value="vertical"><?php echo esc_html__('Vertical Gradient', 'palleon'); ?></option>
                        <option value="horizontal"><?php echo esc_html__('Horizontal Gradient', 'palleon'); ?></option>
                    </select>
                </div>
            </div>
            <div id="text-gradient-settings">
                <div class="palleon-control-wrap control-text-color">
                    <label class="palleon-control-label"><?php echo esc_html__('Color 1', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-gradient-color-1" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#9C27B0" />
                    </div>
                </div>
                <div class="palleon-control-wrap control-text-color">
                    <label class="palleon-control-label"><?php echo esc_html__('Color 2', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-gradient-color-2" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000000" />
                    </div>
                </div>
                <div class="palleon-control-wrap control-text-color">
                    <label class="palleon-control-label"><?php echo esc_html__('Color 3', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-gradient-color-3" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                    </div>
                </div>
                <div class="palleon-control-wrap control-text-color">
                    <label class="palleon-control-label"><?php echo esc_html__('Color 4', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-gradient-color-4" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                    </div>
                </div>
            </div>
            <div id="text-fill-color" class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Color', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="palleon-text-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000" />
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Outline Size', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="palleon-outline-size" class="palleon-form-field" type="number" value="0" data-min="0" data-max="100" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Outline Color', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="palleon-outline-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#fff" />
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Background', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="palleon-text-background" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                </div>
            </div>
            <div class="palleon-control-wrap conditional">
                <label class="palleon-control-label"><?php echo esc_html__('Text Shadow', 'palleon'); ?></label>
                <div class="palleon-control palleon-toggle-control">
                    <label class="palleon-toggle">
                        <input id="palleon-text-shadow" class="palleon-toggle-checkbox" data-conditional="#text-shadow-settings" type="checkbox" autocomplete="off" />
                        <div class="palleon-toggle-switch"></div>
                    </label>
                </div>
            </div>
            <div id="text-shadow-settings" class="d-none conditional-settings">
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Shadow Color', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-shadow-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000" />
                    </div>
                </div>
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Shadow Blur', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-shadow-blur" class="palleon-form-field" type="number" value="5" data-min="0" data-max="1000" step="1" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Offset X', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-shadow-offset-x" class="palleon-form-field" type="number" value="5" data-min="0" data-max="1000" step="1" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Offset Y', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="text-shadow-offset-y" class="palleon-form-field" type="number" value="5" data-min="0" data-max="1000" step="1" autocomplete="off">
                    </div>
                </div>
            </div>
            <hr/>
            <div class="palleon-control-wrap label-block">
                <div class="palleon-control">
                    <div id="palleon-text-flip-btns" class="palleon-btn-group icon-group">
                        <button id="text-flip-x" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip X', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                        <button id="text-flip-y" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip Y', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                        <button type="button" class="palleon-horizontal-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('H-Align Center', 'palleon'); ?>"><span class="material-icons">align_horizontal_center</span></button>
                        <button type="button" class="palleon-vertical-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('V-Align Center', 'palleon'); ?>"><span class="material-icons">vertical_align_center</span></button>
                    </div>
                </div>
            </div>
            <div class="palleon-control-wrap label-block">
                <label class="palleon-control-label slider-label"><?php echo esc_html__('Opacity', 'palleon'); ?><span>1</span></label>
                <div class="palleon-control">
                    <input id="text-opacity" type="range" min="0" max="1" value="1" step="0.1" class="palleon-slider" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap label-block">
                <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew X', 'palleon'); ?><span>0</span></label>
                <div class="palleon-control">
                    <input id="text-skew-x" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap label-block">
                <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew Y', 'palleon'); ?><span>0</span></label>
                <div class="palleon-control">
                    <input id="text-skew-y" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap label-block">
                <label class="palleon-control-label slider-label"><?php echo esc_html__('Rotate', 'palleon'); ?><span>0</span></label>
                <div class="palleon-control">
                    <input id="text-rotate" type="range" min="0" max="360" value="0" step="1" class="palleon-slider" autocomplete="off">
                </div>
            </div>
        </div>
    </div>
</div>