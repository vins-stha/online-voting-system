<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Tag;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    //
    public function index()
    {
        $tags = Tag::all();
//        dd($tags);

        return CustomServices::customResponse(
            "data", $tags, 200, null);
    }

    public function create(Request $request)
    {
        $validator = Validator::make(
            ['name' => $request->get('name')],
            ['name' => 'required|min:3|unique:tags']
        );

        if ($validator->fails())
            return CustomServices::customResponse(
                "validation error", $validator->errors(), 500, null);
        try {
            $tag = Tag::create([
                'name' => $request->get('name')
            ]);
       $tag->save();
            return CustomServices::customResponse("message", "Tag created", 201, []);
        } catch (\PDOException $e) {
            throw new CustomException($e->getMessage());
        }
    }

    public static function returnTagId($tagname){
        try {
            $tag_id = Tag::where('name', $tagname)->value('id');
            if(!$tag_id)
            {
                try {
                    $tag = Tag::create([
                        'name' => $tagname
                    ]);
                    $tag->save();
                    return $tag->id;
                } catch (\PDOException $e) {
                    throw new CustomException($e->getMessage());
                }
            }
            return $tag_id;
        }catch (\Exception $e){
            throw new CustomException($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        $tag = Tag::find($id);

        if (!$tag)
            return CustomServices::customResponse("message", "Tag not found", 404, []);
        try {
            $tag->delete();
            return CustomServices::customResponse("message", "Tag delete", 200, []);

        } catch (Exception $e) {
            throw new CustomException($e->getMessage());
        }

    }

}
