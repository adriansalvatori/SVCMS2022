<div id="palleon-draw" class="palleon-icon-panel-content panel-hide">
    <div class="palleon-btn-set">
        <button id="palleon-draw-btn" type="button" class="palleon-btn primary palleon-lg-btn"><span class="material-icons">edit</span><?php echo esc_html__('Start Drawing', 'palleon'); ?></button>
        <button id="palleon-draw-undo" type="button" class="palleon-btn palleon-lg-btn" autocomplete="off" title="<?php echo esc_html__('Undo', 'palleon'); ?>" disabled><span class="material-icons">undo</span></button>
    </div>
    <div id="palleon-draw-settings" class="palleon-sub-settings">
        <div class="notice notice-info">
            <?php echo esc_html__('You can draw a straight line by pressing the shift key.', 'palleon'); ?>
        </div>
        <div class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Brush Type', 'palleon'); ?></label>
            <div class="palleon-control">
                <select id="palleon-brush-select" class="palleon-select" autocomplete="off">
                    <option value="pencil" selected><?php echo esc_html__('Pencil', 'palleon'); ?></option>
                    <option value="circle"><?php echo esc_html__('Circle', 'palleon'); ?></option>
                    <option value="spray"><?php echo esc_html__('Spray', 'palleon'); ?></option>
                    <option value="hline"><?php echo esc_html__('H-line Pattern', 'palleon'); ?></option>
                    <option value="vline"><?php echo esc_html__('V-line Pattern', 'palleon'); ?></option>
                    <option value="square"><?php echo esc_html__('Square Pattern', 'palleon'); ?></option>
                    <option value="erase"><?php echo esc_html__('Erase BG Image', 'palleon'); ?></option>
                </select>
            </div>
        </div>
        <div class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Brush Width', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="brush-width" class="palleon-form-field numeric-field" type="number" value="50" autocomplete="off" data-min="1" data-max="1000" data-step="1">
            </div>
        </div>
        <div id="palleon-brush-pattern-width" class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Pattern Width', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="brush-pattern-width" class="palleon-form-field numeric-field" type="number" value="10" autocomplete="off" data-min="1" data-max="1000" data-step="1">
            </div>
        </div>
        <div id="palleon-brush-pattern-distance" class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Pattern Distance', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="brush-pattern-distance" class="palleon-form-field numeric-field" type="number" value="5" autocomplete="off" data-min="1" data-max="1000" data-step="1">
            </div>
        </div>
        <div id="not-erase-brush">
            <div class="palleon-control-wrap control-text-color">
                <label class="palleon-control-label"><?php echo esc_html__('Brush Color', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="brush-color" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="#ffffff" />
                </div>
            </div>
            <div class="palleon-control-wrap conditional">
                <label class="palleon-control-label"><?php echo esc_html__('Brush Shadow', 'palleon'); ?></label>
                <div class="palleon-control palleon-toggle-control">
                    <label class="palleon-toggle">
                        <input id="palleon-brush-shadow" class="palleon-toggle-checkbox" data-conditional="#line-shadow-settings" type="checkbox" autocomplete="off" />
                        <div class="palleon-toggle-switch"></div>
                    </label>
                </div>
            </div>
            <div id="line-shadow-settings" class="d-none conditional-settings">
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Blur', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="brush-shadow-width" class="palleon-form-field" type="number" value="5" data-min="0" data-max="1000" step="1" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Offset X', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="brush-shadow-shadow-offset-x" class="palleon-form-field" type="number" value="5" data-min="0" data-max="100" step="1" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap">
                    <label class="palleon-control-label"><?php echo esc_html__('Offset Y', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="brush-shadow-shadow-offset-y" class="palleon-form-field" type="number" value="5" data-min="0" data-max="100" step="1" autocomplete="off">
                    </div>
                </div>
                <div class="palleon-control-wrap control-text-color">
                    <label class="palleon-control-label"><?php echo esc_html__('Color', 'palleon'); ?></label>
                    <div class="palleon-control">
                        <input id="brush-shadow-color" type="text" class="palleon-colorpicker allow-empty" autocomplete="off" value="#000000" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>