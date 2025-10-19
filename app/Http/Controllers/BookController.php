<?php

/**
 * @OA\Tag(
 *     name="Books",
 *     description="API Endpoints for managing books"
 * )
 */

/**
 * @OA\Get(
 *     path="/api/v1/books",
 *     tags={"Books"},
 *     summary="Get all books",
 *     @OA\Response(
 *         response=200,
 *         description="List of books",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Book")
 *         )
 *     )
 * )
 */

/**
 * @OA\Post(
 *     path="/api/v1/books",
 *     tags={"Books"},
 *     summary="Create a new book",
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
 *     @OA\Response(
 *         response=201,
 *         description="Book created successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Book")
 *     )
 * )
 */

/**
 * @OA\Put(
 *     path="/api/v1/books/{id}",
 *     tags={"Books"},
 *     summary="Update an existing book",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="isbn", type="integer"),
 *             @OA\Property(property="publication_date", type="string", format="date"),
 *             @OA\Property(property="authors", type="array", @OA\Items(type="integer"))
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Book updated successfully",
 *         @OA\JsonContent(ref="#/components/schemas/Book")
 *     ),
 *     @OA\Response(response=404, description="Book not found")
 * )
 */

/**
 * @OA\Delete(
 *     path="/api/v1/books/{id}",
 *     tags={"Books"},
 *     summary="Delete a book",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Book deleted successfully"
 *     ),
 *     @OA\Response(response=404, description="Book not found"),
 *     @OA\Response(response=400, description="Book is currently borrowed")
 * )
 */

/**
 * @OA\Get(
 *     path="/api/v1/books/search",
 *     tags={"Books"},
 *     summary="Search books by title and author IDs",
 *     @OA\Parameter(
 *         name="title",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="authors",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="array", @OA\Items(type="integer"))
 *     ),
 *     @OA\Response(response=200, description="Search results", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))),
 *     @OA\Response(response=404, description="No books found")
 * )
 */

/**
 * @OA\Get(
 *     path="/api/v1/authors/{authorId}/books",
 *     tags={"Books"},
 *     summary="Fetch all books by a specific author",
 *     @OA\Parameter(
 *         name="authorId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of books by author",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
 *     )
 * )
 */


namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Author;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookController extends Controller
{
    /**
     * Injecting the BookService class into the constructor.
     * Then, we have access to the service in whatever methods we need
     */

    private BookService $book_service;

    public function __construct(BookService $book_service)
    {
        $this->book_service = $book_service;
    }

    /**
     * Books
     * Point 2: Retrieving all Books
     */
    public function index()
    {
        return response()->json(
            BookResource::collection($this->book_service->getAllBooks()),
            Response::HTTP_OK
        );
    }

    /**
     * Books
     * Point 2: Creating/Storing a Book
     */
    public function store(StoreBookRequest $request)
    {
        $validatedData = $request->validated();
        $book = $this->book_service->store($validatedData, $validatedData['authors']);
        return response()->json(new BookResource($book), Response::HTTP_CREATED);
    }

    /**
     * Books
     * Point 2: Updating a Book
     */
    public function update(StoreBookRequest $request, Book $book)
    {
        // Check if the book exists
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // If book exists
        return response()->json(
            new BookResource($this->book_service->update($request->validated(), $book)),
            Response::HTTP_OK
        );
    }

    /**
     * Books
     * Point 2: Deleting a Book
     */
    public function destroy(Book $book)
    {
        // Check if the book exists
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the book is borrowed by any patron
        if ($book->patron) {
            return response()->json(['error' => 'Book is currently borrowed and cannot be deleted'], Response::HTTP_BAD_REQUEST);
        }

        // If book exists and is not borrowed
        $this->book_service->delete($book);

        return response()->json(['message' => 'Book deleted successfully'], Response::HTTP_OK);
    }


    /**
     * Books
     * Point 3: Implementing a feature to search for books by title and author
     */
    public function search(Request $request)
    {
        $title = $request->input('title');
        $authorIds = $request->input('authors');

        $authors = Author::whereIn('id', $authorIds)->get();
        $books = $this->book_service->searchByTitleAndAuthor($title, $authors);

        if ($books->isEmpty()) {
            return response()->json(['error' => 'No books found for the specified title and author'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['books' => $books], Response::HTTP_OK);
    }

    /**
     * Books
     * Point 4:  fetching all books by a particular author
     */
    public function fetchBooksByAuthor(Author $author)
    {
        $books = $this->book_service->fetchBooksByAuthor($author);
        return response()->json(BookResource::collection($books), Response::HTTP_OK);
    }
}
