<?php

namespace PH\Listeners\Mail;

use PH\Contracts\Model;
use PH\Models\Project;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

class SendProjectApprovedMail extends ImmediateMail
{
    public function handle(Model $project, $approved)
    {
        $current_user = wp_get_current_user();
        $project_users = $project->subscribedUsers();

        // send each email individually
        foreach ($project_users as $user) {
            // exclude user who commented
            if (get_current_user_id() === $user->ID) {
                continue;
            }

            // bail on suppressions
            if ($user->isSuppressed('project_approvals')) {
                continue;
            }

            // does the user have a suppression
            if (apply_filters('ph_disable_project_approvals_emails', $user->isSuppressed('project_approvals'), $user->ID)) {
                continue;
            }

            try {
                (new Mailer('project_approvals', $project->ID))
                    ->template(
                        ph_locate_template('email/project-approval-email.php'),
                        [
                            'commenter'       => sanitize_text_field(html_entity_decode($current_user->display_name)),
                            'avatar'          => $this->avatar($current_user->ID),
                            'approval_status' => $approved ? __('Approved', 'project-huddle') : __('Unapproved', 'project-huddle'),
                            'project_name'    => ph_get_the_title($project->ID),
                            'link'            => ph_email_link($project->getAccessLink(), __('View Project', 'project-huddle')),
                        ]
                    )
                    ->subject(apply_filters('ph_mockup_approved_project_subject', sprintf(__('%1$1s marked the project %2$2s as %3$3s', 'project-huddle'), '{{commenter}}', '{{project_name}}', '{{approval_status}}'), $project->ID))
                    ->to($user)
                    ->send();
            } catch (\Exception $e) {
                // log error
                error_log($e);
            }
        }
    }
}
