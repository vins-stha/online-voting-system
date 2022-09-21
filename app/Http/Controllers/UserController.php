<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Exceptions\DuplicateResourceException;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Validator;

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

    public function findById(Request $request, $id)
    {
        try {
            $user = User::find($id);
            $questions = Question::where('user_id', $id)->get();
            // ->paginate(3);

            if (!$user)
                throw new NotFoundResourceException("User with id " . $id . " not found");
            return response()->json([
                'data' => $user,
                'questions' => $questions,
                'answers' => self::userPoints($request, $id),
                'stat'=> self::userSummary($id),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ],400);
        };
    }

    public function getDetailedInformation(Request $request){

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

                if ($request->file()) {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'avatar' => 'mimes:jpg,jpeg,png,bmp|max:2048'
                        ]
                    );

                    if ($validator->fails()) {
                        return response()->json([
                            'Validation_Error' => "Unsupported file format for 'avatar'",
                        ]);
                    }

                    $fileName = "user_" . $request->get('name') . "_avatar";

                    $filePath = $request->file('avatar')->storePubliclyAs('avatars', $fileName, 'public');
                    $user['avatar'] = '/storage/' . $filePath;
                }
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

    public function update(Request $request, $id=null)
    {
        $user = User::find($id);

        if (!$user) {
            throw new NotFoundResourceException("User not registered yet.");
        } else {
            try {
                $user['name'] = $request->get('name') ? $request->get('name') : $user['name'];
                $user['email'] = $request->get('email') ? $request->get('email') : $user['email'];;
                $user['password'] = $request->get('password') ? Hash::make($request->get('password')) : $user['password'];
                if ($request->file()) {
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'avatar' => 'mimes:jpg,jpeg,png,bmp|max:2048'
                        ]
                    );

                    if ($validator->fails()) {
                        return response()->json([
                            'Validation_Error' => "Unsupported file format for 'avatar'",
                        ]);
                    }

                    $fileName = "user_" . $id . "_avatar.".$request->file('avatar')->extension();

                    Storage::delete("user_" . $request->get('name') . "_avatar");

                    $request->file('avatar')->move(public_path('images/avatars'), $fileName);

                    $user['avatar'] = asset("images/avatars/".$fileName);
                }
                $user->save();
//                return view('update', ['data' => $user]);
                return response()->json([
                    'data' => $user,
                ]);
            } catch (Exception $e) {
                throw new CustomException("Exception occured $e. " . $e->getMessage());
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


    public function userSummary($id)
    {
        $questions = Question::where('user_id', $id)->get();
        $answers = Answer::where('user_id', $id)->get();

        $votes = [];
        $votes['up_votes_received'] =  $votes['down_votes_received'] =  $votes['up_voted_answer']  =  $votes['down_voted_answer'] = 0;

         $votes['votedQuestions'] = DB::table('question_user')->where('user_id', $id)->count();
        $votes['votedAnswers'] = DB::table('answer_user')->where('user_id', $id)->count();

        if ($questions) {
            foreach ($questions as $question) {
                $votes['up_votes_received'] += $question->vote_counts;
                $votes['down_votes_received'] += $question->down_votes;
            }
        }

        if ($answers) {
            foreach ($answers as $answer) {
                $votes['up_voted_answer'] += $answer->up_vote_counts;
                $votes['down_voted_answer'] += $answer->down_vote_count;
            }
        }
        $contribution = [];
        $contribution['questions'] = count($questions);
        $contribution['answers'] = count($answers);

        $userStat = [
            'votes' => $votes,
            'contribution' => $contribution
        ];

        return $userStat;
    }
}
