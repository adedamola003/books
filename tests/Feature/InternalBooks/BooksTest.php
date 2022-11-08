<?php

namespace InternalBooks;

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

    //test that an authenticated user can publish messages
    public function test_authenticated_user_can_publish_messages()
    {

        $token = $this->loginUser();
        $response = $this->withHeader('Authorization', 'Bearer '.$token)->post('/api/v1/books', [
            "name" => "The Game",
            "authors" => "adeyinka adedamola,adeyinka adetayo",
            "release_date" => "2021-01-01",
            "isbn" => "124-840292825",
            "number_of_pages" => "45",
            "country" => "Nigeria",
            "publisher" => "The house"
        ]);


        $this->assertAuthenticated();
        $response->assertStatus(201);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals(201, $response['status_code']);
    }


}
