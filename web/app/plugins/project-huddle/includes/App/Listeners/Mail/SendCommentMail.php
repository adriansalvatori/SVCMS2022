<?php

namespace PH\Listeners\Mail;

use PH\Models\Thread;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendCommentMail extends ImmediateMail
{
    /**
     * Handle
     *
     * @param integer $id
     * @param WP_Comment $comment
     * @param array $mentioned_user_ids
     * @return void
     */
    public function handle($id, $comment, $mentioned_user_ids)
    {
        // must have a thread
        if (!isset($comment->comment_post_ID)) {
            return;
        }

        // get thread
        if (!$thread = new Thread($comment->comment_post_ID)) {
            return;
        }

        // get members
        if (!$members = $thread->subscribedUsers()) {
            return;
        }

        $comment_meta = get_comment_meta( $comment->comment_ID, 'is_private', true);
        $check_private = filter_var( $comment_meta, FILTER_VALIDATE_BOOLEAN );
        $ph_roles = get_option('ph_private_comment_access', false);
        $ph_roles_array = is_array( $ph_roles ) ? $ph_roles : array();

        // send each email individually
        foreach ($members as $member) {
            $is_accessible = 1;
            // exclude mentioned users
            if (in_array($member->ID, $mentioned_user_ids)) {
                continue;
            }

            // exclude user who commented
            if (get_current_user_id() === $member->ID) {
                continue;
            }

            if( $check_private ) {
                $user_roles = is_array( $member->roles ) ? $member->roles : array();
                $accessible = array_intersect( $ph_roles_array, $user_roles );
                $is_accessible = ( is_array( $accessible ) && 0 !== sizeof( $accessible ) ) ? 1 : 0;
            }
            if( ( $check_private && ( ! $is_accessible ) ) ) {
                continue;
            }

            // send email
            try {
                (new Mailer('comments', $thread->projectId()))
                    ->to($member)
                    ->template(
                        ph_locate_template('email/new-comment-email.php'),
                        [
                            'commenter'    => sanitize_text_field($comment->comment_author),
                            'avatar'       => $this->avatar(get_current_user_id()),
                            'project_name' => ph_get_the_title($thread->parentsIds()['project']),
                            'item_name'    => ph_get_the_title($thread->parentsIds()['item']),
                            'content'      => wpautop($comment->comment_content),
                            'link'         => ph_email_link($thread->getAccessLink(), __('View Comment', 'project-huddle')),
                            'comment_text' => ( $check_private && $is_accessible ) ? __('private comment', 'project-huddle') : __('comment', 'project-huddle'),
                        ]
                    )
                    ->subject(apply_filters('ph_mockup_new_comment_email_subject', sprintf(__('%1$1s made a new comment on %2$2s.', 'project-huddle'), '{{commenter}}', '{{project_name}}'), $id, $comment, $member->user_email))
                    ->send();
            } catch (Exception $e) {
                // log error but don't crash anything
                error_log($e);
            }
        }
    }
}
