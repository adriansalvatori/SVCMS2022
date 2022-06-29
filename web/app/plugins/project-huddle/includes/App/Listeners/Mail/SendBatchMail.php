<?php

namespace PH\Listeners\Mail;

use PH\Models\User;
use PH\Support\Mail\ScheduledMail;
use PH\Controllers\Mail\Mailers\ActivityMailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendBatchMail extends ScheduledMail
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($post_ids)
    {
        // loop through each user
        if (!$users = ph_get_all_project_users()) {
            return;
        }

        foreach ($users as $user_id) {
            // get users activity comments
            if (!$user = new User($user_id)) {
                continue;
            }

            // does the user have a suppression
            if (apply_filters('ph_disable_batch_emails', false, $user->ID)) {
                continue;
            }

            // get get immediate batch project activity
            $activity = $user->projectsActivity()
                ->from('now')
                ->to('now')
                ->includeIds($post_ids)
                ->subscribed(apply_filters('ph_activity_emails_subscribed_only', true))
                ->groupBy('project_id')
                ->get();

            // send email if we have activity
            if ($activity) {
                (new ActivityMailer('batch'))
                    ->activity($activity)
                    ->to($user)
                    ->send();
            }
        }
    }
}
