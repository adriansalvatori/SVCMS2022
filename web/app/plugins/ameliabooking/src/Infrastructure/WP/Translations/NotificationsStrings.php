<?php

namespace AmeliaBooking\Infrastructure\WP\Translations;

/**
 * Class NotificationsStrings
 *
 * @package AmeliaBooking\Infrastructure\WP\Translations
 *
 * @SuppressWarnings(ExcessiveMethodLength)
 */
class NotificationsStrings
{
    /**
     * Array of default customer's notifications that are not time based
     *
     * @return array
     */
    public static function getAppointmentCustomerNonTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'customer_appointment_approved',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%service_name% Appointment Approved',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>You have successfully scheduled
                     <strong>%service_name%</strong> appointment with <strong>%employee_full_name%</strong>. We are 
                     waiting you at <strong>%location_address% </strong>on <strong>%appointment_date_time%</strong>.
                     <br><br>Thank you for choosing our company,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_appointment_pending',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%service_name% Appointment Pending',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>The <strong>%service_name%</strong> appointment 
                     with <strong>%employee_full_name%</strong> at <strong>%location_address%</strong>, scheduled for
                     <strong>%appointment_date_time%</strong> is waiting for a confirmation.<br><br>Thank you for 
                     choosing our company,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_appointment_rejected',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%service_name% Appointment Rejected',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>Your <strong>%service_name%</strong> 
                     appointment, scheduled on <strong>%appointment_date_time%</strong> at <strong>%location_address%
                     </strong>has been rejected.<br><br>Thank you for choosing our company,
                     <br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_appointment_canceled',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%service_name% Appointment Canceled',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>Your <strong>%service_name%</strong> 
                     appointment, scheduled on <strong>%appointment_date_time%</strong> at <strong>%location_address%
                     </strong>has been canceled.<br><br>Thank you for choosing our company,
                     <br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_appointment_rescheduled',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%service_name% Appointment Rescheduled',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>The details for your 
                     <strong>%service_name%</strong> appointment with <strong>%employee_full_name%</strong> at 
                     <strong>%location_name%</strong> has been changed. The appointment is now set for 
                     <strong>%appointment_date%</strong> at <strong>%appointment_start_time%</strong>.<br><br>
                     Thank you for choosing our company,<br><strong>%company_name%</strong>'
            ]
        ];
    }

    /**
     * Array of default customer's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getAppointmentCustomerTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'customer_appointment_next_day_reminder',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%service_name% Appointment Reminder',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>We would like to remind you that you have 
                     <strong>%service_name%</strong> appointment tomorrow at <strong>%appointment_start_time%</strong>.
                     We are waiting for you at <strong>%location_name%</strong>.<br><br>Thank you for 
                     choosing our company,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_appointment_follow_up',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 1800,
                'sendTo'     => 'customer',
                'subject'    => '%service_name% Appointment Follow Up',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>Thank you once again for choosing our company. 
                     We hope you were satisfied with your <strong>%service_name%</strong>.<br><br>We look forward to 
                     seeing you again soon,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_birthday_greeting',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'Happy Birthday',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>Happy birthday!<br>We wish you all the best.
                    <br><br>Thank you for choosing our company,<br><strong>%company_name%</strong>'
            ]
        ];
    }


    /**
     * Array of default employee's notifications that are not time based
     *
     * @return array
     */
    public static function getAppointmentProviderNonTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'provider_appointment_approved',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%service_name% Appointment Approved',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>You have one confirmed 
                     <strong>%service_name%</strong> appointment at <strong>%location_name%</strong> on 
                     <strong>%appointment_date%</strong> at <strong>%appointment_start_time%</strong>. The appointment 
                     is added to your schedule.<br><br>Thank you,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'provider_appointment_pending',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%service_name% Appointment Pending',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>You have new appointment 
                     in <strong>%service_name%</strong>. The appointment is waiting for a confirmation.<br><br>Thank 
                     you,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'provider_appointment_rejected',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%service_name% Appointment Rejected',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>Your <strong>%service_name%</strong> appointment 
                     at <strong>%location_name%</strong>, scheduled for <strong>%appointment_date%</strong> at  
                     <strong>%appointment_start_time%</strong> has been rejected.
                     <br><br>Thank you,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'provider_appointment_canceled',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%service_name% Appointment Canceled',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>Your <strong>%service_name%</strong> appointment,
                     scheduled on <strong>%appointment_date%</strong>, at <strong>%location_name%</strong> has been 
                     canceled.<br><br>Thank you,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'provider_appointment_rescheduled',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%service_name% Appointment Rescheduled',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>The details for your 
                     <strong>%service_name%</strong> appointment at <strong>%location_name%</strong> has been changed. 
                     The appointment is now set for <strong>%appointment_date%</strong> at 
                     <strong>%appointment_start_time%</strong>.<br><br>Thank you,<br><strong>%company_name%</strong>'
            ]
        ];
    }

    /**
     * Array of default providers's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getAppointmentProviderTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'provider_appointment_next_day_reminder',
                'entity'     => 'appointment',
                'type'       => 'email',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%service_name% Appointment Reminder',
                'content'    =>
                    'Dear <strong>%employee_full_name%</strong>,<br><br>We would like to remind you that you have 
                     <strong>%service_name%</strong> appointment tomorrow at <strong>%appointment_start_time%</strong>
                     at <strong>%location_name%</strong>.<br><br>Thank you, 
                     <br><strong>%company_name%</strong>'
            ]
        ];
    }

    /**
     * Array of default customer's notifications that are not time based
     *
     * @return array
     */
    public static function getAppointmentCustomerNonTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'customer_appointment_approved',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,

You have successfully scheduled %service_name% appointment with %employee_full_name%. We are waiting for you at %location_address% on %appointment_date_time%.

Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_appointment_pending',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%, 
                    
The %service_name% appointment with %employee_full_name% at %location_address%, scheduled for %appointment_date_time% is waiting for a confirmation.
                    
Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_appointment_rejected',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
Your %service_name% appointment, scheduled on %appointment_date_time% at %location_address% has been rejected.
                    
Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_appointment_canceled',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
Your %service_name% appointment, scheduled on %appointment_date_time% at %location_address% has been canceled. 
                    
Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_appointment_rescheduled',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
The details for your %service_name% appointment with %employee_full_name% at %location_name% has been changed. The appointment is now set for %appointment_date% at %appointment_start_time%.
                    
Thank you for choosing our company,
%company_name%'
            ]
        ];
    }

    /**
     * Array of default customer's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getAppointmentCustomerTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'customer_appointment_next_day_reminder',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
We would like to remind you that you have %service_name% appointment tomorrow at %appointment_start_time%. We are waiting for you at %location_name%.
                    
Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_appointment_follow_up',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 1800,
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
Thank you once again for choosing our company. We hope you were satisfied with your %service_name%.
                     
We look forward to seeing you again soon,
%company_name%'
            ],
            [
                'name'       => 'customer_birthday_greeting',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
Happy birthday! We wish you all the best. 
                    
Thank you for choosing our company,
%company_name%'
            ]
        ];
    }


    /**
     * Array of default employee's notifications that are not time based
     *
     * @return array
     */
    public static function getAppointmentProviderNonTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'provider_appointment_approved',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,
                    
You have one confirmed %service_name% appointment at %location_name% on %appointment_date% at %appointment_start_time%. The appointment is added to your schedule.
                    
Thank you,
%company_name%'
            ],
            [
                'name'       => 'provider_appointment_pending',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,
                    
You have new appointment in %service_name%. The appointment is waiting for a confirmation.
                    
Thank you,
%company_name%'
            ],
            [
                'name'       => 'provider_appointment_rejected',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,
                    
Your %service_name% appointment at %location_name%, scheduled for %appointment_date% at %appointment_start_time% has been rejected. 
                    
Thank you,
%company_name%'
            ],
            [
                'name'       => 'provider_appointment_canceled',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,
                    
Your %service_name% appointment, scheduled on %appointment_date%, at %location_name% has been canceled.
                    
Thank you,
%company_name%'
            ],
            [
                'name'       => 'provider_appointment_rescheduled',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,
                    
The details for your %service_name% appointment at %location_name% has been changed. The appointment is now set for %appointment_date% at %appointment_start_time%.
                    
Thank you,
%company_name%'
            ]
        ];
    }

    /**
     * Array of default providers's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getAppointmentProviderTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'provider_appointment_next_day_reminder',
                'entity'     => 'appointment',
                'type'       => 'sms',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %employee_full_name%, 
                    
We would like to remind you that you have %service_name% appointment tomorrow at %appointment_start_time% at %location_name%.
                    
Thank you, 
%company_name%'
            ]
        ];
    }

    /**
     * Array of default customer's notifications that are not time based
     *
     * @return array
     */
    public static function getEventCustomerNonTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'customer_event_approved',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%event_name% Event Booked',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>You have successfully scheduled
                     <strong>%event_name%</strong> event. We are
                     waiting you at <strong>%event_location% </strong>on <strong>%event_start_date_time%</strong>.
                     <br><br>Thank you for choosing our company,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_event_rejected',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%event_name% Event Canceled By Admin',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>Your <strong>%event_name%</strong>
                     event, scheduled on <strong>%event_start_date_time%</strong> at <strong>%event_location%
                     </strong>has been canceled.<br><br>Thank you for choosing our company,
                     <br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_event_canceled',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%event_name% Event Canceled By Attendee',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>Your <strong>%event_name%</strong>
                     event, scheduled on <strong>%event_start_date_time%</strong> at <strong>%event_location%
                     </strong>has been canceled.<br><br>Thank you for choosing our company,
                     <br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_event_rescheduled',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%event_name% Event Rescheduled',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>The details for your
                     <strong>%event_name%</strong> event at
                     <strong>%event_location%</strong> has been changed. The event is now set for
                     <strong>%event_start_date_time%</strong>.<br><br>
                     Thank you for choosing our company,<br><strong>%company_name%</strong>'
            ]
        ];
    }

    /**
     * Array of default customer's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getEventCustomerTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'customer_event_next_day_reminder',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => '%event_name% Event Reminder',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>We would like to remind you that you have
                     <strong>%event_name%</strong> event tomorrow at <strong>%event_start_date_time%</strong>.
                     We are waiting for you at <strong>%event_location%</strong>.<br><br>Thank you for
                     choosing our company,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'customer_event_follow_up',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 1800,
                'sendTo'     => 'customer',
                'subject'    => '%event_name% Event Follow Up',
                'content'    =>
                    'Dear <strong>%customer_full_name%</strong>,<br><br>Thank you once again for choosing our company.
                     We hope you were satisfied with your <strong>%event_name%</strong>.<br><br>We look forward to
                     seeing you again soon,<br><strong>%company_name%</strong>'
            ]
        ];
    }

    /**
     * Array of default employee's notifications that are not time based
     *
     * @return array
     */
    public static function getEventProviderNonTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'provider_event_approved',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%event_name% Event Booked',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>You have one confirmed
                     <strong>%event_name%</strong> Event at <strong>%event_location%</strong> on
                     <strong>%event_start_date_time%</strong>. The event
                     is added to your schedule.<br><br>Thank you,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'provider_event_rejected',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%event_name% Event Canceled By Admin',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>Your <strong>%event_name%</strong> event
                     at <strong>%event_location%</strong>, scheduled for <strong>%event_start_date_time%</strong>
                     has been canceled.<br><br>Thank you,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'provider_event_canceled',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%event_name% Event Canceled By Customer',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>Your <strong>%event_name%</strong> event,
                     scheduled on <strong>%event_start_date_time%</strong>, at <strong>%event_location%</strong> has been
                     canceled.<br><br>Thank you,<br><strong>%company_name%</strong>'
            ],
            [
                'name'       => 'provider_event_rescheduled',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%event_name% Event Rescheduled',
                'content'    =>
                    'Hi <strong>%employee_full_name%</strong>,<br><br>The details for your
                     <strong>%event_name%</strong> event at <strong>%event_location%</strong> has been changed.
                     The event is now set for <strong>%event_start_date_time%</strong>.
                     <br><br>Thank you,<br><strong>%company_name%</strong>'
            ]
        ];
    }

    /**
     * Array of default providers's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getEventProviderTimeBasedEmailNotifications()
    {
        return [
            [
                'name'       => 'provider_event_next_day_reminder',
                'entity'     => 'event',
                'type'       => 'email',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => '%event_name% Event Reminder',
                'content'    =>
                    'Dear <strong>%employee_full_name%</strong>,<br><br>We would like to remind you that you have 
                     <strong>%event_name%</strong> event at <strong>%event_start_date_time%</strong>
                     at <strong>%event_location%</strong>.<br><br>Thank you, 
                     <br><strong>%company_name%</strong>'
            ]
        ];
    }

    /**
     * Array of default customer's notifications that are not time based
     *
     * @return array
     */
    public static function getEventCustomerNonTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'customer_event_approved',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,

You have successfully scheduled %event_name% event. We are waiting for you at %event_location% on %event_start_date_time%.

Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_event_rejected',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,

Your %event_name% event, scheduled on %event_start_date_time% at %event_location% has been cancelled.

Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_event_canceled',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,

Your %event_name% event, scheduled on %event_start_date_time% at %event_location% has been cancelled.

Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_event_rescheduled',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,

The details for your %event_name% event at %event_location% has been changed. The event is now set for %event_start_date_time%.

Thank you for choosing our company,
%company_name%'
            ]
        ];
    }

    /**
     * Array of default customer's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getEventCustomerTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'customer_event_next_day_reminder',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
We would like to remind you that you have %event_name% event at %event_start_date_time%. We are waiting for you at %event_location%.
                    
Thank you for choosing our company,
%company_name%'
            ],
            [
                'name'       => 'customer_event_follow_up',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 1800,
                'sendTo'     => 'customer',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %customer_full_name%,
                    
Thank you once again for choosing our company. We hope you were satisfied with your %event_name%.
                     
We look forward to seeing you again soon,
%company_name%'
            ]
        ];
    }

    /**
     * Array of default employee's notifications that are not time based
     *
     * @return array
     */
    public static function getEventProviderNonTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'provider_event_approved',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,

You have one confirmed %event_name% event at %event_location% on %event_start_date_time%. The event is added to your schedule.

Thank you,
%company_name%'
            ],
            [
                'name'       => 'provider_event_rejected',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,

Your %event_name% event at %event_location%, scheduled for %event_start_date_time% has been canceled by admin.

Thank you,
%company_name%'
            ],
            [
                'name'       => 'provider_event_canceled',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,

Your %event_name% event, scheduled on %event_start_date_time%, at %event_location% has been canceled.

Thank you,
%company_name%'
            ],
            [
                'name'       => 'provider_event_rescheduled',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => 'NULL',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Hi %employee_full_name%,

The details for your %event_name% event at %event_location% has been changed. The event is now set for %event_start_date_time%.

Thank you,
%company_name%'
            ]
        ];
    }

    /**
     * Array of default providers's notifications that are time based (require cron job)
     *
     * @return array
     */
    public static function getEventProviderTimeBasedSMSNotifications()
    {
        return [
            [
                'name'       => 'provider_event_next_day_reminder',
                'entity'     => 'event',
                'type'       => 'sms',
                'time'       => '"17:00:00"',
                'timeBefore' => 'NULL',
                'timeAfter'  => 'NULL',
                'sendTo'     => 'provider',
                'subject'    => 'NULL',
                'content'    =>
                    'Dear %employee_full_name%, 
                    
We would like to remind you that you have %event_name% event at %event_start_date_time% at %event_location%.
                    
Thank you, 
%company_name%'
            ]
        ];
    }

    /**
     * default customer's notification
     *
     * @return array
     */
    public static function getAccountRecoveryNotification()
    {
        return [
            'name'       => 'customer_account_recovery',
            'entity'     => 'appointment',
            'type'       => 'email',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'customer',
            'subject'    => 'Customer Panel Access',
            'content'    =>
                'Dear <strong>%customer_full_name%</strong>,<br><br>You can access your profile on this <b><a href="%customer_panel_url%">link</a></b>.
                    <br><br>Thank you for choosing our company,<br><strong>%company_name%</strong>'
        ];
    }

    /**
     * default customer's notification
     *
     * @return array
     */
    public static function getEmployeeAccountRecoveryNotification()
    {
        return [
            'name'       => 'provider_panel_recovery',
            'entity'     => 'appointment',
            'type'       => 'email',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'provider',
            'subject'    => 'Employee Panel Access',
            'content'    =>
                'Dear <strong>%employee_full_name%</strong>,<br><br>You can access your profile and track your bookings on this <b><a href="%employee_panel_url%">link</a></b>.
                    <br><br>Best regards,<br><strong>%company_name%</strong>'
        ];
    }

    /**
     * Employee Panel Access Notification
     *
     * @return array
     */
    public static function getEmployeePanelAccessNotification()
    {
        return [
            'name'       => 'provider_panel_access',
            'entity'     => 'appointment',
            'type'       => 'email',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'provider',
            'subject'    => 'Employee Panel Access',
            'content'    =>
                'Dear <strong>%employee_full_name%</strong>,<br><br>You can access your profile and track your bookings on this <b><a href="%employee_panel_url%">link</a></b>.<br><br>Your login credentials:<br>Email: <b>%employee_email%</b><br>Password: <b>%employee_password%</b>
                    <br><br>Best regards,<br><strong>%company_name%</strong>'
        ];
    }

    /**
     * default customer's package notification
     *
     * @return array
     */
    public static function getCustomerPackagePurchasedEmailNotification()
    {
        return [
            'name'       => 'customer_package_purchased',
            'entity'     => 'appointment',
            'type'       => 'email',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'customer',
            'subject'    => 'Package %package_name% purchased',
            'content'    =>
                'Dear <strong>%customer_full_name%</strong>,<br><br>You have successfully purchased
                     <strong>%package_name%</strong>.
                     <br><br>Thank you for choosing our company,<br><strong>%company_name%</strong>'
        ];
    }

    /**
     * default customer's package notification
     *
     * @return array
     */
    public static function getCustomerPackagePurchasedSmsNotification()
    {
        return [
            'name'       => 'customer_package_purchased',
            'entity'     => 'appointment',
            'type'       => 'sms',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'customer',
            'subject'    => 'Package %package_name% purchased',
            'content'    =>
                'Dear %customer_full_name%,

You have successfully purchased %package_name%.

Thank you for choosing our company, %company_name%'
        ];
    }

    /**
     * default provider's package notification
     *
     * @return array
     */
    public static function getProviderPackagePurchasedEmailNotification()
    {
        return [
            'name'       => 'provider_package_purchased',
            'entity'     => 'appointment',
            'type'       => 'email',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'provider',
            'subject'    => 'Package %package_name% purchased',
            'content'    =>
                'Hi <strong>%employee_full_name%</strong>,<br><br>
                     Customer <strong>%customer_full_name%</strong> has purchased <strong>%package_name%</strong> package.<br><br>
                     Thank you,<br><strong>%company_name%</strong>'
        ];
    }

    /**
     * default provider's package notification
     *
     * @return array
     */
    public static function getProviderPackagePurchasedSmsNotification()
    {
        return [
            'name'       => 'provider_package_purchased',
            'entity'     => 'appointment',
            'type'       => 'sms',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'provider',
            'subject'    => 'Package %package_name% purchased',
            'content'    =>
                'Hi %employee_full_name%,

Customer %customer_full_name% has purchased %package_name% package.
                     
Thank you, %company_name%'
        ];
    }

    /**
     * default customer's package canceled notification
     *
     * @return array
     */
    public static function getCustomerPackageCanceledEmailNotification()
    {
        return [
            'name'       => 'customer_package_canceled',
            'entity'     => 'appointment',
            'type'       => 'email',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'customer',
            'subject'    => 'Package %package_name% canceled',
            'content'    =>
                'Dear <strong>%customer_full_name%</strong>, 
                    The <strong>%package_name%</strong> that you have purchased has been canceled. 
                    Thank you,
                    <strong>%company_name%</strong>'
        ];
    }

    /**
     * default customer's package canceled notification
     *
     * @return array
     */
    public static function getCustomerPackageCanceledSmsNotification()
    {
        return [
            'name'       => 'customer_package_canceled',
            'entity'     => 'appointment',
            'type'       => 'sms',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'customer',
            'subject'    => 'Package %package_name% canceled',
            'content'    =>
                'Dear %customer_full_name%, 
The %package_name% that you have purchased has been canceled. 
Thank you,
%company_name%'
        ];
    }

    /**
     * default provider's package canceled notification
     *
     * @return array
     */
    public static function getProviderPackageCanceledEmailNotification()
    {
        return [
            'name'       => 'provider_package_canceled',
            'entity'     => 'appointment',
            'type'       => 'email',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'provider',
            'subject'    => 'Package %package_name% canceled',
            'content'    =>
                'Dear <strong>%employee_full_name%</strong>,
The <strong>%package_name%</strong> purchased by <strong>%customer_full_name%</strong> has been canceled.'
        ];
    }

    /**
     * default provider's package canceled notification
     *
     * @return array
     */
    public static function getProviderPackageCanceledSmsNotification()
    {
        return [
            'name'       => 'provider_package_canceled',
            'entity'     => 'appointment',
            'type'       => 'sms',
            'time'       => 'NULL',
            'timeBefore' => 'NULL',
            'timeAfter'  => 'NULL',
            'sendTo'     => 'provider',
            'subject'    => 'Package %package_name% canceled',
            'content'    =>
                'Dear %employee_full_name%,
The %package_name% purchased by %customer_full_name% has been canceled.'
        ];
    }
}
