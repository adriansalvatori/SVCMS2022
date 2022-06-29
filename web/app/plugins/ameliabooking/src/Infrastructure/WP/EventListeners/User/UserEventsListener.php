<?php
/**
 * Handle WP part of user-related events
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\User;

use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\WP\EventListeners\User\Provider\ProviderAddedEventHandler;
use AmeliaBooking\Infrastructure\WP\EventListeners\User\Provider\ProviderUpdatedEventHandler;
use Interop\Container\Exception\ContainerException;
use League\Event\ListenerInterface;
use League\Event\EventInterface;

/**
 * Class UserEventsListener
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\User
 */
class UserEventsListener implements ListenerInterface
{
    /** @var Container */
    private $container;

    /**
     * AppointmentEventsListener constructor.
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Check if provided argument is the listener
     *
     * @param mixed $listener
     *
     * @return bool
     */
    public function isListener($listener)
    {
        return $listener === $this;
    }

    /**
     * Handle events
     *
     * @param EventInterface $event
     * @param mixed          $param
     *
     * @throws QueryExecutionException
     * @throws ContainerException
     */
    public function handle(EventInterface $event, $param = null)
    {
        // Handling the event
        switch ($event->getName()) {
            case 'user.added':
                UserAddedEventHandler::handle($param);
                break;
            case 'provider.updated':
                ProviderUpdatedEventHandler::handle($param, $this->container);
                break;
            case 'provider.added':
                ProviderAddedEventHandler::handle($param, $this->container);
                break;
        }
    }
}
