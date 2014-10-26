<?php
namespace Dino\HangmanBundle\Model;

class GuessCommand extends HangmanCommand
{
    public $char;
    
    public function __construct($gameId, $char)
    {
        parent::__construct($gameId);
        $this->char = $char;
    }
}