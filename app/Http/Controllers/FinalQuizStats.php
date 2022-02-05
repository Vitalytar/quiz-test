<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GetQuestion;
use Illuminate\Http\Request;

class FinalQuizStats extends Controller
{
    /**
     * @return string|false
     */
    public function execute(Request $request): string|false
    {
        $currQuestionId = (int)$request->get('question_id');
        $currAnswer = $request->get('answer_number');
        $isAnswerCorrect = $this->isAnswerCorrect($currQuestionId, $currAnswer);

        $this->setLastQuestionData($currQuestionId, $isAnswerCorrect);

        return json_encode([
            'total_questions' => GetQuestion::QUESTIONS_COUNT,
            'correct_answers_count' => $this->getNumberOfCorrectAnswers(),
            'last_failed' => $this->lastFailedQuestion()
        ]);
    }

    /**
     * @param int  $currQuestionId
     * @param bool $isAnswerCorrect
     * 
     * @return void
     */
    protected function setLastQuestionData($currQuestionId, $isAnswerCorrect): void
    {
        session()->put("user-quiz-data.$currQuestionId.answered", true);
        session()->put("user-quiz-data.$currQuestionId.answered_correctly", $isAnswerCorrect);
    }

    /**
     * @return int
     */
    protected function getNumberOfCorrectAnswers(): int
    {
        $allSessionQuestions = session()->get('user-quiz-data');
        $answersCounter = 0;

        foreach ($allSessionQuestions as $question) {
            if ($question['answered_correctly']) {
                $answersCounter++;
            }
        }

        return $answersCounter;
    }

    /**
     * @param int $currQuestionNumber
     * @param int $currAnswer
     * 
     * @return bool
     */
    protected function isAnswerCorrect($currQuestionNumber, $currAnswer): bool
    {
        $correctAnswer = (int)session()->get('user-quiz-data')[$currQuestionNumber]['correct_answer'];

        if ($correctAnswer === (int)$currAnswer) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function lastFailedQuestion(): string
    {
        $allSessionQuestions = array_reverse(session()->get('user-quiz-data'));

        foreach ($allSessionQuestions as $question) {
            if (!$question['answered_correctly']) {
                $lastFailedQuestion = $question['question_nr'];
                $lastFailedQuestionAnswer = $question['correct_answer'];
                return __("Last failed question was #$lastFailedQuestion and the correct answer for this was - $lastFailedQuestionAnswer");
            }
        }

        return 'You didn\'t fail any question, great job!';
    }
}
