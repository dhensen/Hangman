<?php
namespace Dino\HangmanBundle\Model;

class StartedEvent extends HangmanEvent
{
    /**
     *
     * @var Word
     */
    public $word;
    
    public function __construct($gameId, Word $word)
    {
        parent::__construct($gameId);
        $this->word = $word;
    }
}