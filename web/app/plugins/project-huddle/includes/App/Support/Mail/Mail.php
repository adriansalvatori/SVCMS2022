<?php

namespace PH\Support\Mail;

abstract class Mail
{
    protected function avatar($id)
    {
        return get_avatar(
            $id,
            42,
            '',
            '',
            array(
                'force_display' => true,
                'extra_attr'    => 'style="border-radius: 42px; display: inline-block;"',
            )
        );
    }

    protected function avatarAndName($id)
    {
        if (!$user = wp_get_current_user()) {
            return;
        }
        ob_start(); ?>
        <table cellspacing="0" border="0" cellpadding="0" bgcolor="transparent" style="border:none;border-collapse:separate;border-spacing:0;margin:0;table-layout:fixed">
            <tbody>
                <tr height="15">
                </tr>
                <tr>
                    <td valign="middle" width="50" align="left" style="border:none;font-family:Helvetica,Arial,sans-serif;padding:0;vertical-align:middle!important">
                        <?php echo $this->avatar($id); ?>
                    </td>
                    <td valign="middle" align="left" style="border:none;font-family:Helvetica,Arial,sans-serif;padding:0;vertical-align:middle!important">
                        <strong style="color:#222"><?php echo $user->display_name; ?></td>
                </tr>
            </tbody>
        </table>
<?php return ob_get_clean();
    }
}
