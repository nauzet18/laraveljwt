<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

class AuthenticateController extends Controller
{
	public function __construct()
  {
     // Apply the jwt.auth middleware to all methods in this controller
     // except for the authenticate method. We don't want to prevent
     // the user from retrieving their token if they don't already have it
     $this->middleware('jwt.auth', ['except' => ['authenticate', 'singup']]);
  }
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
      $users = User::all();
      return $users;
  }

  public function authenticate(Request $request)
  {
      $credentials = $request->only('name', 'password');

      try {
          // verify the credentials and create a token for the user
          if (! $token = JWTAuth::attempt($credentials)) {
              return response()->json(['error' => 'El usuario no existe'], 401);
          }
      } catch (JWTException $e) {
          // something went wrong
          return response()->json(['error' => 'Error al crear el authToken'], 500);
      }

      // if no errors are encountered we can return a JWT
      return response()->json(compact('token'));
  }
	
	public function singup(Request $request)
  {
		try {
			$user = User::where('name', '=', $request->name)->count();
			
			if ($user == 0)
			{
				$user = new User();
				$user->name = $request->name;
				$user->email = $request->email;
				$user->password = bcrypt($request->password);

				$user->save();
			}
			else
			{
				return response()->json(['error' => 'El usuario ya existe'], 400);
			}
		} 
		catch (Exception $e) {
      return response()->json(['error' => 'User already exists.'], 400);
		} 
		return response()->json(['msg' => 'Success'], 200);
  }
	
	public function getAuthenticatedUser()
  {
		try {
          if (! $user = JWTAuth::parseToken()->authenticate())
          {
          	return response()->json(['user_not_found'], 404);
          }
        } 
        catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) 
        {
          return response()->json(['token_expired'], $e->getStatusCode());
        } 
        catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) 
        {
        	return response()->json(['token_invalid'], $e->getStatusCode());
        } 
        catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

        	return response()->json(['token_absent'], $e->getStatusCode());
        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }
}