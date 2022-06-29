<div id="palleon-shapes" class="palleon-icon-panel-content panel-hide">
    <div class="palleon-select-btn-set">
        <select id="palleon-shape-select" class="palleon-select" autocomplete="off">
            <option value="none" selected><?php echo esc_html__('Select Shape', 'palleon'); ?></option>
            <option value="circle"><?php echo esc_html__('Circle', 'palleon'); ?></option>
            <option value="ellipse"><?php echo esc_html__('Ellipse', 'palleon'); ?></option>
            <option value="square"><?php echo esc_html__('Square', 'palleon'); ?></option>
            <option value="rectangle"><?php echo esc_html__('Rectangle', 'palleon'); ?></option>
            <option value="triangle"><?php echo esc_html__('Triangle', 'palleon'); ?></option>
            <option value="trapezoid"><?php echo esc_html__('Trapezoid', 'palleon'); ?></option>
            <option value="emerald"><?php echo esc_html__('Emerald', 'palleon'); ?></option>
            <option value="star"><?php echo esc_html__('Star', 'palleon'); ?></option>
        </select>
        <button id="palleon-shape-add" class="palleon-btn primary" autocomplete="off" disabled><span class="material-icons">add_circle</span></button>
    </div>
    <div id="palleon-shape-settings" class="palleon-sub-settings">
        <div class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Fill Style', 'palleon'); ?></label>
            <div class="palleon-control">
                <select id="palleon-shape-gradient" class="palleon-select" autocomplete="off">
                    <option value="none" selected><?php echo esc_html__('Solid Color', 'palleon'); ?></option>
                    <option value="vertical"><?php echo esc_html__('Vertical Gradient', 'palleon'); ?></option>
                    <option value="horizontal"><?php echo esc_html__('Horizontal Gradient', 'palleon'); ?></option>
                </select>
            </div>
        </div>
        <div id="shape-gradient-settings">
            <div class="palleon-control-wrap control-text-color">
                <label class="palleon-control-label"><?php echo esc_html__('Color 1', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-gradient-color-1" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#9C27B0" />
                </div>
            </div>
            <div class="palleon-control-wrap control-text-color">
                <label class="palleon-control-label"><?php echo esc_html__('Color 2', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-gradient-color-2" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000000" />
                </div>
            </div>
            <div class="palleon-control-wrap control-text-color">
                <label class="palleon-control-label"><?php echo esc_html__('Color 3', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-gradient-color-3" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                </div>
            </div>
            <div class="palleon-control-wrap control-text-color">
                <label class="palleon-control-label"><?php echo esc_html__('Color 4', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-gradient-color-4" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="" />
                </div>
            </div>
        </div>
        <div id="shape-fill-color" class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Fill Color', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="palleon-shape-color" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="#fff" />
            </div>
        </div>
        <div class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Outline Size', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="shape-outline-width" class="palleon-form-field" type="number" value="0" data-min="0" data-max="1000" step="1" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Outline Color', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="shape-outline-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000000" />
            </div>
        </div>
        <div class="palleon-control-wrap conditional">
            <label class="palleon-control-label"><?php echo esc_html__('Shadow', 'palleon'); ?></label>
            <div class="palleon-control palleon-toggle-control">
                <label class="palleon-toggle">
                    <input id="palleon-shape-shadow" class="palleon-toggle-checkbox" data-conditional="#shape-shadow-settings" type="checkbox" autocomplete="off" />
                    <div class="palleon-toggle-switch"></div>
                </label>
            </div>
        </div>
        <div id="shape-shadow-settings" class="d-none conditional-settings">
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Shadow Color', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-shadow-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000" />
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Shadow Blur', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-shadow-blur" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Offset X', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-shadow-offset-x" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Offset Y', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-shadow-offset-y" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                </div>
            </div>
        </div>
        <hr/>
        <div class="palleon-control-wrap label-block">
            <div class="palleon-control">
                <div class="palleon-btn-group icon-group">
                    <button type="button" class="palleon-horizontal-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Horizontal Align Center', 'palleon'); ?>"><span class="material-icons">align_horizontal_center</span></button>
                    <button type="button" class="palleon-vertical-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Vertical Align Center', 'palleon'); ?>"><span class="material-icons">vertical_align_center</span></button>
                </div>
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Opacity', 'palleon'); ?><span>1</span></label>
            <div class="palleon-control">
                <input id="shape-opacity" type="range" min="0" max="1" value="1" step="0.1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew X', 'palleon'); ?><span>0</span></label>
            <div class="palleon-control">
                <input id="shape-skew-x" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew Y', 'palleon'); ?><span>0</span></label>
            <div class="palleon-control">
                <input id="shape-skew-y" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Rotate', 'palleon'); ?><span>0</span></label>
            <div class="palleon-control">
                <input id="shape-rotate" type="range" min="0" max="360" value="0" step="1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div id="shape-custom-width-wrap">
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Custom Width', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-custom-width" class="palleon-form-field" type="number" value="" data-min="0" data-max="10000" step="1" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Custom Height', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="shape-custom-height" class="palleon-form-field" type="number" value="" data-min="0" data-max="10000" step="1" autocomplete="off">
                </div>
            </div>
        </div>
    </div>
</div>