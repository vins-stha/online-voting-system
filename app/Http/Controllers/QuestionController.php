<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Models\Tag;
use App\Models\User;
use http\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class QuestionController extends Controller
{
    public function listQuestionsByTag(Request $request)
    {
        // separate tags received in request
        $tags = explode(",", $request->tags);
        $questions = [];

        foreach ($tags as $tag) {
            $tag_ids[] = TagController::returnTagId($tag, $action = "search");
        }

        foreach ($tag_ids as $id) {
            $tag = Tag::find($id);
            $found_questions = $tag->questions;
            foreach ($found_questions as $fq) {
                if (!in_array($fq, $questions)) {
                    $questions[] = $fq;
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
        // if (!$request->user()->id) 
        $questions = Question::with(['answers', 'tags'])    
        ->orderBy('created_at', 'desc')
        ->get();
 
        return response()->json([
            'questions' => $questions
        ], 200);
    }

    public function askQuestion(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'question' => 'required|min:10',
                'tags' => 'required|min:2|max:5'
            ]
        );

        if ($validator->fails()) {
            return CustomServices::customResponse(
                "validation_error",
                $validator->errors(),
                500,
                null
            );
        }

        $user_id = $request->user()->id;
        $tags = $request->get('tags');
        $tags_ids = [];

        foreach ($tags as $tag) {
            // get ids of each tags received
            $id =  TagController::returnTagId($tag, "create");

            // check for possible duplicates
            if (!in_array($id, $tags_ids)) {
                $tags_ids[] = $id;
            }
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
        $question = Question::with(['answers', 'tags'])->find($id);
        if (!$question)
            throw new NotFoundResourceException("Question with id " . $id . " not found");
        return response()->json([
            'data' => $question,
        ]);
    }

    // Find all question by user
    public function findQuestionsByUserId(Request $request, $user_id)
    {
        $question = Question::with('answers')->where('user_id', $user_id)->get(); //find($id);
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
                    "validation error",
                    $validator->errors(),
                    500,
                    []
                );

            $question = Question::find($id);
            $question->question = $request->get('question');

            $old_tags_ids = [];
            foreach ($question->tags as $tag) {
                $old_tags_ids[] = $tag->id;
            }
            // remove the old tags from the question
            $question->tags()->detach($old_tags_ids);

            // retrieve all the tags from reqeust body
            $tags = $request->get('tags');
            $tags_ids = [];

            foreach ($tags as $tag) {
                // get ids of each tags received
                $id =  TagController::returnTagId($tag, "update");

                // check for possible duplicates
                if (!in_array($id, $tags_ids)) {
                    $tags_ids[] = $id;
                }
            }
            // replace old tags and save the new tags
            $question->tags()->attach($tags_ids);

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

    public function handleReportDuplicate(Request $request, $qid)
    {
        $user_id = auth()->user()->id;
        $item = $request->item;
        $item_id = $request->itemid;
        $status = 200;

        if (!self::isAuthor($request, $item_id)) {
            $user_points = UserController::userPoints($request, $user_id)['total_points'];
            if ($user_points >= User::USER_MINIMUM_POINTS) {
                // increase count of duplicate
                $question = Question::find($item_id);

                if ($question->count_duplicate_reports++ >= User::MINIMUM_DUPLICATE_REPORTS) {

                    // delete question
                    $question->delete();
                    $message = "Thank you for your effort. Question has been reported for duplicate.";
                    $status = 204;
                } else {
                    $question->count_duplicate_reports = $question->count_duplicate_reports++;
                    $question->save();
                    $message = "Thank you for your effort. Question has been reported for duplicate.";
                }
                // send email to author Todo                

            } else

                $message =  "Could not report duplicate. You dont have enough points ";
        } else {
            $message = 'As author you are not allowed to report duplicate. Either edit or delete the question';
        }

        return response()->json([
            'message' => $message,
            'user_points' => $user_points,
            'item' => $item,
            'item id' => $item_id
        ], $status);
    }

    public static function isAuthor(Request $request, $qid)
    {
        $user_id = auth()->user()->id;
        $question = Question::find($qid);
        // var_dump($user_id,$qid);
        // var_dump($question->user_id);

        return $user_id === $question->user_id;
    }
}
