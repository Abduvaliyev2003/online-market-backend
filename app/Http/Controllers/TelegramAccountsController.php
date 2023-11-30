<?php

namespace App\Http\Controllers;

use App\Models\TelegramAccounts;
use App\Http\Requests\StoreTelegramAccountsRequest;
use App\Http\Requests\UpdateTelegramAccountsRequest;

class TelegramAccountsController extends Controller
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
    public function store(StoreTelegramAccountsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TelegramAccounts $telegramAccounts)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TelegramAccounts $telegramAccounts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTelegramAccountsRequest $request, TelegramAccounts $telegramAccounts)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TelegramAccounts $telegramAccounts)
    {
        //
    }
}
