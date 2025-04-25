<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /**
     * Display the change password page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('admin.password');
    }
} 