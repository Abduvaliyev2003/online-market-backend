<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest\StoreUserAddressRequest;
use App\Http\Requests\UserAddressRequest\UpdateUserAddressRequest;
use App\Models\UserAddress;


class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

   

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserAddressRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UserAddress $userAddress)
    {
        //
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserAddressRequest $request, UserAddress $userAddress)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserAddress $userAddress)
    {
        //
    }
}
