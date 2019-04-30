<?php

/**
 * @author Mygento Team
 * @copyright 2014-2019 Mygento (https://www.mygento.ru)
 * @package Mygento_Base
 */

namespace Mygento\Base\Model\Logger;

class Database extends \Monolog\Handler\AbstractProcessingHandler
{
    /**
     * @var \Mygento\Base\Api\EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var \Mygento\Base\Api\Data\EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @param \Mygento\Base\Api\EventRepositoryInterface $eventRepository
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Mygento\Base\Api\Data\EventInterfaceFactory $eventFactory
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(
        \Mygento\Base\Api\EventRepositoryInterface $eventRepository,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Mygento\Base\Api\Data\EventInterfaceFactory $eventFactory,
        $level = \Monolog\Logger::DEBUG,
        $bubble = true
    ) {
        parent::__construct($level, $bubble);
        $this->eventRepository = $eventRepository;
        $this->jsonEncoder = $jsonEncoder;
        $this->eventFactory = $eventFactory;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param array $record
     * @return void
     */
    protected function write(array $record)
    {
        $event = $this->eventFactory->create();
        $event->setInstance(gethostname());
        $event->setLevel($record['level']);
        $event->setChannel($record['channel']);
        $event->setMessage($record['message']);

        //serialize
        $event->setContext($this->serialize($record['context']));
        $event->setExtra($this->serialize($record['extra']));

        $this->eventRepository->save($event);
    }

    /**
     * Serialize field
     *
     * @param mixed $field
     * @return bool|string|null
     */
    private function serialize($field)
    {
        if (empty($field)) {
            return null;
        }

        return $this->jsonEncoder->encode($field);
    }
}
