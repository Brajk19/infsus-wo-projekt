<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ContentDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $name,

        #[Assert\NotBlank]
        public readonly string $description,

        #[Assert\GreaterThanOrEqual(0)]
        #[Assert\LessThanOrEqual(18)]
        public readonly int $minimumAge,

        public readonly int $channel
    ){
    }
}