<?php

namespace App\Listener;

use App\Repository\AccessTokenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class KernelRequestListener
{
    public function __construct(private AccessTokenRepository $tokenRepo) {}

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!str_contains($request->getRequestUri(), '/data/create'))
            return;

        $authToken = $request->headers->get('authorization');
        if ($authToken === null) {
            $event->setResponse($this->lostTokenResponse());
        }
        else {
            $tokenEntity = $this->tokenRepo->findOneByValue($authToken);
            if ($tokenEntity === null) {
                $event->setResponse($this->invalidTokenResponse());
            }
            elseif ($tokenEntity->getActiveUntil() < time()) {
                $this->tokenRepo->remove($tokenEntity, true);
                $event->setResponse($this->expiredTokenResponse());
            }
        }
    }

    private function lostTokenResponse(): Response
    {
        return (new JsonResponse([
            'status_code' => Response::HTTP_FORBIDDEN,
            'message' => 'request without access-token'
        ]))->setStatusCode(Response::HTTP_FORBIDDEN);
    }

    private function expiredTokenResponse(): Response
    {
        return (new JsonResponse([
            'status_code' => Response::HTTP_FORBIDDEN,
            'message' => 'your access-token is expired. try to refresh it'
        ]));
    }

    private function invalidTokenResponse(): Response
    {
        return (new JsonResponse([
            'status_code' => Response::HTTP_FORBIDDEN,
            'message' => 'such access-token is not registered'
        ]));
    }
}