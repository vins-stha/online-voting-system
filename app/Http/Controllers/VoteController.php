<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    // vote up or down 
    public function vote(Request $request)
    {
        // allow to be up voted or down voted by others than owner
        $uid = $request->uid;
        $votetype = $request->votetype;
        $item = $request->item;
        $itemid = $request->itemid;

        if (!self::isAuthor($request, $item, $itemid)) {
            if ($item == "answer") {
                $answer = Answer::find($itemid);
                if ($votetype == "up")
                    $answer->up_vote_counts += 1;
                else
                    $answer->down_vote_counts += 1;
                $answer->save();
                return response()->json([
                    'answers' => $answer
                ], 200);
            }
            if ($item == "question") {
                $question = Question::find($itemid);
                if ($votetype == "up")
                    $question->vote_counts += 1;
                else if ($votetype == "down")
                    $question->down_votes += 1;
                else
                    // To do
                    return;
                $question->save();
                return response()->json([
                    'question' => $question
                ], 200);
            }
        }
        return CustomServices::customResponse("message", "Unauthorized or Not allowed", 401, []);
    }

    public static function isAuthor(Request $request, $item, $itemid)
    {
        $user_id = auth()->user()->id;
        if ($item == "answer") {
            $answer = Answer::find($itemid);
            return $user_id === $answer->user_id;
        }

        if ($item == "question") {
            $answer = Question::find($itemid);
            return $user_id === $answer->user_id;
        }
    }
}
