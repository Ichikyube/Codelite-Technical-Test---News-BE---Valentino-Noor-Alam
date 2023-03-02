<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;

class NewsController extends Controller
{
    /**
     * Response trait to handle return responses.
     */
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = News::latest()->get();
            return $this->responseSuccess($data, 'News List Fetch Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }    
    
    /**
     * @OA\GET(
     *     path="/api/news/view/search",
     *     tags={"Newss"},
     *     summary="All Newss - Publicly Accessible",
     *     description="All Newss - Publicly Accessible",
     *     operationId="search",
     *     @OA\Parameter(name="perPage", description="perPage, eg; 20", example=20, in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="search", description="search, eg; Test", example="Test", in="query", @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="All Newss - Publicly Accessible" ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function search(Request $request)
    {
        try {
            //$data = $this->newsRepository->searchNews($request->search, $request->perPage);
            //return $this->responseSuccess($data, 'News List Fetched Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
    * Store a newly created resource in storage.
    */
    public function store(Request $request) 
    {
        try {
            $validated = $request->validate([
                'title' => ['required'],
                'banner' => ['mimes:jpg,jpeg,png'],
                'content' => ['required'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response()->json([
                'status' => false,
                'message' => $th->validator->errors()
            ], 403);
        }
        
        // trim title and convert it to title case
        $validated['title'] = Str::of($validated['title'])->trim()->title();

        if ($validated['banner']) {
            $banner = $request->file('banner');
            $imageName = date('YmdHis') . "." . $banner->getClientOriginalName();
            $path =  $request->getSchemeAndHttpHost() . '/storage/' . $banner->storeAs('img',$imageName);
            $validated['banner'] = $path;
            
        } else
        {
            return response()->json([
                'status' => false,
                'message' => 'Your source is not valid',
                'data' => null
            ], 403);
        }
        
        $article = News::create($validated);
        return $this->responseSuccess($article, 'New Article Created Successfully !');
    }

    public function edit($id)
    {
        $article = News::query()
        ->where("id", $id)
        ->first();

        if (!isset($article)) {
            return response()->json([
                "status" => false,
                "message" => "no data",
                "data" => null
            ]);
        }

        return response()->json([
            "status" => true,
            "data" => $article
        ]);
    }
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\News  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => ['required'],
                'content' => ['required'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $th) {
            return response()->json([
                'status' => false,
                'message' => $th->validator->errors()
            ], 403);
        }

        try {
            $post = News::where("id", $id)
                    ->firstOrFail();

            // trim title and convert it to title case
            $validated['title'] = Str::of($validated['title'])->trim()->title();
                
            if ($request->hasFile('banner')) {
                $banner = $request->file('banner');
                $storage = Storage::disk('public');

                if($storage->exists($post->banner))
                    $storage->delete($post->banner);

                $banner = $request->file('banner');
                $imageName = date('YmdHis') . "." . $banner->getClientOriginalName();
                $banner->move(public_path('img'),$imageName);
                $path =  $request->getSchemeAndHttpHost() ."/img/" . $imageName;
                $validated['banner'] = $path;

            } else
            {
                return response()->json([
                    'status' => false,
                    'message' => 'Your source is not valid',
                    'data' => null
                ], 403);
            }
            $post->fill($validated);
            //$post->update($validated);
            $post->save();
            return $this->responseSuccess($post, 'Article Updated Successfully !');
        }catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
    }
        

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\News  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id){
        try {
            $post = News::query()
            ->where("id", $id);
            if (empty($post)) {
                return $this->responseError(null, 'Article Not Found', Response::HTTP_NOT_FOUND);
            }
            $deleted = $post->delete();
            if (!$deleted) {
                return $this->responseError(null, 'Failed to delete the article.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return $this->responseSuccess($post, 'Article Deleted Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
