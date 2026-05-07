<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class articleController extends Controller
{
    public function index()
    {
        return view('articles.articleForm');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $createArtisan)
    {
        dd($request->all());

        $validation = $request->validate([
            'title' => 'required|max:255',
            'excerpt' => 'required|max:500|min:10',
            'body' => 'required|min:10|max:50000',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required',
            'cover' => 'nullable|image'
        ]);

        if (Auth::check()) {
            $attempt = $request->all();

            // unset();

        }
        return to_route('register')->with('error','You must be authorize before posting artical');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
