<?php

/**
 * Sends immediate page approval email
 */

namespace PH\Controllers\Emails\Approval;

use PH\Support\Mail\ImmediateMail;

class ApprovalMail extends ImmediateMail
{
    protected $comment, $model;
    protected $name = '';

    public function __construct($comment, $model)
    {
        if (!$this->comment = get_comment($comment)) {
            throw new \Exception('Not a valid comment.');
        }
        $this->model = $model;
        $this->name = "{$this->model->getSimpleType()}_approvals";
    }

    public function subject()
    {
        return $this->model->isProject() ?
            sprintf(__('%1$1s marked the %2$2s %3$3s as %4$4s', 'project-huddle'), '{{commenter}}', strtolower($this->model->getSingularName()), '{{project_name}}', '{{approval_status}}') :
            sprintf(__('%1$1s marked a %2$2s in %3$3s as %4$4s', 'project-huddle'), '{{commenter}}', strtolower($this->model->getSingularName()), '{{project_name}}', '{{approval_status}}');
    }

    public function send()
    {
        if (!$this->when() || !$this->comment) {
            return;
        }

        $current_user = wp_get_current_user();

        // send each email individually
        foreach ($this->model->project()->subscribedUsers() as $user) {
            // does the user have a suppression
            if (apply_filters("ph_disable_{$this->name}_emails", $user->isSuppressed($this->name), $user->ID)) {
                continue;
            }

            // exclude user who commented
            if ($current_user->ID === $user->ID) {
                continue;
            }

            // send.
            try {
                $this->mailer($this->name, $this->model->projectId())
                    ->template(
                        ph_locate_template("email/{$this->model->getSimpleType()}-approval-email.php"),
                        [
                            'commenter'       => sanitize_text_field(html_entity_decode($current_user->display_name)),
                            'avatar'          => $this->avatar($current_user->ID),
                            'approval_status' => $this->model->isApproved() ? __('Approved', 'project-huddle') : __('Unapproved', 'project-huddle'),
                            'item_name'       => ph_get_the_title($this->model->ID),
                            'project_name'    => ph_get_the_title($this->model->projectId()),
                            'link'            => ph_email_link($this->model->getAccessLink(), __('View', 'project-huddle')),
                        ]
                    )
                    ->subject(apply_filters("ph_{$this->model->projectType()}_approved_{$this->model->getSimpleType()}_subject", $this->subject(), $this->model->projectId(), $user))
                    ->to($user)
                    ->send();
            } catch (\Exception $e) {
                // log error
                error_log($e);
                ph_log($e->getMessage());
            }
        }
    }
}
