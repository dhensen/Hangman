<?php
namespace Dino\HangmanBundle\Model;

use Dino\HangmanBundle\Entity\Word;

class StartCommand extends HangmanCommand
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