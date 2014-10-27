<?php

namespace Dino\HangmanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Dino\HangmanBundle\Entity\Hangman;
use Dino\HangmanBundle\Entity\Word;
use Dino\HangmanBundle\Model\GuessCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Dino\HangmanBundle\Model\HangmanCommandHandler;
use Dino\HangmanBundle\Model\HangmanRepository;
use Broadway\EventStore\InMemoryEventStore;
use Dino\HangmanBundle\Model\StartCommand;

class DefaultController extends Controller
{
    private $hangman;
    
    /**
     * List all games.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listGamesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $what = $em->getRepository('DinoHangmanBundle:Hangman')->findAll();
        
        $games = array();
        foreach ($what as $hangman) {
            $games[] = $hangman->toArray();
        }
        
        return new JsonResponse($games);
    }
    
    /**
     * Start a new game.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function startNewGameAction()
    {
        $wordSupplier = $this->get('dino_hangman.random_word');
        $word = new Word($wordSupplier->getRandom());
        
        $commandBus = $this->get('broadway.command_handling.command_bus');
        /* @var $commandBus \Broadway\CommandHandling\CommandBusInterface */
        $repository = new HangmanRepository(new InMemoryEventStore(), $this->get('broadway.event_handling.event_bus'));
        $repository->setObjectManager($this->getDoctrine()->getManager());
        $commandHandler = new HangmanCommandHandler($repository);
        // inject controller to get the hangman instance afterwards is nasty-ish
        $commandHandler->setController($this);
        $commandBus->subscribe($commandHandler);
        $commandBus->dispatch(new StartCommand(null, $word));
        
        return new JsonResponse($this->hangman->toArray());
    }
    
    public function setHangman(Hangman $hangman)
    {
        $this->hangman = $hangman;
    }
    
    /**
     * Request hangman game status for given id.
     *
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function gameStatusAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $hangman = $em->getRepository('DinoHangmanBundle:Hangman')->find($id);
        
        return new JsonResponse($hangman->toArray());
    }
    
    /**
     * Guess a character.
     *
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function guessCharacterAction($id)
    {
        $char = $this->getRequest()->get('char', null);
        
        if (!Word::isValid($char)) {
            return new JsonResponse(['error_message' => 'invalid character'], 400);
        }
        
        $commandBus = $this->get('broadway.command_handling.command_bus');
        /* @var $commandBus \Broadway\CommandHandling\CommandBusInterface */
        $repository = new HangmanRepository(new InMemoryEventStore(), $this->get('broadway.event_handling.event_bus'));
        $repository->setObjectManager($this->getDoctrine()->getManager());
        $commandHandler = new HangmanCommandHandler($repository);
        $commandHandler->setController($this);
        $commandBus->subscribe($commandHandler);
        $commandBus->dispatch(new GuessCommand($id, $char));
        
        return new JsonResponse($this->hangman->toArray());
    }
}
