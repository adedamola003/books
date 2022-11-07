<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Classes\InternalBookClass;
use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookController extends BaseController
{
    private InternalBookClass $bookService;

    public function __construct()
    {
        $this->bookService = new InternalBookClass();
    }

    public function index(Request $request): JsonResponse
    {
        //get query parameters
        $queryParameter = $request->all();

        //validate number of query parameters
        if(count($queryParameter) > 1) return $this->sendError('Only one parameter is allowed', [], 400);
        //query for all books
        if (count($queryParameter) == 0){
            $getAllBooks = $this->bookService->getAllBooks();
        }
        //query based on parameter passed
        else{
            //get the first key in the array
            $key = array_key_first($queryParameter);
            $getAllBooks = $this->bookService->getAllBooksByKeyValue(array_key_first($queryParameter), $request->$key);
        }
        //query based on parameter passed


        return $this->sendResponse($getAllBooks['data'], $getAllBooks['message'], 200);
    }

    public function show($id): JsonResponse
    {
        $getBook = $this->bookService->getBook($id);
        if(!$getBook['status']) return $this->sendError($getBook['message'], $getBook['data'], 404);

        return $this->sendResponse($getBook['data'], $getBook['message'], 200);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $thisAddBook = $this->bookService->addBook($request);
        if(!$thisAddBook['status']) return $this->sendError($thisAddBook['message'], $thisAddBook['data'], 400);

        return $this->sendResponse($thisAddBook['data'], $thisAddBook['message'], 201);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $thisUpdateBook = $this->bookService->updateBook($request, $id);
        if(!$thisUpdateBook['status']) return $this->sendError($thisUpdateBook['message'], $thisUpdateBook['data'], 400);

        return $this->sendResponse($thisUpdateBook['data'], $thisUpdateBook['message'], 200);
    }

    public function destroy($id): JsonResponse
    {
        $thisDeleteBook = $this->bookService->deleteBook($id);
        if(!$thisDeleteBook['status']) return $this->sendError($thisDeleteBook['message'], $thisDeleteBook['data'], 400);

        return $this->sendResponse($thisDeleteBook['data'], $thisDeleteBook['message'], 204);
    }
}
