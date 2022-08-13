<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Question;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class QuestionController extends Controller
{
    //
    public function index(Request $request)
    {
        $questions = Question::all()->sortByDesc('timestamps');
//        $questions = Question::with(['answers'])->get();
        return response()->json([
            'questions' => $questions
        ], 200);
    }

    public function askQuestion(Request $request)
    {
        $validator = Validator::make(
            ['question' => $request->get('question')],
            ['question' => 'required|min:10']
        );

        if ($validator->fails()) {
            return CustomServices::customResponse(
                "validation error", $validator->errors(), 500, null);
        }

        $user_id = $request->user()->id;
        $question = Question::create([
            'user_id' => $user_id,
            'question' => $request->get('question')
        ]);

        return CustomServices::customResponse("message", "Question posted", 200, []);
    }

    public function findById(Request $request, $id)
    {
        try {
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
        $question = Question::find($id);
        if (!$question)
            throw new NotFoundResourceException("Question with id " . $id . " not found");
        return response()->json([
            'data' => $question,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user_id = auth()->user()->id;
        if (self::isAuthor($request, $id)) {
            $validator = Validator::make(
                ['question' => $request->get('question')],
                ['question' => 'required|min:10']
            );
            if ($validator->fails())
                return CustomServices::customResponse(
                    "validation error", $validator->errors(), 500, []);

            $question = Question::find($id);
            $question->question = $request->get('question');
            $question->save();

            return CustomServices::customResponse("message", "Question updated", 200, []);
        }
        return CustomServices::customResponse("message", "Unauthorized", 401, []);
    }

    public function delete(Request $request, $id)
    {
        if (self::isAuthor($request, $id)) {
            $question = Question::find($id);
            if (!$question)
                throw new NotFoundResourceException("Question not found.");
            else {
                $question->delete();

                return response()->json([
                    'data' => "Question deleted."
                ]);
            }
        }
        return CustomServices::customResponse("message", "Unauthorized", 401, []);
    }

    public static function isAuthor(Request $request, $qid)
    {
        $user_id = auth()->user()->id;
        $question = Question::find($qid);

        return $user_id === $question->user_id;
    }

}
