<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class BookService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Retrieve all books with their related authors.
     *
     * Results are cached for 60 minutes to improve performance.
     *
     * @return \Illuminate\Database\Eloquent\Collection<Book>
     */
    public function getAllBooks(): Collection
    {
        return Cache::remember('all_books', 60, function () {
            return Book::with('authors')->get(['id', 'title', 'description', 'isbn', 'publication_date']);
        });
    }

    /**
     * Create a new book and attach authors.
     *
     * The created book is cached for 60 minutes.
     *
     * @param array $bookData The book data including title, description, isbn, and publication_date
     * @param array $authorIds Array of author IDs to attach to the book
     * @return \App\Models\Book
     */
    public function store(array $bookData, array $authorIds): Book
    {
        return Cache::remember('created_book', 60, function () use ($bookData, $authorIds) {
            $book = Book::create($bookData);
            $book->authors()->attach($authorIds); // Attach authors to the book

            return $book;
        });
    }

    /**
     * Update an existing book and sync its authors.
     *
     * The updated book is cached for 60 minutes.
     *
     * @param array $bookData The updated book data
     * @param \App\Models\Book $book The book instance to update
     * @return \App\Models\Book
     */
    public function update(array $bookData, Book $book): Book
    {
        // Update book attributes
        $book->update($bookData);

        // Sync authors for the updated book
        if (isset($bookData['authors']) && is_array($bookData['authors'])) {
            $book->authors()->sync($bookData['authors']);
        }

        // Cache the updated book data
        Cache::put('book_' . $book->id, $book, 60); // Cache for 60 minutes

        return $book->fresh(); // Return the fresh instance of the book
    }

    /**
     * Delete a book and detach all related authors.
     *
     * Books that are currently borrowed cannot be deleted.
     *
     * @param \App\Models\Book $book The book instance to delete
     * @return bool True if deleted successfully, false if book is borrowed
     */
    public function delete(Book $book): bool
    {
        // Check if the book is currently borrowed
        if (Cache::has('book_borrowed_' . $book->id)) {
            return false; // Book cannot be deleted if borrowed
        }

        $book->authors()->detach(); // Detach book from authors
        $deleted = $book->delete();

        if ($deleted) {
            // Cache the fact that the book is not borrowed
            Cache::put('book_borrowed_' . $book->id, false, 60); // Cache for 60 minutes
        }

        return $deleted;
    }

    /**
     * Search for books by title and author.
     *
     * Results are cached for 60 minutes using a unique cache key based on search parameters.
     *
     * @param string $title The title of the book to search for
     * @param \Illuminate\Database\Eloquent\Collection $authors Collection of Author models to filter by
     * @return \Illuminate\Database\Eloquent\Collection<Book>
     */
    public function searchByTitleAndAuthor(string $title, $authors)
    {
        $cacheKey = 'search_' . md5($title . serialize($authors));

        return Cache::remember($cacheKey, 60, function () use ($title, $authors) {
            return Book::where('title', $title)
                ->whereHas('authors', function ($query) use ($authors) {
                    $query->whereIn('authors.id', $authors->pluck('id')->toArray());
                })
                ->with('authors:id,first_name,last_name') // Eager loading the authors with specific columns
                ->get(['id', 'title']);
        });
    }

    /**
     * Fetch all books written by a specific author.
     *
     * Results are cached for 60 minutes per author.
     *
     * @param \App\Models\Author $author The author whose books to retrieve
     * @return \Illuminate\Database\Eloquent\Collection<Book>
     */
    public function fetchBooksByAuthor(Author $author)
    {
        $cacheKey = 'author_books_' . $author->id;

        return Cache::remember($cacheKey, 60, function () use ($author) {
            return $author->books()->get();
        });
    }
}
