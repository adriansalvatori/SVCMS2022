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
class SendShareMail extends ImmediateMail
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
    public function handle($address, $subject = '', $message = '', $post_id = '')
    {
        $check = $this->checkPermissions($post_id);
        if (is_wp_error($this->checkPermissions($post_id))) {
            return $check->get_error_message();
        }

        $project = new Project($post_id);
        $user = new User(get_current_user_id());

        // email each new person
        foreach ((array) $address as $email) {
            // send the email
            try {
                (new Mailer('share'))
                    ->template(
                        ph_locate_template('email/generic-email.php'),
                        [
                            'commenter' => sanitize_text_field($user->display_name),
                            'avatar'    => $this->avatar($user->ID),
                            'content'   => wpautop($message),
                            'link'      => ph_email_link($project->getAccessLink(), __('View', 'project-huddle')),
                        ]
                    )
                    ->to(apply_filters('ph_share_post_email_to', $email, $post_id))
                    ->subject(apply_filters('ph_share_post_email_subject', $subject, $post_id, $email))
                    ->send($post_id);
            } catch (Exception $e) {
                // log error
                error_log($e);
            }
        }
    }

    /**
     * Check special permissions to send this one
     *
     * @param Integer $post_id
     * @return void
     */
    protected function checkPermissions($post_id)
    {
        $post = get_post($post_id);
        $post_type_obj = get_post_type_object($post->post_type);
        if (!current_user_can($post_type_obj->cap->read_private_posts)) {
            return new WP_Error('forbidden', __('Sorry, you must be logged in and have permissions to view this.'), array('status' => rest_authorization_required_code()));
        }
        return true;
    }
}
