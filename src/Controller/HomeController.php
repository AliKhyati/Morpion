<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(GameRepository $gameRepository): Response
    {

        $game = $gameRepository->findAll();
        $checkX = $gameRepository->findBy(['type' => 'croix'],['posX' => 'ASC']);
        $checkY = $gameRepository->findBy(['type' => 'croix'],['posY' => 'ASC']);
        $checkBotX = $gameRepository->findBy(['type' => 'rond'],['posX' => 'ASC']);
        $checkBotY = $gameRepository->findBy(['type' => 'rond'],['posY' => 'ASC']);

        if (count($checkX) >= 3) {
           for ($i = 2; $i < count($checkX)+2; $i++) {
               if($i < count($checkX)) {
                   if ($checkX[$i]->getPosX() === $checkX[$i - 1]->getPosX() && $checkX[$i]->getPosX() === $checkX[$i - 2]->getPosX()) {
                       return $this->redirectToRoute('winner');
                   }elseif ($checkY[$i]->getPosY() === $checkY[$i - 1]->getPosY() && $checkY[$i]->getPosY() === $checkY[$i - 2]->getPosY()) {
                       return $this->redirectToRoute('winner');
                   }elseif ($checkX[$i]->getPosX() === $checkX[$i]->getPosY() && $checkX[$i-1]->getPosX() === $checkX[$i-1]->getPosY() &&$checkX[$i-2]->getPosX() === $checkX[$i-2]->getPosY()){
                       return $this->redirectToRoute('winner');
                   }elseif ($checkX[$i]->getPosX() === $checkX[$i-2]->getPosY() && $checkX[$i-1]->getPosX() === $checkX[$i-1]->getPosY() && $checkX[$i-2]->getPosX() === $checkX[$i]->getPosY() && $checkX[$i]->getPosX() === 3 && $checkX[$i]->getPosY() === 1){
                       return $this->redirectToRoute('winner');
                   }
                   if(count($game) <= 8) {
                       if ($checkBotX[$i]->getPosX() === $checkBotX[$i - 1]->getPosX() && $checkBotX[$i]->getPosX() === $checkBotX[$i - 2]->getPosX()) {
                           return $this->redirectToRoute('loser');
                       } elseif ($checkBotY[$i]->getPosY() === $checkBotY[$i - 1]->getPosY() && $checkBotY[$i]->getPosY() === $checkBotY[$i - 2]->getPosY()) {
                           return $this->redirectToRoute('loser');
                       } elseif ($checkBotX[$i]->getPosX() === $checkBotX[$i]->getPosY() && $checkBotX[$i - 1]->getPosX() === $checkBotX[$i - 1]->getPosY() && $checkBotX[$i-2]->getPosX() === $checkBotX[$i - 2]->getPosY()) {
                           return $this->redirectToRoute('loser');
                       } elseif ($checkBotX[$i]->getPosX() === $checkBotX[$i - 2]->getPosY() && $checkBotX[$i - 1]->getPosX() === $checkBotX[$i-1]->getPosY() && $checkBotX[$i-2]->getPosX() === $checkBotX[$i]->getPosY()) {
                           return $this->redirectToRoute('loser');
                       }
                   }else{
                       return $this->redirectToRoute('draw');

                   }
               }
           }
       }

        return $this->render('home/index.html.twig', [
            'game' => $game,
        ]);
    }
    /**
     * @Route("/winner", name="winner")
     */
    public function winner(ManagerRegistry $doctrine): Response
    {
        if (!is_null($this->getUser())){
            $entityManager = $doctrine->getManager();
            $test = $this->getUser();
            $test->setScore($test->getScore()+rand(1, 10));
            $entityManager->persist($test);
            $entityManager->flush();
        }


        return $this->render('home/winner.html.twig');

    }
    /**
     * @Route("/draw", name="draw")
     */
    public function draw(): Response
    {


        return $this->render('home/draw.html.twig');

    }
    /**
     * @Route("/classement", name="classement")
     */
    public function classement(UserRepository $userRepository): Response
    {
        $users = $userRepository->findBy([], ['score' => 'DESC']);
        $scoreBoard = [];
        $count = 0;
        foreach($users as $user){
            $name = explode('@', $user->getEmail());
            $scoreBoard[$count][] = $name[0];
            $scoreBoard[$count][] = $user->getScore();
            $count++;
        }


        return $this->render('home/classement.html.twig', [
            'scoreBoard' => $scoreBoard,
        ]);

    }
    /**
     * @Route("/loser", name="loser")
     */
    public function loser(ManagerRegistry $doctrine): Response
    {
        if (!is_null($this->getUser())) {
            $entityManager = $doctrine->getManager();
            $test = $this->getUser();
            $test->setScore($test->getScore() - rand(1, 10));
            $entityManager->persist($test);
            $entityManager->flush();
        }

        return $this->render('home/loser.html.twig');

    }

    /**
     * @Route("/start", name="start")
     */
    public function start(GameRepository $gameRepository, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $rounds = $gameRepository->findAll();
        foreach($rounds as $round){
            $entityManager->remove($round);
        }
        $entityManager->flush();

        return $this->redirectToRoute('home');

    }
    /**
     * @Route("play/{x}/{y}", name="play", requirements={"x"="\d+", "y"="\d+"})
     */
    public function round(int $x, int $y, ManagerRegistry $doctrine, GameRepository $gameRepository): Response
    {

        $entityManager = $doctrine->getManager();
        $playerRound = new Game();
        $rounds = $gameRepository->findBy([
            'posX' => $x,
            'posY' => $y,
        ]);
        $rounds2 = $gameRepository->findAll();

        if (empty($rounds) && count($rounds2) <= 8) {
            $playerRound->setPosX($x);
            $playerRound->setPosY($y);
            $playerRound->setType('croix');
            $entityManager->persist($playerRound);
            if (count($rounds2) < 8) {
                $array = [];
                foreach ($rounds2 as $round) {
                    $array[] = $round->getPosX() . $round->getPosY();
                }
                $array[] = $playerRound->getPosX() . $playerRound->getPosY();
                $positions = ['11', '12', '13', '21', '22', '23', '31', '32', '33'];
                $possibilities = [];
                for ($i = 0; $i < count($positions); $i++) {
                    if (!in_array($positions[$i], $array)) {
                        $possibilities[] = $positions[$i];
                    }
                }
                $test = $possibilities;
                $test[] = $playerRound->getPosX() . $playerRound->getPosY();
                sort($test);

                $botRound = new Game();

                if (rand(0, 10) > 5) {
                    if (isset($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) + 3])) {
                        $nextOne = str_split($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) + 3]);
                        $botRound->setPosX($nextOne[0]);
                        $botRound->setPosY($nextOne[1]);
                    } elseif (isset($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) - 1])) {
                        $previousOne = str_split($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) - 1]);
                        $botRound->setPosX($previousOne[0]);
                        $botRound->setPosY($previousOne[1]);
                    }
                }else{
                    if (isset($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) - 1])) {
                        $previousOne = str_split($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) - 1]);
                        $botRound->setPosX($previousOne[0]);
                        $botRound->setPosY($previousOne[1]);
                    }elseif (isset($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) + 3])) {
                        $nextOne = str_split($test[array_search($playerRound->getPosX() . $playerRound->getPosY(), $test) + 3]);
                        $botRound->setPosX($nextOne[0]);
                        $botRound->setPosY($nextOne[1]);
                    }
                }

                $botRound->setType('rond');
                $entityManager->persist($botRound);
            }
        }
        $entityManager->flush();




        return $this->redirectToRoute('home');


    }
}
