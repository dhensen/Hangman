<?php
namespace Dino\HangmanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="word")
 */
class Word
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=100)
     * @var string
     */
    private $value;
    
    /**
     * @ORM\Column(type="string", length=100)
     * @var string
     */
    private $masked_value;
    
    const MASK_CHARACTER = '.';
    
    public function __construct($value)
    {
        if (static::isValid($value)) {
            $this->value = $value;
            $this->masked_value = str_repeat(self::MASK_CHARACTER, strlen($value));
        } else {
            throw new \InvalidArgumentException('Invalid word value given');
        }
    }
    
    /**
     *
     * @return string
     */
    public function getMaskedValue()
    {
        return $this->masked_value;
    }
    
    /**
     * Returns true if the value is a valid word value or false otherwise.
     *
     * @param string $value
     * @return boolean
     */
    public static function isValid($value)
    {
        return is_string($value) && preg_match('/^[a-z]+$/', $value) === 1;
    }
    
    /**
     * Unmasks all occurances of the given character in the masked_value and returns the amount of occurances.
     *
     * @param string $comparisonCharacter
     * @return number
     */
    public function unmask($comparisonCharacter)
    {
        if (strlen($comparisonCharacter) !== 1 && self::isValid($comparisonCharacter)) {
            throw new \InvalidArgumentException('Only a single character is allowed');
        }
        
        $unmaskedCount = 0;
        
        $value_length = strlen($this->value);
        //$this->value as $k => $occuringCharacter
        for ($i = 0; $i < $value_length; $i++) {
            
            // when comparisonCharacter is occurring in original word value
            if ($this->value[$i] == $comparisonCharacter) {
                // replace the mask character with the original word value character
                $this->masked_value[$i] = $this->value[$i];
                
                $unmaskedCount++;
            }
        }
        
        return $unmaskedCount;
    }
    
    /**
     * @return boolean
     */
    public function isCompletelyUnmasked()
    {
        // return true if the mask character can not be found in the masked value
        return strpos($this->masked_value, self::MASK_CHARACTER) === false;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Word
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set masked_value
     *
     * @param string $maskedValue
     * @return Word
     */
    public function setMaskedValue($maskedValue)
    {
        $this->masked_value = $maskedValue;

        return $this;
    }
}
