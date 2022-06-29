<?php

namespace PH\Support\Mail;

class ScheduledMail extends Mail
{
    /**
     * Scheduled emails should only trigger when enabled
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
        return PH()->activity_emails->is_throttled() && !empty(ph_get_all_project_users());
    }
}
