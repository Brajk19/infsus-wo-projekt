<?php

declare(strict_types=1);

namespace App\Tests\DTO;

use App\DTO\ChannelDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @covers \App\DTO\ChannelDTO
 */
class ChannelDTOTest extends KernelTestCase
{
    /**
     * @dataProvider dtoDataProvider
     */
    public function testValidation(array $data, string $invalidProperty, string $message): void
    {
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $dto = new ChannelDTO(...$data);

        $violations = $validator->validate($dto);

        self::assertCount(1, $violations);
        self::assertSame($invalidProperty, $violations->get(0)->getPropertyPath());
        self::assertSame($message, $violations->get(0)->getMessage());
    }

    public function dtoDataProvider(): iterable
    {
        yield [
            [
                'name' => 'valid name',
                'description' => 'valid description',
                'websiteUrl' => 'invalid url',
                'isPaid' => false,
            ],
            'websiteUrl',
            'This value is not a valid URL.'
        ];

        yield [
            [
                'name' => 'valid name',
                'description' => 'valid description',
                'websiteUrl' => "",
                'isPaid' => true,
            ],
            'websiteUrl',
            'URL should not be empty if channel is paid'
        ];

        yield [
            [
                'name' => 'valid name',
                'description' => '',
                'websiteUrl' => 'https://fer.hr',
                'isPaid' => false,
            ],
            'description',
            'This value should not be blank.'
        ];

        yield [
            [
                'name' => '',
                'description' => 'valid description',
                'websiteUrl' => 'https://fer.hr',
                'isPaid' => false,
            ],
            'name',
            'This value should not be blank.'
        ];
    }
}