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

    public function create()
    {
        return view('admin.cms.create');
    }

    public function edit($id)
    {
        return view('admin.cms.edit', compact('id'));
    }

    public function show($id)
    {
        return view('admin.cms.show', compact('id'));
    }
}
