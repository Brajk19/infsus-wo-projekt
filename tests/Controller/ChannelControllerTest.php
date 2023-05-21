<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Service\ChannelService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


/**
 * @coversDefaultClass \App\Controller\ChannelController
 */
class ChannelControllerTest extends WebTestCase
{
    private const LIST_URL = '/channels';
    private const CREATE_URL = '/channel';
    private const CHANNEL_URL = '/channel/%d';

    private const CHANNEL_DATA = [
        'name' => 'HBO',
        'description' => 'HBO',
        'websiteUrl' => 'https://hbo.com',
        'isPaid' => true
    ];

    /**
     * @covers ::createChannel
     * @covers ::getChannels
     * @covers ::updateChannel
     * @covers ::deleteChannel
     */
    public function testCRUD(): void
    {
        // create
        $client = static::createClient();

        $client->jsonRequest(
            method: 'POST',
            uri: self::CREATE_URL,
            parameters: self::CHANNEL_DATA
        );

        self::assertResponseIsSuccessful();

        $response = json_decode(
            json: $client->getResponse()->getContent(),
            associative: true
        );

        self::assertArrayHasKey('id', $response);
        self::assertIsInt($response['id']);

        self::assertNotNull(self::getContainer()->get(ChannelService::class)->getChannel($response['id']));

        self::assertSame(self::CHANNEL_DATA['name'], $response['name']);
        self::assertSame(self::CHANNEL_DATA['description'], $response['description']);
        self::assertSame(self::CHANNEL_DATA['websiteUrl'], $response['websiteUrl']);
        self::assertSame(self::CHANNEL_DATA['isPaid'], $response['isPaid']);

        //read
        $client->jsonRequest(
            method: 'GET',
            uri: self::LIST_URL
        );

        self::assertResponseIsSuccessful();

        $list = json_decode(
            json: $client->getResponse()->getContent(),
            associative: true
        );

        self::assertCount(1, $list);
        self::assertSame($list[0], $response);

        //update
        $id = $response['id'];
        $url = sprintf(self::CHANNEL_URL, $id);

        $payload = self::CHANNEL_DATA;
        $payload['description'] = 'new description';

        $client->jsonRequest(
            method: 'PUT',
            uri: $url,
            parameters: $payload
        );

        $response = json_decode(
            json: $client->getResponse()->getContent(),
            associative: true
        );

        self::assertSame('new description', $response['description']);


        //delete
        $client->jsonRequest(
            method: 'DELETE',
            uri: $url
        );

        self::assertResponseStatusCodeSame(204);

        self::assertNull(self::getContainer()->get(ChannelService::class)->getChannel($response['id']));
    }

    /**
     * @covers ::getChannel
     */
    public function test404Response(): void
    {
        $client = static::createClient();

        $client->jsonRequest(
            method: 'GET',
            uri: sprintf(self::CHANNEL_URL, random_int(1, 100000))
        );

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @covers ::createChannel
     */
    public function test422Response(): void
    {
        $payload = self::CHANNEL_DATA;
        $payload['name'] = '';

        $client = static::createClient();
        $client->jsonRequest(
            method: 'POST',
            uri: self::CREATE_URL,
            parameters: $payload
        );

        self::assertResponseStatusCodeSame(422);
    }
}