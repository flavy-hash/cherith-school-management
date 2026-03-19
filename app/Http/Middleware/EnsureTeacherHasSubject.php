<?php

namespace App\Http\Middleware;

use App\Models\TeacherSubject;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherHasSubject
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $assignment = TeacherSubject::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $assignment) {
            abort(403, 'You are not assigned to any subject.');
        }

        $request->attributes->set('teacherSubject', $assignment);

        return $next($request);
    }
}
