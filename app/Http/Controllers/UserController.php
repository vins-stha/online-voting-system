<?php

namespace App\Http\Controllers;

use App\Exceptions\DuplicateDataException;
use App\Models\User;
use ErrorException;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use PHPUnit\Framework\MockObject\DuplicateMethodException;
use Spatie\FlareClient\Http\Exceptions\NotFound;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserController extends Controller
{

    /**
     * Get all users
     */
    public function index(){
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
            if(!$user)
             throw new NotFoundResourceException("User with id ".$id." not found");
            return response()->json([
                'data'=> $user,
                'code'=> 200
            ]);
        // }catch (Exception $e){
        //     var_dump($e->getMessage());
        // };
     
    }

    public function create(Request $request) {
        $email = $request->get('email');
        if($email)
        {
            $user = User::where('email', $email)
            ->value('id')
            ->get();
        }

        if($user)
        {
            throw new DuplicateDataException("Email already registered");
        }
    }
}
