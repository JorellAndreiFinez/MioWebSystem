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

    function checkBlanks($sentence) {
        $words = explode(' ', $sentence);

        foreach ($words as $word) {
            if (preg_match('/^_+$/', $word)) {
                return true;
            }
        }

        return false;
    }

    public function getFillActivity(Request $request, string $subjectId, string $difficulty, string $activityId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/fill/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if(empty($activity)){
                return response()->json([
                    'success' => false,
                    'message' => "activity not found"
                ]);
            }

            $bucket = $this->storage->getBucket();
            $items = [];
            foreach($activity['items'] as $index => $item){
                $audio_path = $bucket->object($item['audio_path'])->signedUrl(now()->addMinutes(15));

                $items[$index] = [
                    "audio_path" => $audio_path,
                    "distractors" => $item['distractors'],
                    "sentence" => $item['sentence'],
                    "filename" => $item['filename'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $items,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getHomonymsActivity(Request $request, string $subjectId, string $difficulty, string $activityId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/homonyms/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if(empty($activity)){
                return response()->json([
                    'success' => false,
                    'message' => "activity not found"
                ]);
            }

            $items = [];
            $bucket = $this->storage->getBucket();

            foreach($activity['items'] as $index => $item){
                $audio_path_1 = $bucket->object($item['audio_path_1'])->signedUrl(now()->addMinutes(15));
                $audio_path_2 = $bucket->object($item['audio_path_2'])->signedUrl(now()->addMinutes(15));

                $items[$index] = [
                    'audio_path_1' => $audio_path_1,
                    'audio_path_2' => $audio_path_2,
                    'answer_1' => $item['answer_1'],
                    'answer_2' => $item['answer_2'],
                    'distractors' => $item['distractors'],
                    'filename_1' => $item['filename_1'],
                    'filename_2' => $item['filename_2'],
                    'setence_1' => $item['sentence_1'],
                    'setence_2' => $item['sentence_2'],
                ];
            }

            return response()->json([
                'success' => true,
                'items' => $items,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createHomonymsActivity(Request $request, string $subjectId) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'homonyms' => 'required|array|min:1',
            'homonyms.*.sentences' => 'required|array|size:2',
            'homonyms.*.sentences.*' => 'required|string|min:1',
            'homonyms.*.answers' => 'required|array|size:2',
            'homonyms.*.answers.*' => 'required|string|min:1',
            'homonyms.*.audio' => 'required|array|size:2',
            'homonyms.*.audio.*' => 'required|file|mimes:mp3,wav|max:5120',
            'homonyms.*.distractors' => 'required|array|min:1',
            'homonyms.*.distractors.*' => 'required|string|min:1'
        ]);

        try {
            $items = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['homonyms'] as $index => $item) {
                $sentence_1 = $item['sentences'][0];
                $sentence_2 = $item['sentences'][1];
                $answer_1 = $item['answers'][0];
                $answer_2 = $item['answers'][1];

                $cleaned_answer_1 = trim(preg_replace("/[^\p{L}\p{N}\s]/u", "", $answer_1));
                $cleaned_answer_2 = trim(preg_replace("/[^\p{L}\p{N}\s]/u", "", $answer_2));

                if (!$this->checkBlanks($sentence_1)) {
                    return response()->json([
                        'message' => "Sentence in item #" . ($index + 1) . " (homonym #1) must contain exactly one underscore (_) as a blank.",
                    ], 422);
                }

                if (!$this->checkBlanks($sentence_2)) {
                    return response()->json([
                        'message' => "Sentence in item #" . ($index + 1) . " (homonym #2) must contain exactly one underscore (_) as a blank.",
                    ], 422);
                }

                $item_choices = [
                    $cleaned_answer_1,
                    $cleaned_answer_2
                ];

                foreach ($item['distractors'] as $dist) {
                    $cleaned_dist = trim(preg_replace("/[^\p{L}\p{N}\s]/u", "", $dist));
                    if ($cleaned_dist === $cleaned_answer_1 || $cleaned_dist === $cleaned_answer_2) {
                        return response()->json([
                            'message' => "Distractors must not match correct answers in item #" . ($index + 1),
                        ], 422);
                    }
                    $item_choices[] = $cleaned_dist;
                }

                $audio_1_remotePath = null;
                $audio_2_remotePath = null;
                $filename_1 = null;
                $filename_2 = null;

                if (isset($item['audio'][0])) {
                    $audio_1 = $item['audio'][0];
                    $audio_1_id = (string) Str::uuid();
                    $filename_1 = $audio_1->getClientOriginalName();
                    $audio_1_remotePath = "audio/language/" . $audio_1_id . $filename_1;

                    $bucket->upload(
                        fopen($audio_1->getPathname(), 'r'),
                        ['name' => $audio_1_remotePath]
                    );
                }

                if (isset($item['audio'][1])) {
                    $audio_2 = $item['audio'][1];
                    $audio_2_id = (string) Str::uuid();
                    $filename_2 = $audio_2->getClientOriginalName();
                    $audio_2_remotePath = "audio/language/" . $audio_2_id . $filename_2;

                    $bucket->upload(
                        fopen($audio_2->getPathname(), 'r'),
                        ['name' => $audio_2_remotePath]
                    );
                }

                $item_id = (string) Str::uuid();
                $items[$item_id] = [
                    'sentence_1' => $sentence_1,
                    'sentence_2' => $sentence_2,
                    'answer_1' => $cleaned_answer_1,
                    'answer_2' => $cleaned_answer_2,
                    'audio_path_1' => $audio_1_remotePath,
                    'audio_path_2' => $audio_2_remotePath,
                    'filename_1' => $filename_1,
                    'filename_2' => $filename_2,
                    'choices' => $item_choices,
                    'distractors' => array_slice($item_choices, 2),

                ];
            }

            $date = now()->toDateTimeString();

            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId("SPE");

            $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/homonyms/{$difficulty}/{$activity_id}")
                ->set([
                    'items' => $items,
                    'total' => count($items),
                    'created_at' => $date,
                    'created_by' => $userId
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Homonym auditory activity successfully created.',
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
            'activity.*.audio' => 'nullable|file|mimes:mp3,wav|max:5120',
            'activity.*.distractors' => 'required|array|min:1',
            'activity.*.distractors.*' => 'required|string|min:1',
        ]);

        try {
            $activity_data = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['activity'] as $activity) {
                $activity_item_id = (string) Str::uuid();
                $remote_path = null;
                $filename = null;

                if (isset($activity['audio'])) {
                    if ($remote_path) {
                        $bucket->object($remote_path)->delete();
                    }

                    $audio_file = $activity['audio'];
                    $audio_id = (string) Str::uuid();
                    $filename = $audio_file->getClientOriginalName();
                    $remote_path = "audio/language/" . $audio_id . '_' . $filename;

                    $bucket->upload(
                        fopen($audio_file->getPathname(), 'r'),
                        ['name' => $remote_path]
                    );
                }

                $activity_data[$activity_item_id] = [
                    'sentence' => $activity['sentence'],
                    'distractors' => $activity['distractors'],
                    'audio_path' => $remote_path,
                    'filename' => $filename
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
                    'created_by' => $userId
                ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully created fill activity",
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editHomonymsActivity(Request $request, string $subjectId, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'homonyms' => 'required|array|min:1',
            'homonyms.*.item_id' => 'nullable|string|uuid',
            'homonyms.*.sentences' => 'required|array|size:2',
            'homonyms.*.sentences.*' => 'required|string|min:1',
            'homonyms.*.answers' => 'required|array|size:2',
            'homonyms.*.answers.*' => 'required|string|min:1',
            'homonyms.*.audio' => 'nullable|array|size:2',
            'homonyms.*.audio.*' => 'nullable|file|mimes:mp3,wav',
            'homonyms.*.distractors' => 'required|array|min:1',
            'homonyms.*.distractors.*' => 'required|string|min:1'
        ]);

        try {
            $existing_activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/homonyms/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (empty($existing_activity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity not found.'
                ], 404);
            }

            $mapped_paths_1 = [];
            $mapped_paths_2 = [];
            $mapped_filename_1 = [];
            $mapped_filename_2 = [];

            foreach ($existing_activity['items'] as $item_id => $item) {
                $mapped_paths_1[$item_id] = $item['audio_path_1'] ?? null;
                $mapped_paths_2[$item_id] = $item['audio_path_2'] ?? null;
                $mapped_filename_1[$item_id] = $item['filename_1'] ?? null;
                $mapped_filename_2[$item_id] = $item['filename_2'] ?? null;
            }

            $bucket = $this->storage->getBucket();
            $items = [];

            foreach ($validated['homonyms'] as $index => $item) {
                $item_id = $item['item_id'] ?? (string) Str::uuid();
                $sentence_1 = $item['sentences'][0];
                $sentence_2 = $item['sentences'][1];
                $answer_1 = $item['answers'][0];
                $answer_2 = $item['answers'][1];

                $cleaned_answer_1 = trim(preg_replace("/[^\p{L}\p{N}\s]/u", "", $answer_1));
                $cleaned_answer_2 = trim(preg_replace("/[^\p{L}\p{N}\s]/u", "", $answer_2));

                if (!$this->checkBlanks($sentence_1)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sentence #1 in item #" . ($index + 1) . " must contain exactly one underscore (_)."
                    ], 422);
                }

                if (!$this->checkBlanks($sentence_2)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Sentence #2 in item #" . ($index + 1) . " must contain exactly one underscore (_)."
                    ], 422);
                }

                $item_choices = [$cleaned_answer_1, $cleaned_answer_2];

                foreach ($item['distractors'] as $dist) {
                    $cleaned_dist = trim(preg_replace("/[^\p{L}\p{N}\s]/u", "", $dist));
                    if (in_array($cleaned_dist, [$cleaned_answer_1, $cleaned_answer_2])) {
                        return response()->json([
                            'message' => "Distractors in item #" . ($index + 1) . " must not match answers."
                        ], 422);
                    }
                    $item_choices[] = $cleaned_dist;
                }

                $audio_remotePath_1 = $mapped_paths_1[$item_id] ?? null;
                $audio_remotePath_2 = $mapped_paths_2[$item_id] ?? null;
                $filename_1 = $mapped_filename_1[$item_id] ?? null;
                $filename_2 = $mapped_filename_2[$item_id] ?? null;;

                if (isset($item['audio'][0])) {
                    $audio_1 = $item['audio'][0];
                    $audio_1_id = (string) Str::uuid();
                    $filename_1 = $audio_1->getClientOriginalName();
                    $audio_remotePath_1 = "audio/language/" . $audio_1_id . $filename_1;

                    if(isset($mapped_paths_1[$item_id])){
                        $bucket->object($mapped_paths_1[$item_id])->delete();
                    }

                    $bucket->upload(fopen($audio_1->getPathname(), 'r'), ['name' => $audio_remotePath_1]);
                }

                if (isset($item['audio'][1])) {
                    $audio_2 = $item['audio'][1];
                    $audio_2_id = (string) Str::uuid();
                    $filename_2 = $audio_2->getClientOriginalName();
                    $audio_remotePath_2 = "audio/language/" . $audio_2_id . "_" . $audio_2->getClientOriginalName();

                    if(isset($mapped_paths_2[$item_id])){
                        $bucket->object($mapped_paths_2[$item_id])->delete();
                    }
                    
                    $bucket->upload(fopen($audio_2->getPathname(), 'r'), ['name' => $audio_remotePath_2]);
                }

                $items[$item_id] = [
                    'sentence_1' => $sentence_1,
                    'sentence_2' => $sentence_2,
                    'answer_1' => $cleaned_answer_1,
                    'answer_2' => $cleaned_answer_2,
                    'audio_path_1' => $audio_remotePath_1,
                    'audio_path_2' => $audio_remotePath_2,
                    'filename_1' => $filename_1,
                    'filename_2' => $filename_2,
                    'distractors' => array_slice($item_choices, 2),
                    'choices' => $item_choices,
                ];
            }

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/homonyms/{$difficulty}/{$activityId}")
                ->set([
                    'items' => $items,
                    'updated_at' => now()->toDateTimeString(),
                    'updated_by' => $userId,
                    'total' => count($items)
                ]);

            return response()->json([
                'success' => true,
                'message' => "Homonyms activity updated successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function editFillActivity(Request $request, string $subjectId, string $difficulty, string $activityId ){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity' => 'required|array|min:1',
            'activity.*.item_id' => 'nullable|string|min:1',
            'activity.*.sentence' => 'required|string|min:1',
            'activity.*.audio' => 'nullable|file|mimes:mp3,wav',
            'activity.*.distractors' => 'required|array|min:1',
            'activity.*.distractors.*' => 'required|string|min:1',
        ]);

        try{
            $existing_activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/fill/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if(empty($existing_activity)){
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found"
                ],404);
            }

            $mapped_items = [];
            $mapped_filename = [];
            foreach($existing_activity['items'] as $item_id => $item){
                $mapped_items[$item_id] = $item['audio_path'] ?? "";
                $mapped_filename[$item_id] = $item['filename'] ?? "";
            }

            $items = [];
            $bucket = $this->storage->getBucket();

            foreach($validated['activity'] as $activity){
                $item_id = $activity['item_id'] ?? (String) Str::uuid();
                $remote_path = $mapped_items[$item_id] ?? null;
                $filename = $mapped_filename[$item_id] ?? null;

                if(isset($item['audio'])){
                    $audio_file = $activity['audio'];
                    $audio_id = (String) Str::uuid();
                    $filename = $audio_file->getClientOriginalName();
                    $remote_path = "audio/language/" . $audio_id . $filename;

                    if ($remote_path) {
                        $bucket->object($remote_path)->delete();
                    }

                    $bucket->upload(
                        fopen($audio_file->getPathname(), 'r'),
                        ['name' => $remote_path]
                    );
                }

                $items[$item_id] = [
                    'distractors' => $activity['distractors'],
                    'sentence' => $activity['sentence'],
                    'audio_path' => $remote_path,
                    'filename' => $filename
                ];
            }

            $date = now()->toDateTimeString();

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/fill/{$difficulty}/{$activityId}")
                ->set([
                    'items' => $items,
                    'updated_at' => $date,
                    'updated_by' => $userId,
                    'total' => count($items)
                ]);

            return response()->json([
                'success' => true,
                'message' => "Updated successfully"
            ]);

        } catch (\Exception $e) {
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
