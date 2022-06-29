<div id="palleon-image" class="palleon-icon-panel-content panel-hide">
    <div class="palleon-file-field">
        <input type="file" name="palleon-file" id="palleon-img-upload" class="palleon-hidden-file" accept="image/png, image/jpeg" />
        <label for="palleon-img-upload" class="palleon-btn primary palleon-lg-btn btn-full"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload from computer', 'palleon'); ?></span></label>
    </div>
    <?php if (is_admin()) { ?>
    <button id="palleon-img-media-library" type="button" class="palleon-btn primary palleon-lg-btn btn-full palleon-modal-open" data-target="#modal-media-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Select From Media Library', 'palleon'); ?></button>
    <?php } ?>
    <div id="palleon-image-settings" class="palleon-sub-settings">
        <div class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Border Width', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="img-border-width" class="palleon-form-field" type="number" value="0" data-min="0" data-max="1000" step="1" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap">
            <label class="palleon-control-label"><?php echo esc_html__('Border Color', 'palleon'); ?></label>
            <div class="palleon-control">
                <input id="img-border-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#ffffff" />
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Rounded Corners', 'palleon'); ?><span>0</span></label>
            <div class="palleon-control">
                <input id="img-border-radius" type="range" min="0" max="1000" value="0" step="1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap conditional">
            <label class="palleon-control-label"><?php echo esc_html__('Shadow', 'palleon'); ?></label>
            <div class="palleon-control palleon-toggle-control">
                <label class="palleon-toggle">
                    <input id="palleon-image-shadow" class="palleon-toggle-checkbox" data-conditional="#image-shadow-settings" type="checkbox" autocomplete="off" />
                    <div class="palleon-toggle-switch"></div>
                </label>
            </div>
        </div>
        <div id="image-shadow-settings" class="d-none conditional-settings">
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Shadow Color', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="image-shadow-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000" />
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Shadow Blur', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="image-shadow-blur" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Offset X', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="image-shadow-offset-x" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                </div>
            </div>
            <div class="palleon-control-wrap">
                <label class="palleon-control-label"><?php echo esc_html__('Offset Y', 'palleon'); ?></label>
                <div class="palleon-control">
                    <input id="image-shadow-offset-y" class="palleon-form-field" type="number" value="5" step="1" autocomplete="off">
                </div>
            </div>
        </div>
        <hr/>
        <div class="palleon-control-wrap label-block">
            <div class="palleon-control">
                <div class="palleon-btn-group icon-group">
                    <button id="img-flip-horizontal" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip X', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                    <button id="img-flip-vertical" type="button" class="palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('Flip Y', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                    <button type="button" class="palleon-horizontal-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('H-Align Center', 'palleon'); ?>"><span class="material-icons">align_horizontal_center</span></button>
                    <button type="button" class="palleon-vertical-center palleon-btn tooltip tooltip-top" data-title="<?php echo esc_attr__('V-Align Center', 'palleon'); ?>"><span class="material-icons">vertical_align_center</span></button>
                </div>
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Opacity', 'palleon'); ?><span>1</span></label>
            <div class="palleon-control">
                <input id="img-opacity" type="range" min="0" max="1" value="1" step="0.1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew X', 'palleon'); ?><span>0</span></label>
            <div class="palleon-control">
                <input id="img-skew-x" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Skew Y', 'palleon'); ?><span>0</span></label>
            <div class="palleon-control">
                <input id="img-skew-y" type="range" min="0" max="180" value="0" step="1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <div class="palleon-control-wrap label-block">
            <label class="palleon-control-label slider-label"><?php echo esc_html__('Rotate', 'palleon'); ?><span>0</span></label>
            <div class="palleon-control">
                <input id="img-rotate" type="range" min="0" max="360" value="0" step="1" class="palleon-slider" autocomplete="off">
            </div>
        </div>
        <?php if (is_admin()) { ?>
        <hr/>
        <button id="palleon-img-replace-media-library" type="button" class="palleon-btn palleon-lg-btn btn-full palleon-modal-open" data-target="#modal-media-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Replace Image', 'palleon'); ?></button>
        <?php } ?>
    </div>
</div>