<?php

namespace PH\Listeners\Mail;

use PH\Models\Post;
use PH\Models\User;
use PH\Models\Thread;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;
use PH\Controllers\Mail\Mailers\ActivityMailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendBatchActivityMail extends ImmediateMail
{
    public function when()
    {
        return true;
    }

    /**
     * Get users combined from all posts during batch
     *
     * @param array $post_ids
     * @return array
     */
    protected function getSubscribedIds($post_ids)
    {
        $members = [];
        foreach ($post_ids as $id) {
            $model = (new Post($id))->getModel();
            $subscribed = (array) (new $model($id))->subscribedIds();
            $members = array_merge($members, $subscribed);
        }
        return array_unique($members);
    }

    public function handle($post_ids)
    {
        // get users from these post ids
        if (!$members = $this->getSubscribedIds($post_ids)) {
            return;
        }

        // send each email individually
        foreach ($members as $user_id) {
            // get users activity comments
            if (!$user = new User($user_id)) {
                continue;
            }

            // does the user have a suppression
            if (apply_filters('ph_disable_batch_emails', false, $user->ID)) {
                continue;
            }

            // get user's project activity from now only
            $activity = $user->projectsActivity()
                ->includeIds($post_ids)
                ->groupBy('project_id')
                ->get();

            // send email if we have activity
            if (!empty($activity)) {
                (new ActivityMailer('batch'))
                    ->message('')
                    ->activity($activity)
                    ->to($user)
                    ->send();
            }
        }
    }
}
