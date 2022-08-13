<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Answer;
use App\Models\Question;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AnswerController extends Controller
{
    public function index(Request $request)
    {
        $answers = Answer::all()->groupBy('question_id');
//        $questions = Question::with(['answers'])->get();
        return response()->json([
            '$answers' => $answers
        ], 200);
    }

    public function answer(Request $request, $qid, $uid)
    {
        $validator = Validator::make(
            ['answer' => $request->get('answer')],
            ['answer' => 'required|min:10']
        );

        if ($validator->fails()) {
            return CustomServices::customResponse(
                "validation error", $validator->errors(), 500, null);
        }

        $user_id = $request->user()->id;
        $answer = Answer::create([
            'user_id' => $user_id,
            'question_id' => $qid,
            'answer' => $request->get('answer')
        ]);

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
                ]);
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
