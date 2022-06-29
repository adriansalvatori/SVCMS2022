<?php

namespace AmeliaBooking\Application\Commands\Google;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Domain\Factory\Google\GoogleCalendarFactory;
use AmeliaBooking\Infrastructure\Services\Google\GoogleCalendarService;
use AmeliaBooking\Infrastructure\Repository\Google\GoogleCalendarRepository;

/**
 * Class FetchAccessTokenWithAuthCodeCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Google
 */
class FetchAccessTokenWithAuthCodeCommandHandler extends CommandHandler
{
    /** @var array */
    public $mandatoryFields = [
        'authCode',
        'userId'
    ];

    /**
     * @param FetchAccessTokenWithAuthCodeCommand $command
     *
     * @return CommandResult
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function handle(FetchAccessTokenWithAuthCodeCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var GoogleCalendarRepository $googleCalendarRepository */
        $googleCalendarRepository = $this->container->get('domain.google.calendar.repository');

        /** @var GoogleCalendarService $googleCalService */
        $googleCalService = $this->container->get('infrastructure.google.calendar.service');

        $accessToken = $googleCalService->fetchAccessTokenWithAuthCode(
            $command->getField('authCode'),
            $command->getField('isBackend')
                ? AMELIA_SITE_URL . '/wp-admin/admin.php?page=wpamelia-employees'
                : $command->getField('redirectUri')
        );

        $googleCalendar = GoogleCalendarFactory::create(['token' => $accessToken]);

        $googleCalendarRepository->beginTransaction();

        if (!$googleCalendarRepository->add($googleCalendar, $command->getField('userId'))) {
            $googleCalendarRepository->rollback();
        }

        $googleCalendarRepository->commit();

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully fetched access token');

        return $result;
    }
}
