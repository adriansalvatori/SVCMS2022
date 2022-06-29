<div id="palleon-icon-panel">
    <div id="palleon-icon-panel-inner">
    <?php
    $frames = PalleonSettings::get_option('module_frames', 'enable');
    $text = PalleonSettings::get_option('module_text', 'enable');
    $image = PalleonSettings::get_option('module_image', 'enable');
    $shapes = PalleonSettings::get_option('module_shapes', 'enable');
    $elements = PalleonSettings::get_option('module_elements', 'enable');
    $brushes = PalleonSettings::get_option('module_brushes', 'enable');
    include_once("panel-parts/adjust.php");
    if ($frames == 'enable') {
        include_once("panel-parts/frames.php");
    }
    if ($text == 'enable') {
        include_once("panel-parts/text.php");
    }
    if ($image == 'enable') {
        include_once("panel-parts/image.php");
    }
    if ($shapes == 'enable') {
        include_once("panel-parts/shapes.php");
    }
    if ($elements == 'enable') {
        include_once("panel-parts/elements.php");
        include_once("panel-parts/icons.php");
    }
    if ($brushes == 'enable') {
        include_once("panel-parts/brushes.php");
    }
    include_once("panel-parts/settings.php");
    ?>
    </div>
</div>
<div id="palleon-toggle-left"><span class="material-icons">chevron_left</span></div>