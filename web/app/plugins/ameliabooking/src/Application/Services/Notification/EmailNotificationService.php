<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Notification;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Application\Services\Placeholder\PlaceholderService;
use AmeliaBooking\Application\Services\Settings\SettingsService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Notification\Notification;
use AmeliaBooking\Domain\Entity\Notification\NotificationLog;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\String\NotificationStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationLogRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\Services\Notification\MailgunService;
use AmeliaBooking\Infrastructure\Services\Notification\PHPMailService;
use AmeliaBooking\Infrastructure\Services\Notification\SMTPService;
use Exception;
use InvalidArgumentException;
use Slim\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class EmailNotificationService
 *
 * @package AmeliaBooking\Application\Services\Notification
 */
class EmailNotificationService extends AbstractNotificationService
{
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array        $appointmentArray
     * @param Notification $notification
     * @param bool         $logNotification
     * @param int|null     $bookingKey
     *
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws Exception
     */
    public function sendNotification(
        $appointmentArray,
        $notification,
        $logNotification,
        $bookingKey = null
    ) {
        /** @var NotificationLogRepository $notificationLogRepo */
        $notificationLogRepo = $this->container->get('domain.notificationLog.repository');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var PHPMailService|SMTPService|MailgunService $mailService */
        $mailService = $this->container->get('infrastructure.mail.service');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.{$appointmentArray['type']}.service");

        /** @var SettingsService $settingsAS */
        $settingsAS = $this->container->get('application.settings.service');

        /** @var \AmeliaBooking\Domain\Services\Settings\SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $notificationSettings = $settingsService->getCategorySettings('notifications');

        if (!$notificationSettings['senderEmail'] || !$notificationSettings['senderName']) {
            return;
        }

        $isCustomerPackage = isset($appointmentArray['isForCustomer']) && $appointmentArray['isForCustomer'];
        $isBackend         = isset($appointmentArray['isBackend']) && $appointmentArray['isBackend'];

        if ($appointmentArray['type'] === Entities::PACKAGE) {
            $info = $isCustomerPackage ? json_encode($appointmentArray['customer']) : null;
        } else {
            $info = $bookingKey !== null ? $appointmentArray['bookings'][$bookingKey]['info'] : null;
        }

        $notificationSubject = $helperService->getBookingTranslation(
            $info,
            $notification->getTranslations() ? $notification->getTranslations()->getValue() : null,
            'subject'
        ) ?: $notification->getSubject()->getValue();

        $notificationContent = $helperService->getBookingTranslation(
            $info,
            $notification->getTranslations() ? $notification->getTranslations()->getValue() : null,
            'content'
        ) ?: $notification->getContent()->getValue();

        $data = $placeholderService->getPlaceholdersData(
            $appointmentArray,
            $bookingKey,
            'email'
        );

        $sendIcs        = $settingsService->getSetting('ics', 'sendIcsAttachment');
        $sendIcsPending = $settingsService->getSetting('ics', 'sendIcsAttachmentPending');

        $bookingStatus = $bookingKey ? $appointmentArray['bookings'][$bookingKey]['status'] : $appointmentArray['status'];
        if (!empty($data['icsFiles'])) {
            $icsFiles = ($sendIcs && $bookingStatus === BookingStatus::APPROVED || $sendIcsPending && $bookingStatus === BookingStatus::PENDING)
                ? $data['icsFiles'][($isCustomerPackage || $bookingKey !== null) && !$isBackend ? 'translated' : 'original'] : [];
        }

        $subject = $placeholderService->applyPlaceholders($notificationSubject, $data);

        $body = $placeholderService->applyPlaceholders($notificationContent, $data);

        $users = $this->getUsersInfo(
            $notification->getSendTo()->getValue(),
            $appointmentArray,
            $bookingKey,
            $data
        );

        foreach ($users as $user) {
            try {
                if ($user['email']) {
                    if (!empty($appointmentArray['isRetry'])) {
                        /** @var Collection $sentNotifications */
                        $sentNotifications = $notificationLogRepo->getSentNotificationsByUserAndEntity(
                            $user['id'],
                            'email',
                            $appointmentArray['type'],
                            $appointmentArray['type'] === Entities::PACKAGE ?
                                $appointmentArray['packageCustomerId'] : $appointmentArray['id']
                        );

                        if ($sentNotifications->length()) {
                            continue;
                        }
                    }

                    $reParsedData = !$isCustomerPackage ?
                        $placeholderService->reParseContentForProvider(
                            $appointmentArray,
                            $subject,
                            $body,
                            $user['id']
                        ) : [
                            'body'    => $body,
                            'subject' => $subject,
                        ];

                    $logNotificationId = null;

                    if ($logNotification) {
                        $logNotificationId = $notificationLogRepo->add(
                            $notification,
                            $user['id'],
                            $appointmentArray['type'] === Entities::APPOINTMENT ? $appointmentArray['id'] : null,
                            $appointmentArray['type'] === Entities::EVENT ? $appointmentArray['id'] : null,
                            $appointmentArray['type'] === Entities::PACKAGE ? $appointmentArray['packageCustomerId'] : null,
                            json_encode(
                                [
                                    'subject'  => $reParsedData['subject'],
                                    'body'     => $this->getParsedBody($reParsedData['body']),
                                    'icsFiles' => !empty($icsFiles) ? $icsFiles : [],
                                ]
                            )
                        );
                    }

                    $mailService->send(
                        $user['email'],
                        $reParsedData['subject'],
                        $this->getParsedBody($reParsedData['body']),
                        $settingsAS->getBccEmails(),
                        !empty($icsFiles) ? $icsFiles : []
                    );

                    if ($logNotificationId) {
                        $notificationLogRepo->updateFieldById((int)$logNotificationId, 1, 'sent');
                    }
                }
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function sendUndeliveredNotifications()
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var NotificationLogRepository $notificationLogRepo */
        $notificationLogRepo = $this->container->get('domain.notificationLog.repository');

        /** @var Collection $undeliveredNotifications */
        $undeliveredNotifications = $notificationLogRepo->getUndeliveredNotifications('email');

        /** @var PHPMailService|SMTPService|MailgunService $mailService */
        $mailService = $this->container->get('infrastructure.mail.service');

        /** @var SettingsService $settingsAS */
        $settingsAS = $this->container->get('application.settings.service');

        /** @var NotificationLog $undeliveredNotification */
        foreach ($undeliveredNotifications->getItems() as $undeliveredNotification) {
            try {
                /** @var AbstractUser $user */
                $user = $userRepository->getById($undeliveredNotification->getUserId()->getValue());

                $data = json_decode($undeliveredNotification->getData()->getValue(), true);

                $mailService->send(
                    $user->getEmail()->getValue(),
                    $data['subject'],
                    $data['body'],
                    $settingsAS->getBccEmails(),
                    $data['icsFiles']
                );

                $notificationLogRepo->updateFieldById($undeliveredNotification->getId()->getValue(), 1, 'sent');
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws Exception
     */
    public function sendBirthdayGreetingNotifications()
    {
        /** @var Collection $notifications */
        $notifications = $this->getByNameAndType('customer_birthday_greeting', $this->type);

        foreach ($notifications->getItems() as $notification) {
            // Check if notification is enabled and it is time to send notification
            if ($notification->getStatus()->getValue() === NotificationStatus::ENABLED &&
                $notification->getTime() &&
                DateTimeService::getNowDateTimeObject() >=
                DateTimeService::getCustomDateTimeObject($notification->getTime()->getValue())
            ) {
                /** @var NotificationLogRepository $notificationLogRepo */
                $notificationLogRepo = $this->container->get('domain.notificationLog.repository');

                /** @var PHPMailService|SMTPService|MailgunService $mailService */
                $mailService = $this->container->get('infrastructure.mail.service');

                /** @var PlaceholderService $placeholderService */
                $placeholderService = $this->container->get('application.placeholder.appointment.service');

                /** @var SettingsService $settingsAS */
                $settingsAS = $this->container->get('application.settings.service');

                /** @var \AmeliaBooking\Domain\Services\Settings\SettingsService $settingsService */
                $settingsService = $this->container->get('domain.settings.service');

                $notificationSettings = $settingsService->getCategorySettings('notifications');

                if (!$notificationSettings['senderEmail'] || !$notificationSettings['senderName']) {
                    return;
                }

                $customers = $notificationLogRepo->getBirthdayCustomers($this->type);

                $companyData = $placeholderService->getCompanyData();

                $customersArray = $customers->toArray();

                foreach ($customersArray as $bookingKey => $customerArray) {
                    if ($customerArray['email']) {
                        $data = [
                            'customer_email'      => $customerArray['email'],
                            'customer_first_name' => $customerArray['firstName'],
                            'customer_last_name'  => $customerArray['lastName'],
                            'customer_full_name'  => $customerArray['firstName'] . ' ' . $customerArray['lastName'],
                            'customer_phone'      => $customerArray['phone']
                        ];

                        /** @noinspection AdditionOperationOnArraysInspection */
                        $data += $companyData;

                        $subject = $placeholderService->applyPlaceholders(
                            $notification->getSubject()->getValue(),
                            $data
                        );

                        $body = $placeholderService->applyPlaceholders(
                            $notification->getContent()->getValue(),
                            $data
                        );

                        try {
                            $mailService->send(
                                $data['customer_email'],
                                $subject,
                                $this->getParsedBody($body),
                                $settingsAS->getBccEmails()
                            );

                            $notificationLogRepo->add(
                                $notification,
                                $customerArray['id']
                            );
                        } catch (Exception $e) {
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Customer $customer
     * @param string   $locale
     *
     * @return void
     *
     * @throws ContainerValueNotFoundException
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws Exception
     */
    public function sendRecoveryEmail($customer, $locale, $cabinetType)
    {
        /** @var Collection $notifications */
        $notifications = $cabinetType === 'customer' ?
            $this->getByNameAndType('customer_account_recovery', 'email') :
            $this->getByNameAndType('provider_panel_recovery', 'email');


        /** @var PHPMailService|SMTPService|MailgunService $mailService */
        $mailService = $this->container->get('infrastructure.mail.service');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get('application.placeholder.appointment.service');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var \AmeliaBooking\Domain\Services\Settings\SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $notificationSettings = $settingsService->getCategorySettings('notifications');

        if (!$notificationSettings['senderEmail'] || !$notificationSettings['senderName']) {
            return;
        }

        foreach ($notifications->getItems() as $notification) {
            if ($notification->getStatus()->getValue() === NotificationStatus::ENABLED) {
                $data = [
                    'customer_email'      => $customer->getEmail()->getValue(),
                    'customer_first_name' => $customer->getFirstName()->getValue(),
                    'customer_last_name'  => $customer->getLastName()->getValue(),
                    'customer_full_name'  =>
                        $customer->getFirstName()->getValue() . ' ' . $customer->getLastName()->getValue(),
                    'customer_phone'      => $customer->getPhone() ? $customer->getPhone()->getValue() : '',
                    'customer_panel_url'  => $cabinetType === 'customer' ? $helperService->getCustomerCabinetUrl(
                        $customer->getEmail()->getValue(),
                        'email',
                        null,
                        null,
                        $locale
                    ) : $helperService->getProviderCabinetUrl(
                        $customer->getEmail()->getValue(),
                        'email',
                        null,
                        null
                    )
                ];

                /** @noinspection AdditionOperationOnArraysInspection */
                $data += $placeholderService->getCompanyData();

                if ($cabinetType === 'provider') {
                    $data = array_combine(
                        array_map(
                            function ($key) {
                                return str_replace('customer', 'employee', $key);
                            },
                            array_keys($data)
                        ),
                        $data
                    );
                }

                $subject = $placeholderService->applyPlaceholders(
                    $notification->getSubject()->getValue(),
                    $data
                );

                $body = $placeholderService->applyPlaceholders(
                    $notification->getContent()->getValue(),
                    $data
                );

                try {
                    $mailService->send($cabinetType === 'customer' ? $data['customer_email'] : $data['employee_email'], $subject, $this->getParsedBody($body), []);
                } catch (Exception $e) {
                }
            }
        }
    }

    /**
     * @param Provider $provider
     *
     * @param $plainPassword
     * @return void
     *
     * @throws QueryExecutionException
     */
    public function sendEmployeePanelAccess($provider, $plainPassword)
    {
        /** @var Collection $notifications */
        $notifications = $this->getByNameAndType('provider_panel_access', 'email');

        /** @var PHPMailService|SMTPService|MailgunService $mailService */
        $mailService = $this->container->get('infrastructure.mail.service');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get('application.placeholder.appointment.service');

        /** @var \AmeliaBooking\Domain\Services\Settings\SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $notificationSettings = $settingsService->getCategorySettings('notifications');

        if (!$notificationSettings['senderEmail'] || !$notificationSettings['senderName']) {
            return;
        }

        foreach ($notifications->getItems() as $notification) {
            if ($notification->getStatus()->getValue() === NotificationStatus::ENABLED) {
                $data = [
                    'employee_email'      => $provider['email'],
                    'employee_first_name' => $provider['firstName'],
                    'employee_last_name'  => $provider['lastName'],
                    'employee_full_name'  =>
                        $provider['firstName'] . ' ' . $provider['lastName'],
                    'employee_phone'      => $provider['phone'],
                    'employee_password'   => $plainPassword,
                    'employee_panel_url'  => trim(
                        $this->container->get('domain.settings.service')->getSetting('roles', 'providerCabinet')['pageUrl']
                    )
                ];

                /** @noinspection AdditionOperationOnArraysInspection */
                $data += $placeholderService->getCompanyData();

                $subject = $placeholderService->applyPlaceholders(
                    $notification->getSubject()->getValue(),
                    $data
                );

                $body = $placeholderService->applyPlaceholders(
                    $notification->getContent()->getValue(),
                    $data
                );

                try {
                    $mailService->send($data['employee_email'], $subject, $this->getParsedBody($body), []);
                } catch (Exception $e) {
                }
            }
        }
    }

    /**
     * @param string $body
     *
     * @return string
     */
    public function getParsedBody($body)
    {
        /** @var \AmeliaBooking\Domain\Services\Settings\SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $body = str_replace(
            [
                'class="ql-size-small"',
                'class="ql-size-large"',
                'class="ql-size-huge"',
                'class="ql-font-serif"',
                'class="ql-font-monospace"',
                'class="ql-direction-rtl"',
                'class="ql-align-center"',
                'class="ql-align-justify"',
                'class="ql-align-right"',
                'class="ql-size-small ql-font-monospace"',
                'class="ql-size-large ql-font-monospace"',
                'class="ql-size-huge ql-font-monospace"',
                'class="ql-font-monospace ql-size-small"',
                'class="ql-font-monospace ql-size-large"',
                'class="ql-font-monospace ql-size-huge"',
                'class="ql-size-small ql-font-serif"',
                'class="ql-size-large ql-font-serif"',
                'class="ql-size-huge ql-font-serif"',
                'class="ql-font-serif ql-size-small"',
                'class="ql-font-serif ql-size-large"',
                'class="ql-font-serif ql-size-huge"',
                'class="ql-align-justify ql-direction-rtl"',
                'class="ql-align-center ql-direction-rtl"',
                'class="ql-align-right ql-direction-rtl"',
                'class="ql-direction-rtl ql-align-justify"',
                'class="ql-direction-rtl ql-align-center"',
                'class="ql-direction-rtl ql-align-right"'
            ],
            [
                'style="font-size: 0.75em;"',
                'style="font-size: 1.5em;"',
                'style="font-size: 2.5em;"',
                'style="font-family: Georgia, Times New Roman, serif;"',
                'style="font-family: Monaco, Courier New, monospace;"',
                'style="direction: rtl; text-align: inherit;"',
                'style="text-align: center;"',
                'style="text-align: justify;"',
                'style="text-align: right;"',
                'style="font-size: 0.75em; font-family: Monaco, Courier New, monospace;"',
                'style="font-size: 1.5em; font-family: Monaco, Courier New, monospace;"',
                'style="font-size: 2.5em; font-family: Monaco, Courier New, monospace;"',
                'style="font-family: Monaco, Courier New, monospace; font-size: 0.75em;"',
                'style="font-family: Monaco, Courier New, monospace; font-size: 1.5em;"',
                'style="font-family: Monaco, Courier New, monospace; font-size: 2.5em;"',
                'style="font-size: 0.75em; font-family: Georgia, Times New Roman, serif;"',
                'style="font-size: 1.5em; font-family: Georgia, Times New Roman, serif;"',
                'style="font-size: 2.5em; font-family: Georgia, Times New Roman, serif;"',
                'style="font-family: Georgia, Times New Roman, serif; font-size: 0.75em;"',
                'style="font-family: Georgia, Times New Roman, serif; font-size: 1.5em;"',
                'style="font-family: Georgia, Times New Roman, serif; font-size: 2.5em;"',
                'style="text-align: justify; direction: rtl;"',
                'style="text-align: center; direction: rtl;"',
                'style="text-align: right; direction: rtl;"',
                'style="direction: rtl; text-align: justify;"',
                'style="direction: rtl; text-align: center;"',
                'style="direction: rtl; text-align: right;"'
            ],
            $body
        );

        $body = preg_replace("/\r|\n/", "", $body);

        // fix for 2 x style attribute on same html tag
        $splitBodyByTags = explode('<', $body);

        foreach ($splitBodyByTags as $k => $v) {
            if (substr_count($v, "style") === 2) {
                $splitBodyByTags[$k] = str_replace(';" style="', '; ', $v);
            }
        }

        $body = implode('<', $splitBodyByTags);

        $breakReplacement = $settingsService->getSetting('notifications', 'breakReplacement');

        $replaceSource = [
            '</p><p>',
            '<p>',
            '</p>'
        ];

        $replaceTarget = [
            '<br>',
            '',
            ''
        ];

        if (strpos($body, '<p>') !== false) {
            array_unshift($replaceSource, '<br>');
            array_unshift($replaceTarget, '');
        }

        return $breakReplacement === '' || $breakReplacement === '<br>' ?
            str_replace(
                $replaceSource,
                $replaceTarget,
                $body
            ) :
            str_replace('<p><br></p>', $breakReplacement, $body);
    }
}
