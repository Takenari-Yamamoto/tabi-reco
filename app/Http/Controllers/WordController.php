<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\S3\S3Client;  
use Aws\Exception\AwsException;
use App\Word;
use Illuminate\Support\Facades\Auth;

class WordController extends Controller
{
    public function index() {

        $user_id = Auth::id();
        $words = Word::all()->where('user_id', $user_id);
        return $words;
    
    }

    public function store(Request $request)
    {
      $word = new Word;
      $word->id = $request->id;
      $word->word = $request->word;
      $word->classification = $request->classification;
      $word->meaning = $request->meaning;
      $word->pronunciation = $request->pronunciation;
      $word->user_id = $request->user()->id;
      $word->save();

      return response()->json($word);
    }

    public function update(Request $request)
    {
        $words = Word::all();
        foreach($words as $word) {
            $word->classification = $request->classification;
            $word->meaning = $request->meaning;
            $word->pronunciation = $request->pronunciation;
            $word->save();
            return response()->json($words);
        }
    }

    public function destroy(Request $request)
    {
        $word = Word::where('id', $request->id)->delete();
        $words = Word::all();
        return $words;
    }

}