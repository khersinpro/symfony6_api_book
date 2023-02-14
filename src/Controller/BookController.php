<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Service\VersioningService;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class BookController extends AbstractController
{
    #[Route('/api/book', name: 'book.get.all', methods: ['GET'])]
    public function getBookList(BookRepository $bookRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache,
        VersioningService $versioningService
    ): JsonResponse
    {
        $version = $versioningService->getVersion();
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $idCache = "getBookList-$page-$limit";

        $jsonBookList = $cache->get($idCache, function (ItemInterface $item) use ($bookRepository, $page, $limit, $serializer, $version) {
            $item->tag("booksCache");
            $bookList = $bookRepository->getPaginatedListBook($page, $limit);
            $context = SerializationContext::create()->setGroups(["getbooks"]);
            $context->setVersion($version);
            return $serializer->serialize($bookList, 'json', $context);
        });

        return new JsonResponse($jsonBookList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/book/{id}', name: 'book.get.one', methods: ['GET'])]
    public function getDetailBook(Book $book, SerializerInterface $serializer, VersioningService $versioningService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setGroups(["getbooks"]);
        $context->setVersion($version);
        $jsonBook = $serializer->serialize($book, 'json', $context);
        
        return new JsonResponse($jsonBook, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[IsGranted("ROLE_ADMIN")]
    #[Route('/api/book/create', name: 'book.create.one', methods: ['POST'])]
    public function createBook(SerializerInterface $serializer, Request $request, BookRepository $bookRepository, AuthorRepository $authorRepository,
        ValidatorInterface $validator, TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $requestData = $request->toArray();
        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        $book->setAuthor($authorRepository->find($requestData['author_id']?? -1));
        
        $errors = $validator->validate($book);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $bookRepository->save($book, true);
        $cache->invalidateTags(['booksCache']);

        return new JsonResponse(null, Response::HTTP_CREATED, []);
    }
    
    #[IsGranted("ROLE_ADMIN")]
    #[Route('/api/book/update/{id}', name: 'book.update.one', methods: ['PUT'])]
    public function updateBook(Book $currentBook, SerializerInterface $serializer, Request $request, ValidatorInterface $validator, 
    AuthorRepository $authorRepository, BookRepository $bookRepository, TagAwareCacheInterface $cache
    ): JsonResponse
    {
        $requestData = $request->toArray();
        $newBook = $serializer->deserialize($request->getContent(), Book::class, 'json');
        
        $errors = $validator->validate($newBook);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        
        $currentBook->setTitle($newBook->getTitle());
        $currentBook->setContent($newBook->getContent());
        $currentBook->setComment($newBook->getComment());
        $currentBook->setAuthor($authorRepository->find($requestData['author_id']?? -1));
        
        $bookRepository->save($currentBook, true);
        $cache->invalidateTags(['booksCache']);
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, ['accept' => 'json']);
    }

    #[Route('/api/book/delete/{id}', name: 'book.delete.one', methods: ['DELETE'])]
    #[IsGranted("ROLE_ADMIN")]
    public function deleteBook(Book $book, SerializerInterface $serializer, BookRepository $bookRepository, TagAwareCacheInterface $cache): JsonResponse
    {
        $bookRepository->remove($book, true);
        $cache->invalidateTags(['booksCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT, ['accept' => 'json']);
    }
}
