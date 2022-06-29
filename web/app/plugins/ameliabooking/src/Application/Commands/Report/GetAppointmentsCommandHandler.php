<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Commands\Report;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Bookable\PackageApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Report\ReportServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;

/**
 * Class GetCustomersCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Report
 */
class GetAppointmentsCommandHandler extends CommandHandler
{
    /**
     * @param GetAppointmentsCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(GetAppointmentsCommand $command)
    {
        $currentUser = $this->getContainer()->get('logged.in.user');

        if (!$this->getContainer()->getPermissionsService()->currentUserCanRead(Entities::APPOINTMENTS)) {
            throw new AccessDeniedException('You are not allowed to read appointments.');
        }

        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');
        /** @var ReportServiceInterface $reportService */
        $reportService = $this->container->get('infrastructure.report.csv.service');
        /** @var SettingsService $settingsDS */
        $settingsDS = $this->container->get('domain.settings.service');
        /** @var CustomFieldRepository $customFieldRepository */
        $customFieldRepository = $this->container->get('domain.customField.repository');
        /** @var PackageApplicationService $packageAS */
        $packageAS = $this->container->get('application.bookable.package');

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $params = $command->getField('params');

        if ($params['dates'] && $params['dates'][0]) {
            $params['dates'][0] .= ' 00:00:00';
        }

        if ($params['dates'] && !empty($params['dates'][1])) {
            $params['dates'][1] .= ' 23:59:59';
        }

        switch ($currentUser->getType()) {
            case 'customer':
                $params['customerId'] = $currentUser->getId()->getValue();
                break;
            case 'provider':
                $params['providers'] = [$currentUser->getId()->getValue()];
                break;
        }

        $appointments = $appointmentRepo->getFiltered($params);
        $packageAS->setPackageBookingsForAppointments($appointments);

        $appointmentsArray = isset($params['count']) ?
            array_slice($appointments->toArray(), 0, $params['count']) :
            $appointments->toArray();


        $rows = [];

        $dateFormat = $settingsDS->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsDS->getSetting('wordpress', 'timeFormat');

        foreach ((array)$appointmentsArray as $appointment) {
            $numberOfPersonsData = [
                AbstractUser::USER_ROLE_PROVIDER => [
                    BookingStatus::APPROVED => 0,
                    BookingStatus::PENDING  => 0,
                    BookingStatus::CANCELED => 0,
                    BookingStatus::REJECTED => 0,
                ]
            ];

            foreach ((array)$appointment['bookings'] as $booking) {
                $numberOfPersonsData[AbstractUser::USER_ROLE_PROVIDER][$booking['status']] += $booking['persons'];
            }

            $numberOfPersons = [];

            foreach ((array)$numberOfPersonsData[AbstractUser::USER_ROLE_PROVIDER] as $key => $value) {
                if ($value) {
                    $numberOfPersons[] = BackendStrings::getCommonStrings()[$key] . ': ' . $value;
                }
            }

            $row = [];

            $customers          = [];
            $customFields       =[];
            $customFieldsValues = [];

            if ($params['separate'] !== "true") {
                foreach ((array)$appointment['bookings'] as $booking) {
                    $infoJson = json_decode($booking['info'], true);

                    $customerInfo = $infoJson ?: $booking['customer'];

                    $phone = $booking['customer']['phone'] ?: '';

                    $customers[] = $customerInfo['firstName'] . ' ' . $customerInfo['lastName'] . ' ' . ($booking['customer']['email'] ?: '') . ' ' . ($customerInfo['phone'] ?: $phone);

                    $customFieldsJson = json_decode($booking['customFields'], true);

                    foreach ((array)$customFieldsJson as $customFiled) {
                        if ($customFiled && array_key_exists('type', $customFiled) && $customFiled['type'] === 'file') {
                            continue;
                        }

                        if (is_array($customFiled['value'])) {
                            foreach ($customFiled['value'] as $customFiledValue) {
                                $customFieldsValues[] =  $customFiledValue;
                            }
                            $customFields[] = $customFiled['label'] . ': ' . implode('|', $customFieldsValues);
                        } else {
                            $customFields[] = $customFiled['label'] . ': ' . $customFiled['value'];
                        }
                    }
                }

                if (in_array('customers', $params['fields'], true)) {
                    $row[BackendStrings::getCustomerStrings()['customers']] = implode(', ', $customers);
                }

                $this->getRowData($params, $row, $appointment, $dateFormat, $timeFormat, $customFields, $numberOfPersons);

                $rows[] = $row;
            } else {
                $allCustomFields = $customFieldRepository->getAll();

                foreach ((array)$appointment['bookings'] as $booking) {
                    $row[BackendStrings::getAppointmentStrings()['appointment_id']] = $appointment['id'];
                    if (in_array('customers', $params['fields'], true)) {
                        $infoJson = json_decode($booking['info'], true);

                        $customerInfo = $infoJson ?: $booking['customer'];

                        $phone = $booking['customer']['phone'] ?: '';

                        $row[BackendStrings::getAppointmentStrings()['customer_name']]  = $customerInfo['firstName'] . ' ' . $customerInfo['lastName'];
                        $row[BackendStrings::getAppointmentStrings()['customer_email']] = $booking['customer']['email'];
                        $row[BackendStrings::getAppointmentStrings()['customer_phone']] = $customerInfo['phone'] ? $customerInfo['phone'] : $phone;
                    }

                    $this->getRowData($params, $row, $appointment, $dateFormat, $timeFormat, null, $numberOfPersons, $booking);

                    $customFieldsJson = json_decode($booking['customFields'], true);
                    if (in_array('customFields', $params['fields'], true)) {
                        foreach ($allCustomFields->getItems() as $customFiledColumnIndex => $customFiledColumn) {
                            if ($customFiledColumn->getType()->getValue() !== 'file') {
                                $customFieldValue = '';

                                foreach ((array)$customFieldsJson as $customFiledIndex => $customFiled) {
                                    if ($customFiledColumnIndex === $customFiledIndex) {
                                        if (is_array($customFiled['value'])) {
                                            $customFieldsValues = [];
                                            foreach ($customFiled['value'] as $customFiledValue) {
                                                $customFieldsValues[] = $customFiledValue;
                                            }
                                            $customFieldValue = implode('|', $customFieldsValues);
                                        } else {
                                            $customFieldValue = $customFiled['value'];
                                        }
                                    }
                                }
                                $row[$customFiledColumn->getLabel()->getValue()] = $customFieldValue;
                            }
                        }
                    }
                    $rows[] = $row;
                }
            }
        }

        $reportService->generateReport($rows, Entities::APPOINTMENT . 's', $params['delimiter']);

        $result->setAttachment(true);

        return $result;
    }

    /**
     * @throws \Interop\Container\Exception\ContainerException
     */
    private function getRowData($params, &$row, $appointment, $dateFormat, $timeFormat, $customFields, $numberOfPersons, $booking = null)
    {
        if (in_array('employee', $params['fields'], true)) {
            $row[BackendStrings::getCommonStrings()['employee']] =
                $appointment['provider']['firstName'] . ' ' . $appointment['provider']['lastName'];
        }

        if (in_array('service', $params['fields'], true)) {
            $row[BackendStrings::getCommonStrings()['service']] = $appointment['service']['name'];
        }

        if (in_array('startTime', $params['fields'], true)) {
            $row[BackendStrings::getAppointmentStrings()['start_time']] =
                DateTimeService::getCustomDateTimeObject($appointment['bookingStart'])
                    ->format($dateFormat . ' ' . $timeFormat);
        }

        if (in_array('endTime', $params['fields'], true)) {
            $row[BackendStrings::getAppointmentStrings()['end_time']] =
                DateTimeService::getCustomDateTimeObject($appointment['bookingEnd'])
                    ->format($dateFormat . ' ' . $timeFormat);
        }

        if (in_array('price', $params['fields'], true)) {
            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');
            if ($booking) {
                if ($booking['packageCustomerService']) {
                    $row[BackendStrings::getAppointmentStrings()['price']] = BackendStrings::getAppointmentStrings()['package_deal'];
                } else {
                    $row[BackendStrings::getAppointmentStrings()['price']] = $helperService->getFormattedPrice($this->getBookingPrice($booking));
                }
            } else {
                $price       = 0;
                $packageText = '';
                foreach ($appointment['bookings'] as $booking2) {
                    if ($booking2['packageCustomerService']) {
                        $packageText = BackendStrings::getAppointmentStrings()['package_deal'];
                    } else {
                        $price += $this->getBookingPrice($booking2);
                    }
                }
                if ($price > 0) {
                    if ($packageText) {
                        $row[BackendStrings::getAppointmentStrings()['price']] = $helperService->getFormattedPrice($price) . ' + ' . $packageText;
                    } else {
                        $row[BackendStrings::getAppointmentStrings()['price']] = $helperService->getFormattedPrice($price);
                    }
                } else {
                    if ($packageText) {
                        $row[BackendStrings::getAppointmentStrings()['price']] = $packageText;
                    } else {
                        $row[BackendStrings::getAppointmentStrings()['price']] = 0;
                    }
                }
            }
        }

        if (in_array('paymentStatus', $params['fields'], true)) {
            if ($booking) {
                $status = $booking['payments'] && count($booking['payments']) > 0 ?
                    $booking['payments'][0]['status'] : 'pending';
                $row[BackendStrings::getCommonStrings()['payment_status']] = $status === 'partiallyPaid' ? BackendStrings::getCommonStrings()['partially_paid'] : BackendStrings::getCommonStrings()[$status];
            } else {
                $statuses = [];
                foreach ($appointment['bookings'] as $booking2) {
                    $status     = $booking2['payments'] && count($booking2['payments']) > 0 ?
                        $booking2['payments'][0]['status'] : 'pending';
                    $statuses[] = $status === 'partiallyPaid' ? BackendStrings::getCommonStrings()['partially_paid'] : BackendStrings::getCommonStrings()[$status];
                }
                $row[BackendStrings::getCommonStrings()['payment_status']] = implode(', ', $statuses);
            }
        }

        if (in_array('paymentMethod', $params['fields'], true)) {
            if ($booking) {
                $method = $booking['payments'] && count($booking['payments']) > 0 ?
                    $booking['payments'][0]['gateway'] : 'onSite';
                if ($method === 'wc') {
                    $method = 'wc_name';
                }
                $row[BackendStrings::getCommonStrings()['payment_method']] = !$method || $method === 'onSite' ? BackendStrings::getCommonStrings()['on_site'] : BackendStrings::getSettingsStrings()[$method];
            } else {
                $methods = [];
                foreach ($appointment['bookings'] as $booking2) {
                    $method = $booking2['payments'] && count($booking2['payments']) > 0 ?
                        $booking2['payments'][0]['gateway'] : 'onSite';
                    if ($method === 'wc') {
                        $method = 'wc_name';
                    }
                    $methods[] = !$method || $method === 'onSite' ? BackendStrings::getCommonStrings()['on_site'] : BackendStrings::getSettingsStrings()[$method];
                }
                $row[BackendStrings::getCommonStrings()['payment_method']] = implode(', ', $methods);
            }
        }

        if (in_array('wcOrderId', $params['fields'], true)) {
            if ($booking) {
                $wcOrderId = $booking['payments'] && count($booking['payments']) > 0 ?
                    $booking['payments'][0]['wcOrderId'] : '';
                $row[BackendStrings::getCommonStrings()['wc_order_id_export']] = $wcOrderId;
            } else {
                $wcOrderIds = [];
                foreach ($appointment['bookings'] as $bookingWc) {
                    $wcOrderId    = $bookingWc['payments'] && count($bookingWc['payments']) > 0 ?
                        $bookingWc['payments'][0]['wcOrderId'] : '';
                    $wcOrderIds[] = $wcOrderId;
                }
                $row[BackendStrings::getCommonStrings()['wc_order_id_export']] = implode(', ', $wcOrderIds);
            }
        }

        if (in_array('note', $params['fields'], true)) {
            $row[BackendStrings::getCommonStrings()['note']] = $appointment['internalNotes'];
        }

        if (in_array('status', $params['fields'], true)) {
            $row[BackendStrings::getCommonStrings()['status']] =
                ucfirst(BackendStrings::getCommonStrings()[$appointment['status']]);
        }

        if ($customFields && in_array('customFields', $params['fields'], true)) {
            $row[BackendStrings::getSettingsStrings()['custom_fields']] = implode(', ', $customFields);
        }

        if (in_array('persons', $params['fields'], true)) {
            $row[BackendStrings::getNotificationsStrings()['ph_booking_number_of_persons']] =
                implode(', ', $numberOfPersons);
        }
    }

    private function getBookingPrice($booking)
    {
        if ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING) {
            $aggregatedPrice  = isset($booking['aggregatedPrice']) && $booking['aggregatedPrice'];
            $extrasPriceTotal = 0;
            foreach ($booking['extras'] as $extra) {
                $aggregatedExtraPrice = isset($extra['aggregatedPrice']) && $extra['aggregatedPrice'] !== null ? $extra['aggregatedPrice'] : $aggregatedPrice;
                $extrasPriceTotal    += $extra['price'] * $extra['quantity'] * ($aggregatedExtraPrice ? $booking['persons'] : 1);
            }

            $servicePriceTotal = $booking['price'] * ($aggregatedPrice ? $booking['persons'] : 1);
            $subTotal          = $servicePriceTotal + $extrasPriceTotal;
            $discountTotal     = ($subTotal / 100 * ($booking['coupon'] ? $booking['coupon']['discount'] : 0)) + ($booking['coupon'] ? $booking['coupon']['deduction'] : 0);

            return $subTotal - $discountTotal;
        }
        return 0;
    }
}
