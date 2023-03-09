<?php

namespace App\Interfaces;

use App\Http\Requests\ArticleStoreRequest;

interface CrudInterface
{

    public function getAll();


    // public function getPaginatedData(int $perPage);


    public function create(ArticleStoreRequest $request);


    public function delete(int $id);


    public function getByID(int $id);


    public function update(int $id, ArticleStoreRequest $request);
}
