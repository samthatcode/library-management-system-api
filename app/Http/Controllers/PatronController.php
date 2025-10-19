<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatronRequest;
use App\Http\Resources\PatronResource;
use App\Models\Patron;
use App\Services\PatronService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatronController extends Controller
{
    protected $patron_service;

    public function __construct(PatronService $patron_service)
    {
        $this->patron_service = $patron_service;
    }
    /**
     * Patrons
     * Point 2: Retrieving all Patrons
     */
    public function index()
    {
        return response()->json(
            PatronResource::collection($this->patron_service->getPatronsWithBooks()),
            Response::HTTP_OK
        );
    }

    /**
     * Patrons
     * Point 2: Storing Patrons
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
     * Patrons
     * Point 2: Updating Patrons
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
     * Patrons
     * Point 2: Deleting Patrons
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
     * Patrons
     * Point 3: Borrow Book
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
     * Patrons
     * Point 4: Return Book
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
