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
        $rounds = $gameRepository->findAll();

        return $this->render('home/index.html.twig', [
            'rounds' => $rounds,
        ]);
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
