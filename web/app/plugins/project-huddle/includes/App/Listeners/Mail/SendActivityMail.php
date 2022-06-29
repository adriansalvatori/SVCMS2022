<?php

namespace PH\Listeners\Mail;

use PH\Models\User;
use PH\Support\Mail\ScheduledMail;
use PH\Controllers\Mail\Mailers\ActivityMailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendActivityMail extends ScheduledMail
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle()
    {
        // get last date
        $from = '-' . PH()->activity_emails->get_interval() . ' minutes';
        $last = PH()->activity_emails->get_last_scheduled_action_date('ph_activity_summary_email');
        if (!$last || strtotime($last) > strtotime($from)) {
            $last = $from;
        }

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
            if (apply_filters('ph_disable_activity_emails', $user->isSuppressed('activity'), $user->ID)) {
                continue;
            }

            // get user's project activity
            $activity = $user->projectsActivity()
                ->type('activity')
                ->subscribed(apply_filters('ph_activity_emails_subscribed_only', true))
                ->groupBy('project_id')
                ->get();

            // send email if we have activity
            if ($activity) {
                (new ActivityMailer('activity'))
                    ->activity($activity)
                    ->to($user)
                    ->send();
            }
        }
    }
}
