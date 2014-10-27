<?php

namespace Dino\HangmanBundle\Tests\Model;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventHandling\EventBusInterface;
use Dino\HangmanBundle\Model\HangmanRepository;
use Dino\HangmanBundle\Model\HangmanCommandHandler;
use Dino\HangmanBundle\Model\StartCommand;
use Dino\HangmanBundle\Entity\Word;
use Dino\HangmanBundle\Model\StartedEvent;
use Dino\HangmanBundle\Model\GuessCommand;
use Dino\HangmanBundle\Model\GuessedEvent;
use Dino\HangmanBundle\Entity\Hangman;

class HangmanCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    private $repository;
    
    public function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus)
    {
        $this->repository = new HangmanRepository($eventStore, $eventBus);
        
        return new HangmanCommandHandler($this->repository);
    }
    
    public function testStartNewGame()
    {
        $id = 1;
        
        $word = new Word('test');
        
        $this->scenario
            ->withAggregateId($id)
            ->given([])
            ->when(new StartCommand($id, $word))
            ->then([new StartedEvent($id, $word)]);
        
        $this->assertEquals(Hangman::STATUS_BUSY, $this->repository->load($id)->getStatus());
    }
    
    public function testGuessChar()
    {
        $id = 2;
        
        $word = new Word('test');
        
        $this->scenario
            ->withAggregateId($id)
            ->given([new StartedEvent($id, $word)])
            ->when(new GuessCommand($id, 'e'))
            ->then([new GuessedEvent($id, 'e')]);
    }
    
    public function testSuccesStatus()
    {
        $id = 3;
        
        $word = new Word('test');
        
        $this->scenario
            ->withAggregateId($id)
            ->given([
                new StartedEvent($id, $word),
                new GuessedEvent($id, 't'),
                new GuessedEvent($id, 'e'),
            ])
            ->when(new GuessCommand($id, 's'))
            ->then([new GuessedEvent($id, 's')]);
            
        $this->assertEquals(Hangman::STATUS_SUCCESS, $this->repository->load($id)->getStatus());
    }
    
    public function testDecrementTriesLeft()
    {
        $id = 4;
    
        $word = new Word('test');
    
        $this->scenario
            ->withAggregateId($id)
            ->given([new StartedEvent($id, $word)])
            ->when(new GuessCommand($id, 'x'))
            ->then([new GuessedEvent($id, 'x')]);
        
        $this->assertEquals(10, $this->repository->load($id)->getTriesLeft());
    }
    
    public function testFailedStatus()
    {
        $id = 5;
        
        $word = new Word('test');
        
        $this->scenario
            ->withAggregateId($id)
            ->given([
                new StartedEvent($id, $word),
                new GuessedEvent($id, 'a'), // tries left: 10
                new GuessedEvent($id, 'b'), // tries left:  9
                new GuessedEvent($id, 'c'), // tries left:  8
                new GuessedEvent($id, 'd'), // tries left:  7
                new GuessedEvent($id, 'e'), // no decrement
                new GuessedEvent($id, 'f'), // tries left:  6
                new GuessedEvent($id, 'g'), // tries left:  5
                new GuessedEvent($id, 'h'), // tries left:  4
                new GuessedEvent($id, 'i'), // tries left:  3
                new GuessedEvent($id, 'j'), // tries left:  2
                new GuessedEvent($id, 'k'), // tries left:  1
            ])
            ->when(new GuessCommand($id, 'l')) // tries left: 0 => fail
            ->then([new GuessedEvent($id, 'l')]);
            
        $this->assertEquals(0, $this->repository->load($id)->getTriesLeft());
        $this->assertEquals(Hangman::STATUS_FAIL, $this->repository->load($id)->getStatus());
    }
}