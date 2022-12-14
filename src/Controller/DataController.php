<?php

namespace App\Controller;

use App\Entity\Data;
use App\Form\ChangeDataFormType;
use App\Repository\AccessTokenRepository;
use App\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class DataController extends AbstractController
{
    #[Route('/data/create', name: 'data_create')]
    public function create(Request $request, DataRepository $dataRepo, AccessTokenRepository $tokenRepo): Response
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

    #[Route('/data/show', name: 'data_show')]
    public function show(Request $request, DataRepository $dataRepo, Environment $twig)
    {
        $dataEntities = $dataRepo->findAll();
        foreach ($dataEntities as $dataEntity) {
            $dataEntity->json = json_encode($dataEntity->getData(),
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return new Response($twig->render('data/index.html.twig', [
            'data_entities' => $dataEntities,
        ]));
    }

    #[Route('/data/delete', name: 'data_delete')]
    public function delete(Request $request, DataRepository $dataRepo)
    {
        $entityId = $request->query->get('entity_id');
        if (null === $entityId)
            return (new JsonResponse([
                'message' => 'request parameter entity_id is missing',
                'code' => '400'
            ]))->setStatusCode(Response::HTTP_BAD_REQUEST);

        $dataRepo->remove($dataRepo->find($entityId), true);
        return (new JsonResponse([
            'message' => 'ok',
            'code' => '200'
        ]))->setStatusCode(Response::HTTP_OK);
    }

    #[Route('/data/change', name: 'data_change')]
    public function change(Request $request, DataRepository $dataRepo)
    {
        $id = $request->query->get('id') ?? $request->request->get('id');
        $data = $request->query->get('data') ?? $request->request->get('data');
        $dataEntity = $dataRepo->find($id);
        $dataEntity->setData(json_decode($data, true));
        $dataRepo->save($dataEntity, true);

        return $this->redirectToRoute('data_show');
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
