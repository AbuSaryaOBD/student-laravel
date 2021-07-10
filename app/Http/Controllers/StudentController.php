<?php

namespace App\Http\Controllers;

use App\Actions\Students\FetchStudents;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $fetch = new FetchStudents();
        $students = $fetch->list($request);

        return Inertia::render('Students/Index', [
            'filters' => $request->all('search', 'match'),
            'students' => $students,
        ]);
    }
}
