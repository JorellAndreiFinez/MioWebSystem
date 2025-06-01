<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Storage as FirebaseStorage;

class SpecializedLanguageApi extends Controller
{
    public function __construct()
    {
        $path = base_path('storage/firebase/firebase.json');

        if (!file_exists($path)) {
            die("This File Path .{$path}. does not exist.");
        }

        $this->database = (new Factory)
            ->withServiceAccount($path)
            ->withDatabaseUri('https://miolms-default-rtdb.firebaseio.com')
            ->createDatabase();

        $this->storage = (new Factory())
            ->withServiceAccount($path)
            ->withDefaultStorageBucket('miolms.firebasestorage.app')
            ->createStorage();
    }

    private function generateUniqueId(string $prefix): string
    {
        $now = now();
        $currentYear = $now->year;
        $currentMonth = str_pad($now->month, 2, '0', STR_PAD_LEFT);
        $currentDay = str_pad($now->day, 2, '0', STR_PAD_LEFT);
        $randomDigits = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        $announcementId = "{$prefix}{$currentYear}{$currentMonth}{$currentDay}{$randomDigits}";

        return $announcementId;
    }

    public function createHomonymsActivity(Request $request, string $subjectId) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'homonym' => 'required|array|min:1',
            'homonym.*.sentences' => 'required|array|min:2',
            'homonym.*.sentences.*' => 'required|string|min:1',
            'homonym.*.audio' => 'required|array',
            'homonym.*.audio.*' => 'required|file|mimetypes:video/mp4,audio/mp3',
            'homonym.*.answers' => 'required|array|min:2',
            'homonym.*.answers.*' => 'required|string',
            'homonym.*.choices' => 'required|array|min:2',
            'homonym.*.choices.*' => 'required|string|min:1'
        ]);

        try{
            $activity_data = [];

            $bucket = $this->storage->getBucket();

            foreach ($validated['homonym'] as $item) {
                $item_id = (string) Str::uuid();

                if (count($item['answers']) !== count($item['sentences'])) {
                    return response()->json([
                        "success" => false,
                        "message" => "Each sentence must have a corresponding answer. The number of answers must match the number of sentences."
                    ], 422);
                }

                foreach ($item['answers'] as $ans) {
                    if (!in_array($ans, $item['choices'])) {
                        return response()->json([
                            "success" => false,
                            "message" => "Each answer must be one of the choices."
                        ], 422);
                    }
                }

                $sentence_answer_pairs = [];

                foreach ($item['sentences'] as $index => $sentence) {
                    $sentence_id = (string) Str::uuid();

                    $answer = $item['answers'][$index] ?? null;


                    if ($answer === null) {
                        return response()->json([
                            'success' => false,
                            'message' => "Missing answer for sentence at position " . ($index + 1) . ". Each sentence must have a corresponding answer."
                        ], 422);
                    }

                    $audio_file = $item['audio'][$index];
                    $audio_id = (string) Str::uuid();
                    $filename = $uuid . $file->getClientOriginalName();
                    $remoteAudioPath = "audio/language/" . $filename;

                    $bucket->upload(
                        fopen($audio_file->getPathName(), 'r'),
                        ['name' => $remoteAudioPath]
                    );

                    $sentence_answer_pairs[$sentence_id] = [
                        'sentence_id' => $sentence_id,
                        'text' => $sentence,
                        'answer' => $answer,
                        'audio_path' => $remoteAudioPath,
                    ];
                }

                $activity_data[$item_id] = [
                    'homonym' => $sentence_answer_pairs,
                    'choices' => $item['choices'],
                ];
            }

            $date = now()->toDateTimeString();

            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId("SPE");

            $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/homonyms/{$difficulty}/{$activity_id}")
                ->set([
                    'items' => $activity_data,
                    'total' => count($activity_data),
                    'created_at' => $date,
                ]);

            return response()->json([
                'success' => true,
                'activity' => $activity_data,
            ]);
            
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createFillActivity(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'difficulty' => 'required|in:easy,average,difficult,challenge',
            'activity' => 'required|array|min:1',
            'activity.*.sentence' => 'required|string',
            'activity.*.audio' => 'required|file|mimetypes:video/mp4,audio/mp3',
            'activity.*.distractor' => 'required|string|min:1',
        ]);

        try {
            $activity_data = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['activity'] as $activity) {
                $activity_item_id = (string) Str::uuid();

                $audio_file = $activity['audio'];
                $audio_id = (string) Str::uuid();
                $filename = $audio_id . $audio_file->getClientOriginalName(); // Use $audio_id for uniqueness
                $remote_path = "audio/language/" . $filename;

                $bucket->upload(
                    fopen($audio_file->getPathname(), 'r'),
                    ['name' => $remote_path]
                );

                $activity_data[$activity_item_id] = [
                    'sentence' => $activity['sentence'],
                    'distractors' => $activity['distractor'],
                    'audio_path' => $remote_path
                ];
            }

            $activity_id = $this->generateUniqueId("SPE");
            $date = now()->toDateTimeString();
            $difficulty = $validated['difficulty'];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/fill/{$difficulty}/{$activity_id}")
                ->set([
                    'items' => $activity_data,
                    'total' => count($activity_data),
                    'created_at' => $date,
                ]);

            return response()->json([
                'success' => true,
                'activity_id' => $activity_id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createTalk2MeActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([]);

        try{

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function takeHomonymActivity(Request $request, string $subjectId, string $difficulty, string $activityId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/homonyms/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if ($activity === null) {
                return response()->json([
                    'success' => false,
                    'message' => "The requested homonyms activity does not exist or may have been deleted."
                ],404);
            }
            
            $attempt_data = [];

            $bucket = $this->storage->getBucket();

            foreach ($activity['items'] as $item_id => $item) {
                $items = [];

                foreach ($item['homonym'] as $index => $homonym) {
                    $text = strtolower($homonym['text']);
                    $cleaned_sentence = preg_replace('/[^a-z0-9\s]/i', '', $text);
                    $words = explode(' ', $cleaned_sentence);
                    $words = array_filter($words);

                    $answer = strtolower($homonym['answer']);
                    $cleaned_answer = preg_replace('/[^a-z0-9\s]/i', '', $answer);

                    foreach ($words as $i => $word) {
                        if ($word === $cleaned_answer) {
                            $words[$i] = "_____";
                        }
                    }

                    $masked_sentence = ucfirst(implode(' ', $words));

                    $items[] = [
                        "sentence_id" => $homonym['sentence_id'],
                        "sentence" => $masked_sentence,
                        'audio_path' => $homonym['audio_path']
                    ];
                }

                $attempt_data[$item_id] = [
                    'homonyms' => $items,
                    'choices' => $item['choices']
                ];
            }

            $attempt_id = $this->generateUniqueId("ATTM");
            $date = now()->toDateTimeString();

            $this->database
                ->getReference("/subjects/GR{$gradeLevel}/{$subjectId}/attempts/homonyms/{$activityId}/{$userId}/{$attempt_id}")
                ->set([
                    'items' => $attempt_data,
                    'status' => "in-progress",
                    'created_at' => $date,
                ]);

            return response()->json([
                'success' => true,
                'attempt_id' => $attempt_id,
                'activity' => $attempt_data
            ],201);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function takeFillActivity(Request $request, string $subjectId, string $difficulty, string $activityId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/fill/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if ($activity === null) {
                return response()->json([
                    'success' => false,
                    'message' => "The requested Fill in the blanks activity does not exist or may have been deleted."
                ],404);
            }

            $attempt_data = [];

            $bucket = $this->storage->getBucket();

            foreach($activity['items'] as $item_id => $item){
                $sentence = $item['sentence'];
                $distractors = $item['distractors'];

                $lowercase_sentence = strtolower($sentence);
                $distractors_sentence = strtolower($distractors);

                $clean_sentence = preg_replace('/[^a-z0-9\s]/i', '', $lowercase_sentence);
                $clean_distractors = preg_replace('/[^a-z0-9\s]/i', '', $distractors_sentence);

                $sentence_array = explode(" ", $clean_sentence);
                $distractors_array = explode(" ", $clean_distractors);

                $combined = array_merge($sentence_array, $distractors_array);
                shuffle($combined);

                $jumbled = implode(" ", $combined);

                $audio = $bucket->object($item['audio_path'])->signedUrl(now()->addMinutes(15));

                $attempt_data[$item_id] = [
                    'sentence' => $jumbled,
                    'audio_path' => $audio
                ];
            }

            $attempt_id = $this->generateUniqueId("ATTM");
            $date = now()->toDateTimeString();

            $this->database
                ->getReference("/subjects/GR{$gradeLevel}/{$subjectId}/attempts/fill/{$activityId}/{$userId}/{$attempt_id}")
                ->set([
                    'items' => $attempt_data,
                    'status' => "in-progress",
                    'created_at' => $date,
                ]);

            return response()->json([
                'success' => true,
                'activity' => $attempt_data,
                'attempt_id' => $attempt_id,
            ],201);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function finalizeHomonymsAttempt(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId,
        string $attemptId
    ){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.item_id' => 'required|string|uuid',
            'answers.*.answers' => 'required|array|min:1',
            'answers.*.answers.*.sentence_id' => 'required|string|uuid',
            'answers.*.answers.*.answer' => 'required|string|min:1',
        ]);

        try{
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/homonyms/{$difficulty}/{$activityId}/items")
                ->getSnapshot()
                ->getValue() ?? [];

            if ($activity === null) {
                return response()->json([
                    'success' => false,
                    'message' => "The requested homonyms activity does not exist or may have been deleted."
                ],404);
            }

            $mapped_correct_answers = [];
            $mapped_sentences = [];

            foreach ($activity as $item) {
                foreach ($item['homonym'] as $homonym) {
                    $mapped_correct_answers[$homonym['sentence_id']] = $homonym['answer'];
                    $mapped_sentences[$homonym['sentence_id']] = $homonym['text'];
                }
            }

            $attempt_items = [];
            $score = 0;
            foreach ($validated['answers'] as $itemAnswer) {
                $item_id = $itemAnswer['item_id'];
                $answers = [];

                foreach ($itemAnswer['answers'] as $index => $userAnswer) {
                    $sentence_id = $userAnswer['sentence_id'];
                    $user_response = strtolower(trim($userAnswer['answer']));

                    if (isset($mapped_correct_answers[$sentence_id])) {
                        $correct_answer = strtolower(trim($mapped_correct_answers[$sentence_id]));
                        if ($user_response === $correct_answer) {
                            $score++;
                            $answers[$index] = [
                                'is_correct' => true,
                                'sentence_id' => $sentence_id,
                                'student_answer' => $user_response,
                                'sentence' => $mapped_sentences[$sentence_id] ?? '',
                                'correct_answer' => $correct_answer,
                            ];
                        }else{
                            $answers[$index] = [
                                'is_correct' => false,
                                'sentence_id' => $sentence_id,
                                'student_answer' => $user_response,
                                'sentence' => $mapped_sentences[$sentence_id] ?? '',
                                'correct_answer' => $correct_answer,
                            ];
                        }
                    }
                }

                $attempt_items[$item_id] = $answers;
            }

            $date = now()->toDateTimeString();

            $this->database
                ->getReference("/subjects/GR{$gradeLevel}/{$subjectId}/attempts/homonyms/{$activityId}/{$userId}/{$attemptId}")
                ->update([
                    'status' => "submitted",
                    'submitted_at' => $date,
                    'items' => $attempt_items,
                    'score' => $score
                ]);

            return response()->json([
                'success' => true,
                'message' => "Attempt successfully submitted!",
                'score' => $score,
            ],200);

        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function finalizeFillAttempt(
        Request $request,
        string $subjectId,
        string $difficulty,
        string $activityId,
        string $attemptId
    ){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.item_id' => 'required|string|min:1',
            'answers.*.sentence' => 'required|string|min:1',
        ]);

        try {
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/fill/{$difficulty}/{$activityId}/items")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($activity)) {
                return response()->json([
                    'success' => false,
                    'message' => "The requested Fill in the blanks activity does not exist or may have been deleted."
                ], 404);
            }

            $attempt_items = [];
            $score = 0;

            foreach ($validated['answers'] as $answer) {
                $item_id = $answer['item_id'];
                $student_sentence = preg_replace('/[^a-z0-9\s]/i', '', strtolower(trim($answer['sentence'])));

                if (isset($activity[$item_id])) {
                    $correct_sentence = preg_replace('/[^a-z0-9\s]/i', '', strtolower(trim($activity[$item_id]['sentence'])));
                    $is_correct = $student_sentence === $correct_sentence;

                    if ($is_correct) {
                        $score++;
                    }

                    $attempt_items[$item_id] = [
                        'student_answer' => $answer['sentence'],
                        'correct_answer' => $activity[$item_id]['sentence'],
                        'is_correct' => $is_correct,
                    ];
                }
            }

            $date = now()->toDateTimeString();

            $this->database
                ->getReference("/subjects/GR{$gradeLevel}/{$subjectId}/attempts/fill/{$activityId}/{$userId}/{$attemptId}")
                ->update([
                    'status' => "submitted",
                    'submitted_at' => $date,
                    'items' => $attempt_items,
                    'score' => $score
                ]);

            return response()->json([
                'success' => true,
                'message' => "Attempt successfully submitted!",
                'score' => $score,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
