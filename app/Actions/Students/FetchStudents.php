<?php

namespace App\Actions\Students;

use App\Http\Resources\StudentResource;
use App\Models\User;
use Illuminate\Http\Request;

class FetchStudents
{
    public function list(Request $request)
    {
        $students = User::role('Student')
            ->filter($request->all('search', 'match'))
            ->get();

        return StudentResource::collection($students);
    }
}
