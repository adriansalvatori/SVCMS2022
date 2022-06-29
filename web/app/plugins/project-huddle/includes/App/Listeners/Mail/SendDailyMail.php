<?php

namespace PH\Listeners\Mail;

use PH\Models\User;
use PH\Support\Mail\ScheduledMail;
use PH\Controllers\Mail\Mailers\ActivityMailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendDailyMail extends ScheduledMail
{

    /**
     * Scheduled emails should only trigger when we have project users
     *
     * @return void
     */
    public function when()
    {
        // only run if we have project users
        return !empty(ph_get_all_project_users());
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle()
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
            if (apply_filters('ph_disable_daily_emails', $user->isSuppressed('daily'), $user->ID)) {
                continue;
            }

            $activity = $user->projectsActivity()
                ->type('daily')
                ->groupBy('project_id')
                ->get();

            // send activity email
            if ($activity) {
                (new ActivityMailer('daily'))
                    ->activity($activity)
                    ->to($user)
                    ->subject(__('Your Daily Report', 'project-huddle'))
                    ->title(__('Your Daily Report', 'project-huddle'))
                    ->message(__('Here\'s a summary of your project activity for the last day.', 'project-huddle'))
                    ->send();
            }
        }
    }
}
