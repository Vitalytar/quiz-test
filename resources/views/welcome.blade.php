<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Quiz</title>
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="{{ url('css/quiz-styling.css') }}">
    </head>
    <body>
        <span id="success"></span>
        <span id="error"></span>
        <div class="Reset-Quiz">
            <a href="#">{{ __('Reset Quiz') }}</a>
        </div>
        <div class="Quiz-Progress">
            <span class="Current-Question">{{ $quizData['question_nr']+1 }}</span>/{{ $quizData['questions_total'] }}
        </div>
        <h1 class="Title">Trivia Quiz</h1>
        <div class="Question-Container">
            <div class="Question" data-question-id="{{ $quizData['question_nr'] }}">
                <span class="Question-Highlight">Question</span>
                <div class="Question-Main">{{ $question }}</div>
            </div>
            <div class="Answers-Container">
                @foreach ($quizData['answers'] as $index => $answerNumber)
                    <div class="Answers-Option Index-{{ $index }}">
                        {{ $answerNumber }}
                    </div>
                @endforeach
            </div>
            <button type="submit">{{ __('Submit Answer') }}</button>
            <span id="no-selected"></span>
        </div>
        <script type="text/javascript">
            $('.Answers-Option').on('click', function () {
                $('.Answers-Option').removeClass('Selected');
                $(this).addClass('Selected');
            });

            $('button[type="submit"]').on('click', function () {
                if ($('.Answers-Option').hasClass('Selected')) {
                    $('#no-selected').text('');
                    let totalQuestionsAmount = <?= $quizData['questions_total'] - 1 ?>;

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    if (parseInt($('.Question').attr('data-question-id')) === totalQuestionsAmount) {
                        $.ajax({
                            url: "{{ route('get.final.stats') }}",
                            type: 'POST',
                            data: {
                                question_id: $('.Question').attr('data-question-id'),
                                answer_number: parseInt($('.Answers-Option.Selected').text())
                            },
                            success: function (data) {
                                let response = JSON.parse(data);

                                $('.Question-Container').html(
                                    `<div class="Final-Results-Box">`
                                    + `<div class="Final-Results-Total">Correct answers (correct/total)</div>`
                                    + `<span class="Final-Reults-Correct">${response.correct_answers_count}</span>`
                                    + ` / <span class="Final-Results-Total">${response.total_questions}</span></div>`
                                    + `<div class="Final-Results-Last-Failed">${response.last_failed}</div>`
                                );
                            },
                            error: function (request, status, error) {
                                $('#error').addClass('show').text('Some error appeared during quiz update');
                            }
                        });

                    } else {
                        $.ajax({
                        url: "{{ route('save.and.retrieve') }}",
                        type: 'POST',
                        data: {
                            question_id: $('.Question').attr('data-question-id'),
                            answer_number: parseInt($('.Answers-Option.Selected').text())
                        },
                        success: function (data) {
                            let response = JSON.parse(data);
                            $('.Question').attr('data-question-id', response.next_question_id);
                            $('.Question-Main').text(response.next_question);
                            $('.Current-Question').text(response.next_question_id + 1);

                            $.each(response.next_question_answers, function (index, value) {
                                $(`.Answers-Option.Index-${index}`).text(value);
                                $('.Answers-Option').removeClass('Selected');
                            });
                        },
                        error: function (request, status, error) {
                            $('#error').addClass('show').text('Some error appeared during answer submit. Please, reset your quiz/page');
                        }
                    });
                    }
                } else {
                    $('#no-selected').text('Please, select answer');
                }
            });

            $('.Reset-Quiz').on('click', function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('flushQuiz') }}",
                    type: 'POST',
                    success: function (data) {
                        $('#success').addClass('show').text('Flushed your questions. Page will reload shortly');
                        window.location = '/';
                    },
                    error: function (request, status, error) {
                        $('#error').addClass('show').text('Some error appeared during quiz update');
                    }
                });
            });
        </script>
    </body>
</html>