<?php
namespace Dino\HangmanBundle\Model;

abstract class HangmanEvent
{
    public $gameId;
    
    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }
}