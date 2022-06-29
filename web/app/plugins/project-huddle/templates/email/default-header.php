<?php

/**
 * Default Email Header
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
	<style type="text/css">
		<?php
		// email styles
		$file = PH_PLUGIN_DIR . 'assets/css/email-styles.css';

		if (file_exists($file)) {
			readfile($file);
		}
		?>body {
			margin: 0px !important;
			padding: 0px !important;
		}
	</style>
	<title><?php _e('ProjectHuddle Email', 'project-huddle'); ?></title>
</head>

<body>
	<table cellpadding="0" cellspacing="0" width="100%">
		<tbody>
			<tr>
				<td align="center" valign="top">
					<table cellpadding="0" cellspacing="0" width="">
						<tbody>
							<tr>
								<td align="left" width="400" style="padding: 45px;">
									<?php if ($logo = apply_filters('ph_email_logo', get_option('ph_login_logo'))) : ?>

										<?php
										// get logo image
										$logo_image = wp_get_attachment_image_src($logo, 'full');

										// check retina option
										if (apply_filters('ph_login_logo_retina', get_option('ph_login_logo_retina'))) :
											$logo_image[1] = $logo_image[1] / 2;
											$logo_image[2] = $logo_image[2] / 2;
										endif;
										?>

										<table cellpadding="0" cellspacing="0" width="100%">
											<tbody>
												<tr>
													<td style="width:50px;vertical-align:top; text-align:center; padding-bottom:25px;">
														<img src="<?php echo esc_url($logo_image[0]); ?>" width="<?php echo (float) $logo_image[1]; ?>" height="<?php echo (float) $logo_image[2]; ?>" alt="<?php _e('Avatar', 'project-huddle'); ?>" style="margin-top:2px;height:auto;line-height:100%;outline:none;text-decoration:none;" />
													</td>
												</tr>
											</tbody>
										</table>

									<?php endif; ?>


									<?php do_action('ph_default_email_header'); ?>

									<table cellpadding="0" cellspacing="0" width="100%">
										<tbody>
											<tr>