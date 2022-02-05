<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

/**
 * Prepare quiz
 * 
 * @package App\Http\Controllers
 * 
 * @class GetQuestion
 */
class GetQuestion extends Controller
{
    const QUIZ_TYPE = 'trivia';
    const QUESTIONS_COUNT = 20;

    /**
     * @return array
     */
    public function getQuestionFromApi(): array
    {
        if (!session()->get('user-session-key')) {
            $this->setUserSession();
            $numbersForQuestions = $this->generateRandomNumbers(self::QUESTIONS_COUNT);
            $questionList = [];
            $questionsCounter = 0;

            foreach ($numbersForQuestions as $number) {
                $requestUrl = $this->prepareRequestUrl('http://numbersapi.com/', $number, self::QUIZ_TYPE);
                $mainQuestion = Http::get($requestUrl);
                $questionList[$questionsCounter] = [
                    'question_nr' => $questionsCounter,
                    'question' => (string)$mainQuestion->getBody(),
                    'correct_answer' => $number,
                    'randomly_generated_answers' => $this->generateRandomAnswers($number),
                    'questions_total' => self::QUESTIONS_COUNT,
                    'answered' => false
                ];
                $questionsCounter++;
            }

            session()->put('user-quiz-data', $questionList);

            return [
                'question' => session()->get('user-quiz-data')[0]['question'],
                'correct_answer' => session()->get('user-quiz-data')[0]['correct_answer'],
                'answers' => session()->get('user-quiz-data')[0]['randomly_generated_answers'],
                'question_nr' => session()->get('user-quiz-data')[0]['question_nr'],
                'questions_total' => session()->get('user-quiz-data')[0]['questions_total']
            ];
        }

        return [
            'question' => session()->get('user-quiz-data')[0]['question'],
            'correct_answer' => session()->get('user-quiz-data')[0]['correct_answer'],
            'answers' => session()->get('user-quiz-data')[0]['randomly_generated_answers'],
            'question_nr' => session()->get('user-quiz-data')[0]['question_nr'],
            'questions_total' => session()->get('user-quiz-data')[0]['questions_total']
        ];
    }

    
    /**
     * Set unique user session
     *
     * @return void
     */
    protected function setUserSession(): void
    {
        session()->put('user-session-key', sha1(microtime(true).mt_rand(10000,90000)));
    }

    /**
     * Unset session for user
     *
     * @return void
     */
    protected function unsetUserSession(): void
    {
        session()->invalidate();
    }

    /**
     * Generate unique numbers to get questions from the API
     * 
     * @return array
     */
    protected function generateRandomNumbers($numbersCount): array
    {
        $numbers = range(0, 100);
        shuffle($numbers);

        return array_slice($numbers, 0, $numbersCount);
    }

    /**
     * Prepare URL for request
     *
     * @param string $baseUrl
     * @param int    $number
     * @param string $quizType
     * 
     * @return string
     */
    public function prepareRequestUrl($baseUrl, $number, $quizType): string
    {
        return $baseUrl . $number . '/' . $quizType . '?fragment';
    }

    /**
     * Generate random numbers as answer options
     *
     * @param int $correctAnswer
     * 
     * @return array
     */
    public function generateRandomAnswers($correctAnswer): array
    {
        $possibleAnswers = [];

        for ($start = 0; $start < 3; $start++) {
            $answerOption = $this->generateRandomNumbers(1)[0];
            
            if ($answerOption !== $correctAnswer) {
                $possibleAnswers[] = $answerOption;
            } else {
                $start--;
            }
        }

        array_push($possibleAnswers, $correctAnswer);
        shuffle($possibleAnswers);

        return $possibleAnswers;
    }
}
