<?php

declare(strict_types=1);

namespace App\Tests\DTO;

use App\DTO\ChannelDTO;
use App\DTO\ContentDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @covers \App\DTO\ContentDTO
 */
class ContentDTOTest extends KernelTestCase
{
    /**
     * @dataProvider dtoDataProvider
     */
    public function testValidation(array $data, string $invalidProperty, string $message): void
    {
        $validator = self::getContainer()->get(ValidatorInterface::class);

        $dto = new ContentDTO(...$data);

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
                'minimumAge' => 23,
                'channel' => 2,
            ],
            'minimumAge',
            'This value should be less than or equal to 18.'
        ];

        yield [
            [
                'name' => 'valid name',
                'description' => 'valid description',
                'minimumAge' => -1,
                'channel' => 2,
            ],
            'minimumAge',
            'This value should be greater than or equal to 0.'
        ];
    }
}