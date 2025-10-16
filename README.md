# Library Management System API

## Overview

This README file provides instructions on how to set up and run the project, along with explanations about the code structure and implementation details.

## Setup Instructions

### Prerequisites

-   PHP >= 11
-   Composer
-   PgSQL database

### Installation Steps

1. Clone the repository to your local machine:

    git clone git@github.com:samthatcode/library-management-system-api.git

2. Navigate to the project directory:

    cd project-name

3. Install PHP dependencies using Composer:

    composer install

4. Copy the `.env.example` file and rename it to `.env`:

    cp .env.example .env

5. Generate an application key:

    php artisan key:generate

6. Configure your database settings in the `.env` file:

    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=LMS_API
    DB_USERNAME=postgres
    DB_PASSWORD=

7. Run database migrations to create the necessary tables:

    php artisan migrate

8. (Optional) Seed the database with sample data:

    php artisan db:seed

9. Serve the application locally:

    php artisan serve

10. Access the application in your web browser at `library-management-system-api.test/`.

## Code Explanations

-   Services Classes have been used to include main business logics

-   Controllers use Resource and Collection to test the API

-   JSON responses have been used to see the response of an endpoint

## API End Points

-   Books:

1. List all books: GET library-management-system-api.test/api/v1/books
2. Create a new book: POST library-management-system-api.test/api/v1/books
3. Update a book: PUT library-management-system-api.test/api/v1/books/{id}
4. Delete a book: DELETE library-management-system-api.test/api/v1/books/{id}

-   Authors:

1. List all authors: GET library-management-system-api.test/api/v1/authors
2. Create a new author: POST library-management-system-api.test/api/v1/authors
3. Update an author: PUT library-management-system-api.test/api/v1/authors/{id}
4. Delete an author: DELETE library-management-system-api.test/api/v1/authors/{id}

-   Patrons:

1. List all patrons: GET library-management-system-api.test/api/v1/patrons
2. Create a new patron: POST library-management-system-api.test/api/v1/patrons
3. Update a patron: PUT library-management-system-api.test/api/v1/patrons/{id}
4. Delete a patron: DELETE library-management-system-api.test/api/v1/patrons/{id}

-   Book Search:

1. Search for books: GET library-management-system-api.test/api/v1/books/search?keyword={keyword}

Replace {keyword} with the search term or keyword.

-   Fetch Books by Author:

1. List books by author: GET library-management-system-api.test/api/v1/authors/{author}/books

Replace {author} with the ID or slug of the author.

-   Borrow Book:

1. Borrow a book for a patron: POST library-management-system-api.test/api/v1/patrons/{patronId}/books/{bookId}/borrow

Replace {patronId} with the ID of the patron and {bookId} with the ID of the book.

-   Return Book:

1. Return a borrowed book: POST library-management-system-api.test/api/v1/patrons/{patronId}/books/{bookId}/return

Replace {patronId} with the ID of the patron and {bookId} with the ID of the book.

These endpoints follow the structure of the provided Laravel routes and are prefixed with /api/v1/ as specified in the routes/api.php file. Adjust the base URL (library-management-system-api.test/) based on your actual development environment.

### API Versioning

-   The API versioning is implemented using URL-based versioning in the `routes/api.php` file.
-   Each API version has its own route group prefixed with the version number (e.g., `/v1`).
-   Middleware such as throttle rate limiting (`throttle:3,10`) is applied to specific routes or route groups to control the rate of incoming requests.

### Caching Strategy

-   Caching is implemented using Laravel's caching mechanisms (`Cache::remember`) in service classes and controllers.
-   Frequently accessed data, such as book listings, is cached to optimize response times and reduce database queries.

## Additional Notes

-   Modify the `.env` file to suit your local development environment, including database settings, cache driver, and other configuration options.
-   Use appropriate namespace and controller names based on your actual project structure.
-   Refer to Laravel's official documentation for more information on Laravel installation, configuration, and best practices.
