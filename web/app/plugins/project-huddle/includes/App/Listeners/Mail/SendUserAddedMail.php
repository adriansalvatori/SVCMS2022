<?php

namespace PH\Listeners\Mail;

use PH\Models\User;
use PH\Models\Project;
use PH\Controllers\Mail\Mailers\Mailer;
use PH\Support\Mail\ImmediateMail;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendUserAddedMail extends ImmediateMail
{
    /**
     * Immediate emails should only trigger when enabled
     * And we have project users
     *
     * @return void
     */
    public function when()
    {
        return true;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($id, $project_id)
    {
        // needs to be a project
        if (!in_array(get_post_type($project_id), ph_get_post_types())) {
            return;
        }

        // get current user
        $user = new User($id);
        $current_user = new User(get_current_user_id());
        $project = new Project($project_id);

        // do not notify ourselves
        if (!$current_user->display_name || $id === $current_user->ID) {
            return;
        }

        // send the email
        try {
            (new Mailer('user_added'))
                ->template(
                    ph_locate_template('email/subscribe-project-email.php'),
                    [
                        'commenter'    => sanitize_text_field($current_user->display_name),
                        'avatar'       => $this->avatar($current_user->ID),
                        'project_name' => sanitize_text_field($project->post->post_title),
                        'link'         => ph_email_link($project->getAccessLink(), __('View Project', 'project-huddle')),
                    ]
                )
                ->to($user)
                ->subject(htmlspecialchars_decode(apply_filters('ph_subscribe_project_email_subject', sprintf(__('You have been invited to collaborate on %1s.', 'project-huddle'), '{{project_name}}'), $user, $project_id)))
                ->send($project_id);
        } catch (\Exception $e) {
            // log error
            error_log($e);
            ph_log($e);
        }
    }
}
