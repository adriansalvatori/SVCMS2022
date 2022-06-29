<?php
$img_url = '';
$filename = '';
$attachment_id = '';
if (isset($_GET['attachment_id'])) {
    $attachment_url = wp_get_attachment_url($_GET['attachment_id']);
    if (!empty($attachment_url)) {
        $img_url = esc_url($attachment_url);
        $filename = esc_attr(get_the_title( $_GET['attachment_id'] ));
        $attachment_id = esc_attr($_GET['attachment_id']);
    }
}
$bg_color = Palleon::get_user_option('custom-background', get_current_user_id(), '');
?>
<div id="palleon-body">
    <div class="palleon-wrap">
        <div class="palleon-inner-wrap">
            <div id="palleon-content" class="<?php if (!empty($bg_color)) { echo esc_attr('nobg'); } ?>">
                <div id="palleon-canvas-img-wrap">
                    <img id="palleon-canvas-img" src="<?php echo $img_url; ?>" data-filename="<?php echo $filename; ?>" data-id="<?php echo $attachment_id; ?>" />
                </div>
                <div id="palleon-canvas-wrap">
                    <div id="palleon-canvas-overlay"></div>
                    <div id="palleon-canvas-loader">
                        <div class="palleon-loader"></div>
                    </div>
                    <canvas id="palleon-canvas"></canvas>
                </div>
                <div class="palleon-content-bar">
                    <div class="palleon-img-size"><span id="palleon-img-width">0</span>px<span class="material-icons">clear</span><span id="palleon-img-height">0</span>px</div>
                    <button id="palleon-img-drag" class="palleon-btn"><span class="material-icons">pan_tool</span></button>
                    <div id="palleon-img-zoom-counter" class="palleon-counter">
                        <button id="palleon-img-zoom-out" class="counter-minus palleon-btn"><span class="material-icons">remove</span></button>
                        <input id="palleon-img-zoom" class="palleon-form-field numeric-field" type="text" value="100" autocomplete="off" data-min="10" data-max="200" data-step="5">
                        <button id="palleon-img-zoom-in" class="counter-plus palleon-btn"><span class="material-icons">add</span></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>