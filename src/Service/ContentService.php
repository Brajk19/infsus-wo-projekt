<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ContentDTO;
use App\Entity\Content;
use App\Repository\ChannelRepository;
use App\Repository\ContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContentService
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly ContentRepository $contentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly NormalizerInterface $normalizer,
    ){
    }

    public function createContent(ContentDTO $dto): array
    {
        $content = new Content();

        $this->setFields($dto, $content);

        $this->entityManager->persist($content);
        $this->entityManager->flush();

        return $this->normalizer->normalize($content, 'json');
    }

    public function updateContent(ContentDTO $dto, Content $content): array
    {
        $this->setFields($dto, $content);

        $this->entityManager->flush();

        return $this->normalizer->normalize($content, 'json');
    }

    public function deleteContent(Content $content): void
    {
        $this->entityManager->remove($content);
        $this->entityManager->flush();
    }

    public function getContent(int $id): ?Content
    {
        return $this->contentRepository->find($id);
    }

    private function setFields(ContentDTO $dto, Content $content): void
    {
        $content->setName($dto->name);
        $content->setDescription($dto->description);
        $content->setMinimumAge($dto->minimumAge);
        $content->setChannel($this->channelRepository->find($dto->channel));
    }
}