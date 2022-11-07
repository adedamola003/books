<?php

namespace App\Classes;

use App\Models\Book;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InternalBookClass
{
    private Book $book;

    public function __construct()
    {
        $this->book = new Book();
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function addBook($request): array
    {
        $messages = [
            'isbn.unique' => 'A book already exists with this ISBN',
        ];
        //validate request
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'authors' => ['required', 'string', 'max:255'],
            'release_date' => ['required', 'date_format:Y-m-d'],
            'isbn' => ['required', 'string', 'max:255', 'unique:books'],
            'number_of_pages' => ['required', 'integer', 'min:1'],
            'country' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
        ], $messages);
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validation Error.', 'data' => $validator->messages()];
        }
        $validated = $validator->validated();

        //authors string to array
        $authors = explode(',', $validated['authors']);

        //validate each author
        foreach ($authors as $key => $author){
            $validator = Validator::make(['author' => $author], [
                'author' => ['required', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                return ['status' => false, 'message' => 'Validation Error.', 'data' => $validator->messages()];
            }
            $authors[$key] = formatSentenceCase($author);
        }

        //store book to database
        $thisBook = $this->book::create([
            'name' => formatSentenceCase($validated['name']),
            'isbn' => $validated['isbn'],
            'authors' => json_encode($authors),
            'number_of_pages' => (int)$validated['number_of_pages'],
            'publisher' => formatSentenceCase($validated['publisher']),
            'country' => formatSentenceCase($validated['country']),
            'release_date' => $validated['release_date'],
        ]);

        if(!$thisBook) return ['status' => false, 'message' => 'Book could not be added', 'data' => []];

        $data['book'] = [
            'name' => $thisBook->name,
            'isbn' => $thisBook->isbn,
            'authors' => $authors,
            'number_of_pages' => $thisBook->number_of_pages,
            'publisher' => $thisBook->publisher,
            'country' => $thisBook->country,
            'release_date' => $thisBook->release_date,
        ];

        return ['status' => true, 'message' => 'success', 'data' => $data];
    }

    public  function getAllBooks(): array
    {
        $books = $this->book::all();

        return $this->sortBooksData($books);
    }

    public function getAllBooksByKeyValue($key, $value): array
    {

        if($key == 'name' || $key == 'country' || $key == 'publisher'){
            //validate request parameter
            $validator = Validator::make([$key => $value], [
                $key => ['required', 'string', 'max:255'],
            ]);
            if ($validator->fails()) return ['status' => false, 'message' => 'Validation Error.', 'data' => $validator->messages()];

            $books = $this->book::where([$key => $value])->get();
        }
        elseif($key = 'year'){
            $validator = Validator::make([$key => $value], [
                $key => ['required', 'date_format:Y', 'regex:/[0-9]{4}/', 'min:0001']
            ]);
            if ($validator->fails()) return ['status' => false, 'message' => 'Validation Error.', 'data' => $validator->messages()];
            $startOfYear = Carbon::now()->setYear($value)->startOfYear()->toDateString();
            $endOfYear = Carbon::now()->setYear($value)->endOfYear()->toDateString();
            $books = $this->book::whereBetween('release_date', [$startOfYear, $endOfYear])->get();
        }
        else{
            return ['status' => false, 'message' => 'Invalid search parameter', 'data' => ['key' => $value]];
        }


        return $this->sortBooksData($books);
    }

    public function getBook($id): array
    {
        $book = $this->book::find($id);
        if(!$book) return ['status' => false, 'message' => 'Book not found', 'data' => []];

        $data = [
            'id' => $book->id,
            'name' => $book->name,
            'isbn' => $book->isbn,
            'authors' => json_decode($book->authors),
            'number_of_pages' => $book->number_of_pages,
            'publisher' => $book->publisher,
            'country' => $book->country,
            'release_date' => $book->release_date,
        ];

        return ['status' => true, 'message' => 'success', 'data' => $data];
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function updateBook($request, $id): array
    {
        $thisBook = $this->book::find($id);
        if(!$thisBook) return ['status' => false, 'message' => 'Book not found', 'data' => []];
        //validate request
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'authors' => ['nullable', 'string', 'max:255'],
            'release_date' => ['nullable', 'date_format:Y-m-d'],
            'isbn' => ['nullable', 'string', 'max:255', 'unique:books'],
            'number_of_pages' => ['nullable', 'integer', 'min:1'],
            'country' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return ['status' => false, 'message' => 'Validation Error.', 'data' => $validator->messages()];
        }
        $validated = $validator->validated();

        //validate authors
        if(isset($validated['authors'])){
            $authors = explode(',', $validated['authors']);
            //validate each author
            foreach ($authors as $key => $author){
                $validator = Validator::make(['author' => $author], [
                    'author' => ['required', 'string', 'max:255'],
                ]);
                if ($validator->fails()) {
                    return ['status' => false, 'message' => 'Validation Error.', 'data' => $validator->messages()];
                }
                $authors[$key] = formatSentenceCase($author);
            }
            $authors = json_encode($authors);
        }


        $name = $validated['name'] ?? $thisBook->name;
        $isbn = $validated['isbn'] ?? $thisBook->isbn;
        $authors = array_key_exists('authors', $validated) ? $authors : $thisBook->authors;
        $number_of_pages = $validated['number_of_pages'] ?? $thisBook->number_of_pages;
        $publisher = $validated['publisher'] ?? $thisBook->publisher;
        $country = $validated['country'] ?? $thisBook->country;
        $release_date = $validated['release_date'] ?? $thisBook->release_date;

        //update book to database
        $thisBookUpdate = $thisBook->update([
            'name' => $name,
            'isbn' => $isbn,
            'authors' => $authors,
            'number_of_pages' => $number_of_pages,
            'publisher' => $publisher,
            'country' => $country,
            'release_date' => $release_date,
        ]);

        if(!$thisBookUpdate) return ['status' => false, 'message' => 'Book could not be updated', 'data' => []];

        $data['book'] = [
            'name' => $name,
            'isbn' => $isbn,
            'authors' => json_decode($authors),
            'number_of_pages' => $number_of_pages,
            'publisher' => $publisher,
            'country' => $country,
            'release_date' => $release_date,
        ];

        return ['status' => true, 'message' => ' The book ' .$thisBook->name. ' was updated successfully', 'data' => $data];


    }

    public function deleteBook($id): array
    {
        $thisBook = $this->book::find($id);
        if(!$thisBook) return ['status' => false, 'message' => 'Book not found', 'data' => []];

        $thisBookDelete = $thisBook->delete();
        if(!$thisBookDelete) return ['status' => false, 'message' => 'Error deleting book, try again', 'data' => []];

        return ['status' => true, 'message' => ' The book ' .$thisBook->name. ' was deleted successfully', 'data' => []];
    }

    /**
     * @param $books
     * @return array
     */
    private function sortBooksData($books): array
    {
        $data = [];
        foreach ($books as $book) {
            $data[] = [
                'id' => $book->id,
                'name' => $book->name,
                'isbn' => $book->isbn,
                'authors' => json_decode($book->authors),
                'number_of_pages' => $book->number_of_pages,
                'publisher' => $book->publisher,
                'country' => $book->country,
                'release_date' => $book->release_date,
            ];
        }

        return ['status' => true, 'message' => 'success', 'data' => $data];
    }
}
