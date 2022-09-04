<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Exceptions\DuplicateResourceException;
use App\Models\Answer;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{

    /**
     * Get all users
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            'data' => $users,
        ]);
    }

    public function findById($id)
    {
        try {
            $user = User::find($id);
            if (!$user)
                throw new NotFoundResourceException("User with id " . $id . " not found");
            return response()->json([
                'data' => $user,
            ]);
        } catch (Exception $e) {
            var_dump($e->getMessage());
        };
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
        ]);
    }

    public function update(Request $request, $id)
    {

        $user = User::find($id);

        if (!$user) {
            throw new NotFoundResourceException("User not registered yet.");
        } else {

            try {
                $user['name'] = $request->get('name') ? $request->get('name') : $user['name'];
                $user['email'] = $request->get('email') ? $request->get('email') : $user['email'];;
                $user['password'] = $request->get('password') ? Hash::make($request->get('password')) : $user['password'];
                $user->save();
                return response()->json([
                    'data' => $user,
                ]);
            } catch (Exception $e) {
                throw new CustomException($e->getMessage());
            }
        }
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user)
            throw new NotFoundResourceException("User not found.");
        else {
            $user->delete();

            return response()->json([
                'data' => "User deleted."
            ]);
        }
    }

    public function updateCounter($userId, Request $request, $data = [])
    {
        if (self::userExists($userId)) {
            $user = User::find($userId);
            $data = json_decode($request->getContent());
            foreach ($data as $k => $v) {
                if (in_array($k, CustomConstants::USERCOUNTERS)) {
                    $user[$k] = $user[$k] + 1;
                }

                $user->save();
            }
        } else
            throw new NotFoundResourceException("User not found.");
    }

    public static function userExists($id)
    {
        if ($id) {
            $user = User::find($id);
            if ($user)
                return true;
        }
        return false;
    }

    public function getUserId(Request $request)
    {
        $user_id = $request->user()->id;
        return response()->json([
            'user_id' => $user_id
        ]);
    }

    // get number of answers by a user
    public static function userPoints(Request $request, $id)
    {
        $uid = $request->get('id');
        $count = DB::table('answers')
            ->where('user_id', $id)
            ->count();

        $user = User::find($id);
        $upvotes = $user->up_votes_received;
        $downvotes = $user->down_votes_received;

        return [
            'answers_count' => $count,
            'uid' => $request->get('id'),
            'total_points' => $count + $upvotes - $downvotes,
            'min' => User::USER_MINIMUM_POINTS
        ];
    }
}
