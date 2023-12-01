<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValueRequest\StoreValueRequest;
use App\Http\Requests\ValueRequest\UpdateValueRequest;
use App\Models\Value;


class ValueController extends Controller
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
    public function store(StoreValueRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Value $value)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateValueRequest $request, Value $value)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Value $value)
    {
        //
    }
}
