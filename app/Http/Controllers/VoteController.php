<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    // vote up or down 
    public function vote(Request $request)
    {
        // allow to be up voted or down voted by others than owner
        $votetype = $request->votetype;
        $item = $request->item;
        $itemid = $request->itemid;
        try {

            if (!self::isAuthor($request, $item, $itemid)) {
                $uid = auth()->user()->id;
                $user = User::find(auth()->user()->id);

                if ($item == "answer") {
                    $voters = [];
                    $answer = Answer::find($itemid);
                 
                    foreach ($answer->voters as $voter) {
                        $voters[] = $voter['id'];
                    }

                    // check if uid is present in voterslist
                    if (!in_array($uid, $voters)) {
                        if ($votetype == "up")
                            $answer->up_vote_counts += 1;
                        else if ($votetype == "down")
                            $answer->down_vote_counts += 1;
                        else
                            // To do
                            return;
                        // // attach voters id into question
                        $answer->voters()->attach($uid);
                    }
                    // return error
                    else {
                        throw new Exception("Vote already registered");
                        // return response()->json([
                        //     'error' => "Vote already registered",
                        // ], 200);

                    }
                    $answer->save();
                    return response()->json([
                        'question' => $answer,
                    ], 200);        
                }
                if ($item == "question") {
                    $voters = [];
                    $question = Question::find($itemid);

                    foreach ($question->voters as $voter) {
                        $voters[] = $voter['id'];
                    }
                    // check if uid is present in voterslist

                    if (!in_array($uid, $voters)) {
                        if ($votetype == "up")
                            $question->vote_counts += 1;
                        else if ($votetype == "down")
                            $question->down_votes += 1;
                        else
                            // To do
                            return;
                        // // attach voters id into question
                        $question->voters()->attach($uid);
                    }
                    // return error
                    else {
                        throw new Exception("Vote already registered");
                        // return response()->json([
                        //     'error' => "Vote already registered",
                        // ], 200);

                    }
                    $question->save();
                    return response()->json([
                        'question' => $question,
                    ], 200);
                }
            }
        } catch (Exception $e) {

            return CustomServices::customResponse("message", $e->getMessage(), 400, []);
        }
    }

    public static function isAuthor(Request $request, $item, $itemid)
    {
        $user_id = auth()->user()->id;
        if ($item == "answer") {
            $answer = Answer::find($itemid);
            return $user_id === $answer->user_id;
        }

        if ($item == "question") {
            $question = Question::find($itemid);
            return $user_id === $question->user_id;
        }
    }
}
