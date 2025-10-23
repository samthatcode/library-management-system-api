<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Author;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Books Management
 *
 * APIs for managing and retrieving books within the library system.
 *
 * These endpoints handle CRUD operations and search functionality for books.
 *
 * Base URL: `/api/v1/books`
 */
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
     * Display a listing of all available books.
     *
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "The Great Gatsby",
     *       "description": "A classic American novel",
     *       "isbn": "9780743273565",
     *       "publication_date": "1925-04-10",
     *       "authors": [
     *         {
     *           "id": 1,
     *           "name": "F. Scott Fitzgerald"
     *         }
     *       ],
     *       "created_at": "2025-10-18T12:00:00Z"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        return response()->json(
            BookResource::collection($this->book_service->getAllBooks()),
            Response::HTTP_OK
        );
    }

    /**
     * Store a newly created book.
     *
     * @bodyParam title string required The title of the book. Example: The Great Gatsby
     * @bodyParam description string required A description of the book. Example: A classic American novel set in the Jazz Age
     * @bodyParam isbn numeric required The ISBN number of the book. Example: 9780743273565
     * @bodyParam publication_date date required The publication date of the book. Example: 1925-04-10
     * @bodyParam authors array required Array of author IDs associated with the book. Example: [1, 2]
     * @bodyParam authors.* integer required Each author ID must exist in the authors table. Example: 1
     *
     * @response 201 scenario="Created" {
     *   "data": {
     *     "id": 1,
     *     "title": "The Great Gatsby",
     *     "description": "A classic American novel",
     *     "isbn": "9780743273565",
     *     "publication_date": "1925-04-10",
     *     "authors": [
     *       {
     *         "id": 1,
     *         "name": "F. Scott Fitzgerald"
     *       }
     *     ],
     *     "created_at": "2025-10-18T12:00:00Z"
     *   }
     * }
     */
    public function store(StoreBookRequest $request)
    {
        $validatedData = $request->validated();
        $book = $this->book_service->store($validatedData, $validatedData['authors']);
        return response()->json(new BookResource($book), Response::HTTP_CREATED);
    }

    /**
     * Update an existing book.
     *
     * @urlParam book integer required The ID of the book to update. Example: 1
     * @bodyParam title string optional The updated title of the book. Example: The Great Gatsby (Revised Edition)
     * @bodyParam description string optional Updated description of the book.
     * @bodyParam isbn numeric optional Updated ISBN number.
     * @bodyParam publication_date date optional Updated publication date.
     * @bodyParam authors array optional Updated array of author IDs.
     * @bodyParam authors.* integer optional Each author ID must exist in the authors table.
     *
     * @response 200 scenario="Updated" {
     *   "data": {
     *     "id": 1,
     *     "title": "The Great Gatsby (Revised Edition)",
     *     "description": "A classic American novel",
     *     "isbn": "9780743273565",
     *     "publication_date": "1925-04-10",
     *     "authors": [
     *       {
     *         "id": 1,
     *         "name": "F. Scott Fitzgerald"
     *       }
     *     ],
     *     "updated_at": "2025-10-18T15:00:00Z"
     *   }
     * }
     * @response 404 scenario="Not Found" {"error": "Book not found"}
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
     * Delete a book.
     *
     * @urlParam book integer required The ID of the book to delete. Example: 1
     *
     * @response 200 scenario="Deleted" {"message": "Book deleted successfully"}
     * @response 404 scenario="Not Found" {"error": "Book not found"}
     * @response 400 scenario="Book is borrowed" {"error": "Book is currently borrowed and cannot be deleted"}
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
     * Search for books by title and author.
     *
     * @queryParam title string optional The title of the book to search for. Example: Gatsby
     * @queryParam authors array optional Array of author IDs to filter by. Example: [1, 2]
     *
     * @response 200 scenario="Success" {
     *   "books": [
     *     {
     *       "id": 1,
     *       "title": "The Great Gatsby",
     *       "description": "A classic American novel",
     *       "isbn": "9780743273565",
     *       "publication_date": "1925-04-10",
     *       "authors": [
     *         {
     *           "id": 1,
     *           "name": "F. Scott Fitzgerald"
     *         }
     *       ]
     *     }
     *   ]
     * }
     * @response 404 scenario="Not Found" {"error": "No books found for the specified title and author"}
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
     * Fetch all books by a particular author.
     *
     * @urlParam author integer required The ID of the author. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "The Great Gatsby",
     *       "description": "A classic American novel",
     *       "isbn": "9780743273565",
     *       "publication_date": "1925-04-10",
     *       "authors": [
     *         {
     *           "id": 1,
     *           "name": "F. Scott Fitzgerald"
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function fetchBooksByAuthor(Author $author)
    {
        $books = $this->book_service->fetchBooksByAuthor($author);
        return response()->json(BookResource::collection($books), Response::HTTP_OK);
    }
}
