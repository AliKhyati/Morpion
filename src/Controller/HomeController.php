<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(GameRepository $gameRepository, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $checkX = $gameRepository->findBy(['type' => 'croix'],['posX' => 'ASC']);
        $checkY = $gameRepository->findBy(['type' => 'croix'],['posY' => 'ASC']);
        $checkBotX = $gameRepository->findBy(['type' => 'rond'],['posX' => 'ASC']);
        $checkBotY = $gameRepository->findBy(['type' => 'rond'],['posY' => 'ASC']);

        if (count($checkX) >= 3) {
           for ($i = 2; $i < count($checkX)+2; $i++) {
               if($i < count($checkX)) {
                   if ($checkX[$i]->getPosX() === $checkX[$i - 1]->getPosX() && $checkX[$i]->getPosX() === $checkX[$i - 2]->getPosX()) {
                       dd('x');
                   }elseif ($checkY[$i]->getPosY() === $checkY[$i - 1]->getPosY() && $checkY[$i]->getPosY() === $checkY[$i - 2]->getPosY()) {
                       dd('y');
                   }elseif ($checkX[$i]->getPosX() === $checkX[$i]->getPosY() && $checkX[$i-1]->getPosX() === $checkX[$i-1]->getPosY() &&$checkX[$i-2]->getPosX() === $checkX[$i-2]->getPosY()){
                       dd('diag ez');
                   }elseif ($checkX[$i]->getPosX() === $checkX[$i-2]->getPosY() && $checkX[$i-1]->getPosX() === $checkX[$i-1]->getPosY() &&$checkX[$i-2]->getPosX() === $checkX[$i]->getPosY()){
                       dd('diag not ez');
                   }
                   if ($checkBotX[$i]->getPosX() === $checkBotX[$i - 1]->getPosX() && $checkBotX[$i]->getPosX() === $checkBotX[$i - 2]->getPosX()) {
                       dd('bot x');
                   }elseif ($checkBotY[$i]->getPosY() === $checkBotY[$i - 1]->getPosY() && $checkBotY[$i]->getPosY() === $checkBotY[$i - 2]->getPosY()) {
                       dd('bot y');
                   }elseif ($checkBotX[$i]->getPosX() === $checkBotX[$i]->getPosY() && $checkBotX[$i-1]->getPosX() === $checkBotX[$i-1]->getPosY() &&$checkBotX[$i-2]->getPosX() === $checkBotX[$i-2]->getPosY()){
                       dd('bot diag ez');
                   }elseif ($checkBotX[$i]->getPosX() === $checkBotX[$i-2]->getPosY() && $checkBotX[$i-1]->getPosX() === $checkBotX[$i-1]->getPosY() &&$checkBotX[$i-2]->getPosX() === $checkBotX[$i]->getPosY()){
                       dd('bot diag not ez');
                   }
               }
           }
       }


        return $this->render('home/index.html.twig', [

        ]);
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
                $botRound = new Game();
                $random = str_split($possibilities[array_rand($possibilities)]);
                $botRound->setPosX((int)$random[0]);
                $botRound->setPosY((int)$random[1]);
                $botRound->setType('rond');
                $entityManager->persist($botRound);
            }
        }
        $entityManager->flush();




        return $this->redirectToRoute('home');


    }
}
