<?php

defined('ABSPATH') or die('No script kiddies please!');

use AmeliaBooking\Application\Commands\Activation;
use AmeliaBooking\Application\Commands\Bookable;
use AmeliaBooking\Application\Commands\Booking;
use AmeliaBooking\Application\Commands\Coupon;
use AmeliaBooking\Application\Commands\CustomField;
use AmeliaBooking\Application\Commands\Entities;
use AmeliaBooking\Application\Commands\Stash;
use AmeliaBooking\Application\Commands\Google;
use AmeliaBooking\Application\Commands\Location;
use AmeliaBooking\Application\Commands\Notification;
use AmeliaBooking\Application\Commands\Outlook;
use AmeliaBooking\Application\Commands\Payment;
use AmeliaBooking\Application\Commands\PaymentGateway;
use AmeliaBooking\Application\Commands\Report;
use AmeliaBooking\Application\Commands\Import;
use AmeliaBooking\Application\Commands\Search;
use AmeliaBooking\Application\Commands\Settings;
use AmeliaBooking\Application\Commands\Stats;
use AmeliaBooking\Application\Commands\User;
use AmeliaBooking\Application\Commands\Zoom;

// @codingStandardsIgnoreStart
$entries['command.bus'] = function ($c) {
    return League\Tactician\Setup\QuickStart::create(
        [
            // Activation
            Activation\ActivatePluginCommand::class                   => new Activation\ActivatePluginCommandHandler($c),
            Activation\DeactivatePluginCommand::class                 => new Activation\DeactivatePluginCommandHandler($c),
            Activation\DeactivatePluginEnvatoCommand::class           => new Activation\DeactivatePluginEnvatoCommandHandler($c),
            Activation\ParseDomainCommand::class                      => new Activation\ParseDomainCommandHandler($c),
            // Stash
            Stash\UpdateStashCommand::class                           => new Stash\UpdateStashCommandHandler($c),
            // Bookable/Category
            Bookable\Category\AddCategoryCommand::class               => new Bookable\Category\AddCategoryCommandHandler($c),
            Bookable\Category\DeleteCategoryCommand::class            => new Bookable\Category\DeleteCategoryCommandHandler($c),
            Bookable\Category\GetCategoriesCommand::class             => new Bookable\Category\GetCategoriesCommandHandler($c),
            Bookable\Category\GetCategoryCommand::class               => new Bookable\Category\GetCategoryCommandHandler($c),
            Bookable\Category\UpdateCategoriesPositionsCommand::class => new Bookable\Category\UpdateCategoriesPositionsCommandHandler($c),
            Bookable\Category\UpdateCategoryCommand::class            => new Bookable\Category\UpdateCategoryCommandHandler($c),
            // Bookable/Extra
            Bookable\Extra\AddExtraCommand::class                     => new Bookable\Extra\AddExtraCommandHandler($c),
            Bookable\Extra\DeleteExtraCommand::class                  => new Bookable\Extra\DeleteExtraCommandHandler($c),
            Bookable\Extra\GetExtraCommand::class                     => new Bookable\Extra\GetExtraCommandHandler($c),
            Bookable\Extra\GetExtrasCommand::class                    => new Bookable\Extra\GetExtrasCommandHandler($c),
            Bookable\Extra\UpdateExtraCommand::class                  => new Bookable\Extra\UpdateExtraCommandHandler($c),
            // Bookable/Service
            Bookable\Service\AddServiceCommand::class                 => new Bookable\Service\AddServiceCommandHandler($c),
            Bookable\Service\DeleteServiceCommand::class              => new Bookable\Service\DeleteServiceCommandHandler($c),
            Bookable\Service\GetServiceCommand::class                 => new Bookable\Service\GetServiceCommandHandler($c),
            Bookable\Service\GetServiceDeleteEffectCommand::class     => new Bookable\Service\GetServiceDeleteEffectCommandHandler($c),
            Bookable\Service\GetServicesCommand::class                => new Bookable\Service\GetServicesCommandHandler($c),
            Bookable\Service\UpdateServiceCommand::class              => new Bookable\Service\UpdateServiceCommandHandler($c),
            Bookable\Service\UpdateServiceStatusCommand::class        => new Bookable\Service\UpdateServiceStatusCommandHandler($c),
            Bookable\Service\UpdateServicesPositionsCommand::class    => new Bookable\Service\UpdateServicesPositionsCommandHandler($c),
            // Bookable/Package
            Bookable\Package\AddPackageCommand::class                 => new Bookable\Package\AddPackageCommandHandler($c),
            Bookable\Package\DeletePackageCommand::class              => new Bookable\Package\DeletePackageCommandHandler($c),
            Bookable\Package\GetPackagesCommand::class                => new Bookable\Package\GetPackagesCommandHandler($c),
            Bookable\Package\GetPackageDeleteEffectCommand::class     => new Bookable\Package\GetPackageDeleteEffectCommandHandler($c),
            Bookable\Package\UpdatePackageCommand::class              => new Bookable\Package\UpdatePackageCommandHandler($c),
            Bookable\Package\UpdatePackageStatusCommand::class        => new Bookable\Package\UpdatePackageStatusCommandHandler($c),
            Bookable\Package\DeletePackageCustomerCommand::class      => new Bookable\Package\DeletePackageCustomerCommandHandler($c),
            Bookable\Package\UpdatePackageCustomerCommand::class      => new Bookable\Package\UpdatePackageCustomerCommandHandler($c),
            Bookable\Package\UpdatePackagesPositionsCommand::class    => new Bookable\Package\UpdatePackagesPositionsCommandHandler($c),
            // Booking/Event
            Booking\Event\AddEventCommand::class                      => new Booking\Event\AddEventCommandHandler($c),
            Booking\Event\GetEventCommand::class                      => new Booking\Event\GetEventCommandHandler($c),
            Booking\Event\GetEventsCommand::class                     => new Booking\Event\GetEventsCommandHandler($c),
            Booking\Event\UpdateEventCommand::class                   => new Booking\Event\UpdateEventCommandHandler($c),
            Booking\Event\UpdateEventStatusCommand::class             => new Booking\Event\UpdateEventStatusCommandHandler($c),
            Booking\Event\DeleteEventBookingCommand::class            => new Booking\Event\DeleteEventBookingCommandHandler($c),
            Booking\Event\UpdateEventBookingCommand::class            => new Booking\Event\UpdateEventBookingCommandHandler($c),
            Booking\Event\DeleteEventCommand::class                   => new Booking\Event\DeleteEventCommandHandler($c),
            Booking\Event\GetEventDeleteEffectCommand::class          => new Booking\Event\GetEventDeleteEffectCommandHandler($c),
            Booking\Event\GetCalendarEventsCommand::class               => new Booking\Event\GetCalendarEventsCommandHandler($c),
            // Booking/Appointment
            Booking\Appointment\AddAppointmentCommand::class          => new Booking\Appointment\AddAppointmentCommandHandler($c),
            Booking\Appointment\AddBookingCommand::class              => new Booking\Appointment\AddBookingCommandHandler($c),
            Booking\Appointment\DeleteBookingCommand::class           => new Booking\Appointment\DeleteBookingCommandHandler($c),
            Booking\Appointment\CancelBookingCommand::class           => new Booking\Appointment\CancelBookingCommandHandler($c),
            Booking\Appointment\CancelBookingRemotelyCommand::class   => new Booking\Appointment\CancelBookingRemotelyCommandHandler($c),
            Booking\Appointment\DeleteAppointmentCommand::class       => new Booking\Appointment\DeleteAppointmentCommandHandler($c),
            Booking\Appointment\GetAppointmentCommand::class          => new Booking\Appointment\GetAppointmentCommandHandler($c),
            Booking\Appointment\GetAppointmentsCommand::class         => new Booking\Appointment\GetAppointmentsCommandHandler($c),
            Booking\Appointment\GetIcsCommand::class                  => new Booking\Appointment\GetIcsCommandHandler($c),
            Booking\Appointment\GetTimeSlotsCommand::class            => new Booking\Appointment\GetTimeSlotsCommandHandler($c),
            Booking\Appointment\UpdateAppointmentCommand::class       => new Booking\Appointment\UpdateAppointmentCommandHandler($c),
            Booking\Appointment\UpdateAppointmentStatusCommand::class => new Booking\Appointment\UpdateAppointmentStatusCommandHandler($c),
            Booking\Appointment\UpdateAppointmentTimeCommand::class   => new Booking\Appointment\UpdateAppointmentTimeCommandHandler($c),
            Booking\Appointment\ReassignBookingCommand::class         => new Booking\Appointment\ReassignBookingCommandHandler($c),
            Booking\Appointment\SuccessfulBookingCommand::class       => new Booking\Appointment\SuccessfulBookingCommandHandler($c),
            // Coupon
            Coupon\AddCouponCommand::class                            => new Coupon\AddCouponCommandHandler($c),
            Coupon\DeleteCouponCommand::class                         => new Coupon\DeleteCouponCommandHandler($c),
            Coupon\GetCouponCommand::class                            => new Coupon\GetCouponCommandHandler($c),
            Coupon\GetCouponsCommand::class                           => new Coupon\GetCouponsCommandHandler($c),
            Coupon\GetValidCouponCommand::class                       => new Coupon\GetValidCouponCommandHandler($c),
            Coupon\UpdateCouponCommand::class                         => new Coupon\UpdateCouponCommandHandler($c),
            Coupon\UpdateCouponStatusCommand::class                   => new Coupon\UpdateCouponStatusCommandHandler($c),
            // CustomField
            CustomField\GetCustomFieldsCommand::class                 => new CustomField\GetCustomFieldsCommandHandler($c),
            CustomField\GetCustomFieldFileCommand::class              => new CustomField\GetCustomFieldFileCommandHandler($c),
            CustomField\AddCustomFieldCommand::class                  => new CustomField\AddCustomFieldCommandHandler($c),
            CustomField\DeleteCustomFieldCommand::class               => new CustomField\DeleteCustomFieldCommandHandler($c),
            CustomField\UpdateCustomFieldCommand::class               => new CustomField\UpdateCustomFieldCommandHandler($c),
            CustomField\UpdateCustomFieldsPositionsCommand::class     => new CustomField\UpdateCustomFieldsPositionsCommandHandler($c),
            // Entities
            Entities\GetEntitiesCommand::class                        => new Entities\GetEntitiesCommandHandler($c),
            // Google
            Google\DisconnectFromGoogleAccountCommand::class          => new Google\DisconnectFromGoogleAccountCommandHandler($c),
            Google\FetchAccessTokenWithAuthCodeCommand::class         => new Google\FetchAccessTokenWithAuthCodeCommandHandler($c),
            Google\GetGoogleAuthURLCommand::class                     => new Google\GetGoogleAuthURLCommandHandler($c),
            // Outlook
            Outlook\DisconnectFromOutlookAccountCommand::class        => new Outlook\DisconnectFromOutlookAccountCommandHandler($c),
            Outlook\GetOutlookAuthURLCommand::class                   => new Outlook\GetOutlookAuthURLCommandHandler($c),
            Outlook\FetchAccessTokenWithAuthCodeOutlookCommand::class => new Outlook\FetchAccessTokenWithAuthCodeOutlookCommandHandler($c),
            // Location
            Location\AddLocationCommand::class                        => new Location\AddLocationCommandHandler($c),
            Location\DeleteLocationCommand::class                     => new Location\DeleteLocationCommandHandler($c),
            Location\GetLocationCommand::class                        => new Location\GetLocationCommandHandler($c),
            Location\GetLocationDeleteEffectCommand::class            => new Location\GetLocationDeleteEffectCommandHandler($c),
            Location\GetLocationsCommand::class                       => new Location\GetLocationsCommandHandler($c),
            Location\UpdateLocationCommand::class                     => new Location\UpdateLocationCommandHandler($c),
            Location\UpdateLocationStatusCommand::class               => new Location\UpdateLocationStatusCommandHandler($c),
            // Notification
            Notification\GetNotificationsCommand::class               => new Notification\GetNotificationsCommandHandler($c),
            Notification\SendScheduledNotificationsCommand::class     => new Notification\SendScheduledNotificationsCommandHandler($c),
            Notification\SendUndeliveredNotificationsCommand::class   => new Notification\SendUndeliveredNotificationsCommandHandler($c),
            Notification\SendTestEmailCommand::class                  => new Notification\SendTestEmailCommandHandler($c),
            Notification\UpdateNotificationCommand::class             => new Notification\UpdateNotificationCommandHandler($c),
            Notification\AddNotificationCommand::class                => new Notification\AddNotificationCommandHandler($c),
            Notification\DeleteNotificationCommand::class             => new Notification\DeleteNotificationCommandHandler($c),
            Notification\UpdateNotificationStatusCommand::class       => new Notification\UpdateNotificationStatusCommandHandler($c),
            Notification\SendAmeliaSmsApiRequestCommand::class        => new Notification\SendAmeliaSmsApiRequestCommandHandler($c),
            Notification\UpdateSMSNotificationHistoryCommand::class   => new Notification\UpdateSMSNotificationHistoryCommandHandler($c),
            Notification\GetSMSNotificationsHistoryCommand::class     => new Notification\GetSMSNotificationsHistoryCommandHandler($c),
            // Payment
            Payment\AddPaymentCommand::class                          => new Payment\AddPaymentCommandHandler($c),
            Payment\DeletePaymentCommand::class                       => new Payment\DeletePaymentCommandHandler($c),
            Payment\GetPaymentCommand::class                          => new Payment\GetPaymentCommandHandler($c),
            Payment\GetPaymentsCommand::class                         => new Payment\GetPaymentsCommandHandler($c),
            Payment\UpdatePaymentCommand::class                       => new Payment\UpdatePaymentCommandHandler($c),
            PaymentGateway\PayPalPaymentCallbackCommand::class        => new PaymentGateway\PayPalPaymentCallbackCommandHandler($c),
            PaymentGateway\PayPalPaymentCommand::class                => new PaymentGateway\PayPalPaymentCommandHandler($c),
            PaymentGateway\WooCommercePaymentCommand::class           => new PaymentGateway\WooCommercePaymentCommandHandler($c),
            PaymentGateway\WooCommerceProductsCommand::class          => new PaymentGateway\WooCommerceProductsCommandHandler($c),
            PaymentGateway\MolliePaymentNotifyCommand::class          => new PaymentGateway\MolliePaymentNotifyCommandHandler($c),
            PaymentGateway\MolliePaymentCommand::class                => new PaymentGateway\MolliePaymentCommandHandler($c),
            PaymentGateway\RazorpayPaymentCommand::class              => new PaymentGateway\RazorpayPaymentCommandHandler($c),
            // Report
            Report\GetAppointmentsCommand::class                      => new Report\GetAppointmentsCommandHandler($c),
            Report\GetCouponsCommand::class                           => new Report\GetCouponsCommandHandler($c),
            Report\GetCustomersCommand::class                         => new Report\GetCustomersCommandHandler($c),
            Import\ImportCustomersCommand::class                      => new Import\ImportCustomersCommandHandler($c),
            Report\GetPaymentsCommand::class                          => new Report\GetPaymentsCommandHandler($c),
            Report\GetEventAttendeesCommand::class                    => new Report\GetEventAttendeesCommandHandler($c),
            // Search
            Search\GetSearchCommand::class                            => new Search\GetSearchCommandHandler($c),
            // Settings
            Settings\GetSettingsCommand::class                        => new Settings\GetSettingsCommandHandler($c),
            Settings\UpdateSettingsCommand::class                     => new Settings\UpdateSettingsCommandHandler($c),
            // Status
            Stats\AddStatsCommand::class                              => new AmeliaBooking\Application\Commands\Stats\AddStatsCommandHandler($c),
            Stats\GetStatsCommand::class                              => new AmeliaBooking\Application\Commands\Stats\GetStatsCommandHandler($c),
            // User/Customer
            User\Customer\AddCustomerCommand::class                   => new User\Customer\AddCustomerCommandHandler($c),
            User\Customer\GetCustomerCommand::class                   => new User\Customer\GetCustomerCommandHandler($c),
            User\Customer\GetCustomersCommand::class                  => new User\Customer\GetCustomersCommandHandler($c),
            User\Customer\UpdateCustomerCommand::class                => new User\Customer\UpdateCustomerCommandHandler($c),
            User\Customer\ReauthorizeCommand::class                   => new User\Customer\ReauthorizeCommandHandler($c),
            // User
            User\DeleteUserCommand::class                             => new User\DeleteUserCommandHandler($c),
            User\GetCurrentUserCommand::class                         => new User\GetCurrentUserCommandHandler($c),
            User\GetUserDeleteEffectCommand::class                    => new User\GetUserDeleteEffectCommandHandler($c),
            User\GetWPUsersCommand::class                             => new User\GetWPUsersCommandHandler($c),
            User\LoginCabinetCommand::class                           => new User\LoginCabinetCommandHandler($c),
            User\LogoutCabinetCommand::class                          => new User\LogoutCabinetCommandHandler($c),
            // User/Provider
            User\Provider\AddProviderCommand::class                   => new User\Provider\AddProviderCommandHandler($c),
            User\Provider\GetProviderCommand::class                   => new User\Provider\GetProviderCommandHandler($c),
            User\Provider\GetProvidersCommand::class                  => new User\Provider\GetProvidersCommandHandler($c),
            User\Provider\UpdateProviderCommand::class                => new User\Provider\UpdateProviderCommandHandler($c),
            User\Provider\UpdateProviderStatusCommand::class          => new User\Provider\UpdateProviderStatusCommandHandler($c),
            // Zoom
            Zoom\GetUsersCommand::class                               => new Zoom\GetUsersCommandHandler($c),
        ]
    );
};
// @codingStandardsIgnoreEnd
