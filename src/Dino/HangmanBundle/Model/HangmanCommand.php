<?php
namespace Dino\HangmanBundle\Model;

abstract class HangmanCommand
{
    public $gameId;
    
    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }
}