<?php
namespace Dino\HangmanBundle\Model;

class UpdateStatusCommand extends HangmanCommand
{
    public $status;
    
    public function __construct($gameId, $status)
    {
        parent::__construct($gameId);
        $this->status = $status;
    }
}