<?php

namespace PH\Support\Mail;

use PH\Controllers\Mail\Mailers\Mailer;

class ImmediateMail extends Mail
{
    protected $project_id;

    protected function mailer($name, $project_id)
    {
        return new Mailer($name, $project_id);
    }

    /**
     * Immediate emails should only trigger when enabled
     * And we have project users
     *
     * @return void
     */
    public function when()
    {
        // bail if emails are not enabled at all
        if (!PH()->activity_emails->emailsEnabled()) {
            return false;
        }

        // only run if emails are set to throttled and we have project users
        return !PH()->activity_emails->is_throttled();
    }

    /**
     * Get subscribed users for a specific thread
     *
     * @param array $args
     * @return void
     */
    public function getThreadUsers($args)
    {
        $args = extract(wp_parse_args($args, [
            'id' => 0,
            'comment' => null,
            'type' => '',
            'exclude' => []
        ]));

        // validate
        if (!$type) {
            return new WP_Error('You need to specify a comment type');
        }
        if (!$id || !is_a($comment, 'WP_Comment')) {
            return new WP_Error('You need to specify a specific comment');
        }
        if (!$comment->comment_post_ID) {
            return new WP_Error('The comment does not belong to a thread.');
        }

        $thread = new Thread($comment->comment_post_ID);

        return $thread->subscribedUsers();


        // to emails
        $to_emails = ph_get_comment_thread_emails($id, get_user_by('email', $comment->comment_author_email));

        // exclude any users
        if (!empty($exclude)) {
            foreach ($exclude as $id) {
                $user = get_user_by('ID', $id);
                if (false !== ($key = array_search($user->user_email, $to_emails))) {
                    unset($to_emails[$key]);
                }
            }
        }

        // exlude suppressed users
        if (!empty($to_emails)) {
            foreach ($to_emails as $key => $id) {
                $user = User($id);
                if ($user->isSuppressed($type)) {
                    unset($to_emails[$key]);
                }
            }
        }

        return (array) apply_filters("ph_{$type}_email_to", $to_emails, $id);
    }
}
