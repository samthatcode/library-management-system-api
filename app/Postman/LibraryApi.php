<?php

namespace App\Postman;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Library Management System API",
 *     description="API documentation for Library Management System",
 *     @OA\Contact(email="support@example.com")
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000/api/v1",
 *     description="Local Development Server"
 * )
 *
 * @OA\Tag(name="Books", description="Operations related to books")
 * @OA\Tag(name="Authors", description="Operations related to authors")
 * @OA\Tag(name="Patrons", description="Operations related to patrons")
 *
 * --- Schemas ---
 *
 * @OA\Schema(
 *     schema="Book",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="isbn", type="integer"),
 *     @OA\Property(property="publication_date", type="string", format="date"),
 *     @OA\Property(
 *         property="authors",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Author")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Author",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="first_name", type="string"),
 *     @OA\Property(property="last_name", type="string"),
 *     @OA\Property(property="books", type="array", @OA\Items(ref="#/components/schemas/Book"))
 * )
 *
 * @OA\Schema(
 *     schema="Patron",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="books", type="array", @OA\Items(ref="#/components/schemas/Book"))
 * )
 *
 * --- Book Endpoints ---
 *
 * @OA\Get(path="/books", tags={"Books"}, summary="Get all books",
 *     @OA\Response(response=200, description="List of books", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book")))
 * )
 *
 * @OA\Post(path="/books", tags={"Books"}, summary="Create a new book",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title","description","isbn","publication_date","authors"},
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="isbn", type="integer"),
 *             @OA\Property(property="publication_date", type="string", format="date"),
 *             @OA\Property(property="authors", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(response=201, description="Book created", @OA\JsonContent(ref="#/components/schemas/Book"))
 * )
 *
 * @OA\Put(path="/books/{id}", tags={"Books"}, summary="Update a book",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Book")),
 *     @OA\Response(response=200, description="Book updated", @OA\JsonContent(ref="#/components/schemas/Book")),
 *     @OA\Response(response=404, description="Book not found")
 * )
 *
 * @OA\Delete(path="/books/{id}", tags={"Books"}, summary="Delete a book",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Book deleted"),
 *     @OA\Response(response=404, description="Book not found"),
 *     @OA\Response(response=400, description="Book is currently borrowed")
 * )
 *
 * @OA\Get(path="/books/search", tags={"Books"}, summary="Search books by title and author",
 *     @OA\Parameter(name="title", in="query", required=false, @OA\Schema(type="string")),
 *     @OA\Parameter(name="authors", in="query", required=false, @OA\Schema(type="array", @OA\Items(type="integer"))),
 *     @OA\Response(response=200, description="Search results", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))),
 *     @OA\Response(response=404, description="No books found")
 * )
 *
 * @OA\Get(path="/authors/{authorId}/books", tags={"Books"}, summary="Fetch books by author",
 *     @OA\Parameter(name="authorId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Books by author", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book")))
 * )
 *
 * --- Author Endpoints ---
 *
 * @OA\Get(path="/authors", tags={"Authors"}, summary="Get all authors",
 *     @OA\Response(response=200, description="List of authors", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Author")))
 * )
 *
 * @OA\Post(path="/authors", tags={"Authors"}, summary="Create an author",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"first_name","last_name","books"},
 *         @OA\Property(property="first_name", type="string"),
 *         @OA\Property(property="last_name", type="string"),
 *         @OA\Property(property="books", type="array", @OA\Items(type="integer"))
 *     )),
 *     @OA\Response(response=201, description="Author created", @OA\JsonContent(ref="#/components/schemas/Author"))
 * )
 *
 * @OA\Put(path="/authors/{id}", tags={"Authors"}, summary="Update an author",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Author")),
 *     @OA\Response(response=200, description="Author updated", @OA\JsonContent(ref="#/components/schemas/Author")),
 *     @OA\Response(response=404, description="Author not found")
 * )
 *
 * @OA\Delete(path="/authors/{id}", tags={"Authors"}, summary="Delete an author",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Author deleted"),
 *     @OA\Response(response=404, description="Author not found")
 * )
 *
 * --- Patron Endpoints ---
 *
 * @OA\Get(path="/patrons", tags={"Patrons"}, summary="Get all patrons",
 *     @OA\Response(response=200, description="List of patrons", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Patron")))
 * )
 *
 * @OA\Post(path="/patrons", tags={"Patrons"}, summary="Create a patron",
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"name","email"},
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string")
 *     )),
 *     @OA\Response(response=201, description="Patron created", @OA\JsonContent(ref="#/components/schemas/Patron"))
 * )
 *
 * @OA\Put(path="/patrons/{id}", tags={"Patrons"}, summary="Update a patron",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Patron")),
 *     @OA\Response(response=200, description="Patron updated", @OA\JsonContent(ref="#/components/schemas/Patron")),
 *     @OA\Response(response=404, description="Patron not found")
 * )
 *
 * @OA\Delete(path="/patrons/{id}", tags={"Patrons"}, summary="Delete a patron",
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Patron deleted"),
 *     @OA\Response(response=404, description="Patron not found"),
 *     @OA\Response(response=400, description="Patron cannot be deleted if associated with books")
 * )
 *
 * @OA\Post(path="/patrons/{patronId}/books/{bookId}/borrow", tags={"Patrons"}, summary="Borrow a book",
 *     @OA\Parameter(name="patronId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="bookId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Book borrowed successfully"),
 *     @OA\Response(response=404, description="Patron or book not found"),
 *     @OA\Response(response=400, description="Book already borrowed")
 * )
 *
 * @OA\Post(path="/patrons/{patronId}/books/{bookId}/return", tags={"Patrons"}, summary="Return a book",
 *     @OA\Parameter(name="patronId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Parameter(name="bookId", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Book returned successfully", @OA\JsonContent(ref="#/components/schemas/Book")),
 *     @OA\Response(response=404, description="Patron or book not found")
 * )
 */
class LibraryApi
{
    // No methods needed, this file is just for Swagger annotations
}
