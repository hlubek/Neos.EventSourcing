<?php
namespace Neos\Cqrs\EventStore;

/*
 * This file is part of the Neos.EventStore package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Cqrs\Exception;
use TYPO3\Flow\Annotations as Flow;

class EventTypesFilter implements EventStreamFilterInterface
{
    /**
     * @var string[]
     */
    private $eventTypes;

    public function __construct(array $eventTypes)
    {
        if ($eventTypes === []) {
            throw new Exception('No type filter provided', 1478299912);
        }
        $this->eventTypes = $eventTypes;
    }

    public function getStreamName(): string
    {
        return '';
    }

    public function hasStreamName(): bool
    {
        return false;
    }

    public function getStreamNamePrefix(): string
    {
        return '';
    }

    public function hasStreamNamePrefix(): bool
    {
        return false;
    }

    /**
     * @return string[] in the format ['Bounded.Context:SomeEvent', 'Bounded.Context:SomeOtherEvent', ...]
     */
    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }

    public function hasEventTypes(): bool
    {
        return true;
    }
}
