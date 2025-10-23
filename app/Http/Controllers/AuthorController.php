<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Authors Management
 *
 * APIs for managing and retrieving authors within the library system.
 *
 * These endpoints handle CRUD operations for authors.
 *
 * Base URL: `/api/v1/authors`
 */
class AuthorController extends Controller
{
    /**
     * Injecting the AuthorService class into the constructor.
     * Then, we have access to the service in whatever methods we need
     */

    private AuthorService $author_service;

    public function __construct(AuthorService $author_service)
    {
        $this->author_service = $author_service;
    }

    /**
     * Display a listing of all available authors.
     *
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "first_name": "F. Scott",
     *       "last_name": "Fitzgerald",
     *       "books": [
     *         {
     *           "id": 1,
     *           "title": "The Great Gatsby"
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
            AuthorResource::collection($this->author_service->getAllAuthors()),
            Response::HTTP_OK
        );
    }

    /**
     * Store a newly created author.
     *
     * @bodyParam first_name string required The first name of the author. Example: F. Scott
     * @bodyParam last_name string required The last name of the author. Example: Fitzgerald
     * @bodyParam books array required Array of book IDs associated with the author. Example: [1, 2]
     * @bodyParam books.* integer required Each book ID must exist in the books table. Example: 1
     *
     * @response 201 scenario="Created" {
     *   "data": {
     *     "id": 1,
     *     "first_name": "F. Scott",
     *     "last_name": "Fitzgerald",
     *     "books": [
     *       {
     *         "id": 1,
     *         "title": "The Great Gatsby"
     *       }
     *     ],
     *     "created_at": "2025-10-18T12:00:00Z"
     *   }
     * }
     */
    public function store(StoreAuthorRequest $request)
    {
        return response()->json(
            new AuthorResource($this->author_service->create($request->validated())),
            Response::HTTP_CREATED
        );
    }

    /**
     * Update an existing author.
     *
     * @urlParam author integer required The ID of the author to update. Example: 1
     * @bodyParam first_name string optional The updated first name of the author. Example: Francis Scott
     * @bodyParam last_name string optional The updated last name of the author. Example: Fitzgerald
     * @bodyParam books array optional Updated array of book IDs associated with the author. Example: [1, 2, 3]
     * @bodyParam books.* integer optional Each book ID must exist in the books table. Example: 1
     *
     * @response 200 scenario="Updated" {
     *   "data": {
     *     "id": 1,
     *     "first_name": "Francis Scott",
     *     "last_name": "Fitzgerald",
     *     "books": [
     *       {
     *         "id": 1,
     *         "title": "The Great Gatsby"
     *       }
     *     ],
     *     "updated_at": "2025-10-18T15:00:00Z"
     *   }
     * }
     * @response 404 scenario="Not Found" {"error": "Author not found"}
     */
    public function update(StoreAuthorRequest $request, Author $author)
    {
        // Check if the author exists
        if (!$author) {
            return response()->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        // If author exists
        return response()->json(
            new AuthorResource($this->author_service->update($request->validated(), $author)),
            Response::HTTP_OK
        );
    }

    /**
     * Delete an author.
     *
     * @urlParam author integer required The ID of the author to delete. Example: 1
     *
     * @response 200 scenario="Deleted" {"message": "Author deleted successfully"}
     * @response 404 scenario="Not Found" {"error": "Author not found"}
     */
    public function destroy(Author $author)
    {
        // Check if the author exists
        if (!$author) {
            return response()->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        // If author exists
        $this->author_service->delete($author);

        return response()->json(
            ['message' => 'Author deleted successfully'],
            Response::HTTP_OK
        );
    }
}
