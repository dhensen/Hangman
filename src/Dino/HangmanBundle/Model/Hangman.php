<?php
namespace Dino\HangmanBundle\Model;

use Broadway\EventSourcing\EventSourcedAggregateRoot;

class Hangman extends EventSourcedAggregateRoot
{
    /**
     *
     * @var Word
     */
    private $gameId;
    private $word;
    private $status;
    private $tries_left;
    
    const INITIAL_TRIES_LEFT = 11;
    
    const STATUS_BUSY = 'busy';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';
    
    public function getAggregateRootId()
    {
        return $this->gameId;
    }
    
    public static function start($gameId, Word $word)
    {
        $hangman = new Hangman();

        // apply StartedEvent
        $hangman->apply(new StartedEvent($gameId, $word));
        
        return $hangman;
    }
    
    public function guess($char)
    {
        // apply GuessedEvent
        $this->apply(new GuessedEvent($this->gameId, $char));
    }
    
    protected function applyStartedEvent(StartedEvent $event)
    {
        $this->gameId = $event->gameId;
        $this->word = $event->word;
        $this->status = self::STATUS_BUSY;
        $this->tries_left = self::INITIAL_TRIES_LEFT;
    }
    
    protected function applyGuessedEvent(GuessedEvent $event)
    {
        // TODO perhaps check if the status is not already fail or success?
        $unmaskedCount = $this->word->unmask($event->char);
        
        // a wrong guess: zero characters are unmasked
        if ($unmaskedCount == 0) {
            $this->tries_left--;
            
            if ($this->tries_left == 0) {
                $this->apply(new UpdateStatusCommand($this->gameId, self::STATUS_FAIL));
            }
        } elseif ($unmaskedCount > 0 && $this->word->isCompletelyUnmasked()) {
            $this->apply(new UpdateStatusCommand($this->gameId, self::STATUS_SUCCESS));
        } // but guessing a correct letter doesn't decrement the amount of tries left
    }
    
    protected function applyUpdatedStatusEvent(UpdatedStatusEvent $event)
    {
        $this->status = $event->status;
    }
    
    public function getStatus()
    {
        return $this->status;
    }
}