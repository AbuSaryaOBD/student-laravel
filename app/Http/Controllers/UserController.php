<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function studetns(Request $request)
    {
        $students = User::role('Student')
            ->filter($request->all('search'))
            ->get();

        return $students;
    }
}
