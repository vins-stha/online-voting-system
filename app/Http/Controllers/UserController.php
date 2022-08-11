<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Exceptions\DuplicateResourceException;
use App\Models\User;
use ErrorException;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\MockObject\DuplicateMethodException;
use PHPUnit\Framework\MockObject\UnknownTypeException;
use Spatie\FlareClient\Http\Exceptions\NotFound;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserController extends Controller
{

    /**
     * Get all users
     */
    public function index()
    {
        $users  = User::all();
        return response()->json([
            'data' => $users,
            'code' => 200
        ]);
    }

    public function findById($id)
    {
        // try {
        $user = User::find($id);
        if (!$user)
            throw new NotFoundResourceException("User with id " . $id . " not found");
        return response()->json([
            'data' => $user,
            'code' => 200
        ]);
        // }catch (Exception $e){
        //     var_dump($e->getMessage());
        // };

    }

    public function create(Request $request)
    {
        $email = $request->get('email');
        if ($email) {
            $user = User::where('email', $email)
                ->value('id');
        }
  
        if ($user) {
            throw new DuplicateResourceException("Email already registered");
        } else {

            try {
                $user['name'] = $request->get('name');
                $user['email'] = $email;
                $user['password'] = Hash::make($request->get('password'));
                $newUser = new User($user);
                $newUser->save();
            } catch (Exception $e) {
                throw new CustomException($e->getMessage());
            }
        }

        return response()->json([
            'data' => $newUser->toJson(),
            'code' => 201
        ]);
    }
}
