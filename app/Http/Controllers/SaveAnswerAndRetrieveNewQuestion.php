<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SaveAnswerAndRetrieveNewQuestion extends Controller
{
    /**
     * @param Request $request
     * 
     * @return string|false
     */
    public function execute(Request $request): string|false
    {
        $currQuestionId = (int)$request->get('question_id');
        $currAnswer = $request->get('answer_number');
        $nextQuestionId = $this->getNextQuestionId($currQuestionId);
        $isAnswerCorrect = $this->isAnswerCorrect($currQuestionId, $currAnswer);

        $this->setCurrentQuestionStatus($currQuestionId, $isAnswerCorrect);

        return json_encode([
            'is_answer_correct' => $isAnswerCorrect,
            'correct_answer_was' => session()->get('user-quiz-data')[$currQuestionId]['correct_answer'],
            'prev_question_id' => $currQuestionId,
            'answer_number' => $request->get('answer_number'),
            'next_question_id' => $nextQuestionId,
            'next_question' => $this->getNextQuestion($nextQuestionId),
            'next_question_answers' => $this->getNextQuestionAnswers($nextQuestionId)
        ]);
    }

    protected function setCurrentQuestionStatus($currQuestionId, $isAnswerCorrect): void
    {
        session()->put("user-quiz-data.$currQuestionId.answered", true);
        session()->put("user-quiz-data.$currQuestionId.answered_correctly", $isAnswerCorrect);
    }

    /**
     * @param int $questionId
     * 
     * @return int
     */
    protected function getNextQuestionId($questionId): int
    {
        return session()->get('user-quiz-data')[$questionId + 1]['question_nr'];
    }

    /**
     * @param int $questionNumber
     * @return string
     */
    protected function getNextQuestion($questionNumber): string
    {
        return session()->get('user-quiz-data')[$questionNumber]['question'];
    }

    /**
     * @param int $questionNumber
     * 
     * @return array
     */
    protected function getNextQuestionAnswers($questionNumber): array
    {
        return session()->get('user-quiz-data')[$questionNumber]['randomly_generated_answers'];
    }

    /**
     * Check if user selected answer is correct for current question
     *
     * @param int $currQuestionNumber
     * @param int $currAnswer
     * 
     * @return boolean
     */
    protected function isAnswerCorrect($currQuestionNumber, $currAnswer): bool
    {
        $correctAnswer = (int)session()->get('user-quiz-data')[$currQuestionNumber]['correct_answer'];

        if ($correctAnswer === (int)$currAnswer) {
            return true;
        }

        return false;
    }
}
