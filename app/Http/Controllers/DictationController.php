<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\S3\S3Client;  
use Aws\Exception\AwsException;
use App\Dictation;
use Illuminate\Support\Facades\Auth;

class DictationController extends Controller
{

    public function index() {
        
        $user_id = Auth::id();
        $dictations = Dictation::all()->where('user_id', $user_id);
        return $dictations;
    }

    public function store(Request $request)
    {
      $dictation = new Dictation;
      $dictation->content = $request->content;
      $dictation->user_id = $request->user()->id;
      $dictation->save();
      return redirect('api/dictations');
    }

    public function update(Request $request, $id)
    {
        $update = [
            'content' => $request->content,
        ];
        Dictation::where('id', $id)->update($update);
    }

    public function show($id)
    {
        $dictation = Dictation::findOrFail($id);
        $this->authorize('view', $dictation);
        return $dictation;
    }

    public function destroy(Request $request)
    {
        $dictation = Dictation::where('id', $request->id)->delete();
        $dictations = Dictation::all();
        return $dictations;
    }

    public function getPresignedUrl(Request $request)
    {
        $s3Client = new \Aws\S3\S3Client([
            'credentials' => [
                'key' => 'AKIA4ZW3CFNN252QQAZH',
                'secret' => '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',
            ],
            'region' => 'ap-northeast-1',
            'version' => 'latest'
        ]);

        $bucket = 'dictationmanager';
        $requestData = $request->all();
        $formInputs = [
            //acl とはアクセスコントロールリスト
            'acl' => 'public-read',
            'key' => 'hoge/' . $requestData['filename'] . '.' . $requestData['fileext'],
        ];
        $options = [
            ['acl' => 'public-read'],
            ['bucket' => $bucket],
            ['starts-with', '$key', 'hoge/'],
        ];
        $expires = '+20 minutes';
        $postObject = new \Aws\S3\PostObjectV4(
            $s3Client,
            $bucket,
            $formInputs,
            $options,
            $expires
        );

        $formAttributes = $postObject->getFormAttributes();
        $formInputs = $postObject->getFormInputs();

        return response()
            ->json([
                'url' => $formAttributes['action'],
                'fields' => $formInputs
            ]);
    }

    public function checkExist(Request $request) 
    {
        $theDictation = Dictation::where('id', $request->dictation_id)
            ->get()
            ->sortByDesc('created_at');
        return $theDictation;
    }
}
