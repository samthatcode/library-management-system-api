<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Database\Eloquent\Collection;

class AuthorService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Retrieve all authors with their related books.
     *
     * @return \Illuminate\Database\Eloquent\Collection<Author>
     */
    public function getAllAuthors(): Collection
    {
        return Author::with('books')->get();
    }

    /**
     * Create a new author and attach their books.
     *
     * @param array $data The author data including first_name, last_name, and optional books array
     * @return \App\Models\Author
     */
    public function create(array $data): Author
    {
        $author = Author::create($data);

        if (isset($data['books']) && is_array($data['books'])) {
            $author->books()->attach($data['books']);
        }

        return $author;
    }

    /**
     * Update an existing author and sync their books.
     *
     * @param \App\Models\Author $author The author instance to update
     * @param array $data The updated author data
     * @return \App\Models\Author
     */
    public function update(Author $author, array $data)
    {
        $author->update($data);

        if (isset($data['books'])) {
            $author->books()->sync($data['books']);
        }

        return $author->fresh();
    }

    /**
     * Delete an author and detach all related books.
     *
     * @param \App\Models\Author $author The author instance to delete
     * @return bool
     */
    public function delete(Author $author): bool
    {
        // Check if the author has written any books
        if ($author->books()->exists()) {
            // Detach the author from all books
            $author->books()->detach();
        }

        // Delete the author
        return $author->delete();
    }
}
