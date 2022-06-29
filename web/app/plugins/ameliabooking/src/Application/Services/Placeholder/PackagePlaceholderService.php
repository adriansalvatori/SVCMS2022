<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerServiceRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class PackagePlaceholderService
 *
 * @package AmeliaBooking\Application\Services\Notification
 */
class PackagePlaceholderService extends AppointmentPlaceholderService
{
    /**
     *
     * @return array
     *
     * @throws ContainerException
     */
    public function getEntityPlaceholdersDummyData($type)
    {
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.appointment.service");

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');

        return array_merge([
            'package_name'                => 'Package Name',
            'reservation_name'            => 'Package Name',
            'package_price'               => $helperService->getFormattedPrice(100),
            'package_deposit_payment'     => $helperService->getFormattedPrice(20),
            'package_description'         => 'Package Description',
            'package_duration'            => date_i18n($dateFormat, date_create()->getTimestamp()),
            'reservation_description'     => 'Reservation Description'
        ], $placeholderService->getEntityPlaceholdersDummyData($type));
    }

    /**
     * @param array        $package
     * @param int          $bookingKey
     * @param string       $type
     * @param AbstractUser $customer
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getPlaceholdersData($package, $bookingKey = null, $type = null, $customer = null)
    {
        $isCustomerPackage = isset($package['isForCustomer']) && $package['isForCustomer'];
        $info = $isCustomerPackage ? json_encode($package['customer']) : null;
         return array_merge(
            $this->getPackageData($package),
            $this->getCompanyData($info),
            $this->getCustomersData(
                $package,
                $type,
                0,
                $customer ?: UserFactory::create($package['customer'])
            ),
            $this->getRecurringAppointmentsData($package, $bookingKey, $type, 'package'),
            [
                'icsFiles' => !empty($package['icsFiles']) ? $package['icsFiles'] : []
            ]
        );
    }

    /**
     * @param array $package
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws ContainerException
     * @throws Exception
     */
    private function getPackageData($package)
    {
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $price = $package['price'];

        if (!$package['calculatedPrice'] && $package['discount']) {
            $subtraction = $price / 100 * ($package['discount'] ?: 0);

            $price = (float)round($price - $subtraction, 2);
        }

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            [
                'customerId' => $package['customer']['id'],
                'packages'   => [$package['id']]
            ]
        );

        $endDate     = null;
        $deposit     = null;
        $paymentType = null;

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
            if ($packageCustomerService->getPackageCustomer()->getEnd()) {
                if ($endDate === null) {
                    $endDate = $packageCustomerService->getPackageCustomer()->getEnd()->getValue();
                }

                if ($packageCustomerService->getPackageCustomer()->getEnd()->getValue() > $endDate) {
                    $endDate = $packageCustomerService->getPackageCustomer()->getEnd()->getValue();
                }
            }
            if ($packageCustomerService->getPackageCustomer()->getPayment()) {
                $payment = $packageCustomerService->getPackageCustomer()->getPayment();
                if ($package['deposit']) {
                    $deposit = $payment->getAmount()->getValue();
                }
                $paymentType = $payment->getGateway()->getName()->getValue() === 'onSite' ? BackendStrings::getCommonStrings()['on_site'] : BackendStrings::getPaymentStrings()['online'];
            }
        }

        $isCustomerPackage = isset($package['isForCustomer']) && $package['isForCustomer'];

        $packageName = $helperService->getBookingTranslation(
            $isCustomerPackage ? json_encode($package['customer']) : null,
            $package['translations'],
            'name'
        ) ?: $package['name'];

        $packageDescription = $helperService->getBookingTranslation(
            $isCustomerPackage ? json_encode($package['customer']) : null,
            $package['translations'],
            'description'
        ) ?: $package['description'];

        return [
            'reservation_name'        => $packageName,
            'package_name'            => $packageName,
            'package_description'     => $packageDescription,
            'package_duration'        => $endDate ?
                date_i18n($dateFormat, $endDate->getTimestamp()) :
                FrontendStrings::getBookingStrings()['package_book_unlimited'],
            'reservation_description' => $packageDescription,
            'package_price'           => $helperService->getFormattedPrice($price),
            'package_deposit_payment' => $deposit !== null ? $helperService->getFormattedPrice($deposit) : '',
            'payment_type'            => $paymentType,
        ];
    }

    /**
     * @param array $entity
     *
     * @param string $subject
     * @param string $body
     * @param int    $userId
     * @return array
     *
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function reParseContentForProvider($entity, $subject, $body, $userId)
    {
        $employeeSubject = $subject;

        $employeeBody = $body;

        foreach ($entity['recurring'] as $recurringData) {
            if ($recurringData['appointment']['providerId'] === $userId) {
                $employeeData = $this->getEmployeeData($recurringData['appointment']);

                $employeeSubject = $this->applyPlaceholders(
                    $subject,
                    $employeeData
                );

                $employeeBody = $this->applyPlaceholders(
                    $body,
                    $employeeData
                );
            }
        }

        return [
            'body'    => $employeeBody,
            'subject' => $employeeSubject,
        ];
    }
}
