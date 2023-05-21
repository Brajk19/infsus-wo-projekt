<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\ContentDTO;
use App\Service\ContentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContentController extends AbstractController
{
    public function __construct(
        private readonly ContentService $contentService,
        private readonly ValidatorInterface $validator,
        private readonly SerializerInterface $serializer
    ){
    }

    #[Route(path: '/content/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getContent(int $id): JsonResponse
    {
        $content = $this->contentService->getContent($id);

        if($content === null) {
            return new JsonResponse(
                data: ['message' => 'Not found'],
                status: 404
            );
        }

        return new JsonResponse(
            data: $this->serializer->serialize($content, 'json'),
            json: true
        );
    }

    #[Route(path: '/content', methods: ['POST'])]
    public function createContent(Request $request): JsonResponse
    {
        $data = $this->getDtoFromRequest($request);

        if($data instanceof ConstraintViolationList) {
            return $this->json($data, 422);
        }

        return new JsonResponse(
            data: $this->contentService->createContent($data),
            status: 201
        );
    }

    #[Route(path: '/content/{id}', methods: ['PUT'])]
    public function updateContent(Request $request, int $id): JsonResponse
    {
        $content = $this->contentService->getContent($id);

        if($content === null) {
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
            data: $this->contentService->updateContent($data, $content)
        );
    }

    #[Route(path: '/content/{id}', methods: ['DELETE'])]
    public function deleteContent(int $id): JsonResponse
    {
        $content = $this->contentService->getContent($id);

        if($content === null) {
            return new JsonResponse(
                data: ['message' => 'Not found'],
                status: 404
            );
        }

        $this->contentService->deleteContent($content);

        return new JsonResponse(status: 204);
    }

    private function getDtoFromRequest(Request $request): ContentDTO|ConstraintViolationList
    {
        $violations = new ConstraintViolationList();

        try {
            $dto = $this->serializer->deserialize(
                data: $request->getContent(),
                type: ContentDTO::class,
                format: 'json'
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