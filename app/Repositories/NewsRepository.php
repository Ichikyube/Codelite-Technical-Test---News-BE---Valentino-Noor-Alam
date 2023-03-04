<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use App\Helpers\UploadHelper;
use App\Interfaces\CrudInterface;
use App\Models\News;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;

class NewsRepository implements CrudInterface
{
    /**
     * @var news
     */
    public News | null $news;

    /**
     * Get All Newss.
     *
     * @return collections Array of News Collection
     */
    public function getAll(): Paginator
    {
        return $this->news
            ->latest()
            ->paginate(10);
    }

    /**
     * Get Paginated News Data.
     *
     * @param int $pageNo
     * @return collections Array of News Collection
     */
    public function getPaginatedData($perPage): Paginator
    {
        $perPage = isset($perPage) ? intval($perPage) : 12;
        return News::latest()
            ->paginate($perPage);
    }

    /**
     * Get Searchable News Data with Pagination.
     *
     * @param int $pageNo
     * @return collections Array of News Collection
     */
    public function searchNews($keyword, $perPage): Paginator
    {
        $perPage = isset($perPage) ? intval($perPage) : 10;

        return News::where('title', 'like', '%' . $keyword . '%')
            ->orWhere('content', 'like', '%' . $keyword . '%')
            ->orWhere('banner', 'like', '%' . $keyword . '%')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create New News.
     *
     * @param array $data
     * @return object News Object
     */
    public function create(Request $request): News
    {
        $data = $request->all();
        $data['title'] = Str::of($data['title'])->trim()->title();// trim title and convert it to title case

        if (!empty($data['banner'])) {
            $titleShort     = Str::slug(substr($data['title'], 0, 20));
            $data['banner'] = UploadHelper::upload($request->hasFile('banner'), $data['banner'],  $titleShort, $request->getSchemeAndHttpHost() . '/', 'images/news');
        }
        return News::create($data);
    }

    /**
     * Update News By ID.
     *
     * @param int $id
     * @param array $data
     * @return object Updated News Object
     */
    public function update(int $id, Request $request): News|null
    {
        $data = $request->all();
        $news = News::find($id);
        if (is_null($news)) {
            return null;
        }
        if (!empty($data['banner'])) {
            $titleShort     = Str::slug(substr($data['title'], 0, 20));
            $data['banner'] = UploadHelper::upload($request->hasFile('banner'), $data['banner'],  $titleShort, $request->getSchemeAndHttpHost() . '/', 'images/news');
        } else {
            $request['banner'] = $news->image;
        }

        // If everything is OK, then update.
        $news->update($data);

        // Finally return the updated product.
        return $this->getByID($news->id);
    }
    /**
     * Delete News.
     *
     * @param int $id
     * @return boolean true if deleted otherwise false
     */
    public function delete(int $id): bool
    {
        $article = News::find($id);
        if (empty($article)) {
            return false;
        }

        UploadHelper::deleteFile('images/news/' . $article->banner);
        $article->delete($article);
        return true;
    }

    /**
     * Get News Detail By ID.
     *
     * @param int $id
     * @return void
     */
    public function getByID(int $id): News|null
    {
        return News::find($id);
    }
}
