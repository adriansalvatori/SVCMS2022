<?php

namespace PH\Listeners\Mail;

use PH\Models\Item;
use PH\Models\Page;
use PH\Support\Mail\ImmediateMail;
use PH\Controllers\Mail\Mailers\Mailer;

class SendItemApprovedMail extends ImmediateMail
{
    public function handle(Item $item, $approved)
    {
        $current_user = wp_get_current_user();
        $project = $item->project();
        $project_users = $project->subscribedUsers();

        // send each email individually
        foreach ($project_users as $user) {
            // does the user have a suppression
            if (apply_filters('ph_disable_image_approvals_emails', $user->isSuppressed('image_approvals'), $user->ID)) {
                continue;
            }

            // exclude user who commented
            if (get_current_user_id() === $user->ID) {
                continue;
            }

            $type = is_a($item, Page::class) ? __('a page', 'project-huddle') : __('an image', 'project-hudde');

            // send.
            try {
                (new Mailer('image_approvals', $item->projectId()))
                    ->template(
                        ph_locate_template('email/item-approval-email.php'),
                        [
                            'commenter'       => sanitize_text_field(html_entity_decode($current_user->display_name)),
                            'avatar'          => $this->avatar($current_user->ID),
                            'approval_status' => $approved ? __('Approved', 'project-huddle') : __('Unapproved', 'project-huddle'),
                            'item_name'       => ph_get_the_title($item->ID),
                            'type'            => esc_html($type),
                            'project_name'    => ph_get_the_title($item->projectId()),
                            'link'            => ph_email_link($item->getAccessLink(), $type === 'image' ? __('View Image', 'project-huddle') : __('View Page', 'project-huddle')),
                        ]
                    )
                    ->subject(apply_filters('ph_item_approved_subject', sprintf(__('%1$1s marked %2$2s in %3$3s as %4$4s', 'project-huddle'), '{{commenter}}', '{{type}}', '{{project_name}}', '{{approval_status}}'), $item->ID, $user))
                    ->to($user)
                    ->send();
            } catch (\Exception $e) {
                // log error
                error_log($e);
            }
        }
    }
}
