<?php
namespace Dino\HangmanBundle\Service;

class RandomWordSupplier
{
    private $filename;
    private $words;
    
    public function __construct($filename)
    {
        $this->filename = $filename;
    }
    
    public function getRandom()
    {
        if (is_null($this->words)) {
            $this->words = file(__DIR__ . '/../Resources/words/'. $this->filename);
        }
        
        return trim($this->words[rand(0, count($this->words) - 1)]);
    }
}