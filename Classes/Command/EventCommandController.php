<?php
namespace Neos\Cqrs\Command;

/*
 * This file is part of the Neos.EventStore.DatabaseStorageAdapter package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Cqrs\EventListener\EventListenerManager;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;
use TYPO3\Flow\Core\Booting\Scripts;

/**
 * CLI Command Controller for event related commands
 *
 * @Flow\Scope("singleton")
 */
class EventCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var EventListenerManager
     */
    protected $eventListenerManager;

    /**
     * @Flow\InjectConfiguration(package="TYPO3.Flow")
     * @var array
     */
    protected $flowSettings;

    /**
     * Forward new events to listeners
     *
     * This command allows you to play all relevant unseen events for all asynchronous event listeners.
     *
     * @param bool $verbose If specified, this command will display information about the events being applied
     * @param bool $quiet If specified, this command won't produce any output apart from errors
     * @return void
     * @see neos.cqrs:event:watch
     */
    public function catchUpCommand($verbose = false, $quiet = false)
    {
        $progressCallback = function ($listenerClassName, $eventType, $eventCount) use ($quiet, $verbose) {
            if (!$quiet) {
                if ($verbose) {
                    $this->outputLine('%s -> %s', [$listenerClassName, $eventType]);
                } else {
                    $this->output('*');
                }
            }
        };

        $eventsCount = $this->eventListenerManager->catchUp($progressCallback);
        if ($verbose) {
            $this->outputLine('Applied %d events.', [$eventsCount]);
        }
    }

    /**
     * Listen to new events
     *
     * This command watches the event store for new events and applies them to the respective asynchronous event
     * listeners. These include projectors, process managers and custom event listeners implementing the relevant
     * interfaces.
     *
     * @param int $lookupInterval Pause between lookups (in seconds)
     * @param bool $verbose If specified, this command will display information about the events being applied
     * @param bool $quiet If specified, this command won't produce any output apart from errors (useful for automation)
     * @return void
     * @see neos.cqrs:event:catchup
     */
    public function watchCommand($lookupInterval = 10, $verbose = false, $quiet = false)
    {
        if ($verbose) {
            $this->outputLine('Watching events ...');
        }

        do {
            $catchupCommandArguments = [
                'quiet' => $quiet ? 'yes' : 'no',
                'verbose' => $verbose ? 'yes' : 'no'
            ];
            Scripts::executeCommand('neos.cqrs:event:catchup', $this->flowSettings, !$quiet, $catchupCommandArguments);
            if (!$quiet) {
                if ($verbose) {
                    $this->outputLine();
                } else {
                    $this->output('.');
                }
            }
            sleep($lookupInterval);
        } while (true);
    }
}
