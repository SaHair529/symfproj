<?php

namespace App\Controller;

use App\Entity\Data;
use App\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends AbstractController
{
    #[Route('/data/create', name: 'data_create')]
    public function create(Request $request, DataRepository $dataRepo): Response
    {
        $memory = memory_get_usage();
        $startTime = new \DateTime('now');

        if ('GET' !== $request->getMethod() && 'POST' !== $request->getMethod())
            return $this->invalidMethodResponse();

        $requestJsonData = json_decode($request->getContent(), true);
        if (empty($requestJsonData))
            return $this->invalidRequestDataResponse();

        $data = (new Data())
            ->setData($requestJsonData);
        $dataRepo->save($data, true);
        $dataId = $dataRepo->getLastCreated()->getId();

        $spentTime = ((new \DateTime('now'))->diff($startTime))
            ->format('%S seconds, %f microseconds');
        $spentMemory = memory_get_usage() - $memory;

        return $this->successResponse($spentTime, $spentMemory.' bytes', $dataId);
    }

    private function invalidMethodResponse(): Response
    {
        return (new JsonResponse([
            'status_code' => Response::HTTP_FORBIDDEN,
            'message' => 'request method is invalid'
        ]))->setStatusCode(Response::HTTP_FORBIDDEN);
    }

    private function invalidRequestDataResponse(): Response
    {
        return (new JsonResponse([
            'status_code' => Response::HTTP_BAD_REQUEST,
            'message' => 'empty or invalid request data'
        ]))->setStatusCode(Response::HTTP_BAD_REQUEST);
    }

    private function successResponse($spentTime, $spentMemory, $dataId): Response
    {
        return (new JsonResponse([
            'spent_time' => $spentTime,
            'spent_memory' => $spentMemory,
            'created_entity_id' => $dataId
        ]))->setStatusCode(Response::HTTP_OK);
    }
}
