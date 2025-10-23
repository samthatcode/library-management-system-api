<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Patron;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PatronService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Retrieve all patrons with their borrowed books.
     *
     * @return \Illuminate\Database\Eloquent\Collection<Patron>
     */
    public function getPatronsWithBooks(): Collection
    {
        return Patron::with('books')->get();
    }

    /**
     * Create a new patron.
     *
     * @param array $data The patron data including name, email, and phone
     * @return \App\Models\Patron
     */
    public function create(array $data)
    {
        return Patron::create($data);
    }

    /**
     * Update an existing patron.
     *
     * @param array $data The updated patron data
     * @param \App\Models\Patron $patron The patron instance to update
     * @return \App\Models\Patron
     */
    public function update(array $data, Patron $patron): Patron
    {
        $patron->update($data);

        return $patron->refresh();
    }

    /**
     * Delete a patron.
     *
     * Patrons with associated books cannot be deleted.
     *
     * @param int $patronId The ID of the patron to delete
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If patron not found
     * @throws \Exception If patron has associated books
     */
    public function deletePatron($patronId)
    {
        $patron = Patron::findOrFail($patronId);

        // Check if the patron has any associated books
        if ($patron->books()->exists()) {
            throw new \Exception('Patron cannot be deleted because they have associated books.');
        }

        // Delete the patron if no associated books exist
        $patron->delete();
    }

    /**
     * Allow a patron to borrow a book.
     *
     * Sets the borrowed_at timestamp and calculates a due_back date (14 days from borrowing).
     *
     * @param int $patronId The ID of the patron borrowing the book
     * @param int $bookId The ID of the book to be borrowed
     * @return bool True if borrowed successfully, false if book is already borrowed
     */
    public function borrowBook($patronId, $bookId)
    {
        $book = Book::find($bookId);

        if (!$book->patron_id) {
            $book->update([
                'patron_id' => $patronId,
                'borrowed_at' => Carbon::now(),
                'due_back' => Carbon::now()->addDays(14), // Example: due back in 14 days
            ]);

            return true; // Successfully borrowed
        }

        return false; // Book is already borrowed
    }

    /**
     * Process the return of a borrowed book from a patron.
     *
     * Sets the returned_at timestamp to mark when the book was returned.
     *
     * @param int $patronId The ID of the patron returning the book
     * @param int $bookId The ID of the book being returned
     * @return \App\Models\Book
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If book not found or not borrowed by this patron
     */
    public function returnBook($patronId, $bookId)
    {
        $book = Book::where('patron_id', $patronId)
            ->findOrFail($bookId);

        $book->returned_at = Carbon::now();
        $book->save();

        return $book;
    }
}
