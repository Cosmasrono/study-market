<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;

class CBTController extends Controller
{
    public function index()
    {
        $courses = Course::where('is_active', true)->get();
        return view('cbt.index', compact('courses'));
    }

    public function start($id)
    {
        $course = Course::findOrFail($id);
        $questions = Question::where('course_id', $id)->get();
        return view('cbt.start', compact('course', 'questions'));
    }

    public function submit(Request $request, $id)
    {
        // TODO: Implement CBT submission logic
        $course = Course::findOrFail($id);
        $questions = Question::where('course_id', $id)->get();
        
        $totalQuestions = $questions->count();
        $correctAnswers = 0;

        foreach ($questions as $question) {
            $userAnswer = $request->input('answer_' . $question->id);
            if ($userAnswer === $question->correct_answer) {
                $correctAnswers++;
            }
        }

        $scorePercentage = ($correctAnswers / $totalQuestions) * 100;

        return view('cbt.result', [
            'course' => $course,
            'totalQuestions' => $totalQuestions,
            'correctAnswers' => $correctAnswers,
            'scorePercentage' => $scorePercentage
        ]);
    }
}
