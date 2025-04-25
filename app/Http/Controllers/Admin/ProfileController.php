<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display the profile page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.profile');
    }
} 