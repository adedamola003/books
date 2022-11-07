<?php

namespace App\Http\Controllers\Api\V1\External;

use App\Classes\IceAndFireClass;
use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SearchController extends BaseController
{
    private IceAndFireClass $iceAndFireService;

    public function __construct()
    {
        $this->iceAndFireService = new IceAndFireClass();
    }

    /**
     * @throws ValidationException
     */
    public function search(Request $request): JsonResponse
    {
        //get query parameters
        $queryParameter = $request->all();

        //validate number of query parameters
        if(count($queryParameter) > 1) return $this->sendError('Only one parameter is allowed', [], 400);
        if (count($queryParameter) < 1) return $this->sendError('No parameter was passed', [], 400);


        //get the first key in the array
        $key = array_key_first($queryParameter);

        if($key == 'name' || $key == 'country' || $key == 'publisher'){
            //validate request parameter
            $validator = Validator::make($request->all(), [
                $key => ['required', 'string']
            ]);
            if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());
            $validated = $validator->validated();

            $result = $this->iceAndFireService->searchByString($key, $validated[$key]);
        }
        elseif($key == 'year'){
            //validate request parameter
            $validator = Validator::make($request->all(), [
                'year' => ['required', 'date_format:Y', 'regex:/[0-9]{4}/', 'min:0001']
            ]);
            if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());
            $validated = $validator->validated();

            $result = $this->iceAndFireService->searchByYear($validated['year']);
        }
        else{
            return $this->sendError('Invalid search parameter', $queryParameter, 400);
        }

        if(!$result['status']) return $this->sendError($result['message'], $result['data'], 404);

        return $this->sendResponse($result['data'], 'success');
    }
}
