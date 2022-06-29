<?php
$frames = PalleonSettings::get_option('module_frames', 'enable');
$text = PalleonSettings::get_option('module_text', 'enable');
$image = PalleonSettings::get_option('module_image', 'enable');
$shapes = PalleonSettings::get_option('module_shapes', 'enable');
$elements = PalleonSettings::get_option('module_elements', 'enable');
$brushes = PalleonSettings::get_option('module_brushes', 'enable');
?>
<div id="palleon-icon-menu">
    <button id="palleon-btn-adjust" type="button" class="palleon-icon-menu-btn active" data-target="#palleon-adjust">
        <span class="material-icons">tune</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Adjust', 'palleon'); ?></span>
    </button>
    <?php if ($frames == 'enable') { ?>
    <button id="palleon-btn-frames" type="button" class="palleon-icon-menu-btn" data-target="#palleon-frames">
        <span class="material-icons">wallpaper</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Frames', 'palleon'); ?></span>
    </button>
    <?php } ?>
    <?php if ($text == 'enable') { ?>
    <button id="palleon-btn-text" type="button" class="palleon-icon-menu-btn" data-target="#palleon-text">
        <span class="material-icons">title</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Text', 'palleon'); ?></span>
    </button>
    <?php } ?>
    <?php if ($image == 'enable') { ?>
    <button id="palleon-btn-image" type="button" class="palleon-icon-menu-btn" data-target="#palleon-image">
        <span class="material-icons">add_photo_alternate</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Image', 'palleon'); ?></span>
    </button>
    <?php } ?>
    <?php if ($shapes == 'enable') { ?>
    <button id="palleon-btn-shapes" type="button" class="palleon-icon-menu-btn" data-target="#palleon-shapes">
        <span class="material-icons">category</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Shapes', 'palleon'); ?></span>
    </button>
    <?php } ?>
    <?php if ($elements == 'enable') { ?>
    <button id="palleon-btn-elements" type="button" class="palleon-icon-menu-btn" data-target="#palleon-elements">
        <span class="material-icons">star</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Elements', 'palleon'); ?></span>
    </button>
    <button id="palleon-btn-shapes" type="button" class="palleon-icon-menu-btn" data-target="#palleon-icons">
        <span class="material-icons">place</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Icons', 'palleon'); ?></span>
    </button>
    <?php } ?>
    <?php if ($brushes == 'enable') { ?>
    <button id="palleon-btn-draw" type="button" class="palleon-icon-menu-btn" data-target="#palleon-draw">
        <span class="material-icons">brush</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Brushes', 'palleon'); ?></span>
    </button>
    <?php } ?>
    <button id="palleon-btn-settings" type="button" class="palleon-icon-menu-btn stick-to-bottom" data-target="#palleon-settings">
        <span class="material-icons">settings</span><span class="palleon-icon-menu-title"><?php echo esc_html__('Settings', 'palleon'); ?></span>
    </button>
</div>