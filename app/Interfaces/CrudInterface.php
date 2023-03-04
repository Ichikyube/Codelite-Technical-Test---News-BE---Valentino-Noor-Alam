<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CrudInterface
{

    public function getAll();


    public function getPaginatedData(int $perPage);


    public function create(Request $request);


    public function delete(int $id);


    public function getByID(int $id);


    public function update(int $id, Request $request);
}
