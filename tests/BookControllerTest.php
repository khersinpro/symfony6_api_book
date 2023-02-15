<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BookControllerTest extends WebTestCase
{

    /**
     * Create a client with a default Authorization header.
     *
     * @param string $username
     * @param string $password
     *
     * @return KernelBrowser
     */
    protected function createAuthenticatedClient($username = 'admin@gmail.com', $password = 'password'): KernelBrowser
    {
        $client = static::createClient();
        $client->request('POST', '/api/login_check', [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => $username,
                'password' => $password,
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    public function testRedirectApiDoc()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('http://localhost/api/doc', '301');
    }

    public function testUnauthorizedUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/book');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetBookListLength(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/book?page=1&limit=10');

        $books = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(10, $books);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
    
    public function testGetOneBook(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/book/25');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testGetOneBook404(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/book/fake');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
    
    public function testCreateValidBook()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/book/create', [], [], [],
            json_encode([
                'title' => 'titre valide',
                'content' => 'mon content'
            ])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
    
    public function testCreateInvalidBook()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/book/create', [], [], [],
        json_encode([
                'title' => 't',
                'content' => 'mon content'
                ])
        );
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("Ce champ doit contenir au minimum 2 caractères.", $data[0]['message']);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
