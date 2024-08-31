<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SmokeTest extends WebTestCase
{
    public function testApiDocUrlIsSuccessful(): void
    {
        $client = self::createClient();
        $client->request('GET','api/doc');

        self::assertResponseIsSuccessful();
    }

    public function testAPiAccountUrlIsSecure(): void
    {
        $client = self::createClient();
        $client->request('GET','api/account/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testLoginRouteCanConnectAValideUser(): void
    {
        $client = self::createClient();
        $client->followRedirects(false);

        $client->request('POST', '/api/registration', [], [], [
            'CONTENT_TYPE' => 'application/json'],
            json_encode([
                'firstName' => 'tato',
                'lastName' => 'tato',
                'email' => 'tato@toto.fr',
                'password' => 'tato'
            ], JSON_THROW_ON_ERROR));
        $statusCode = $client->getResponse()->getStatusCode();
        dd($statusCode);

    }
}