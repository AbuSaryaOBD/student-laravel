<?php

namespace App\Http\Controllers\Api;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Students\FetchStudents;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $fetch = new FetchStudents();
        $students = $fetch->list($request);

        return $students;
    }
}
