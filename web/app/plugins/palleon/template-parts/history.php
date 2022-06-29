<?php
$history = PalleonSettings::get_option('history', 'enable');
$historyLog = PalleonSettings::get_option('max_history_log', 50);
if ($history == 'enable') {
?>
<div id="modal-history" class="palleon-modal">
    <div class="palleon-modal-close" data-target="#modal-history"><span class="material-icons">close</span></div>
    <div class="palleon-modal-wrap">
        <div class="palleon-modal-inner">
            <div class="palleon-modal-bg">
                <h3 class="palleon-history-title"><?php echo esc_html__('History', 'palleon'); ?><button id="palleon-clear-history" type="button" class="palleon-btn danger"><span class="material-icons">clear</span><?php echo esc_html__('Clear History', 'palleon'); ?></button></h3>
                <ul id="palleon-history-list" class="palleon-template-list" data-max="<?php echo esc_attr($historyLog); ?>"></ul>
                <p class="palleon-history-count"><?php echo esc_html__( 'Showing your last', 'palleon' ); ?> <span id="palleon-history-count"></span> <?php echo esc_html__( 'actions.', 'palleon' ); ?></p>
            </div>
        </div>
    </div>
</div>  
<?php } ?>  