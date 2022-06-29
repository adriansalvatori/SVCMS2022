<?php

namespace PH\Listeners\Mail;

use PH\Models\Thread;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendResolveMail extends ImmediateMail
{
    public function handle($attr, $value, $thread, $is_batch)
    {
        // bail if batch editing
        if ($is_batch) {
            return;
        }

        // must be resolved attribute and have value
        if ('resolved' !== $attr || !isset($value)) {
            return;
        }

        // get thread
        if (!$thread = new Thread($thread)) {
            return;
        }

        // get members
        if (!$members = $thread->subscribedUsers()) {
            return;
        }

        $current_user = wp_get_current_user();
        $template = $value ? 'email/resolved-thread-email.php' : 'email/unresolved-thread-email.php';
        $subject = $value ? __('%1$1s resolved an issue on %2$2s.', 'project-huddle') : __('%1$1s unresolved an issue on %2$2s.', 'project-huddle');

        // send each email individually
        foreach ($members as $member) {
            // exclude user who commented
            if (get_current_user_id() === $member->ID) {
                continue;
            }

            // bail on project suppressions
            if ($member->isSuppressed('project', $thread->ID)) {
                continue;
            }

            // does the user have a suppression
            if (apply_filters('ph_disable_resolves_emails', $member->isSuppressed('resolves'), $member->ID)) {
                continue;
            }

            // send email
            try {
                (new Mailer('resolves', $thread->projectId()))
                    ->template(
                        ph_locate_template(sanitize_text_field($template)),
                        [
                            'commenter'    => sanitize_text_field($current_user->display_name),
                            'avatar'       => $this->avatar($current_user->ID),
                            'project_name' => ph_get_the_title($thread->parentsIds()['project']),
                            'item_name'    => ph_get_the_title($thread->parentsIds()['item']),
                            'content'      => wpautop($thread->post->post_content),
                            'link'         => ph_email_link($thread->getAccessLink(), __('View Comment', 'project-huddle')),
                        ]
                    )
                    ->subject(apply_filters("ph_project_resolved_thread_email_subject", sprintf($subject, '{{commenter}}', '{{project_name}}'), $value, $thread))
                    ->to($member)
                    ->send();
            } catch (Exception $e) {
                // log error but don't crash anything
                error_log($e);
            }
        }
    }
}
