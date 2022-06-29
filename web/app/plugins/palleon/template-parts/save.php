<?php 
$templates = PalleonSettings::get_option('module_templates', 'enable'); 
$mytemplates = PalleonSettings::get_option('allow_json','enable'); 
?>
<div id="modal-save" class="palleon-modal">
    <div class="palleon-modal-close" data-target="#modal-save"><span class="material-icons">close</span></div>
    <div class="palleon-modal-wrap">
        <div class="palleon-modal-inner">
            <div class="palleon-tabs">
                <ul class="palleon-tabs-menu">
                    <?php if (is_admin()) { ?>
                    <li class="active" data-target="#modal-tab-save"><span class="material-icons">save</span><?php echo esc_html__('Save', 'palleon'); ?></li>
                    <?php } ?>
                    <li <?php if (!is_admin()) { echo 'class="active"'; } ?> data-target="#modal-tab-download"><span class="material-icons">download</span><?php echo esc_html__('Download', 'palleon'); ?></li>
                </ul>
                <?php if (is_admin()) { ?>
                <div id="modal-tab-save" class="palleon-tab active">
                    <div id="palleon-save-as-img">
                        <div class="palleon-block-50">
                            <div>
                                <label><?php echo esc_html__('File Name', 'palleon'); ?></label>
                                <input id="palleon-save-img-name" class="palleon-form-field palleon-file-name" type="text" value="" autocomplete="off" data-default="">
                            </div>
                            <button id="palleon-save-img" type="button" class="palleon-btn primary"><span class="material-icons">save</span><?php echo esc_html__('Save As Image', 'palleon'); ?></button>
                        </div>
                        <div class="palleon-block-50">
                            <div>
                                <label><?php echo esc_html__('File Format', 'palleon'); ?></label>
                                <select id="palleon-save-img-format" class="palleon-select" autocomplete="off">
                                    <option selected value="jpeg">JPEG</option>
                                    <option value="png">PNG</option>
                                </select>
                            </div>
                            <div>
                                <label><?php echo esc_html__('Image Quality (Only Used For JPEG)', 'palleon'); ?></label>
                                <select id="palleon-save-img-quality" class="palleon-select" autocomplete="off">
                                    <option selected value="1">100%</option>
                                    <option value="0.9">90%</option>
                                    <option value="0.8">80%</option>
                                    <option value="0.7">70%</option>
                                    <option value="0.6">60%</option>
                                    <option value="0.5">50%</option>
                                    <option value="0.4">40%</option>
                                    <option value="0.3">30%</option>
                                    <option value="0.2">20%</option>
                                    <option value="0.1">10%</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php if ($templates == 'enable' && $mytemplates == 'enable') { ?>
                    <div id="palleon-save-as-json">
                        <div class="palleon-block-50">
                            <div>
                                <label><?php echo esc_html__('File Name', 'palleon'); ?></label>
                                <input id="palleon-json-save-name" class="palleon-form-field palleon-file-name" type="text" value="" autocomplete="off" data-default="">
                            </div>
                            <button id="palleon-json-save" type="button" class="palleon-btn primary"><span class="material-icons">save</span><?php echo esc_html__('Save As Template', 'palleon'); ?></button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
                <div id="modal-tab-download" class="palleon-tab <?php if (!is_admin()) { echo 'active'; } ?>">
                    <div id="palleon-download-as-img">
                        <div class="palleon-block-50">
                            <div>
                                <label><?php echo esc_html__('File Name', 'palleon'); ?></label>
                                <input id="palleon-download-name" class="palleon-form-field palleon-file-name" type="text" value="" autocomplete="off" data-default="">
                            </div>
                            <button id="palleon-download" type="button" class="palleon-btn primary"><span class="material-icons">download</span><?php echo esc_html__('Download As Image', 'palleon'); ?></button>
                        </div>
                        <div class="palleon-block-50">
                            <div>
                                <label><?php echo esc_html__('File Format', 'palleon'); ?></label>
                                <select id="palleon-download-format" class="palleon-select" autocomplete="off">
                                    <option selected value="jpeg">JPEG</option>
                                    <option value="png">PNG</option>
                                </select>
                            </div>
                            <div>
                                <label><?php echo esc_html__('Image Quality (Only Used For JPEG)', 'palleon'); ?></label>
                                <select id="palleon-download-quality" class="palleon-select" autocomplete="off">
                                    <option selected value="1">100%</option>
                                    <option value="0.9">90%</option>
                                    <option value="0.8">80%</option>
                                    <option value="0.7">70%</option>
                                    <option value="0.6">60%</option>
                                    <option value="0.5">50%</option>
                                    <option value="0.4">40%</option>
                                    <option value="0.3">30%</option>
                                    <option value="0.2">20%</option>
                                    <option value="0.1">10%</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php if ($templates == 'enable') { ?>
                    <div id="palleon-download-as-json">
                        <div class="palleon-block-50">
                            <div>
                                <label><?php echo esc_html__('File Name', 'palleon'); ?></label>
                                <input id="palleon-json-download-name" class="palleon-form-field palleon-file-name" type="text" value="" autocomplete="off" data-default="">
                            </div>
                            <button id="palleon-json-download" type="button" class="palleon-btn primary"><span class="material-icons">download</span><?php echo esc_html__('Download As Template', 'palleon'); ?></button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>