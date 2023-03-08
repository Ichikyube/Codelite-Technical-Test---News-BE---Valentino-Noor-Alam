<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Repositories\NewsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Traits\HttpResponses;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class NewsController extends Controller
{
    /**
     * Response trait to handle return responses.
     */
    use HttpResponses;


    /**
     * Product Repository class.
     *
     * @var ProductRepository
     */
    public $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->newsRepository->getAll();
            return $this->responseSuccess($data, 'News List Fetch Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function search(Request $request)
    {
        try {
            $data = $this->newsRepository->searchNews($request->search, $request->perPage);
            return $this->responseSuccess($data, 'News List Fetched Successfully !');
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
            $article = $this->newsRepository->create($request);
            return $this->responseSuccess($article, 'New Article Created Successfully !');
        } catch (\Exception $exception) {
            return $this->responseError(null, $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\News  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id):JsonResponse
    {
        $validated = $request->validate();
        try {
            $article = $this->newsRepository->update($id,$validated);
            return $this->responseSuccess($article, 'Article Updated Successfully !');
        }catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function edit($id)
    {
        try {
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
        } catch (\Exception $exception) {
            return $this->responseError(null, $exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\News  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id):JsonResponse
    {
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
