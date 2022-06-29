<?php

namespace PH\Listeners\Mail;

use PH\Models\User;
use PH\Models\Thread;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendMentionMail extends ImmediateMail
{
    /**
     * Always do mentions
     *
     * @return void
     */
    public function when()
    {
        return true;
    }

    public function handle($user_id, $comment_id, $comment)
    {
        if (!$user = new User($user_id)) {
            return;
        }

        // must have a thread
        if (!isset($comment->comment_post_ID)) {
            return;
        }

        // get thread
        if (!$thread = new Thread($comment->comment_post_ID)) {
            return;
        }

        // bail on project suppressions
        if ($user->isSuppressed('project', $thread->ID)) {
            return;
        }

        // exclude user who commented
        if (get_current_user_id() === $user->ID) {
            return;
        }

        $comment_meta = get_comment_meta( $comment->comment_ID, 'is_private', true);
        $check_private = filter_var( $comment_meta, FILTER_VALIDATE_BOOLEAN );
       
        if( $check_private ) {
            $ph_roles = get_option('ph_private_comment_access', false);
            $ph_roles_array = is_array( $ph_roles ) ? $ph_roles : array();
    
            $user_roles = is_array( $user->roles ) ? $user->roles : array();
            $accessible = array_intersect( $ph_roles_array, $user_roles );
            $is_accessible = ( is_array( $accessible ) && 0 !== sizeof( $accessible ) ) ? 1 : 0;

            if( ! $is_accessible ) {
                return;
            }
        }       

        // send email
        try {
            (new Mailer('mention', $thread->projectId()))
                ->template(
                    ph_locate_template('email/user-mention-email.php'),
                    [
                        'commenter'    => sanitize_text_field($comment->comment_author),
                        'avatar'       => $this->avatar(get_current_user_id()),
                        'project_name' => ph_get_the_title($thread->parentsIds()['project']),
                        'item_name'    => ph_get_the_title($thread->parentsIds()['item']),
                        'content'      => wpautop($comment->comment_content),
                        'link'         => ph_email_link($thread->getAccessLink(), __('View Comment', 'project-huddle')),
                    ]
                )
                ->to($user)
                ->subject(apply_filters('ph_mockup_new_comment_email_subject', sprintf(__('%1$1s mentioned you on %2$2s.', 'project-huddle'), '{{commenter}}', '{{project_name}}'), $comment_id, $comment, $user->user_email))
                ->send();
        } catch (Exception $e) {
            // log error.
            error_log($e);
        }
    }
}
