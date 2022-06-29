<?php

namespace AmeliaBooking\Application\Services\Bookable;

use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Gallery\GalleryApplicationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Category;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomer;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageService;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\Schedule\Period;
use AmeliaBooking\Domain\Entity\Schedule\PeriodService;
use AmeliaBooking\Domain\Entity\Schedule\SpecialDay;
use AmeliaBooking\Domain\Entity\Schedule\SpecialDayPeriod;
use AmeliaBooking\Domain\Entity\Schedule\SpecialDayPeriodService;
use AmeliaBooking\Domain\Entity\Schedule\WeekDay;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Bookable\Service\PackageServiceFactory;
use AmeliaBooking\Domain\Factory\Location\LocationFactory;
use AmeliaBooking\Domain\Factory\User\UserFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\Duration;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\PositiveInteger;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\WholeNumber;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\CategoryRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageServiceLocationRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageServiceProviderRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ProviderServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponServiceRepository;
use AmeliaBooking\Infrastructure\Repository\CustomField\CustomFieldServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationRepository;
use AmeliaBooking\Infrastructure\Repository\Notification\NotificationsToEntitiesRepository;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\Repository\Schedule\PeriodServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Schedule\SpecialDayPeriodServiceRepository;
use AmeliaBooking\Infrastructure\Repository\User\ProviderRepository;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class BookableApplicationService
 *
 * @package AmeliaBooking\Application\Services\Booking
 */
class BookableApplicationService
{

    private $container;

    /**
     * BookableApplicationService constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param int $serviceId
     * @param int $providerId
     *
     * @return Service
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function getAppointmentService($serviceId, $providerId)
    {
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var Collection $providerServices */
        $providerServices = $serviceRepository->getProviderServicesWithExtras($serviceId, $providerId);

        return $providerServices->keyExists($serviceId) ?
            $providerServices->getItem($serviceId) : $serviceRepository->getById($serviceId);
    }

    /**
     * @param Collection $categories
     * @param Collection $services
     *
     * @throws InvalidArgumentException
     */
    public function addServicesToCategories($categories, $services)
    {
        /** @var Category $category */
        foreach ($categories->getItems() as $category) {
            $category->setServiceList(new Collection());
        }

        /** @var Service $service */
        foreach ($services->getItems() as $service) {
            $categoryId = $service->getCategoryId()->getValue();

            $categories
                ->getItem($categoryId)
                ->getServiceList()
                ->addItem($service, $service->getId()->getValue());
        }
    }

    /**
     * @param Service    $service
     * @param Collection $providers
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function manageProvidersForServiceAdd($service, $providers)
    {
        /** @var ProviderServiceRepository $providerServiceRepo */
        $providerServiceRepo = $this->container->get('domain.bookable.service.providerService.repository');

        /** @var Provider $provider */
        foreach ($providers->getItems() as $provider) {
            $providerServiceRepo->add($service, $provider->getId()->getValue());
        }
    }

    /**
     * @param Package $package
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function manageServicesForPackageAdd($package)
    {
        /** @var PackageServiceRepository $packageServiceRepository */
        $packageServiceRepository = $this->container->get('domain.bookable.package.packageService.repository');
        /** @var PackageServiceLocationRepository $packageServiceLocationRepository */
        $packageServiceLocationRepository =
            $this->container->get('domain.bookable.package.packageServiceLocation.repository');
        /** @var PackageServiceProviderRepository $packageServiceProviderRepository */
        $packageServiceProviderRepository =
            $this->container->get('domain.bookable.package.packageServiceProvider.repository');

        /** @var PackageService $bookable */
        foreach ($package->getBookable()->getItems() as $bookable) {
            $bookableId = $packageServiceRepository->add($bookable, $package->getId()->getValue());

            $bookable->setId(new Id($bookableId));

            /** @var Location $location */
            foreach ($bookable->getLocations()->getItems() as $location) {
                $packageServiceLocationRepository->add($location, $bookable->getId()->getValue());
            }

            /** @var Provider $provider */
            foreach ($bookable->getProviders()->getItems() as $provider) {
                $packageServiceProviderRepository->add($provider, $bookable->getId()->getValue());
            }
        }
    }

    /**
     * @param Package $package
     * @param array   $bookableServices
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function manageServicesForPackageUpdate($package, $bookableServices)
    {
        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');
        /** @var PackageServiceRepository $packageServiceRepository */
        $packageServiceRepository = $this->container->get('domain.bookable.package.packageService.repository');
        /** @var PackageServiceLocationRepository $packageServiceLocationRepository */
        $packageServiceLocationRepository =
            $this->container->get('domain.bookable.package.packageServiceLocation.repository');
        /** @var PackageServiceProviderRepository $packageServiceProviderRepository */
        $packageServiceProviderRepository =
            $this->container->get('domain.bookable.package.packageServiceProvider.repository');
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var Collection $services */
        $services = $serviceRepository->getByCriteria(['services' => array_keys($bookableServices)]);

        /** @var Package $oldPackage */
        $oldPackage = $packageRepository->getById($package->getId()->getValue());

        $oldPackageServicesIds = [];

        /** @var PackageService $oldPackageService */
        foreach ($oldPackage->getBookable()->getItems() as $oldPackageService) {
            $serviceId = $oldPackageService->getService()->getId()->getValue();

            $oldPackageServicesIds[$serviceId] = [
                'providersIds' => [],
                'locationsIds' => [],
            ];

            /** @var Location $location */
            foreach ($oldPackageService->getLocations()->getItems() as $location) {
                $oldPackageServicesIds[$serviceId]['locationsIds'][] = $location->getId()->getValue();
            }

            /** @var Provider $provider */
            foreach ($oldPackageService->getProviders()->getItems() as $provider) {
                $oldPackageServicesIds[$serviceId]['providersIds'][] = $provider->getId()->getValue();
            }
        }

        foreach ($bookableServices as $serviceId => $data) {
            if (!in_array($serviceId, array_keys($oldPackageServicesIds), false)) {
                $packageService = PackageServiceFactory::create(
                    [
                        'service'          => $services->getItem($serviceId)->toArray(),
                        'quantity'         => $data['quantity'],
                        'minimumScheduled' => $data['minimumScheduled'],
                        'maximumScheduled' => $data['maximumScheduled'],
                        'providers'        => $data['providers'],
                        'locations'        => $data['locations'],
                    ]
                );

                $packageServiceId = $packageServiceRepository->add($packageService, $package->getId()->getValue());

                $packageService->setId(new Id($packageServiceId));

                /** @var Location $location */
                foreach ($packageService->getLocations()->getItems() as $location) {
                    $packageServiceLocationRepository->add($location, $packageService->getId()->getValue());
                }

                /** @var Provider $provider */
                foreach ($packageService->getProviders()->getItems() as $provider) {
                    $packageServiceProviderRepository->add($provider, $packageService->getId()->getValue());
                }
            }
        }

        /** @var PackageService $oldPackageService */
        foreach ($oldPackage->getBookable()->getItems() as $oldPackageService) {
            $serviceId = $oldPackageService->getService()->getId()->getValue();

            if (in_array($serviceId, array_keys($bookableServices), false)) {
                $oldPackageService->setQuantity(new PositiveInteger($bookableServices[$serviceId]['quantity']));

                $oldPackageService->setMinimumScheduled(
                    new WholeNumber($bookableServices[$serviceId]['minimumScheduled'])
                );

                $oldPackageService->setMaximumScheduled(
                    new WholeNumber($bookableServices[$serviceId]['maximumScheduled'])
                );

                $packageServiceRepository->update($oldPackageService->getId()->getValue(), $oldPackageService);

                foreach ($bookableServices[$serviceId]['locations'] as $data) {
                    if (!in_array($data['id'], $oldPackageServicesIds[$serviceId]['locationsIds'], false)) {
                        $packageServiceLocationRepository->add(
                            LocationFactory::create($data),
                            $oldPackageService->getId()->getValue()
                        );
                    }
                }

                $packageServiceLocationRepository->deleteAllNotInLocationsServicesArrayForPackage(
                    array_column($bookableServices[$serviceId]['locations'], 'id'),
                    $oldPackageService->getId()->getValue()
                );

                foreach ($bookableServices[$serviceId]['providers'] as $data) {
                    if (!in_array($data['id'], $oldPackageServicesIds[$serviceId]['providersIds'], false)) {
                        $packageServiceProviderRepository->add(
                            UserFactory::create($data),
                            $oldPackageService->getId()->getValue()
                        );
                    }
                }

                $packageServiceProviderRepository->deleteAllNotInProvidersServicesArrayForPackage(
                    array_column($bookableServices[$serviceId]['providers'], 'id'),
                    $oldPackageService->getId()->getValue()
                );
            } else {
                $packageServiceLocationRepository->deleteAllNotInLocationsServicesArrayForPackage(
                    $oldPackageServicesIds[$serviceId]['locationsIds'],
                    $oldPackageService->getId()->getValue()
                );

                $packageServiceProviderRepository->deleteAllNotInProvidersServicesArrayForPackage(
                    $oldPackageServicesIds[$serviceId]['providersIds'],
                    $oldPackageService->getId()->getValue()
                );
            }
        }

        $packageServiceRepository->deleteAllNotInServicesArrayForPackage(
            array_keys($bookableServices),
            $package->getId()->getValue()
        );
    }

    /**
     * @param Service $service
     * @param array   $serviceProvidersIds
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function manageProvidersForServiceUpdate($service, $serviceProvidersIds)
    {
        /** @var ProviderRepository $providerRepo */
        $providerRepo = $this->container->get('domain.users.providers.repository');
        /** @var ProviderServiceRepository $providerServiceRepo */
        $providerServiceRepo = $this->container->get('domain.bookable.service.providerService.repository');
        /** @var PeriodServiceRepository $periodServiceRepo */
        $periodServiceRepo = $this->container->get('domain.schedule.period.service.repository');
        /** @var SpecialDayPeriodServiceRepository $specialDayPeriodServiceRepo */
        $specialDayPeriodServiceRepo = $this->container->get('domain.schedule.specialDay.period.service.repository');

        /** @var Collection $serviceProviders */
        $serviceProviders = $providerRepo->getByCriteria(['services' => [$service->getId()->getValue()]]);

        $serviceId = $service->getId()->getValue();

        /** @var Provider $provider */
        foreach ($serviceProviders->getItems() as $provider) {
            $isServiceProvider = in_array($provider->getId()->getValue(), $serviceProvidersIds, false);

            if (!$isServiceProvider) {
                /** @var WeekDay $weekDay */
                foreach ($provider->getWeekDayList()->getItems() as $weekDay) {
                    /** @var Period $period */
                    foreach ($weekDay->getPeriodList()->getItems() as $period) {
                        /** @var PeriodService $periodService */
                        foreach ($period->getPeriodServiceList()->getItems() as $periodService) {
                            if ($periodService->getServiceId()->getValue() === $serviceId) {
                                $periodServiceRepo->delete($periodService->getId()->getValue());
                            }
                        }
                    }
                }

                /** @var SpecialDay $specialDay */
                foreach ($provider->getSpecialDayList()->getItems() as $specialDay) {
                    /** @var SpecialDayPeriod $period */
                    foreach ($specialDay->getPeriodList()->getItems() as $period) {
                        /** @var SpecialDayPeriodService $periodService */
                        foreach ($period->getPeriodServiceList()->getItems() as $periodService) {
                            if ($periodService->getServiceId()->getValue() === $serviceId) {
                                $specialDayPeriodServiceRepo->delete($periodService->getId()->getValue());
                            }
                        }
                    }
                }
            }
        }

        $providerServiceRepo->deleteAllNotInProvidersArrayForService($serviceProvidersIds, $serviceId);

        foreach ($serviceProvidersIds as $providerId) {
            if (!in_array($providerId, $serviceProviders->keys(), false)) {
                $providerServiceRepo->add($service, (int)$providerId);
            }
        }
    }

    /**
     * @param Service $service
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function managePackagesForServiceUpdate($service)
    {
        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var Collection $packages */
        $packages = $packageRepository->getByCriteria([]);

        /** @var Package $package */
        foreach ($packages->getItems() as $package) {
            $hasService = false;

            /** @var PackageService $bookable */
            foreach ($package->getBookable()->getItems() as $bookable) {
                if ($bookable->getService()->getId()->getValue() === $service->getId()->getValue()) {
                    $hasService = true;

                    break;
                }
            }

            if ($hasService && $package->getCalculatedPrice()->getValue()) {
                $price = 0;

                /** @var PackageService $bookable */
                foreach ($package->getBookable()->getItems() as $bookable) {
                    $price += $bookable->getService()->getPrice()->getValue() * $bookable->getQuantity()->getValue();
                }

                $package->setPrice(new Price($price));

                $packageRepository->update($package->getId()->getValue(), $package);
            }
        }
    }

    /**
     * @param Service $service
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function manageExtrasForServiceAdd($service)
    {
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');
        /** @var ExtraRepository $extraRepository */
        $extraRepository = $this->container->get('domain.bookable.extra.repository');

        if ($service->getExtras() !== null) {
            $extras = $service->getExtras();
            foreach ($extras->getItems() as $extra) {
                /** @var Extra $extra */
                $extra->setServiceId(new Id($service->getId()->getValue()));

                if (!($extraId = $extraRepository->add($extra))) {
                    $serviceRepository->rollback();
                }

                $extra->setId(new Id($extraId));
            }
        }
    }

    /**
     * @param Service $service
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function manageExtrasForServiceUpdate($service)
    {
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');
        /** @var ExtraRepository $extraRepository */
        $extraRepository = $this->container->get('domain.bookable.extra.repository');

        if ($service->getExtras() !== null) {
            $extras = $service->getExtras();
            foreach ($extras->getItems() as $extra) {
                /** @var Extra $extra */
                $extra->setServiceId(new Id($service->getId()->getValue()));
                if ($extra->getId() === null) {
                    if (!($extraId = $extraRepository->add($extra))) {
                        $serviceRepository->rollback();
                    }

                    $extra->setId(new Id($extraId));
                } else {
                    if (!$extraRepository->update($extra->getId()->getValue(), $extra)) {
                        $serviceRepository->rollback();
                    }
                }
            }
        }
    }

    /**
     * Accept two collection: services and providers
     * For each service function will add providers that are working on this service
     *
     * @param Service    $service
     * @param Collection $providers
     *
     * @return Collection
     *
     * @throws InvalidArgumentException
     */
    public function getServiceProviders($service, $providers)
    {
        $serviceProviders = new Collection();

        /** @var Provider $provider */
        foreach ($providers->getItems() as $provider) {
            /** @var Service $providerService */
            foreach ($provider->getServiceList()->getItems() as $providerService) {
                if ($providerService->getId()->getValue() === $service->getId()->getValue()) {
                    $serviceProviders->addItem($provider, $provider->getId()->getValue());
                }
            }
        }

        return $serviceProviders;
    }

    /**
     * Add 0 as duration for service time before or time after if it is null
     *
     * @param Service $service
     *
     * @throws InvalidArgumentException
     */
    public function checkServiceTimes($service)
    {
        if (!$service->getTimeBefore()) {
            $service->setTimeBefore(new Duration(0));
        }

        if (!$service->getTimeAfter()) {
            $service->setTimeAfter(new Duration(0));
        }
    }

    /**
     * Return collection of extras that are passed in $extraIds array for provided service
     *
     * @param array   $extraIds
     * @param Service $service
     *
     * @return Collection
     * @throws InvalidArgumentException
     */
    public function filterServiceExtras($extraIds, $service)
    {
        $extras = new Collection();

        foreach ((array)$service->getExtras()->keys() as $extraKey) {
            /** @var Extra $extra */
            $extra = $service->getExtras()->getItem($extraKey);

            if (in_array($extra->getId()->getValue(), $extraIds, false)) {
                if (!$extra->getDuration()) {
                    $extra->setDuration(new Duration(0));
                }

                $extras->addItem($extra, $extraKey);
            }
        }

        return $extras;
    }

    /**
     *
     * @param array $servicesIds
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getAppointmentsCountForServices($servicesIds)
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        $futureAppointmentsCount = $appointmentRepository->getPeriodAppointmentsCount(
            [
                'services' => $servicesIds,
                'dates'    => [
                    0 => DateTimeService::getNowDateTime()
                ]
            ]
        );

        $pastAppointmentsCount = $appointmentRepository->getPeriodAppointmentsCount(
            [
                'services' => $servicesIds,
                'dates'    => [
                    1 => DateTimeService::getNowDateTime()
                ]
            ]
        );

        if ($futureAppointmentsCount) {
            return [
                'futureAppointments'  => $futureAppointmentsCount,
                'pastAppointments'    => $pastAppointmentsCount,
            ];
        }

        $futureAppointments = 0;

        $pastAppointments = 0;

        /** @var Collection $appointments */
        $appointments = $appointmentRepository->getFiltered(['services' => $servicesIds]);

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(['services' => $servicesIds]);

        /** @var PackageApplicationService $packageApplicationService */
        $packageApplicationService = $this->container->get('application.bookable.package');

        return [
            'futureAppointments'  => $futureAppointments,
            'pastAppointments'    => $pastAppointments,
            'packageAppointments' => $packageApplicationService->getPackageUnusedBookingsCount(
                $packageCustomerServices,
                $appointments
            ),
        ];
    }

    /**
     *
     * @param array $packagesIds
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getAppointmentsCountForPackages($packagesIds)
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(['packages' => $packagesIds]);

        /** @var Collection $appointments */
        $appointments = $packageCustomerServices->keys() ? $appointmentRepository->getFiltered(
            ['packageCustomerServices' => $packageCustomerServices->keys()]
        ) : new Collection();

        $now = DateTimeService::getNowDateTimeObject();

        $futureAppointments = 0;

        $pastAppointments = 0;

        /** @var Appointment $appointment */
        foreach ($appointments->getItems() as $appointment) {
            if ($appointment->getBookingStart()->getValue() >= $now) {
                $futureAppointments++;
            } else {
                $pastAppointments++;
            }
        }

        /** @var PackageApplicationService $packageApplicationService */
        $packageApplicationService = $this->container->get('application.bookable.package');

        return [
            'futureAppointments'  => $futureAppointments,
            'pastAppointments'    => $pastAppointments,
            'packageAppointments' => $packageApplicationService->getPackageUnusedBookingsCount(
                $packageCustomerServices,
                $appointments
            ),
        ];
    }

    /**
     *
     * @param Category $category
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    public function deleteCategory($category)
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->container->get('domain.bookable.category.repository');

        /** @var Service $service */
        foreach ($category->getServiceList()->getItems() as $service) {
            if (!$this->deleteService($service)) {
                return false;
            }
        }

        return $categoryRepository->delete($category->getId()->getValue());
    }

    /**
     *
     * @param Service $service
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    public function deleteService($service)
    {
        /** @var GalleryApplicationService $galleryService */
        $galleryService = $this->container->get('application.gallery.service');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var CouponServiceRepository $couponServiceRepository */
        $couponServiceRepository = $this->container->get('domain.coupon.service.repository');

        /** @var ProviderServiceRepository $providerServiceRepository */
        $providerServiceRepository = $this->container->get('domain.bookable.service.providerService.repository');

        /** @var PeriodServiceRepository $periodServiceRepository */
        $periodServiceRepository = $this->container->get('domain.schedule.period.service.repository');

        /** @var SpecialDayPeriodServiceRepository $specialDayPeriodServiceRepository */
        $specialDayPeriodServiceRepository =
            $this->container->get('domain.schedule.specialDay.period.service.repository');

        /** @var CustomFieldServiceRepository $customFieldServiceRepository */
        $customFieldServiceRepository = $this->container->get('domain.customFieldService.repository');

        /** @var AppointmentApplicationService $appointmentApplicationService */
        $appointmentApplicationService = $this->container->get('application.booking.appointment.service');

        /** @var NotificationsToEntitiesRepository $notificationEntitiesRepo */
        $notificationEntitiesRepo = $this->container->get('domain.notificationEntities.repository');

        /** @var PackageServiceRepository $packageServiceRepository */
        $packageServiceRepository = $this->container->get('domain.bookable.package.packageService.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository =
            $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var PackageServiceProviderRepository $packageServiceProviderRepository */
        $packageServiceProviderRepository =
            $this->container->get('domain.bookable.package.packageServiceProvider.repository');

        /** @var PackageServiceLocationRepository $packageServiceLocationRepository */
        $packageServiceLocationRepository =
            $this->container->get('domain.bookable.package.packageServiceLocation.repository');

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var Collection $packages */
        $packages = $packageRepository->getByCriteria(['services' => [$service->getId()->getValue()]]);

        /** @var Collection $appointments */
        $appointments = $appointmentRepository->getFiltered(
            [
                'services' => [$service->getId()->getValue()]
            ]
        );

        /** @var Appointment $appointment */
        foreach ($appointments->getItems() as $appointment) {
            if (!$appointmentApplicationService->delete($appointment)) {
                return false;
            }
        }

        if (!$notificationEntitiesRepo->removeIfOnly($service->getId()->getValue())) {
            return false;
        }

        /** @var Extra $extra */
        foreach ($service->getExtras()->getItems() as $extra) {
            if (!$this->deleteExtra($extra)) {
                return false;
            }
        }

        /** @var Package $package */
        foreach ($packages->getItems() as $package) {
            /** @var PackageService $packageService */
            foreach ($package->getBookable()->getItems() as $packageService) {
                if ($packageService->getService()->getId()->getValue() === $service->getId()->getValue()) {
                    if (!$packageServiceProviderRepository->deleteByEntityId(
                        $packageService->getId()->getValue(),
                        'packageServiceId'
                    ) ||
                        !$packageServiceLocationRepository->deleteByEntityId(
                            $packageService->getId()->getValue(),
                            'packageServiceId'
                        )
                    ) {
                        return false;
                    }
                }
            }
        }

        return
            $galleryService->manageGalleryForEntityDelete($service->getGallery()) &&
            $customFieldServiceRepository->deleteByEntityId($service->getId()->getValue(), 'serviceId') &&
            $specialDayPeriodServiceRepository->deleteByEntityId($service->getId()->getValue(), 'serviceId') &&
            $periodServiceRepository->deleteByEntityId($service->getId()->getValue(), 'serviceId') &&
            $providerServiceRepository->deleteByEntityId($service->getId()->getValue(), 'serviceId') &&
            $couponServiceRepository->deleteByEntityId($service->getId()->getValue(), 'serviceId') &&
            $serviceRepository->deleteViewStats($service->getId()->getValue()) &&
            $packageServiceRepository->deleteByEntityId($service->getId()->getValue(), 'serviceId') &&
            $packageCustomerServiceRepository->deleteByEntityId($service->getId()->getValue(), 'serviceId') &&
            $serviceRepository->delete($service->getId()->getValue());
    }

    /**
     *
     * @param Package $package
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    public function deletePackage($package)
    {
        /** @var GalleryApplicationService $galleryService */
        $galleryService = $this->container->get('application.gallery.service');

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var PackageRepository $packageRepository */
        $packageRepository = $this->container->get('domain.bookable.package.repository');

        /** @var PackageServiceRepository $packageServiceRepository */
        $packageServiceRepository = $this->container->get('domain.bookable.package.packageService.repository');

        /** @var PackageServiceLocationRepository $packageServiceLocationRepository */
        $packageServiceLocationRepository =
            $this->container->get('domain.bookable.package.packageServiceLocation.repository');

        /** @var PackageServiceProviderRepository $packageServiceProviderRepository */
        $packageServiceProviderRepository =
            $this->container->get('domain.bookable.package.packageServiceProvider.repository');

        /** @var PackageCustomerRepository $packageCustomerRepository */
        $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $this->container->get('application.payment.service');

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            ['packages' => [$package->getId()->getValue()]]
        );

        /** @var PackageCustomerService $packageCustomerService */
        foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
            if (!$customerBookingRepository->updateByEntityId(
                $packageCustomerService->getId()->getValue(),
                null,
                'packageCustomerServiceId'
            )) {
                return false;
            }
        }

        /** @var Collection $packageCustomers */
        $packageCustomers = $packageCustomerRepository->getByEntityId($package->getId()->getValue(), 'packageId');

        /** @var PackageCustomer $packageCustomer */
        foreach ($packageCustomers->getItems() as $packageCustomer) {
            /** @var Collection $payments */
            $payments = $paymentRepository->getByEntityId(
                $packageCustomer->getId()->getValue(),
                'packageCustomerId'
            );

            /** @var Payment $payment */
            foreach ($payments->getItems() as $payment) {
                if (!$paymentAS->delete($payment)) {
                    return false;
                }
            }

            if (!$packageCustomerServiceRepository->deleteByEntityId(
                $packageCustomer->getId()->getValue(),
                'packageCustomerId'
            ) ||
                !$packageCustomerRepository->delete($packageCustomer->getId()->getValue())
            ) {
                return false;
            }
        }

        /** @var PackageService $packageService */
        foreach ($package->getBookable()->getItems() as $packageService) {
            $packageServiceId = $packageService->getId()->getValue();

            if (!$packageServiceLocationRepository->deleteByEntityId($packageServiceId, 'packageServiceId') ||
                !$packageServiceProviderRepository->deleteByEntityId($packageServiceId, 'packageServiceId')
            ) {
                return false;
            }
        }

        return
            $galleryService->manageGalleryForEntityDelete($package->getGallery()) &&
            $packageServiceRepository->deleteByEntityId($package->getId()->getValue(), 'packageId') &&
            $packageRepository->delete($package->getId()->getValue());
    }

    /**
     *
     * @param PackageCustomer $packageCustomer
     *
     * @return array|bool
     *
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function deletePackageCustomer($packageCustomer)
    {
        /** @var AppointmentApplicationService $appointmentApplicationService */
        $appointmentApplicationService = $this->container->get('application.booking.appointment.service');

        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $this->container->get('application.booking.booking.service');

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var PackageCustomerRepository $packageCustomerRepository */
        $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');

        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var PaymentApplicationService $paymentApplicationService */
        $paymentApplicationService = $this->container->get('application.payment.service');


        $resultData = [
            'updatedAppointments' => [],
            'deletedAppointments' => [],
        ];

        /** @var Collection $packageCustomerServices */
        $packageCustomerServices = $packageCustomerServiceRepository->getByCriteria(
            ['packagesCustomers' => [$packageCustomer->getId()->getValue()]]
        );

        /** @var Collection $packageAppointments */
        $packageAppointments = $packageCustomerServices->length() ? $appointmentRepository->getFiltered(
            [
                'packageCustomerServices' => $packageCustomerServices->keys()
            ]
        ) : new Collection();

        if ($packageAppointments->length()) {
            /** @var Collection $appointments */
            $appointments = $appointmentRepository->getFiltered(['ids' => $packageAppointments->keys()]);

            /** @var Appointment $appointment */
            foreach ($appointments->getItems() as $appointment) {
                $serviceId = $appointment->getServiceId()->getValue();

                /** @var CustomerBooking $customerBooking */
                foreach ($appointment->getBookings()->getItems() as $customerBooking) {
                    if ($customerBooking->getPackageCustomerService() &&
                        $packageCustomerServices->keyExists(
                            $customerBooking->getPackageCustomerService()->getId()->getValue()
                        )
                    ) {
                        /** @var PackageCustomerService $packageCustomerService */
                        $packageCustomerService = $packageCustomerServices->getItem(
                            $customerBooking->getPackageCustomerService()->getId()->getValue()
                        );

                        $packageId = $packageCustomerService->getPackageCustomer()->getPackageId()->getValue();

                        $id = $packageCustomerService->getId()->getValue();

                        $customerId = $customerBooking->getCustomerId()->getValue();

                        if (!empty($packageData[$customerId][$serviceId][$packageId][$id])) {
                            if ($packageData[$customerId][$serviceId][$packageId][$id]['available'] > 0) {
                                $packageData[$customerId][$serviceId][$packageId][$id]['available']--;
                            } else {
                                foreach ($packageData[$customerId][$serviceId][$packageId] as $pcsId => $value) {
                                    if ($value['available'] > 0) {
                                        $packageData[$customerId][$serviceId][$packageId][$pcsId]['available']--;

                                        $customerBooking->getPackageCustomerService()->setId(new Id($pcsId));

                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /** @var Appointment $appointment */
            foreach ($appointments->getItems() as $appointment) {
                if ($appointment->getBookings()->length() === 1) {
                    if (!$appointmentApplicationService->delete($appointment)) {
                        return false;
                    }

                    $resultData['deletedAppointments'][] =
                        $appointmentApplicationService->removeBookingFromNonGroupAppointment(
                            $appointment,
                            $appointment->getBookings()->getItem($appointment->getBookings()->keys()[0])
                        );
                } else {
                    $removedBooking = null;

                    /** @var CustomerBooking $customerBooking */
                    foreach ($appointment->getBookings()->getItems() as $customerBooking) {
                        if ($customerBooking->getPackageCustomerService() &&
                            in_array(
                                $customerBooking->getPackageCustomerService()->getId()->getValue(),
                                $packageCustomerServices->keys()
                            )
                        ) {
                            if (!$bookingApplicationService->delete($customerBooking)) {
                                return false;
                            }

                            $removedBooking = $customerBooking;
                        }
                    }

                    if ($removedBooking) {
                        $result = $appointmentApplicationService->removeBookingFromGroupAppointment(
                            $appointment,
                            $removedBooking
                        );

                        foreach ($result['bookingsWithChangedStatus'] as &$booking) {
                            if ($booking['id'] === $removedBooking->getId()->getValue()) {
                                $booking['skipNotification'] = true;
                            }
                        }

                        $resultData['updatedAppointments'][] = $result;
                    }
                }
            }
        }

        /** @var Collection $payments */
        $payments = $paymentRepository->getByEntityId(
            $packageCustomer->getId()->getValue(),
            'packageCustomerId'
        );

        /** @var Payment $payment */
        foreach ($payments->getItems() as $payment) {
            if (!$paymentApplicationService->delete($payment)) {
                return false;
            }
        }

        if (!$packageCustomerServiceRepository->deleteByEntityId(
            $packageCustomer->getId()->getValue(),
            'packageCustomerId'
        ) ||
        !$packageCustomerRepository->delete($packageCustomer->getId()->getValue())
        ) {
            return false;
        }

        return $resultData;
    }

    /**
     *
     * @param Extra $extra
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function deleteExtra($extra)
    {
        /** @var ExtraRepository $extraRepository */
        $extraRepository = $this->container->get('domain.bookable.extra.repository');

        /** @var CustomerBookingExtraRepository $customerBookingExtraRepository */
        $customerBookingExtraRepository = $this->container->get('domain.booking.customerBookingExtra.repository');

        return
            $customerBookingExtraRepository->deleteByEntityId($extra->getId()->getValue(), 'extraId') &&
            $extraRepository->delete($extra->getId()->getValue());
    }
}
