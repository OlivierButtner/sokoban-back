<?php

namespace App\Controller;

use App\Repository\BoardRepository;
use App\Repository\RowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

        try
        {
            $idSelectedBoard = intval($idSelectedBoard);
        }
        catch(\Exception $e)
        {
            return $response = new Response(
                "Le paramètre ne peut pas etre converti en nombre.",
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'json']
            );
        }

        $board = $boardRepository->findById($idSelectedBoard);

        if(count($board) > 0) {
            $board = $board[0];

            $boardArray = array();
            foreach($board->getRows() as $row) {
                array_push($boardArray, str_split($row->getDescription()));
            }
            return $this->json($boardArray, Response::HTTP_OK);
        }else{
            return new Response(
                "Aucun tableau existe pour la valeur passé en paramètre.",
                Response::HTTP_BAD_REQUEST,
                ['content-type' => 'json']
            );
        }
    }
}
