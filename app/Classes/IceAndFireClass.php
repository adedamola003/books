<?php

namespace App\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class IceAndFireClass
{
    /**
     * @var string[]
     */
    private array $header;

    public function __construct()
    {
        $this->header = ["Authorization: No Auth","Content-Type: application/json"];
    }

    public function searchByString(string $key, string $value): array
    {
        $response = Http::withHeaders($this->header)->get("https://www.anapioficeandfire.com/api/books?$key=$value");
        $thisResponseData = $response->json();

        //check if array is empty
        if(empty($thisResponseData) ||!is_array($thisResponseData) || count($thisResponseData) < 1){
            return ['status' => false, 'message' => 'not found', 'data' => []];
        }

        return $this->formatGatewayData($thisResponseData);
    }

    public function searchByYear(string $year): array
    {
        $startOfYear = Carbon::now()->setYear($year)->startOfYear()->toDateString();
        $endOfYear = Carbon::now()->setYear($year)->endOfYear()->toDateString();
        $response = Http::withHeaders($this->header)->get("https://www.anapioficeandfire.com/api/books?fromReleaseDate=$startOfYear&toReleaseDate=$endOfYear");
        $thisResponseData = $response->json();

        //check if array is empty
        if(empty($thisResponseData) ||!is_array($thisResponseData) || count($thisResponseData) < 1){
            return ['status' => false, 'message' => 'not found', 'data' => []];
        }

        return $this->formatGatewayData($thisResponseData);
    }

    /**
     * @param array $thisResponseData
     * @return array
     */
    private function formatGatewayData(array $thisResponseData): array
    {
        $data = [];
        foreach ($thisResponseData as $thisData) {
            $data[] = [
                'name' => array_key_exists('name', $thisData) ? $thisData['name'] : '',
                'isbn' => array_key_exists('isbn', $thisData) ? $thisData['isbn'] : '',
                'authors' => array_key_exists('authors', $thisData) ? $thisData['authors'] : '',
                'number_of_pages' => array_key_exists('numberOfPages', $thisData) ? $thisData['numberOfPages'] : '',
                'publisher' => array_key_exists('publisher', $thisData) ? $thisData['publisher'] : '',
                'country' => array_key_exists('country', $thisData) ? $thisData['country'] : '',
                'release_date' => array_key_exists('released', $thisData) ? Carbon::parse($thisData['released'])->toDateString() : '',
            ];
        }

        return ['status' => true, 'message' => 'success', 'data' => $data];
    }
}
