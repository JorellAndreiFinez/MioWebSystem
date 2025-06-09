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

class SpecializedSpeechApi extends Controller
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

    private function pronunciationScoreApi(string $audioPath, string $word): array
    {
        $audioPath = Str::after($audioPath, 'public/');

        if (!Storage::disk('public')->exists($audioPath)) {
            return [];
        }

        $filePath = Storage::disk('public')->path($audioPath);

        $client = new Client([
            'base_uri' => 'https://api2.speechace.com',
            'timeout'  => 30,
        ]);

        try {
            $response = $client->request('POST', '/api/scoring/text/v9/json', [
                'query' => [
                    'key'     => env('SPEECHACE_API_KEY'),
                    'dialect' => 'en-us',
                    'user_id' => 'XYZ-ABC-99001',
                ],
                'multipart' => [
                    [
                        'name'     => 'text',
                        'contents' => $word,
                    ],
                    [
                        'name'     => 'user_audio_file',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => basename($filePath),
                    ],
                ],
            ]);

            $decoded = json_decode($response->getBody(), true);

            $cleaned = [
                'text' => $decoded['text_score']['text'] ?? '',
                'overall_quality_score' => $decoded['text_score']['overall_quality_score'] ?? null,
                'ending_punctuation' => $decoded['text_score']['ending_punctuation'] ?? null,
                'ielts_pronunciation_score' => $decoded['text_score']['ielts_score']['pronunciation'] ?? null,
                'pte_pronunciation_score' => $decoded['text_score']['pte_score']['pronunciation'] ?? null,
                'toeic_pronunciation_score' => $decoded['text_score']['toeic_score']['pronunciation'] ?? null,
                'cefr_pronunciation_score' => $decoded['text_score']['cefr_score']['pronunciation'] ?? null,
                'speechace_pronunciation_score' => $decoded['text_score']['speechace_score']['pronunciation'] ?? null,
                'version' => $decoded['version'] ?? null,
                'request_id' => $decoded['request_id'] ?? null,
                'words' => [],
                'timestamp' => now()->toDateTimeString(),
            ];

            if (!empty($decoded['text_score']['word_score_list'])) {
                foreach ($decoded['text_score']['word_score_list'] as $wordData) {
                    $cleaned['words'][] = [
                        'word' => $wordData['word'] ?? '',
                        'quality_score' => $wordData['quality_score'] ?? null,
                        'phones' => $wordData['phone_score_list'] ?? [],
                        'syllables' => $wordData['syllable_score_list'] ?? [],
                    ];
                }
            }

            return $cleaned;

        } catch (RequestException $e) {
            Log::error('Speechace API failure', ['err' => $e->getMessage()]);
            return [];
        }
    }

    public function getSpeechActivities(Request $request, string $subjectId, string $category, string $difficulty){

        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try{
            $activities = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$category}/{$difficulty}")
                ->getSnapshot()
                ->getValue() ?? []; 

            $ids = array_keys($activities);

            return response()->json([
                'success'     => true,
                'activities' => $ids
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getActivityPictureById(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        try {
            $activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($activity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity not found',
                ], 404);
            }

            $flashcards = [];

            $bucket = $this->storage->getBucket();

            foreach($activity['items'] as $index => $item){
                if($item['image_path'] ?? false){
                    $image = $bucket->object($item['image_path'])->signedUrl(now()->addMinutes(15));

                    $flashcards[] = [
                        'flashcard_id' => $index,
                        'image_url' => $image,
                        'text' => $item['text']
                    ];
                }else{
                    $flashcards[] = [
                        'flashcard_id' => $index,
                        'text' => $item['text']
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Activity retrieved successfully',
                'items' => $flashcards
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSpeechPictureActivity(Request $request, string $subjectId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity_type' => 'required|in:picture',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'flashcards' => 'required|array|min:1',
            'flashcards.*.text' => 'required|string|min:1|max:250',
            'flashcards.*.image' => 'required|file|mimes:jpg,png',
        ]);

        try {
            $activity_data = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['flashcards'] as $index => $flashcard) {
                $flashcard_id = (string) Str::uuid();
                $file = $flashcard['image'];
                $text = $flashcard['text'];
                $filename = $file->getClientOriginalName();
                $remotePath = 'images/speech/' . $flashcard_id . $filename ;

                $bucket->upload(
                    fopen($file->getPathname(), 'r'),
                    ['name' => $remotePath]
                );

                $activity_data[$flashcard_id] = [
                    'text' => $text,
                    'filename' => $filename,
                    'image_path' => $remotePath,
                ];
            }

            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId('SPE');
            $date = now()->toDateTimeString();

            $activityData = [
                'items' => $activity_data,
                'total' => count($activity_data),
                'created_at' => $date,
                'created_by' => $userId
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activity_id}")
                ->set($activityData);

            return response()->json([
                'success' => true,
                'message' => "Activity created successfully",
                'activity' => $activityData,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createSpeechActivity(Request $request, string $subjectId){
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'activity_type' => 'required|in:question,phrase,pronunciation',
            'difficulty' => 'required|in:easy,average,difficult,challenge',

            'flashcards' => 'required|array',
            'flashcards.*.text' => 'required|string|min:1|max:250',
        ]);

        try{
            $activity_data = [];

            foreach ($validated['flashcards'] as $index => $flashcard) {
                $id = (string) Str::uuid();
                $activity_data[$id] = [
                    'text' => $flashcard['text'],
                ];
            }

            $activityType = $validated['activity_type'];
            $difficulty = $validated['difficulty'];
            $activity_id = $this->generateUniqueId('SPE');
            $date = now()->toDateTimeString();

            $activityData = [
                'items'=> $activity_data,
                'total' => count($activity_data),
                'created_at' => $date,
                'created_by' => $userId,
            ];

            $this->database
             ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activity_id}")
             ->set($activityData);

            return response()->json([
                'success' => true,
                'message'=> "Activity created successfully",
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editSpeechPictureActivity(Request $request, string $subjectId, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');

        $validated = $request->validate([
            'flashcards' => 'required|array|min:1',
            'flashcards.*.text' => 'required|string|min:1|max:250',
            'flashcards.*.flashcard_id' => 'nullable|uuid',
            'flashcards.*.image' => 'nullable|file|mimes:jpg,png|max:5120',
        ]);

        try {
            $existing_activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/picture/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue();

            if (empty($existing_activity)) {
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found"
                ], 404);
            }

            $mapped_paths = [];
            $mapped_filenames = [];
            foreach ($existing_activity['items'] as $item_id => $item) {
                $mapped_paths[$item_id] = $item['image_path'];
                $mapped_filenames[$item_id] = $item['filename'];
            }

            $updated_items = [];
            $bucket = $this->storage->getBucket();

            foreach ($validated['flashcards'] as $flashcard) {
                $flashcard_id = $flashcard['flashcard_id'] ?? (String) Str::uuid();
                $remotePath = $mapped_paths[$flashcard_id] ?? null;
                $filename = $mapped_filenames[$flashcard_id] ?? null;

                if (isset($flashcard['image']) && $flashcard['image']) {
                    $image = $flashcard['image'];
                    $image_id = (string) Str::uuid();
                    $filename = $image->getClientOriginalName();
                    $remotePath = 'images/speech/' . $image_id . $filename; 

                    if(isset($mapped_paths[$flashcard_id])){
                        $bucket->object($mapped_paths[$flashcard_id])->delete();
                    }

                    $bucket->upload(
                        fopen($image->getPathname(), 'r'),
                        ['name' => $remotePath]
                    );
                }

                $updated_items[$flashcard_id] = [
                    'filename' => $filename,
                    'text' => $flashcard['text'],
                    'image_path' => $remotePath,
                ];
                
            }

            $date = now()->toDateTimeString();
            $userId = $request->get('firebase_user_id');

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/picture/{$difficulty}/{$activityId}")
                ->set([
                    'items' => $updated_items,
                    'total' => count($updated_items),
                    'updated_at' => $date,
                    'updated_by' => $userId
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editSpeechActivity(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        $validated = $request->validate([
            'flashcards' => 'required|array|min:1',
            'flashcards.*.flashcard_id' => 'nullable|uuid',
            'flashcards.*.text' => 'required|string|min:1|max:250',
        ]);

        try {
            $existing_activity = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (empty($existing_activity)) {
                return response()->json([
                    'success' => false,
                    'message' => "Activity not found"
                ], 404);
            }

            $updated_items = [];
            foreach ($validated['flashcards'] as $flashcard) {
                $flashcard_id = $flashcard['flashcard_id'] ?? (string) Str::uuid();

                $updated_items[] = [
                    'flashcard_id' => $flashcard_id,
                    'text' => $flashcard['text'],
                ];
            }

            $date = now()->toDateTimeString();

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->set([
                    'items' => $updated_items,
                    'total' => count($updated_items),
                    'updated_at' => $date,
                    'updated_by' => $userId
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function startFlashcardActivity(Request $request, string $subjectId, string $activityType, string $difficulty, string $activityId)
    {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');

        try{
            $activityData = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/specialized/{$activityType}/{$difficulty}/{$activityId}")
                ->getSnapshot()
                ->getValue() ?? [];

            if (!$activityData) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Activity not found.'
                ], 404);
            }

            $flashcards = $activityData['items'];
            
            $attemptId = $this->generateUniqueId("ATTM");
            $startedAt = now()->toDateTimeString();

            $bucket = $this->storage->getBucket();

            $studentAnswers = [];
            $attemp = [];
            foreach ($flashcards as $flashcardId => $item) {
                $imagePath = $item['image_path'] ?? null;
                $imageUrl = null;

                if ($imagePath) {
                    $imageUrl = $bucket->object($imagePath)->signedUrl(now()->addMinutes(15));
                }

                $studentAnswers[$flashcardId] = [
                    'text' => $item['text'],
                    'image_url' => $imageUrl,
                ];

                $attemp[$flashcardId] = [
                    'text' => $item['text'],
                    'image_path' => $imagePath,
                ];
            }
            
            $initialInfo = [
                'answers' => $attemp,
                'started_at' => $startedAt,
                'status'     => 'in-progress',
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}")
                ->set($initialInfo);

            return response()->json([
                'success' => true,
                'attemptId' => $attemptId,
                'flashcards' => $studentAnswers,
            ],201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitFlashcardAnswer(
        Request $request,
        string $subjectId,
        string $activityType,
        string $activityId,
        string $attemptId,
        string $flashcardId
    ) {
        try {
            $gradeLevel = $request->get('firebase_user_gradeLevel');
            $userId = $request->get('firebase_user_id');

            $data = $request->validate([
                'audio_file' => 'required|file|mimetypes:video/mp4,audio/mp3',
            ]);

            $answersRef = $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}/answers")
                ->getSnapshot()
                ->getValue() ?? [];

            $answer = $answersRef[$flashcardId];

            if (!isset($answersRef[$flashcardId])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid flashcard ID for this attempt',
                ], 400);
            }

            $answer = $answersRef[$flashcardId];
            $file = $request->file('audio_file');
            $uuid = (string) Str::uuid();
            $filename = $uuid . $file->getClientOriginalName();
            $path = $file->storeAs('audio_submissions', $filename, 'public');
            $remotePath = "audio/speech/{$activityType}/{$activityId}/{$userId}/{$attemptId}/{$filename}";
            $word = $answer['text'];

            $pronunciation_details = $this->pronunciationScoreApi($path, $word);
            $bucket = $this->storage->getBucket();
            $bucket->upload(
                fopen($file->getPathName(), 'r'),
                ['name' => $remotePath]
            );

            $now = now()->toDateTimeString();
            $updatedAnswer = [
                'audio_path' => $remotePath,
                'answered_at' => $now,
                'pronunciation_details' => $pronunciation_details,
            ];

            $this->database
                ->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}/answers/{$flashcardId}")
                ->update($updatedAnswer);

            Storage::disk('public')->delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function finalizeFlashcardAttempt(
        Request $request,
        string $subjectId,
        string $activityType,
        string $difficulty,
        string $activityId,
        string $attemptId
    ) {
        $gradeLevel = $request->get('firebase_user_gradeLevel');
        $userId = $request->get('firebase_user_id');
        $now = now()->toDateTimeString();

        $ref = $this->database->getReference("subjects/GR{$gradeLevel}/{$subjectId}/attempts/{$activityType}/{$activityId}/{$userId}/{$attemptId}");

        try {
            $answers = $ref->getChild('answers')->getSnapshot()->getValue() ?? [];

            $scores = [];
            $totalQuality = 0;
            $numCards = count($answers);

            foreach ($answers as $cardId => $answer) {
                $details = $answer['pronunciation_details'] ?? [];
                $wordsList = $details['words'] ?? [];

                if (! empty($wordsList) && is_array($wordsList[0])) {
                    $w = $wordsList[0];

                    $quality = $w['quality_score'] ?? 0;
                    $totalQuality += $quality;

                    $scores[$cardId] = [
                        'word' => $w['word'] ?? '',
                        'quality_score' => $quality,
                        'phones' => $w['phones'] ?? [],
                        'syllables' => $w['syllables'] ?? [],
                        'timestamp' => $details['timestamp'] ?? $now,
                    ];
                } else {
                    $scores[$cardId] = [
                        'word' => '',
                        'quality_score' => 0,
                        'phones' => [],
                        'syllables' => [],
                        'timestamp' => $now,
                    ];
                }
            }

            $overallAverage = $numCards > 0
                ? round($totalQuality / $numCards, 2)
                : 0;

            $ref->update([
                'status' => 'submitted',
                'overall_score' => $overallAverage,
                'submitted_at' => $now,
            ]);

            return response()->json([
                'success'       => true,
                'message'       => 'Activity submitted successfully.',
                'scores'        => $scores, // remove from the frontend // for teahcer only
                'overall_score' => $overallAverage,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Could not update activity status.',
            ], 500);
        }
    }
}