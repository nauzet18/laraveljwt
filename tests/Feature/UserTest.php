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
//    use WithoutMiddleware;

    public function testUserCreate()
    {
        $data = $this->getData();


        // Registramos un usuario
        $this->post('/api/register', $data)
             ->assertExactJson(['msg' => 'Success']);

        // Nos logueamos con dicho usuario
        $response = $this->post('/api/authenticate', $data)
        								 ->assertJsonStructure([ 'token' ] )
                 				 ->assertStatus(200);

        //Obtengo el token de logeo y lo añado como dato
        $tokenValue = $response->original['token'];

        // Creamos un nuevo usuario y verificamos la respuesta
        $data = $this->getData(['name' => 'jane', 'email'     => 'jane@doe.com']);
        $this->post("/api/user?token=$tokenValue", $data)
             ->assertExactJson(['created' => true])
             ->assertStatus(200);

        // Obtengo todos los usuarios
        //$response = $this->json('GET', "/api/user?token=$tokenValue")
        $response = $this->json('GET', "/api/user")
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
				                 ->assertStatus(200);

        //Saco el ID del primer usuario creado.
        $userId = $response->original[0]['id'];

 
        $data = $this->getData(['name' => 'joe2']);
        // Actualizamos el primer usuario  creado
        $this->put('/api/user/'.$userId, $data)
             ->assertExactJson(['updated' => true])
             ->assertStatus(200);
 
 
        // Obtenemos los datos de dicho usuario modificado
        // y verificamos que el nombre sea el correcto
        $this->get('/api/user/'.$userId)
        		 ->assertJson(['name' => 'joe2'])
        		 ->assertStatus(200);
 
        // Eliminamos al usuario
        $this->delete('/api/user/'.$userId)
        		 ->assertExactJson(['deleted' => true])
        		 ->assertStatus(200);
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
