<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $attempt = $request->validate([
            'name' => ['required','min:5','max:50'],
            'email' => ['required', 'string', 'min:10', 'max:255'],
            'password' => ['required', 'string', 'min:4', 'max:255'],
            'role' => ['required'],
        ]);

        if (Auth::attempt($attempt, true)) {
            $request->session()->regenerate();

            return to_route('home')->with('success','You are loged in sucessfully!');
        }

        return back()->withInput()->with('error','The provided credentials do not match our records.');
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
    public function destroy()
    {
        Auth::logout();

        return back();
    }
}
