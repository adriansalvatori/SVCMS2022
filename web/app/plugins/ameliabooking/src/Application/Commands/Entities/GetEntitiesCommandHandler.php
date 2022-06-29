<?php

namespace AmeliaBooking\Application\Commands\Entities;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Bookable\PackageApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Application\Services\User\ProviderApplicationService;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\AuthorizationException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Entity\User\Customer;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Bookable\Service\ServiceFactory;
use AmeliaBooking\Domain\Services\Booking\EventDomainService;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\CategoryRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventTagsRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\ProviderRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use Interop\Container\Exception\ContainerException;

/**
 * Class GetEntitiesCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Entities
 */
class GetEntitiesCommandHandler extends CommandHandler
{
    /**
     * @param GetEntitiesCommand $command
     *
     * @return CommandResult
     * @throws AccessDeniedException
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function handle(GetEntitiesCommand $command)
    {
        /** @var UserApplicationService $userAS */
        $userAS = $this->container->get('application.user.service');

        /** @var EventDomainService $eventDS */
        $eventDS = $this->container->get('domain.booking.event.service');

        try {
            /** @var AbstractUser $currentUser */
            $currentUser = $userAS->authorization(
                $command->getPage() === 'cabinet' ? $command->getToken() : null,
                $command->getCabinetType()
            );
        } catch (AuthorizationException $e) {
            $currentUser =  null;
        }

        $params = $command->getField('params');

        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        $services = new Collection();

        $locations = new Collection();

        $events = new Collection();

        $resultData = [];



        /** Events */
        if (in_array(Entities::EVENTS, $params['types'], true)) {
            /** @var EventRepository $eventRepository */
            $eventRepository = $this->container->get('domain.booking.event.repository');

            $dateFilter = ['dates' => [DateTimeService::getNowDateTime()]];

            /** @var Collection $events */
            $events = $eventRepository->getFiltered($dateFilter);

            $resultData['events'] = $events->toArray();

            $resultData['events'] = $eventDS->getShortcodeForEventList($this->container, $resultData['events']);
        }

        /** Event Tags */
        if (in_array(Entities::TAGS, $params['types'], true)) {
            /** @var EventTagsRepository $eventTagsRepository */
            $eventTagsRepository = $this->container->get('domain.booking.event.tag.repository');

            /** @var Collection $eventsTags */
            $eventsTags = $eventTagsRepository->getAllDistinctByCriteria(
                $events->length() ? ['eventIds' => array_column($events->toArray(), 'id')] : []
            );

            $resultData['tags'] = $eventsTags->toArray();
        }

        /** Locations */
        if (in_array(Entities::LOCATIONS, $params['types'], true)) {
            /** @var LocationRepository $locationRepository */
            $locationRepository = $this->getContainer()->get('domain.locations.repository');

            /** @var Collection $locations */
            $locations = $locationRepository->getAllOrderedByName();

            $resultData['locations'] = $locations->toArray();
        }

        /** Categories */
        if (in_array(Entities::CATEGORIES, $params['types'], true)) {
            /** @var ServiceRepository $serviceRepository */
            $serviceRepository = $this->container->get('domain.bookable.service.repository');
            /** @var CategoryRepository $categoryRepository */
            $categoryRepository = $this->container->get('domain.bookable.category.repository');
            /** @var BookableApplicationService $bookableAS */
            $bookableAS = $this->container->get('application.bookable.service');

            /** @var Collection $services */
            $services = $serviceRepository->getAllArrayIndexedById();

            /** @var Service $service */
            foreach ($services->getItems() as $service) {
                if ($service->getSettings() && json_decode($service->getSettings()->getValue(), true) === null) {
                    $service->setSettings(null);
                }
            }

            /** @var Collection $categories */
            $categories = $categoryRepository->getAllIndexedById();

            $bookableAS->addServicesToCategories($categories, $services);

            $resultData['categories'] = $categories->toArray();
        }

        $resultData['customers'] = [];

        /** Customers */
        if (in_array(Entities::CUSTOMERS, $params['types'], true)) {
            /** @var UserRepository $userRepo */
            $userRepo = $this->getContainer()->get('domain.users.repository');
            /** @var ProviderApplicationService $providerAS */
            $providerAS = $this->container->get('application.user.provider.service');

            $resultData['customers'] = [];

            if ($currentUser) {
                switch ($currentUser->getType()) {
                    case (AbstractUser::USER_ROLE_CUSTOMER):
                        if ($currentUser->getId()) {
                            /** @var Customer $customer */
                            $customer = $userRepo->getById($currentUser->getId()->getValue());

                            $resultData['customers'] = [$customer->toArray()];
                        }

                        break;

                    case (AbstractUser::USER_ROLE_PROVIDER):
                        $resultData['customers'] = $providerAS->getAllowedCustomers($currentUser)->toArray();

                        break;

                    default:
                        /** @var Collection $customers */
                        $customers = $userRepo->getAllWithAllowedBooking();

                        $resultData['customers'] = $customers->toArray();
                }
            }
        }

        /** Providers */
        if (in_array(Entities::EMPLOYEES, $params['types'], true)) {
            /** @var ProviderRepository $providerRepository */
            $providerRepository = $this->container->get('domain.users.providers.repository');

            /** @var ProviderApplicationService $providerAS */
            $providerAS = $this->container->get('application.user.provider.service');

            if (array_key_exists('page', $params) && $params['page'] === Entities::CALENDAR) {
                /** @var Collection $providers */
                $providers = $providerRepository->getByCriteriaWithSchedule([]);

                $providerServicesData = $providerRepository->getProvidersServices();

                foreach ($providerServicesData as $providerKey => $providerServices) {
                    /** @var Provider $provider */
                    $provider = $providers->getItem($providerKey);

                    /** @var Collection $providerServiceList */
                    $providerServiceList = new Collection();

                    foreach ((array)$providerServices as $serviceKey => $providerService) {
                        $service = $services->getItem($serviceKey);


                        if ($service && $provider) {
                            $providerServiceList->addItem(
                                ServiceFactory::create(array_merge($service->toArray(), $providerService)),
                                $service->getId()->getValue()
                            );
                        }
                    }

                    $provider->setServiceList($providerServiceList);
                }
            } else {
                /** @var Collection $providers */
                $providers = $providerRepository->getAllWithServices();

                /** @var Provider $provider */
                foreach ($providers->getItems() as $provider) {
                    /** @var Service $service */
                    foreach ($provider->getServiceList()->getItems() as $service) {
                        if ($service->getSettings() && json_decode($service->getSettings()->getValue(), true) === null) {
                            $service->setSettings(null);
                        }
                    }
                }
            }

            /** @var SettingsService $settingsDS */
            $settingsDS = $this->container->get('domain.settings.service');

            $allowAdminToBookAtAnyTime =
                array_key_exists('page', $params) &&
                in_array($params['page'], [Entities::CALENDAR, Entities::APPOINTMENTS]) &&
                $settingsDS->getSetting('roles', 'allowAdminBookAtAnyTime');

            if ($allowAdminToBookAtAnyTime) {
                $providerAS->setProvidersAlwaysAvailableForAdmin($providers);
            }

            $resultData['entitiesRelations'] = [];

            /** @var Provider $provider */
            foreach ($providers->getItems() as $providerId => $provider) {
                if ($data = $providerAS->getProviderServiceLocations($provider, $locations, $services)) {
                    $resultData['entitiesRelations'][$providerId] = $data;
                }
            }


            $resultData['employees'] = $providerAS->removeAllExceptUser(
                $providers->toArray(),
                (array_key_exists('page', $params) && $params['page'] === Entities::BOOKING) ?
                    null : $currentUser
            );

            if ($currentUser === null || $currentUser->getType() === AbstractUser::USER_ROLE_CUSTOMER) {
                foreach ($resultData['employees'] as &$employee) {
                    unset(
                        $employee['birthday'],
                        $employee['email'],
                        $employee['externalId'],
                        $employee['phone'],
                        $employee['note']
                    );

                    if (isset($params['page']) && $params['page'] !== Entities::CALENDAR) {
                        unset(
                            $employee['weekDayList'],
                            $employee['specialDayList'],
                            $employee['dayOffList']
                        );
                    }
                }
            }
        }

        $resultData[Entities::APPOINTMENTS] = [
            'futureAppointments' => [],
        ];

        if (in_array(Entities::APPOINTMENTS, $params['types'], true)) {
            $userParams = [
                'dates' => [null, null]
            ];

            if (!$this->getContainer()->getPermissionsService()->currentUserCanReadOthers(Entities::APPOINTMENTS)) {
                if ($currentUser->getId() === null) {
                    $userParams[$currentUser->getType() . 'Id'] = 0;
                } else {
                    $userParams[$currentUser->getType() . 'Id'] =
                        $currentUser->getId()->getValue();
                }
            }

            /** @var AppointmentRepository $appointmentRepo */
            $appointmentRepo = $this->container->get('domain.booking.appointment.repository');

            /** @var Collection $appointments */
            $appointments = $appointmentRepo->getFiltered($userParams);

            $resultData[Entities::APPOINTMENTS] = [
                'futureAppointments' => $appointments->toArray(),
            ];
        }

        /** Custom Fields */
        if (in_array(Entities::CUSTOM_FIELDS, $params['types'], true) ||
            in_array('customFields', $params['types'], true)
        ) {
            /** @var CustomFieldRepository $customFieldRepository */
            $customFieldRepository = $this->container->get('domain.customField.repository');

            /** @var Collection $customFields */
            $customFields = $customFieldRepository->getAll();

            $resultData['customFields'] = $customFields->toArray();
        }

        /** Coupons */
        if (in_array(Entities::COUPONS, $params['types'], true) &&
            $this->getContainer()->getPermissionsService()->currentUserCanRead(Entities::COUPONS)
        ) {
            /** @var CouponRepository $couponRepository */
            $couponRepository = $this->container->get('domain.coupon.repository');

            /** @var Collection $coupons */
            $coupons = $couponRepository->getAllIndexedById();

            /** @var CouponRepository $couponRepository */
            $couponRepository = $this->container->get('domain.coupon.repository');

            /** @var ServiceRepository $serviceRepository */
            $serviceRepository = $this->container->get('domain.bookable.service.repository');

            /** @var EventRepository $eventRepository */
            $eventRepository = $this->container->get('domain.booking.event.repository');

            /** @var Collection $allServices */
            $allServices = $serviceRepository->getAllIndexedById();

            foreach ($couponRepository->getCouponsServicesIds($coupons->keys()) as $ids) {
                /** @var Coupon $coupon */
                $coupon = $coupons->getItem($ids['couponId']);

                $coupon->getServiceList()->addItem(
                    $allServices->getItem($ids['serviceId']),
                    $ids['serviceId']
                );
            }

            /** @var Collection $allEvents */
            $allEvents = $eventRepository->getAllIndexedById();

            foreach ($couponRepository->getCouponsEventsIds($coupons->keys()) as $ids) {
                /** @var Coupon $coupon */
                $coupon = $coupons->getItem($ids['couponId']);

                $coupon->getEventList()->addItem(
                    $allEvents->getItem($ids['eventId']),
                    $ids['eventId']
                );
            }

            $resultData['coupons'] = $coupons->toArray();
        }

        /** Settings */
        if (in_array(Entities::SETTINGS, $params['types'], true)) {
            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');

            /** @var SettingsService $settingsDS */
            $settingsDS = $this->container->get('domain.settings.service');

            $languages = $helperService->getLanguages();

            usort(
                $languages,
                function ($x, $y) {
                    return strcasecmp($x['name'], $y['name']);
                }
            );

            $languagesSorted = [];

            foreach ($languages as $language) {
                $languagesSorted[$language['wp_locale']] = $language;
            }

            /** @var \AmeliaBooking\Application\Services\Settings\SettingsService $settingsAS*/
            $settingsAS = $this->container->get('application.settings.service');

            $daysOff = $settingsAS->getDaysOff();

            $resultData['settings'] = [
                'general'   => [
                    'usedLanguages' => $settingsDS->getSetting('general', 'usedLanguages'),
                ],
                'languages' => $languagesSorted,
                'daysOff'   => $daysOff,
            ];
        }

        /** Packages */
        if (in_array(Entities::PACKAGES, $params['types'], true)) {
            /** @var PackageApplicationService $packageApplicationService */
            $packageApplicationService = $this->container->get('application.bookable.package');

            $resultData['packages'] = $packageApplicationService->getPackagesArray();
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved entities');
        $result->setData($resultData);

        return $result;
    }

}
