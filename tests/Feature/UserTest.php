<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
//    use DatabaseMigrations;
    use DatabaseTransactions;
    //use WithoutMiddleware;

    //Función para proveer de un usuario y el token de autentificación a cada test
    public function getLoginTokenAndNewUser() {

      //Creo el usuario en la BD
      $user = factory(\App\User::class)->create();

      //me logueo para obtener el token
      $data = array_merge( $user->toArray(), [ 'password' => 'secret', ] );

      $response = $this->post('/api/authenticate', $data )
                       ->assertStatus(200)
                       ->assertJsonStructure([ 'token' ] )
                       ;

      //Obtengo el token de logeo y lo añado como dato
      $tokenValue = $response->original['token'];

      return array ( $tokenValue, $user );
    }

    public function testUserRegister()
    {
      //Construyo un objeto User
      $user = factory(\App\User::class)->make();

      // Registramos el usuario
      $this->post('/api/register', $user->toArray())
           ->assertStatus(200)
           ->assertExactJson(['msg' => 'Success'])
           ;

      //Comprueba que el usuario registrado esta en la BD
      $this->assertDatabaseHas('users', ['name' => $user->name, 'email' => $user->email ] );
    }

    public function testUserIndex()
    {
      //get params
      list( $tokenValue, $user ) = $this->getLoginTokenAndNewUser();

      // Obtengo todos los usuarios
      $response = $this->json('GET', "/api/user?token=$tokenValue")
                       ->assertStatus(200)
                       ->assertJsonStructure(
                                              [
                                                [
                                                  "id",
                                                  "name",
                                                  "email",
                                                  "created_at",
                                                  "updated_at",
                                                ]
                                              ]
                                             )
                       ;
    }

    public function testUserStore()
    {
      //get params
      list($tokenValue, $user) = $this->getLoginTokenAndNewUser();

      // Creamos un nuevo usuario y verificamos la respuesta
      $data = $this->getData(['name' => 'jane', 'email'     => 'jane@doe.com']);
      $this->post("/api/user?token=$tokenValue", $data)
           ->assertStatus(200)
           ->assertExactJson(['created' => true])
           ;

      //Compruebo en la BD que no existe el usuario
      $this->assertDatabaseHas('users', ['name' => 'jane', 'email'     => 'jane@doe.com'] );
    }

    public function testUserShow()
    {
      //get params
      list($tokenValue, $user) = $this->getLoginTokenAndNewUser();

      //Comprobamos que al obtener el usuario su información sea correcta
      $this->get( '/api/user/'.$user->id."?token=$tokenValue" )
           ->assertStatus(200)
           ->assertJson( $user->toArray() )
           ;
    }

    public function testUserUpdate()
    {
      //get params
      list($tokenValue, $user) = $this->getLoginTokenAndNewUser();

      $user->name = "usernameUpdate";
      $data = array_merge( $user->toArray(), [ 'password' => 'secret', ] );

      // Actualizamos el usuario
      $this->put('/api/user/'.$user->id."?token=$tokenValue", $data )
           ->assertStatus(200)
           ->assertExactJson(['updated' => true])
           ;

      //Compruebo en la BD que existe un usuario con el nombre modificado
      $this->assertDatabaseHas('users', [
        'name' => 'usernameUpdate'
      ]);
    }

    public function testUserDestroy()
    {
      //get params
      list($tokenValue, $user) = $this->getLoginTokenAndNewUser();

      // Eliminamos al usuario
      $this->delete('/api/user/'.$user->id."?token=$tokenValue")
           ->assertStatus(200)
           ->assertExactJson(['deleted' => true])
           ;

      //Compruebo en la BD que no existe el usuario
      $this->assertDatabaseMissing('users', [
        'id' => $user->id
      ]);
    }

    public function getData($custom = array())
    {
        $data = [
            'name'      => 'joe',
            'email'     => 'joe@doe.com',
            'password'  => '12345'
            ];
        $data = array_merge($data, $custom);
        return $data;
    }
}
