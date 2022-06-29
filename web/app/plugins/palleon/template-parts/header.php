<!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <title><?php echo esc_html__('Palleon Photo Editor', 'palleon'); ?></title>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <?php do_action('palleon_head'); ?>
    </head>
    <?php
    $view = 'backend';
    if (!is_admin()) {
        $view = 'frontend';
    }
    ?>
    <body id="palleon" class="<?php echo $view; ?>">
        <?php do_action('palleon_body_start'); ?>