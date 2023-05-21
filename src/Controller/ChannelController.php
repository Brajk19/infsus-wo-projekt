<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ChannelDTO;
use App\Service\ChannelService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChannelController extends AbstractController
{
    public function __construct(
        private readonly ChannelService $channelService,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ){
    }

    #[Route(path: '/channels', methods: ['GET'])]
    public function getChannels(): JsonResponse
    {
        return new JsonResponse(
            data: $this->channelService->getAllChannels()
        );
    }

    #[Route(path: '/channel/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getChannel(int $id): JsonResponse
    {
        $channel = $this->channelService->getChannel($id);

        if($channel === null) {
            return new JsonResponse(
                data: ['message' => 'Not found'],
                status: 404
            );
        }

        return new JsonResponse(
            data: $this->serializer->serialize($channel, 'json'),
            json: true
        );
    }

    #[Route(path: '/channel', methods: ['POST'])]
    public function createChannel(Request $request): JsonResponse
    {
        $data = $this->getDtoFromRequest($request);

        if($data instanceof ConstraintViolationList) {
            return $this->json($data, 422);
        }

        return new JsonResponse(
            data: $this->channelService->createChannel($data),
            status: 201
        );
    }

    #[Route(path: '/channel/{id}', methods: ['PUT'])]
    public function updateChannel(Request $request, int $id): JsonResponse
    {
        $channel = $this->channelService->getChannel($id);

        if($channel === null) {
            return new JsonResponse(
                data: ['message' => 'Not found'],
                status: 404
            );
        }

        $data = $this->getDtoFromRequest($request);

        if($data instanceof ConstraintViolationList) {
            return $this->json($data, 422);
        }

        return new JsonResponse(
            data: $this->channelService->updateChannel($data, $channel)
        );
    }

    #[Route(path: '/channel/{id}', methods: ['DELETE'])]
    public function deleteChannel(int $id): JsonResponse
    {
        $channel = $this->channelService->getChannel($id);

        if($channel === null) {
            return new JsonResponse(
                data: ['message' => 'Not found'],
                status: 404
            );
        }

        $this->channelService->deleteChannel($channel);

        return new JsonResponse(status: 204);
    }

    private function getDtoFromRequest(Request $request): ChannelDTO|ConstraintViolationList
    {
        $violations = new ConstraintViolationList();

        try {
            $dto = $this->serializer->deserialize(
                data: $request->getContent(),
                type: ChannelDTO::class,
                format: 'json',
            );
        } catch (PartialDenormalizationException $e) {
            /** @var NotNormalizableValueException $exception */
            foreach ($e->getErrors() as $exception) {
                $message = sprintf('The type must be one of "%s" ("%s" given).', implode(', ', $exception->getExpectedTypes()), $exception->getCurrentType());
                $parameters = [];
                if ($exception->canUseMessageForUser()) {
                    $parameters['hint'] = $exception->getMessage();
                }
                $violations->add(new ConstraintViolation($message, '', $parameters, null, $exception->getPath(), null));
            }

            return $violations;
        } catch (MissingConstructorArgumentsException $e) {
            $message = 'Missing fields: '. implode(',', $e->getMissingConstructorArguments());
            $violations->add(new ConstraintViolation($message, '', [], null, null, null));
            return $violations;
        }

        $violations = $this->validator->validate($dto);

        if($violations->count() > 0) {
            return $violations;
        }

        return $dto;
    }
}