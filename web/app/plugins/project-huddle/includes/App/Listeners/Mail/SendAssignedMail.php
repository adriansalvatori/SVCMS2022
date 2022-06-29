<?php

namespace PH\Listeners\Mail;

use PH\Models\Thread;
use PH\Models\User;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

if (!defined('ABSPATH')) exit;

/**
 * Send activity summary mail
 */
class SendAssignedMail extends ImmediateMail
{

    protected $user;
    protected $current_user;
    protected $thread;

    public function handle($attr, $value, $thread, $is_batch)
    {
        // bail if batch editing
        if ($is_batch || !$value) {
            return;
        }

        // must be assigned attribute and have value
        if ('assigned' !== $attr || !isset($value)) {
            return;
        }

        // get thread
        if (!$this->thread = new Thread($thread)) {
            return;
        }

        // get user info
        if (!$this->user = new User($value)) {
            return;
        }

        // current user info
        if (!$this->current_user = new User(get_current_user_id())) {
            return;
        }

        // does the user have a suppression
        if (apply_filters('ph_disable_assign_emails', $this->isSuppressed(), $this->user->ID)) {
            return;
        }

        // exclude user who commented
        if ($this->current_user->ID === $this->user->ID) {
            return;
        }

        // send email
        try {
            (new Mailer('assigns', $this->thread->projectId()))
                ->to($this->user)
                ->template(
                    ph_locate_template('email/assigned-thread-email.php'),
                    [
                        'commenter'    => sanitize_text_field($this->current_user->display_name),
                        'avatar'       => $this->avatar($this->current_user->ID),
                        'project_name' => ph_get_the_title($this->thread->parentsIds()['project']),
                        'item_name'    => ph_get_the_title($this->thread->parentsIds()['item']),
                        'content'      => wpautop($this->thread->post->post_content),
                        'link'         => ph_email_link($this->thread->getAccessLink(), __('View Comment', 'project-huddle')),
                    ]
                )
                ->subject(apply_filters("ph_project_assigned_thread_email_subject", sprintf(__('You have been assigned to an issue on %1s.', 'project-huddle'), '{{project_name}}'), $value, $this->thread))
                ->send();
        } catch (Exception $e) {
            // log error but don't crash anything
            error_log($e);
        }
    }


    protected function isSuppressed()
    {
        // don't notify yourself, silly!
        if ($this->user->ID === $this->current_user->ID) {
            return true;
        }

        // bail on project suppressions
        if ($this->user->isSuppressed('project', $this->thread->ID)) {
            return true;
        }

        // bail on assignment suppressions
        if ($this->user->isSuppressed('assigns')) {
            return true;
        }

        return false;
    }
}
