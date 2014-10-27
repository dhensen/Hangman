<?php

namespace Dino\HangmanBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Dino\HangmanBundle\Entity\Hangman;

class DefaultControllerTest extends WebTestCase
{
    public function testListGames()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/games');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertContentTypeIsJson($client);
    }
    
    public function testStartNewGame()
    {
        $client = static::createClient();
        
        $crawler = $client->request('POST', '/games');
        
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertContentTypeIsJson($client);
    }
    
    public function testGameStatus()
    {
        $client = static::createClient();
        
        $crawler = $client->request('GET', '/games/1');
        
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertContentTypeIsJson($client);
    }
    
    public function testGuessCharacter()
    {
        $client = static::createClient();
        
        $crawler = $client->request('POST', '/games/1', array('char' => 'a'));
        
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertContentTypeIsJson($client);
    }
    
    protected function assertContentTypeIsJson($client)
    {
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }
    
    public function testFailStatus()
    {
        $client = static::createClient();
        
        $this->mockWordSupplier($client, 'thisworddoesnotcontaintheletter'); // thisworddoesnotcontaintheletter: q
        
        // TODO need to be able to inject the word for this test, moch the RandonWordSupplier service
        $client->request('POST', '/games');
        
        $jsonDecode = new JsonDecode();
        $data = $jsonDecode->decode($client->getResponse()->getContent(), null);
        $gameId = $data->id;
        
        for ($i = 0; $i < 10; $i++) {
            $client->request('POST', '/games/'.$gameId, array('char' => 'q'));  // q is not in the word
        }
        
        $busyData = $jsonDecode->decode($client->getResponse()->getContent(), null);
        $this->assertEquals(Hangman::STATUS_BUSY, $busyData->status);
        
        $client->request('POST', '/games/'.$gameId, array('char' => 'q')); // after last try it must fail
        
        $client->request('GET', '/games/'.$gameId);
        
        $failData = $jsonDecode->decode($client->getResponse()->getContent(), null);
        $this->assertEquals(Hangman::STATUS_FAIL, $failData->status);
        
    }
    
    public function testSuccessStatus()
    {
        $client = static::createClient();
        
        $this->mockWordSupplier($client, 'awesome');
        
        // TODO need to be able to inject the word for this test, moch the RandonWordSupplier service
        $client->request('POST', '/games');
        
        $jsonDecode = new JsonDecode();
        $data = $jsonDecode->decode($client->getResponse()->getContent(), null);
        $gameId = $data->id;
        
        $client->request('POST', '/games/'.$gameId, array('char' => 'a'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'w'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'e'));
        $client->request('POST', '/games/'.$gameId, array('char' => 's'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'o'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'm')); // all letters in the word are guessed
        
        $client->request('GET', '/games/'.$gameId);
        
        $successData = $jsonDecode->decode($client->getResponse()->getContent(), null);
        $this->assertEquals(Hangman::STATUS_SUCCESS, $successData->status);
    }
    
    private function mockWordSupplier($client, $fixedWordValue)
    {
        // mock dino_hangman.random_word service
        $wordService = $this->getMockBuilder('Dino\HangmanBundle\Service\RandomWordSupplier')
        ->disableOriginalConstructor()
        ->getMock();
        $wordService->expects($this->once())
        ->method('getRandom')
        ->will($this->returnValue($fixedWordValue));
        $client->getContainer()->set('dino_hangman.random_word', $wordService);
    }
    
    public function testCorrectLettersDontDecrementTriesLeft()
    {
        $client = static::createClient();
    
        $this->mockWordSupplier($client, 'awesome');
    
        // TODO need to be able to inject the word for this test, moch the RandonWordSupplier service
        $client->request('POST', '/games');
    
        $jsonDecode = new JsonDecode();
        $data = $jsonDecode->decode($client->getResponse()->getContent(), null);
        $gameId = $data->id;
    
        $client->request('POST', '/games/'.$gameId, array('char' => 'a'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'a'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'a'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'a'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'a'));
        $client->request('POST', '/games/'.$gameId, array('char' => 'a')); // guessed a 6 times
    
        $client->request('GET', '/games/'.$gameId);
    
        $guessData = $jsonDecode->decode($client->getResponse()->getContent(), null);
        
        // correct letter should not decrement tries left
        $this->assertEquals(11, $guessData->tries_left);
        $this->assertEquals(Hangman::STATUS_BUSY, $guessData->status);
    }
}
