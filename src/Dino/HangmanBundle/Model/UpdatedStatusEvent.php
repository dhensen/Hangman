<?php
namespace Dino\HangmanBundle\Model;

class UpdatedStatusEvent extends HangmanEvent
{
    public $status;
    
    public function __construct($gameId, $status)
    {
        parent::__construct($gameId);
        $this->status = $status;
    }
}