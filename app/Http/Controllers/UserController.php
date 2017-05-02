<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Creamos las reglas de validación
        $rules = [
            'name'  => 'required|unique:users,name',
            'email' => 'required|unique:users,email',
            'password'  => 'required'
            ];

        try {
            // Ejecutamos el validador y en caso de que falle devolvemos la respuesta
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return [
                    'created' => false,
                    'errors'  => $validator->errors()->all()
                ];
            }
 
            $inputs = $request->all();
            $inputs['password'] = bcrypt($request->password);

            User::create( $inputs );

            return ['created' => true];
        } catch (Exception $e)
        {
            \Log::info('Error creating user: '.$e);
            return \Response::json(['created' => false], 500);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Creamos las reglas de validación
        $rules = [
            'name'  => 'required|unique:users,name,'.$id,
            ];

        try {
            // Ejecutamos el validador y en caso de que falle devolvemos la respuesta
            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return [
                    'updated' => false,
                    'errors'  => $validator->errors()->all()
                ];
            }
 
            $user = User::findOrFail($id);
            $user->update($request->all());
            return ['updated' => true];

        } catch (Exception $e)
        {
            \Log::info('Error updating user: '.$e);
            return \Response::json(['updated' => false], 500);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::destroy($id);
        return ['deleted' => true];
    }
}
