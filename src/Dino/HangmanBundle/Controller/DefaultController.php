<?php

namespace Dino\HangmanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function viewGamesAction()
    {
        return new Response('overview of all games');
    }
    
    public function startNewGameAction()
    {
        return new Response('start a new game');
    }
    
    public function gameStatusAction($id)
    {
        $commandBus = $this->get('broadway.command_handling.command_bus');
        
        var_dump($commandBus);
        
        return new Response('return json game status ' . $id);
    }
    
    public function guessCharacter($id)
    {
        return new Response('guess a letter ' . $id);
    }
}
