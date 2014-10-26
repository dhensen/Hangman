<?php
namespace Dino\HangmanBundle\Model;

class Word
{
    private $value;
    
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
}