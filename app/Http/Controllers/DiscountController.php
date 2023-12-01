<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiscountRequest\StoreDiscountRequest;
use App\Http\Requests\DiscountRequest\UpdateDiscountRequest;
use App\Models\Discount;


class DiscountController extends Controller
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
    public function store(StoreDiscountRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        //
    }

    

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        //
    }
}
