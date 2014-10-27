<?php
namespace Dino\HangmanBundle\Entity;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Dino\HangmanBundle\Model\StartedEvent;
use Dino\HangmanBundle\Model\GuessedEvent;
use Dino\HangmanBundle\Entity\Word;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="hangman")
 */
class Hangman extends EventSourcedAggregateRoot
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $gameId;
    
    /**
     * @ORM\OneToOne(targetEntity="Word")
     * @ORM\JoinColumn(name="word_id", referencedColumnName="id")
     * @var Word
     */
    private $word;
    
    /**
     * @ORM\Column(type="string", length=10)
     * @var string
     */
    private $status;
    
    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
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
        // if success of failure is already acomplished, bail early
        if ($this->status == self::STATUS_SUCCESS || $this->status == self::STATUS_FAIL) {
            return;
        }
        
        $unmaskedCount = $this->word->unmask($event->char);
        
        // a wrong guess: zero characters are unmasked
        if ($unmaskedCount == 0) {
            $this->tries_left--;
            
            if ($this->tries_left == 0) {
                $this->status = self::STATUS_FAIL;
            }
        } elseif ($unmaskedCount > 0 && $this->word->isCompletelyUnmasked()) {
            $this->status = self::STATUS_SUCCESS;
        } // but guessing a correct letter doesn't decrement the amount of tries left
    }
    
    public function getTriesLeft()
    {
        return $this->tries_left;
    }

    /**
     * Get gameId
     *
     * @return integer
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Hangman
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set tries_left
     *
     * @param integer $triesLeft
     * @return Hangman
     */
    public function setTriesLeft($triesLeft)
    {
        $this->tries_left = $triesLeft;

        return $this;
    }

    /**
     * Set word
     *
     * @param \Dino\HangmanBundle\Entity\Word $word
     * @return Hangman
     */
    public function setWord(\Dino\HangmanBundle\Entity\Word $word = null)
    {
        $this->word = $word;

        return $this;
    }

    /**
     * Get word
     *
     * @return \Dino\HangmanBundle\Entity\Word
     */
    public function getWord()
    {
        return $this->word;
    }
    
    public function toArray()
    {
        return array(
            'id'         => $this->gameId,
            'word'       => $this->word->getMaskedValue(),
            'tries_left' => $this->tries_left,
            'status'     => $this->status
        );
    }
}
