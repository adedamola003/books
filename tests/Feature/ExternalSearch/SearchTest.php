<?php

namespace Tests\Feature\ExternalSearch;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchTest extends TestCase
{
    //use RefreshDatabase;

    //test that a user can search for a book by query parameter
    public function test_a_user_can_search_for_a_book()
    {
        $response = $this->get('/api/external-books?name=A Game of Thrones');
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
    }

    //test that a user cannot search for a book without query parameter
    public function test_a_user_cannot_search_for_a_book_without_query_parameter()
    {
        $response = $this->get('/api/external-books');
        $response->assertStatus(400);
        $this->assertEquals('No parameter was passed', $response['status']);
        $this->assertEquals('400', $response['status_code']);
    }

    //test that a user cannot search for a book with empty query parameter
    public function test_a_user_cannot_search_for_a_book_with_empty_query_parameter()
    {
        $response = $this->get('/api/external-books?name=');
        $response->assertStatus(400);
        $this->assertEquals('400', $response['status_code']);
    }

    //test that a user cannot search for a book with invalid query parameter
    public function test_a_user_cannot_search_for_a_book_with_invalid_query_parameter()
    {
        $response = $this->get('/api/external-books?owner=1234567890');
        $response->assertStatus(400);
        $this->assertEquals('400', $response['status_code']);
        $this->assertEquals('Invalid search parameter', $response['status']);
    }

    //test that a user cannot search with more than one query parameter
    public function test_a_user_cannot_search_with_more_than_one_query_parameter()
    {
        $response = $this->get('/api/external-books?name=A Game of Thrones&author=George R. R. Martin');
        $response->assertStatus(400);
        $this->assertEquals('400', $response['status_code']);
        $this->assertEquals('Only one parameter is allowed', $response['status']);
    }
}
