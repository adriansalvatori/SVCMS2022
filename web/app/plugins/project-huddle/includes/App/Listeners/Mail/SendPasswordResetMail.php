<?php

namespace PH\Listeners\Mail;

use PH\Models\User;
use PH\Models\Project;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendPasswordResetMail extends ImmediateMail
{
    /**
     * Always do shares
     *
     * @return void
     */
    public function when()
    {
        return true;
    }

    /**
     * Handle the action
     *
     * @param array $address
     * @param string $subject
     * @param string $message
     * @param integer $post_id
     * @return void
     */
    public function handle($user, $request)
    {
        // bail if user already provided password
        if (isset($request['password'])) {
            return;
        }

        //  get user login
        if (!is_a($user, 'WP_User') && is_string($user)) {
            if (filter_var($user, FILTER_VALIDATE_EMAIL)) {
                $user = get_user_by('email', $user);
            } else {
                $user = get_user_by('login', $user);
            }
        }

        if (is_wp_error($user)) {
            return $user;
        }

        // password reset key
        $key        = get_password_reset_key($user);
        $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
        
        try {
            (new Mailer('password_reset', (int) $request['project_id']))
                ->template(
                    ph_locate_template('email/registration-email.php'),
                    [
                        'link'         => $reset_link,
                        'username'     => sanitize_text_field($user->user_login),
                        'site_name' => sanitize_text_field(get_bloginfo()),
                    ]
                )
                ->to($user)
                ->subject(apply_filters('ph_set_password_email_subject', __('Please set your password', 'project-huddle'), $user, $request))
                ->send();
        } catch (Exception $e) {
            // log error
            error_log($e);
        }
    }
}
