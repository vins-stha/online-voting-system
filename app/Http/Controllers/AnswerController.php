<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AnswerController extends Controller
{
    public function index(Request $request)
    {
        $answers = Answer::all()->groupBy('question_id');
        return response()->json([
            '$answers' => $answers
        ], 200);
    }

    // vote up or down for answer
    public function addVote(Request $request)
    {
        // allow to be up voted or down voted by others than owner
        $aid = $request->aid;
        $votetype = $request->votetype;

        if (!self::isAuthor($request, $aid)) {
            $answer = Answer::find($aid);
            $author = User::find($answer->user_id);
            if ($votetype == "up") {
                $answer->up_vote_counts += 1;
                // increase the points of author               
                $author->up_votes_recieved++;
            } else {
                $answer->down_vote_counts += 1;
                // decrease the poinst of the author
                $author->down_votes_received++;
            }
            $answer->save();
            $author->save();

            return response()->json([
                'answers' => $answer
            ], 200);
        }
        return CustomServices::customResponse("message", "Unauthorized or Not allowed", 401, []);

    }

    public function answer(Request $request, $qid)
    {
        $validator = Validator::make(
            ['answer' => $request->get('answer')],
            ['answer' => 'required|min:10']
        );

        if ($validator->fails()) {
            return CustomServices::customResponse(
                "Validation_error", $validator->errors(), 500, null);
        }
        $uid = $request->user();
        $user_id = $uid->id;

        $answer = Answer::create([
            'user_id' => $user_id,
            'question_id' => $qid,
            'answer' => $request->get('answer')
        ]);
        $question = Question::find($qid);
        $question->answers()->save($answer);

        return CustomServices::customResponse("message", "Answer posted", 200, []);
    }

    public function update(Request $request, $aid)
    {
        $user_id = auth()->user()->id;
        if (self::isAuthor($request, $aid)) {
            $validator = Validator::make(
                ['answer' => $request->get('answer')],
                ['answer' => 'required|min:10']
            );
            if ($validator->fails())
                return CustomServices::customResponse(
                    "validation error", $validator->errors(), 500, []);

            $answer = Answer::find($aid);
            $answer->answer = $request->get('answer');
            $answer->save();

            return CustomServices::customResponse("message", "Answer updated", 200, []);
        }
        return CustomServices::customResponse("message", "Unauthorized", 401, []);
    }

    public function delete(Request $request, $id)
    {
        if (self::isAuthor($request, $id)) {
            $answer = Answer::find($id);
            if (!$answer)
                throw new NotFoundResourceException("Answer not found.");
            else {
                $answer->delete();

                return response()->json([
                    'data' => "Answer deleted."
                ], 204);
            }
        }
        return CustomServices::customResponse("message", "Unauthorized", 401, []);
    }


    public static function isAuthor(Request $request, $qid)
    {
        $user_id = auth()->user()->id;
        $answer = Answer::find($qid);

        return $user_id === $answer->user_id;
    }
}
