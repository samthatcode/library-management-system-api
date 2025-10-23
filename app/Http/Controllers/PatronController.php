<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatronRequest;
use App\Http\Resources\PatronResource;
use App\Models\Patron;
use App\Services\PatronService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Patrons Management
 *
 * APIs for managing library patrons and their book borrowing activities.
 *
 * These endpoints handle CRUD operations for patrons and book borrowing/returning functionality.
 *
 * Base URL: `/api/v1/patrons`
 */
class PatronController extends Controller
{
    protected $patron_service;

    public function __construct(PatronService $patron_service)
    {
        $this->patron_service = $patron_service;
    }

    /**
     * Display a listing of all patrons with their borrowed books.
     *
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john.doe@example.com",
     *       "phone": "+1234567890",
     *       "borrowed_books": [
     *         {
     *           "id": 1,
     *           "title": "The Great Gatsby",
     *           "borrowed_at": "2025-10-15T10:00:00Z"
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
            PatronResource::collection($this->patron_service->getPatronsWithBooks()),
            Response::HTTP_OK
        );
    }

    /**
     * Store a newly created patron.
     *
     * @bodyParam name string required The full name of the patron. Example: John Doe
     * @bodyParam email string required The email address of the patron. Example: john.doe@example.com
     * @bodyParam phone string required The phone number of the patron. Example: +1234567890
     *
     * @response 201 scenario="Created" {
     *   "message": "Patron created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     "phone": "+1234567890",
     *     "created_at": "2025-10-18T12:00:00Z"
     *   }
     * }
     */
    public function store(StorePatronRequest $request)
    {
        return response()->json(
            [
                'message' => 'Patron created successfully',
                'data' => $this->patron_service->create($request->validated())
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * Update an existing patron.
     *
     * @urlParam patron integer required The ID of the patron to update. Example: 1
     * @bodyParam name string optional The updated full name of the patron. Example: Jane Doe
     * @bodyParam email string optional The updated email address of the patron. Example: jane.doe@example.com
     * @bodyParam phone string optional The updated phone number of the patron. Example: +1987654321
     *
     * @response 200 scenario="Updated" {
     *   "message": "Patron updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Jane Doe",
     *     "email": "jane.doe@example.com",
     *     "phone": "+1987654321",
     *     "updated_at": "2025-10-18T15:00:00Z"
     *   }
     * }
     * @response 404 scenario="Not Found" {"error": "Patron not found"}
     */
    public function update(StorePatronRequest $request, Patron $patron)
    {
        // Check if the patron exists
        if (!$patron) {
            return response()->json(['error' => 'Patron not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(
            [
                'message' => 'Patron updated successfully',
                'data' => $this->patron_service->update($request->validated(), $patron)
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Delete a patron.
     *
     * @urlParam id integer required The ID of the patron to delete. Example: 1
     *
     * @response 200 scenario="Deleted" {
     *   "message": "Patron deleted successfully",
     *   "data": null
     * }
     * @response 400 scenario="Bad Request" {"error": "Patron has borrowed books and cannot be deleted"}
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->patron_service->deletePatron($id);
            return response()->json([
                'message' => 'Patron deleted successfully',
                'data' => null // No data to return after deletion
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Borrow a book for a patron.
     *
     * @urlParam patronId integer required The ID of the patron borrowing the book. Example: 1
     * @urlParam bookId integer required The ID of the book to be borrowed. Example: 5
     *
     * @response 200 scenario="Success" {"message": "Book borrowed successfully"}
     * @response 404 scenario="Patron Not Found" {"error": "Patron not found"}
     * @response 400 scenario="Book Already Borrowed" {"error": "Book is already borrowed"}
     */
    public function borrowBook(Request $request, $patronId, $bookId)
    {
        $patron = Patron::find($patronId);

        if (!$patron) {
            return response()->json(['error' => 'Patron not found'], Response::HTTP_NOT_FOUND);
        }

        $borrowed = $this->patron_service->borrowBook($patron->id, $bookId);

        if ($borrowed) {
            return response()->json(['message' => 'Book borrowed successfully'], Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Book is already borrowed'], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Return a borrowed book from a patron.
     *
     * @urlParam patronId integer required The ID of the patron returning the book. Example: 1
     * @urlParam bookId integer required The ID of the book to be returned. Example: 5
     *
     * @response 200 scenario="Success" {
     *   "message": "Book returned successfully",
     *   "data": {
     *     "id": 5,
     *     "title": "The Great Gatsby",
     *     "patron_id": null,
     *     "returned_at": "2025-10-18T16:00:00Z"
     *   }
     * }
     */
    public function returnBook(Request $request, $patronId, $bookId)
    {
        $book = $this->patron_service->returnBook($patronId, $bookId);

        return response()->json([
            'message' => 'Book returned successfully',
            'data' => $book,
        ], Response::HTTP_OK);
    }
}
