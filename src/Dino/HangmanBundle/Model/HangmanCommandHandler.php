<?php
namespace Dino\HangmanBundle\Model;

use Broadway\CommandHandling\CommandHandler;
use Broadway\EventSourcing\EventSourcingRepository;

class HangmanCommandHandler extends CommandHandler
{
    private $repository;
    
    public function __construct(EventSourcingRepository $repository)
    {
        $this->repository = $repository;
    }
    
    protected function handleStartCommand(StartCommand $command)
    {
        // create new Hangman object
        $hangman = Hangman::start($command->gameId, $command->word);
        
        // add it to the event sourcing repository
        $this->repository->add($hangman);
    }
    
    protected function handleGuessCommand(GuessCommand $command)
    {
        // load the hangman object from repository by gameId
        $hangman = $this->repository->load($command->gameId);
        
        $hangman->guess($command->char);
        
        // add it to the event sourcing repository
        $this->repository->add($hangman);
    }
}