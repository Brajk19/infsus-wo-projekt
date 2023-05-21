<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ChannelDTO;
use App\Entity\Channel;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ChannelService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NormalizerInterface $normalizer,
        private readonly ChannelRepository $channelRepository
    ){
    }

    public function getAllChannels(): array
    {
        $channels = $this->channelRepository->findBy([], ['id' => 'ASC']);

        return $this->normalizer->normalize($channels);
    }

    public function createChannel(ChannelDTO $dto): array
    {
        $channel = new Channel();

        self::setFields($dto, $channel);

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $this->normalizer->normalize($channel, 'json');
    }

    public function updateChannel(ChannelDTO $dto, Channel $channel): array
    {
        self::setFields($dto, $channel);

        $this->entityManager->flush();

        return $this->normalizer->normalize($channel, 'json');
    }

    public function deleteChannel(Channel $channel): void
    {
        $this->entityManager->remove($channel);
        $this->entityManager->flush();
    }

    public function getChannel(int $id): ?Channel
    {
        return $this->channelRepository->find($id);
    }

    private static function setFields(ChannelDTO $dto, Channel $channel): void
    {
        $channel->setName($dto->name);
        $channel->setDescription($dto->description);
        $channel->setIsPaid($dto->isPaid);
        $channel->setWebsiteUrl($dto->websiteUrl);
    }
}