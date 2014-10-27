<?php
namespace Dino\HangmanBundle\Model;

use Broadway\CommandHandling\CommandHandler;
use Broadway\EventSourcing\EventSourcingRepository;
use Dino\HangmanBundle\Entity\Hangman;

class HangmanCommandHandler extends CommandHandler
{
    private $repository;
    private $controller;
    
    public function __construct(EventSourcingRepository $repository)
    {
        $this->repository = $repository;
    }
    
    protected function handleStartCommand(StartCommand $command)
    {
        // create new Hangman object
        $hangman = Hangman::start($command->gameId, $command->word);
        
        if (!is_null($this->controller)) {
            $this->controller->setHangman($hangman);
        }
        
        // add it to the event sourcing repository
        $this->repository->add($hangman);
    }
    
    protected function handleGuessCommand(GuessCommand $command)
    {
        // load the hangman object from repository by gameId
        $hangman = $this->repository->load($command->gameId);
        
        if (is_null($hangman)) {
            return;
        }
        
        $hangman->guess($command->char);
        
        if (!is_null($this->controller)) {
            $this->controller->setHangman($hangman);
        }
        
        // add it to the event sourcing repository
        $this->repository->add($hangman);
    }
    
    public function setController($controller)
    {
        $this->controller = $controller;
    }
}