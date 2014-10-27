<?php
namespace Dino\HangmanBundle\Model;

use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Dino\HangmanBundle\Entity\Hangman;
use Broadway\Domain\AggregateRoot;

class HangmanRepository extends EventSourcingRepository
{
    /**
     *
     * @var ObjectManager
     */
    private $objectManager;
    
    public function __construct(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        parent::__construct($eventStore, $eventBus, 'Dino\HangmanBundle\Entity\Hangman', new PublicConstructorAggregateFactory());
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    
    public function load($id)
    {
        if ($this->objectManager instanceof ObjectManager) {
            $hangman = $this->objectManager->find('DinoHangmanBundle:Hangman', $id);
            return $hangman;
        } else {
            return parent::load($id);
        }
    }
    
    public function add(AggregateRoot $aggregate)
    {
        parent::add($aggregate);
        
        if ($aggregate instanceof Hangman && $this->objectManager instanceof ObjectManager) {
            $this->objectManager->persist($aggregate);
            $this->objectManager->persist($aggregate->getWord());
            $this->objectManager->flush();
        }
    }
}