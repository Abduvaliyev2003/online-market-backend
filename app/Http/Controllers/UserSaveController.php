<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserSaveRequest\StoreUserSaveRequest;
use App\Http\Requests\UserSaveRequest\UpdateUserSaveRequest;
use App\Models\UserSave;


class UserSaveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserSaveRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UserSave $userSave)
    {
        //
    }

  

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserSaveRequest $request, UserSave $userSave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserSave $userSave)
    {
        //
    }
}
