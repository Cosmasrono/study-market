<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;

class ExamController extends Controller
{
    /**
     * Display a listing of exams
     */
    public function index()
    {
        try {
            // Fetch active exams
            $exams = Exam::where('is_active', true)->get();

            Log::info('Available exams count: ' . $exams->count());

            // Annotate exams with access information
            $exams->transform(function ($exam) {
                $user = auth()->user();
                
                $exam->can_view = $user && $user->hasMembership();
                $exam->can_start = $exam->can_view && 
                    ($exam->is_free || 
                    // Check if user has purchased this specific exam
                    Transaction::where('user_id', $user->id)
                        ->where('item_type', 'exam')
                        ->where('item_id', $exam->id)
                        ->where('status', 'completed')
                        ->exists());

                return $exam;
            });

            return view('exams.index', compact('exams'));

        } catch (\Exception $e) {
            Log::error('Error fetching exams', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('exams.index', ['exams' => collect()])
                ->withErrors(['error' => 'Unable to fetch exams. Please try again later.']);
        }
    }

    /**
     * Show details of a specific exam
     */
    public function show(Exam $exam)
    {
        try {
            // Check if exam is available
            if (!$exam->is_active) {
                return redirect()->route('exams.index')
                    ->with('error', 'This exam is not currently available.');
            }

            $user = auth()->user();

            // Check user's membership and exam access
            $exam->can_view = $user->hasMembership();
            $exam->can_start = $exam->can_view && 
                ($exam->is_free || 
                Transaction::where('user_id', $user->id)
                    ->where('item_type', 'exam')
                    ->where('item_id', $exam->id)
                    ->where('status', 'completed')
                    ->exists());

            // Check if user has already taken this exam
            $exam->previous_result = ExamResult::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->latest()
                ->first();

            return view('exams.show', compact('exam'));

        } catch (\Exception $e) {
            Log::error('Error showing exam', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('exams.index')
                ->with('error', 'Exam details could not be loaded.');
        }
    }

    /**
     * Start an exam
     */
    public function start(Exam $exam)
    {
        try {
            $user = auth()->user();

            // Validate exam access
            if (!$user->hasMembership()) {
                return redirect()->route('membership.payment')
                    ->with('error', 'You need an active membership to take exams.');
            }

            // Check if exam is free or purchased
            $canStart = $exam->is_free || 
                Transaction::where('user_id', $user->id)
                    ->where('item_type', 'exam')
                    ->where('item_id', $exam->id)
                    ->where('status', 'completed')
                    ->exists();

            if (!$canStart) {
                return redirect()->route('exams.purchase', $exam->id)
                    ->with('error', 'You need to purchase this exam to start.');
            }

            // Log exam start
            Log::info('Exam started', [
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'exam_title' => $exam->title
            ]);

            return view('exams.start', compact('exam'));

        } catch (\Exception $e) {
            Log::error('Error starting exam', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('exams.index')
                ->with('error', 'Unable to start exam. Please try again later.');
        }
    }

    /**
     * Submit exam answers
     */
    public function submit(Request $request, Exam $exam)
    {
        try {
            $user = auth()->user();

            // Validate submission
            $validatedData = $request->validate([
                'answers' => 'required|array',
                'answers.*' => 'required'
            ]);

            // Calculate exam result
            $result = $this->calculateExamResult($exam, $validatedData['answers']);

            // Save exam result
            $examResult = ExamResult::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'score' => $result['score'],
                'total_questions' => $result['total_questions'],
                'passed' => $result['passed']
            ]);

            // Log exam submission
            Log::info('Exam submitted', [
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'score' => $result['score'],
                'passed' => $result['passed']
            ]);

            return redirect()->route('exams.result', $examResult->id)
                ->with('success', 'Exam submitted successfully!');

        } catch (\Exception $e) {
            Log::error('Error submitting exam', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Unable to submit exam. Please try again.']);
        }
    }

    /**
     * Show exam result
     */
    public function result(ExamResult $result)
    {
        // Ensure user can only view their own results
        if ($result->user_id !== auth()->id()) {
            return redirect()->route('exams.index')
                ->with('error', 'You are not authorized to view this result.');
        }

        return view('exams.result', compact('result'));
    }

    /**
     * Calculate exam result
     */
    private function calculateExamResult(Exam $exam, array $submittedAnswers)
    {
        $totalQuestions = count($exam->questions);
        $correctAnswers = 0;

        foreach ($exam->questions as $question) {
            $submittedAnswer = $submittedAnswers[$question->id] ?? null;
            if ($submittedAnswer === $question->correct_answer) {
                $correctAnswers++;
            }
        }

        $score = round(($correctAnswers / $totalQuestions) * 100, 2);
        $passed = $score >= $exam->passing_score;

        return [
            'score' => $score,
            'total_questions' => $totalQuestions,
            'passed' => $passed
        ];
    }
}
