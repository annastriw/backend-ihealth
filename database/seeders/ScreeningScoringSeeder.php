<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionSet;
use App\Models\Question;
use App\Models\Option;
use App\Models\ScreeningScoring;
use Illuminate\Support\Str;

class ScreeningScoringSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Question Set (bank soal)
        $questionSet = QuestionSet::create([
            'id' => Str::uuid(),
            'name' => 'Bank Soal Skoring Hipertensi',
        ]);

        // Buat pertanyaan dan opsi berskoring
        $question = Question::create([
            'id' => Str::uuid(),
            'question_set_id' => $questionSet->id,
            'question_text' => 'Seberapa sering Anda merasa pusing?',
        ]);

        Option::insert([
            [
                'id' => Str::uuid(),
                'question_id' => $question->id,
                'option_text' => 'Tidak pernah',
                'score' => 0,
                'option_index' => 1
            ],
            [
                'id' => Str::uuid(),
                'question_id' => $question->id,
                'option_text' => 'Kadang-kadang',
                'score' => 1,
                'option_index' => 2
            ],
            [
                'id' => Str::uuid(),
                'question_id' => $question->id,
                'option_text' => 'Sering',
                'score' => 2,
                'option_index' => 3
            ],
        ]);

        // Buat screening scoring
        ScreeningScoring::create([
            'id' => Str::uuid(),
            'question_set_id' => $questionSet->id,
            'name' => 'Screening Skoring Hipertensi Dummy',
            'type' => 'HT'
        ]);
    }
}
