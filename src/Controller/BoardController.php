<?php

namespace App\Controller;

use App\Repository\BoardRepository;
use App\Repository\RowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class BoardController extends AbstractController
{
    #[Route('/board')]
    public function index(BoardRepository $boardRepository, RowRepository $rowRepository): Response
    {
        $getAllBoard = $boardRepository->getAllBoards();

        $arrayNameIdBoards = array();
        foreach ($getAllBoard as $board) {
            $arrayNameIdBoards[] = [
                "id" => $board->getId(),
                "name" => $board->getName(),
                "nbCol" => $board->getCol(),
                "nbRow" => $board->getRow()
            ];
        }

        $jsonContent = json_encode($arrayNameIdBoards);

        $response = new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['content-type' => 'json']
        );

        return $response;
    }

    #[Route('/select', methods: ['GET'])]
    public function selectBoard(Request $request, BoardRepository $boardRepository, RowRepository $rowRepository): Response
    {
        $idSelectedBoard = $request->query->get('idBoard');

        try {
            $idSelectedBoard = intval($idSelectedBoard);
        } catch (\Exception $e) {
            return $response = new Response(
                "Le paramètre ne peut pas etre converti en nombre.",
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'json']
            );
        }

        $board = $boardRepository->findById($idSelectedBoard);

        if (count($board) > 0) {
            $board = $board[0];

            $boardArray = array();
            foreach ($board->getRows() as $row) {
                array_push($boardArray, str_split($row->getDescription()));
            }
            return $this->json($boardArray, Response::HTTP_OK);
        } else {
            return new Response(
                "Aucun tableau existe pour la valeur passé en paramètre.",
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'json']
            );
        }
    }

    // Fonction pour gérer les déplacements du joueur sur le plateau
    #[Route('/move', methods: ['GET'])]
    public function moveOnBoard(Request $request, BoardRepository $boardRepository): Response
    {
        $session = $request->getSession();

        $idBoard = $request->query->get('idBoard');
        $startPositionPlayer = $request->query->get('startPositionPlayer');
        $endPositionPlayer = $request->query->get('endPositionPlayer');

        $session->set('idBoard', $idBoard);
        //$idBoard = $session->get('idBoard');

        if ($idBoard === null || $startPositionPlayer === null || $endPositionPlayer === null) {
            return $this->json("Valeur en paramètre manquante", Response::HTTP_BAD_REQUEST);
        }
        // Transformation des positions en tableau. x,y devient [x,y]
        $startPositionPlayer = array_map('intval', explode(',', $startPositionPlayer));
        $endPositionPlayer = array_map('intval', explode(',', $endPositionPlayer));

        $board = $boardRepository->findOneBy(['id' => $idBoard]);
        $dataBoard = $this->getAllRowsFromBoard($board);

        // Si la position d'arrivée est un mur ( # ) on ne fait rien
        if ($dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] === "#") {
            return $this->json("Il y a un mur, déplacement impossible.", Response::HTTP_OK, ['content-type' => 'json']);
        }

        // Si la position d'arrivée est vide ( . ) déplacer le joueur sur la case d'arrivée
        if ($dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] === ".") {
            $dataBoard[$startPositionPlayer[0]][$startPositionPlayer[1]] = ".";
            $dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] = "P";

            return $this->json($dataBoard, Response::HTTP_OK, ['content-type' => 'json']);
        }
        // Si la position d'arrivée est une destination ( x ) déplacer le joueur sur la case d'arrivée
        if ($dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] === "x") {
            $dataBoard[$startPositionPlayer[0]][$startPositionPlayer[1]] = ".";
            $dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] = "P";
            return $this->json($dataBoard, Response::HTTP_OK, ['content-type' => 'json']);
        }


        // Si la position d'arrivée est une caisse ( C ) on vérifie si la case d'après est vide ( . ) ou une caisse ( C ) ou un mur ( # )
        // Si la case d'après est un mur ou une caisse on ne fait rien
        // Sinon on déplace le joueur sur la case d'arriver et la caisse sur la case d'après
        if ($dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] === "C") {
            $nextPosition = [$endPositionPlayer[0] + ($endPositionPlayer[0] - $startPositionPlayer[0]), $endPositionPlayer[1] + ($endPositionPlayer[1] - $startPositionPlayer[1])];

            if ($dataBoard[$nextPosition[0]][$nextPosition[1]] === "#" || $dataBoard[$nextPosition[0]][$nextPosition[1]] === "C") {
                return $this->json("Il y a un mur ou une caisse, déplacement impossible.", Response::HTTP_OK, ['content-type' => 'json']);
            } elseif ($dataBoard[$nextPosition[0]][$nextPosition[1]] === "x") {
                $dataBoard[$nextPosition[0]][$nextPosition[1]] = "C";
                $dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] = ".";
            } else {
                $dataBoard[$nextPosition[0]][$nextPosition[1]] = "C";
                $dataBoard[$endPositionPlayer[0]][$endPositionPlayer[1]] = ".";
            }
        }


        return $this->json("Déplacement impossible.", Response::HTTP_OK, ['content-type' => 'json']);
    }

    public function getAllRowsFromBoard($board): array
    {
        if (!$board) {
            return [];
        }

        $allRows = [];
        foreach ($board->getRows() as $row) {
            $allRows[] = str_split($row->getDescription());
        }

        return $allRows;
    }

}
