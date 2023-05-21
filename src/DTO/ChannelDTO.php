<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ChannelDTO
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly string $name,

        #[Assert\NotBlank]
        public readonly string $description,

        #[Assert\Url]
        #[Assert\When(
            expression: 'this.isPaid === true',
            constraints: [
                new Assert\NotNull(message: 'URL should not be null if channel is paid'),
                new Assert\NotBlank(message: 'URL should not be empty if channel is paid')
            ]
        )]
        public readonly ?string $websiteUrl = null,

        public readonly bool $isPaid = false
    ){
    }
}