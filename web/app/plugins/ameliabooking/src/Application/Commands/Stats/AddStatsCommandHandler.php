<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Commands\Stats;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Stats\StatsService;

/**
 * Class AddStatsCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Stats
 */
class AddStatsCommandHandler extends CommandHandler
{

    /**
     * @var array
     */
    public $mandatoryFields = [
        'providerId',
        'serviceId'
    ];

    /**
     * @param AddStatsCommand $command
     *
     * @return CommandResult
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     */
    public function handle(AddStatsCommand $command)
    {
        $result = new CommandResult();

        /** @var StatsService $statsAS */
        $statsAS = $this->container->get('application.stats.service');

        $this->checkMandatoryFields($command);

        $statsAS->addEmployeesViewsStats($command->getField('providerId'));

        $statsAS->addServicesViewsStats($command->getField('serviceId'));

        $statsAS->addLocationsViewsStats($command->getField('locationId'));

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully added stats.');

        return $result;
    }
}
