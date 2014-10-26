<?php
namespace Dino\HangmanBundle\Tests\Model;

use Dino\HangmanBundle\Model\Word;
class WordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider unmaskedCountDataProvider
     *
     * @param string $value
     * @param string $char
     * @param integer $expectedCount
     */
    public function testUnmaskedCount($value, $char, $expectedCount)
    {
        $word = new Word($value);
        $unmaskCount = $word->unmask($char);
        $this->assertEquals($expectedCount, $unmaskCount);
    }
    
    public function unmaskedCountDataProvider()
    {
        return array(
            array('foobar', 'f', 1),
            array('foobar', 'o', 2),
            array('foobar', 'b', 1),
            array('foobar', 'a', 1),
            array('foobar', 'r', 1),
        );
    }
    
    /**
     * @dataProvider isValidDataProvider
     *
     * @param string $value
     * @param boolean $expected
     */
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, Word::isValid($value));
    }
    
    public function isValidDataProvider()
    {
        return array(
            array('bonus', true),
            array('1', false),
            array('42', false),
            array('bonus1', false),
            array('bon1us', false),
        );
    }
    
    /**
     * @dataProvider completelyUnmaskedDataProvider
     *
     * @param string $value
     * @param array $chars
     * @param boolean $expectedCompletelyUnmasked
     */
    public function testCompletelyUnmasked($value, $chars, $expectedCompletelyUnmasked, $expectedMaskedValue)
    {
        $word = new Word($value);
        
        foreach ($chars as $char) {
            $word->unmask($char);
        }
        
        $this->assertEquals($expectedMaskedValue, $word->getMaskedValue());
        $this->assertEquals($expectedCompletelyUnmasked, $word->isCompletelyUnmasked());
    }
    
    public function completelyUnmaskedDataProvider()
    {
        return array(
            array('foobar', array('f', 'o', 'b', 'a', 'r'), true, 'foobar'),
            array('foobar', array('f', 'o', 'b', 'a'), false, 'fooba.'),
            array('foobar', array('f', 'o', 'b', 'a'), false, 'fooba.'),
            array('awesomenes', array('f', 'o', 'b', 'a'), false, 'a...o.....'),
        );
    }
    
    /**
     * @dataProvider invalidValueDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid word value given
     *
     * @param string $invalid_value
     */
    public function testInvalidValue($invalid_value)
    {
        new Word($invalid_value);
    }
    
    public function invalidValueDataProvider()
    {
        return array(
            array('42'),
            array('$ugar'),
            array('€uro'),
            array('@@penstaart'),
            array('l%l'),
            array('^$#&^%^gibberish~'),
            array(' '),
            array('a '),
            array('a a'),
        );
    }
}