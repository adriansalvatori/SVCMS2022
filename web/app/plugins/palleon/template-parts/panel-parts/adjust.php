
 <?php
$basic_adjust = PalleonSettings::get_option('module_basic_adjust', 'enable');
$image_filters = PalleonSettings::get_option('module_filters', 'enable');
?>
<div id="palleon-adjust" class="palleon-icon-panel-content">
    <?php if ($basic_adjust == 'enable') { ?>
    <ul class="palleon-accordion">
        <li class="accordion-crop">
            <a href="#"><span class="material-icons accordion-icon">crop</span><?php echo esc_html__('Crop', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap label-block">
                    <div class="palleon-control">
                        <select id="palleon-crop-style" class="palleon-select palleon-select2" autocomplete="off">
                            <option value=""><?php echo esc_html__('Please select', 'palleon'); ?></option>
                            <option value="freeform" data-icon="crop_free"><?php echo esc_html__('Freeform', 'palleon'); ?></option>
                            <option value="custom" data-icon="crop"><?php echo esc_html__('Custom', 'palleon'); ?></option>
                            <option value="square" data-icon="crop_square"><?php echo esc_html__('Square', 'palleon'); ?></option>
                            <option value="original" data-icon="crop_original"><?php echo esc_html__('Original Ratio', 'palleon'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="palleon-control-wrap palleon-resize-wrap crop-custom">
                    <input id="palleon-crop-width" class="palleon-form-field" type="number" value="" data-max="" autocomplete="off">
                    <span class="material-icons">clear</span>
                    <input id="palleon-crop-height" class="palleon-form-field" type="number" value="" data-max="" autocomplete="off">
                    <button id="palleon-crop-lock" type="button" class="palleon-btn palleon-lock-unlock hide-on-canvas-mode active"><span class="material-icons">lock</span></button>
                </div>
                <div id="palleon-crop-btns" class="palleon-control-wrap palleon-submit-btns disabled">
                    <button id="palleon-crop-apply" type="button" class="palleon-btn primary"><?php echo esc_html__('Apply', 'palleon'); ?></button>
                    <button id="palleon-crop-cancel" type="button" class="palleon-btn"><?php echo esc_html__('Cancel', 'palleon'); ?></button>
                </div>
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">refresh</span><?php echo esc_html__('Rotate', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap label-block">
                    <div class="palleon-control">
                        <div class="palleon-btn-group icon-group">
                            <button id="palleon-rotate-right" type="button" class="palleon-btn tooltip" data-title="<?php echo esc_attr__('Rotate Right', 'palleon'); ?>"><span class="material-icons">rotate_right</span></button>
                            <button id="palleon-rotate-left" type="button" class="palleon-btn tooltip" data-title="<?php echo esc_attr__('Rotate Left', 'palleon'); ?>"><span class="material-icons">rotate_left</span></button>
                            <button id="palleon-flip-horizontal" type="button" class="palleon-btn tooltip" data-title="<?php echo esc_attr__('Flip X', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                            <button id="palleon-flip-vertical" type="button" class="palleon-btn tooltip" data-title="<?php echo esc_attr__('Flip Y', 'palleon'); ?>"><span class="material-icons">flip</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">aspect_ratio</span><?php echo esc_html__('Resize', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-resize-wrap">
                    <input id="palleon-resize-width" class="palleon-form-field" type="number" value="" data-max="" autocomplete="off">
                    <span class="material-icons">clear</span>
                    <input id="palleon-resize-height" class="palleon-form-field" type="number" value="" data-max="" autocomplete="off">
                    <button id="palleon-resize-lock" type="button" class="palleon-btn palleon-lock-unlock hide-on-canvas-mode active"><span class="material-icons">lock</span></button>
                </div>
                <button id="palleon-resize-apply" type="button" class="palleon-btn btn-full primary">Apply</button>
            </div>
        </li>
    </ul>
    <hr class="hide-on-canvas-mode" />
    <?php } ?>
    <?php if ($image_filters == 'enable') { ?>
    <ul class="palleon-accordion hide-on-canvas-mode">
        <li>
            <a href="#"><span class="material-icons accordion-icon">auto_fix_high</span><?php echo esc_html__('Quick Filters', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div id="palleon-filters" class="palleon-grid two-column">
                   <?php Palleon::print_filters(); ?>
                </div>
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">tune</span><?php echo esc_html__('Basic Adjust', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Brightness', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-brightness" class="palleon-toggle-checkbox" data-conditional="#palleon-brightness-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-brightness-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Brightness', 'palleon'); ?><span>0</span></label>
                        <div class="palleon-control">
                            <input id="brightness" type="range" min="-1" max="1" value="0" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Contrast', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-contrast" class="palleon-toggle-checkbox" data-conditional="#palleon-contrast-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-contrast-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Contrast', 'palleon'); ?><span>0</span></label>
                        <div class="palleon-control">
                            <input id="contrast" type="range" min="-1" max="1" value="0" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Saturation', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-saturation" class="palleon-toggle-checkbox" data-conditional="#palleon-saturation-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-saturation-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Saturation', 'palleon'); ?><span>0</span></label>
                        <div class="palleon-control">
                            <input id="saturation" type="range" min="-1" max="1" value="0" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Hue', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-hue" class="palleon-toggle-checkbox" data-conditional="#palleon-hue-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-hue-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Hue', 'palleon'); ?><span>0</span></label>
                        <div class="palleon-control">
                            <input id="hue" type="range" min="-2" max="2" value="0" step="0.02" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">wb_sunny</span><?php echo esc_html__('Gamma', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Gamma', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-gamma" class="palleon-toggle-checkbox" data-conditional="#palleon-gamma-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-gamma-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Red', 'palleon'); ?><span>1</span></label>
                        <div class="palleon-control">
                            <input id="gamma-red" type="range" min="0.2" max="2.2" value="1" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Green', 'palleon'); ?><span>1</span></label>
                        <div class="palleon-control">
                            <input id="gamma-green" type="range" min="0.2" max="2.2" value="1" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Blue', 'palleon'); ?><span>1</span></label>
                        <div class="palleon-control">
                            <input id="gamma-blue" type="range" min="0.2" max="2.2" value="1" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">palette</span><?php echo esc_html__('Blend Color', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Blend Color', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-blend-color" class="palleon-toggle-checkbox" data-conditional="#palleon-blend-color-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-blend-color-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Mode', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <select id="blend-color-mode" class="palleon-select" autocomplete="off">
                                <option selected value="add"><?php echo esc_html__('Add', 'palleon'); ?></option>
                                <option value="diff"><?php echo esc_html__('Diff', 'palleon'); ?></option>
                                <option value="subtract"><?php echo esc_html__('Subtract', 'palleon'); ?></option>
                                <option value="multiply"><?php echo esc_html__('Multiply', 'palleon'); ?></option>
                                <option value="screen"><?php echo esc_html__('Screen', 'palleon'); ?></option>
                                <option value="lighten"><?php echo esc_html__('Lighten', 'palleon'); ?></option>
                                <option value="darken"><?php echo esc_html__('Darken', 'palleon'); ?></option>
                                <option value="overlay"><?php echo esc_html__('Overlay', 'palleon'); ?></option>
                                <option value="exclusion"><?php echo esc_html__('Exclusion', 'palleon'); ?></option>
                                <option value="tint"><?php echo esc_html__('Tint', 'palleon'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Color', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="blend-color-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#ffffff" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Alpha', 'palleon'); ?><span>0.5</span></label>
                        <div class="palleon-control">
                            <input id="blend-color-alpha" type="range" min="0" max="1" value="0.5" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">swap_horiz</span><?php echo esc_html__('Duotone Effect', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Duotone', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-duotone-color" class="palleon-toggle-checkbox" data-conditional="#palleon-duotone-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-duotone-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Light Color', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="duotone-light-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="green" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap">
                        <label class="palleon-control-label"><?php echo esc_html__('Dark Color', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="duotone-dark-color" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="blue" />
                        </div>
                    </div>
                </div> 
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">swap_horiz</span><?php echo esc_html__('Swap Colors', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Swap Colors', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-swap-colors" class="palleon-toggle-checkbox" data-conditional="#palleon-swap-colors-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-swap-colors-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap control-text-color">
                        <label class="palleon-control-label"><?php echo esc_html__('Source', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="color-source" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#ffffff" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap control-text-color">
                        <label class="palleon-control-label"><?php echo esc_html__('Destination', 'palleon'); ?></label>
                        <div class="palleon-control">
                            <input id="color-destination" type="text" class="palleon-colorpicker disallow-empty" autocomplete="off" value="#000000" />
                        </div>
                    </div>
                    <div class="palleon-control-wrap label-block">
                        <div class="palleon-control">
                            <div class="palleon-btn-set">
                                <button id="palleon-swap-apply" type="button" class="palleon-btn primary"><?php echo esc_html__('Apply', 'palleon'); ?></button>
                                <button id="palleon-swap-remove" type="button" class="palleon-btn" autocomplete="off" disabled><span class="material-icons">delete</span></button>
                            </div>  
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li>
            <a href="#"><span class="material-icons accordion-icon">tune</span><?php echo esc_html__('Advanced Edits', 'palleon'); ?><span class="material-icons arrow">keyboard_arrow_down</span></a>
            <div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Blur', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-blur" class="palleon-toggle-checkbox" data-conditional="#palleon-blur-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-blur-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Blur', 'palleon'); ?><span>0</span></label>
                        <div class="palleon-control">
                            <input id="blur" type="range" min="0" max="1" value="0" step="0.01" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Noise', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-noise" class="palleon-toggle-checkbox" data-conditional="#palleon-noise-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-noise-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Noise', 'palleon'); ?><span>0</span></label>
                        <div class="palleon-control">
                            <input id="noise" type="range" min="0" max="1000" value="0" step="1" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="palleon-control-wrap conditional">
                    <label class="palleon-control-label"><?php echo esc_html__('Adjust Pixelate', 'palleon'); ?></label>
                    <div class="palleon-control palleon-toggle-control">
                        <label class="palleon-toggle">
                            <input id="palleon-pixelate" class="palleon-toggle-checkbox" data-conditional="#palleon-pixelate-settings" type="checkbox" autocomplete="off" />
                            <div class="palleon-toggle-switch"></div>
                        </label>
                    </div>
                </div>
                <div id="palleon-pixelate-settings" class="d-none conditional-settings">
                    <div class="palleon-control-wrap label-block">
                        <label class="palleon-control-label slider-label"><?php echo esc_html__('Pixelate', 'palleon'); ?><span>1</span></label>
                        <div class="palleon-control">
                            <input id="pixelate" type="range" min="1" max="20" value="1" step="1" class="palleon-slider" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
    <?php } ?>
</div>