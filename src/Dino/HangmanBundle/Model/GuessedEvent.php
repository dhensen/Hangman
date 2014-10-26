<?php
namespace Dino\HangmanBundle\Model;

class GuessedEvent extends HangmanEvent
{
    public $char;
    
    public function __construct($gameId, $char)
    {
        parent::__construct($gameId);
        $this->char = $char;
    }
}