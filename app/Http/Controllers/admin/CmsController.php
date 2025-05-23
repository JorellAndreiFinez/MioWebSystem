<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CmsController extends Controller
{
    protected $database;
    protected $table = 'PIDCMS';

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
    }


    public function showCMS()
    {
        // Fetch sections from the database
        $pidcms = $this->database->getReference($this->table)->getValue() ?? [];

        return view('mio.head.admin-panel', [
            'page' => 'pid',
            'pidcms' => $pidcms,
        ]);

    }


    public function editNav($key)
{
    $cmsSection = $this->database->getReference($this->table . '/' . $key)->getValue();

    if (!$cmsSection) {
        abort(404, 'CMS Section not found');
    }

    // For demo, if no components exist, initialize with static sample structure
    if (!isset($cmsSection['components'])) {
        $cmsSection['components'] = [
            [
                'type' => 'carousel',
                'name' => 'Homepage Carousel',
                'content' => [
                    [
                        'title' => 'SEGRIA ESGUERRA MEMORIAL FOUNDATION INC',
                        'description' => 'It utilizes “ORAL” instructional methods...',
                        'image' => 'storage/assets/images/1.2.1 home-segria-esguerra-1.png',
                    ],
                    [
                        'title' => 'PHILIPPINE INSTITUTE FOR THE DEAF',
                        'description' => 'Compassionate donors sponsor a child...',
                        'image' => 'storage/assets/images/1.2.2 home-pid.png',
                    ],
                    [
                        'title' => 'SpeechLAB',
                        'description' => 'Speech therapy, aural habilitation...',
                        'image' => 'storage/assets/images/1.2.3 home-speechlab.png',
                    ],
                    [
                        'title' => 'INTEGRATED SCHOOL OF THE PHILIPPINES',
                        'description' => 'A model high school where orally trained deaf graduates...',
                        'image' => 'storage/assets/images/1.2.4 home-isp.png',
                    ],
                ],
            ],
            [
                'type' => 'welcome',
                'name' => 'Welcome Section',
                'content' => [
                    'heading' => 'Welcome to PID, Where Learning Begins!',
                    'paragraph' => 'The Philippine Institute for the Deaf (PID) exists for the purpose of insuring that these unique resources are readily available...',
                    'image' => 'storage/assets/images/1.3 home-welcome-to-pid.png',
                ],
            ],
            [
                'type' => 'why-choose-us',
                'name' => 'Why Choose Us Section',
                'content' => [
                    'heading' => 'Why PID is The Best Choice?',
                    'reasons' => [
                        [
                            'icon' => 'storage/assets/images/1.4.1 home-icon-specialized-education.png',
                            'title' => 'Specialized Education',
                            'description' => 'A proven curriculum that develops listening, speaking, and academic skills.',
                        ],
                        [
                            'icon' => 'storage/assets/images/1.4.2 home-icon-expert-&-caring-educators.png',
                            'title' => 'Expert & Caring Educators',
                            'description' => 'Trained teachers providing personalized guidance for every child.',
                        ],
                        [
                            'icon' => 'storage/assets/images/1.4.3 home-icon-inclusive-community.png',
                            'title' => 'Inclusive Community',
                            'description' => 'A welcoming space where students, families, and educators thrive together.',
                        ],
                    ],
                ],
            ],
            [
                'type' => 'enrollment-process',
                'name' => 'Enrollment Process Section',
                'content' => [
                    'heading' => 'Simple Guide to Join Our School',
                    'steps' => [
                        [
                            'icon' => 'storage/assets/images/icons/enrollment-1.png',
                            'title' => 'Apply for Enrollment',
                            'description' => 'Submit your application and required documents.',
                        ],
                        [
                            'icon' => 'storage/assets/images/icons/interview-1.png',
                            'title' => 'Student Interview',
                            'description' => 'Attend a physical assessment and evaluation.',
                        ],
                        [
                            'icon' => 'storage/assets/images/icons/payment-1.png',
                            'title' => 'Pay Your Balance',
                            'description' => 'Complete your payment to secure enrollment.',
                        ],
                        [
                            'icon' => 'storage/assets/images/icons/waiting-1.png',
                            'title' => 'Wait for First Day',
                            'description' => 'Get ready to start your learning journey!',
                        ],
                    ],
                    'button' => [
                        'text' => 'VIEW DETAILS',
                        'link' => route('enroll'),
                    ],
                ],
            ],
            [
                'type' => 'community-updates',
                'name' => 'Community Updates Section',
                'content' => [
                    'heading' => 'Celebrating Our Achievements',
                    'description' => 'Discover our latest milestones and successes! From student achievements to special events...',
                    'button' => [
                        'text' => 'VIEW STORIES',
                        'link' => route('news'),
                    ],
                    'image' => 'storage/assets/images/1.6 home-community-updates.png',
                ],
            ],
            [
                'type' => 'programs',
                'name' => 'Programs Section',
                'content' => [
                    'heading' => 'Explore Our Programs',
                    'programs' => [
                        [
                            'image' => 'storage/assets/images/1.7.1 home-card-k-12-basic-education.png',
                            'title' => 'K-12 Basic Education',
                        ],
                        [
                            'image' => 'storage/assets/images/1.7.2 home-card-home-economics.png',
                            'title' => 'Home Economics',
                        ],
                        // Add more programs as needed...
                    ],
                ],
            ],
        ];
    }

    return view('mio.head.admin-panel', [
        'page' => 'edit-nav',
        'key' => $key,
        'cms' => $cmsSection,
    ]);
}


    public function updateCMSHomepage(Request $request)
{
    $key = $request->input('key');
    $components = $request->input('components'); // This is already an array

    if (!$key || !$components) {
        return back()->with('error', 'Invalid data submitted.');
    }

    // No need to json_decode, because components is already an array
    if (!is_array($components)) {
        return back()->with('error', 'Components data is invalid.');
    }

    $updateData = ['components' => $components];

    $this->database->getReference($this->table . '/' . $key)->update($updateData);

    return back()->with('success', 'Homepage components updated successfully.');
}







}
