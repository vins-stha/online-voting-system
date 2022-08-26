<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Models\Tag;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class QuestionController extends Controller
{
    public function listQuestionsByTag(Request $request){
        // separate tags received in request
        $tags = explode(",",$request->tags);
        $questions=[];

        foreach ($tags as $tag)
        {
            $tag_ids [] = TagController::returnTagId($tag);

        }

        foreach ($tag_ids as $id)
        {
            $tag = Tag::find($id);
            $found_questions= $tag->questions;
            foreach ($found_questions as $fq)
            {
                if(!in_array($fq, $questions)){
                    $questions [] = $fq;
                }
//                if(!in_array($fq->question, $questions)){
//                    $questions [] = $fq->question;
//                }
            }
        }
        return response()->json([
            'questions' => $questions,
        ], 200);
    }


    // All questions with answers
    public function index(Request $request)
    {

        $questions = Question::with(['answers', 'tags'])
            ->get()
            ->sortByDesc('timestamps');
        return response()->json([
            'questions' => $questions
        ], 200);
    }

    public function askQuestion(Request $request)
    {
        $validator = Validator::make($request->all(),
            ['question' => 'required|min:10',
                'tags' =>'required|min:2|max:5'
            ]
        );

        if ($validator->fails()) {
            return CustomServices::customResponse(
                "validation_error", $validator->errors(), 500, null);
        }

        $user_id = $request->user()->id;
        $tags = $request->get('tags');
        $tags_ids=[];

        foreach ($tags as $tag)
        {
            $tags_ids[] = TagController::returnTagId($tag);
        }

        $question = Question::create([
            'user_id' => $user_id,
            'question' => $request->get('question')
        ]);
        $question->tags()->attach($tags_ids);

        return CustomServices::customResponse("message", "Question posted", 200, []);
    }

    // Find question by id with answer
    public function findById(Request $request, $id)
    {
        try {
        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }
        $question = Question::with('answers')->find($id);
        if (!$question)
            throw new NotFoundResourceException("Question with id " . $id . " not found");
        return response()->json([
            'data' => $question,
        ]);
    }

    // Find all question by user
    public function findQuestionsByUserId(Request $request, $user_id)
    {
        $question = Question::with('answers')->where('user_id', $user_id)->get();//find($id);
        if (!$question)
            throw new NotFoundResourceException("Question with id " . $user_id . " not found");
        return response()->json([
            'data' => $question,
        ]);
    }

    // find question by tag


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
