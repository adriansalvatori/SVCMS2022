<div id="palleon-icons" class="palleon-icon-panel-content panel-hide">
    <div class="palleon-tabs">
        <ul class="palleon-tabs-menu">
            <li class="active" data-target="#palleon-all-icons"><?php echo esc_html__('Icons', 'palleon'); ?></li>
            <li data-target="#palleon-customsvg-upload"><?php echo esc_html__('Custom SVG', 'palleon'); ?></li>
        </ul>
        <div id="palleon-all-icons" class="palleon-tab active">
            <div class="palleon-control-wrap" style="margin:0px;">
                <label class="palleon-control-label"><?php echo esc_html__('Icon Style', 'palleon'); ?></label>
                <div class="palleon-control">
                    <select id="palleon-icon-style" class="palleon-select" autocomplete="off">
                        <option selected value="materialicons"><?php echo esc_html__('Filled', 'palleon'); ?></option>
                        <option value="materialiconsoutlined"><?php echo esc_html__('Outlined', 'palleon'); ?></option>
                        <option value="materialiconsround"><?php echo esc_html__('Round', 'palleon'); ?></option>
                    </select>
                </div>
            </div>
            <hr/>
            <div class="palleon-search-wrap">
                <input id="palleon-icon-search" type="search" class="palleon-form-field" placeholder="<?php echo esc_html__('Enter a keyword...', 'palleon'); ?>" autocomplete="off" />
                <span id="palleon-icon-search-icon" class="material-icons">search</span>
            </div>
            <div id="palleon-icons-grid" class="palleon-grid palleon-elements-grid four-column">
            </div>
            <div id="palleon-noicons" class="notice notice-warning"><?php echo esc_html__('Nothing found.', 'palleon'); ?></div>
        </div>
        <div id="palleon-customsvg-upload" class="palleon-tab">
            <div class="palleon-file-field">
                <input type="file" name="palleon-element-upload" id="palleon-element-upload" class="palleon-hidden-file" accept="image/svg+xml" />
                <label for="palleon-element-upload" class="palleon-btn primary palleon-lg-btn btn-full"><span class="material-icons">upload</span><span><?php echo esc_html__('Upload SVG from computer', 'palleon'); ?></span></label>
            </div>
            <?php $allowSVG =  PalleonSettings::get_option('allow_svg', 'enable');
            if ($allowSVG == 'enable' && is_admin()) {
            ?>
            <button id="palleon-svg-media-library" type="button" class="palleon-btn primary palleon-lg-btn btn-full palleon-modal-open" data-target="#modal-svg-library"><span class="material-icons">photo_library</span><?php echo esc_html__('Select From Media Library', 'palleon'); ?></button>
            <?php } ?>
        </div>
    </div>
</div>