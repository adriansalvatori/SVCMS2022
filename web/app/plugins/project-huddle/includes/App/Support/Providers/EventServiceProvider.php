<?php

namespace PH\Support\Providers;

use PH\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register all events on construct
     */
    public function __construct()
    {
        $this->registerEvents();
    }

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }

    /**
     * When to add action
     *
     * @return void
     */
    public function when()
    {
        return true;
    }

    /**
     * Register events
     *
     * @return void
     */
    public function registerEvents()
    {
        if (empty($this->listens())) {
            return false;
        }

        foreach ($this->listens() as $event => $listeners) {
            if (empty($listeners)) {
                continue;
            }

            foreach ($listeners as $options) {
                if (is_string($options)) {
                    $opt['class'] = $options;
                } else {
                    $opt = $options;
                }

                extract(wp_parse_args($opt, [
                    'class' => null,
                    'priority' => 10,
                    'args' => 1
                ]));


                // if (!class_exists($class)) {
                //     continue;
                // }

                $listenerClass = new $class();

                // condition to run
                if (method_exists($class, 'when')) {
                    if (!$listenerClass->when()) {
                        continue;
                    }
                }

                // if we have a handler
                if ($class && method_exists($class, 'handle')) {
                    add_action($event, array($listenerClass, 'handle'), $priority, $args);
                }
            }
        }
    }
}
