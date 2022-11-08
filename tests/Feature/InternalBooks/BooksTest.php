<?php

namespace InternalBooks;

use App\Models\Book;
use App\Models\User;
use Tests\TestCase;

class BooksTest extends TestCase
{
    public function loginUser(){
        $user = User::factory()->create();

        $response = $this->post('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $response['data']['accessToken'];

    }
   //test that an authenticated user cannot add books
    public function test_authenticated_user_cannot_add_books()
    {
        $response = $this->post('/api/v1/books', [
            "name" => "The Game",
            "authors" => "adeyinka adedamola,adeyinka adetayo",
            "release_date" => "2021-01-01",
            "isbn" => "124-840292825",
            "number_of_pages" => "45",
            "country" => "Nigeria",
             "publisher" => "The house"
        ]);



        $response->assertStatus(401);
        $response->assertUnauthorized();
        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Unauthenticated', $response['message']);
    }

    //test that an authenticated user can add books
    public function test_authenticated_user_can_add_books()
    {

        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->post('/api/v1/books', [
            "name" => "The Game",
            "authors" => "adeyinka adedamola,adeyinka adetayo",
            "release_date" => "2021-01-01",
            "isbn" => date('Ymdhis').'-'.rand(100000000,999999999),
            "number_of_pages" => "45",
            "country" => "Nigeria",
            "publisher" => "The house"
        ]);


        $this->assertAuthenticated();
        $response->assertStatus(201);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(201, $response['status_code']);
    }

    //test an authenticated user can view all books
    public function test_authenticated_user_can_view_all_books()
    {
        $book = Book::factory()->create();
        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->get('/api/v1/books');

        $this->assertAuthenticated();
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(200, $response['status_code']);
    }

    //test an authenticated user can view books by querying parameter
    public function test_authenticated_user_can_view_books_by_querying_parameter()
    {
        $book = Book::factory()->create();
        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->get('/api/v1/books?name=The Game');

        $this->assertAuthenticated();
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(200, $response['status_code']);
    }

    //test an authenticated user cannot view books by querying parameter with more than 1 parameter
    public function test_authenticated_user_cannot_view_books_by_querying_parameter_with_more_than_1_parameter()
    {
        $book = Book::factory()->create();
        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->get('/api/v1/books?name=The Game&country=Nigeria');

        $this->assertAuthenticated();
        $response->assertStatus(400);
        $this->assertEquals('Only one parameter is allowed', $response['status']);
        $this->assertEquals(400, $response['status_code']);
    }



    //test an authenticated user can view a book by id
    public function test_authenticated_user_can_view_a_book_by_id()
    {
        $book = Book::factory()->create();
        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->get('/api/v1/books/'.$book->id);

        $this->assertAuthenticated();
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(200, $response['status_code']);
    }

    //test an authenticated user can update a book by id
    public function test_authenticated_user_can_update_a_book_by_id()
    {
        $book = Book::factory()->create();
        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->PATCH('/api/v1/books/'.$book->id, [
            "name" => "The Game",
            "authors" => "adeyinka adedamola,adeyinka adetayo",
            "release_date" => "2021-01-01",
            "isbn" => fake()->isbn13(),
            "number_of_pages" => "45",
            "country" => "Nigeria",
            "publisher" => "The house"
        ]);

        $this->assertAuthenticated();
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(200, $response['status_code']);
    }

    //test an authenticated user cannot update a book by id with an isbn that already exists
    public function test_authenticated_user_cannot_update_a_book_by_id_with_an_isbn_that_already_exists()
    {
        $book = Book::factory()->create();
        $book2 = Book::factory()->create();
        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->PATCH('/api/v1/books/'.$book->id, [
            "name" => "The Game",
            "authors" => "adeyinka adedamola,adeyinka adetayo",
            "release_date" => "2021-01-01",
            "isbn" => $book2->isbn,
            "number_of_pages" => "45",
            "country" => "Nigeria",
            "publisher" => "The house"
        ]);

        $this->assertAuthenticated();
        $response->assertStatus(400);
        $this->assertEquals('The isbn has already been taken.', $response['status']);
        $this->assertEquals(400, $response['status_code']);
    }

    //test an authenticated user can delete a book by id
    public function test_authenticated_user_can_delete_a_book_by_id()
    {
        $book = Book::factory()->create();
        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->delete('/api/v1/books/'.$book->id);

        $this->assertAuthenticated();
        $response->assertStatus(200);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(204, $response['status_code']);
    }





}
