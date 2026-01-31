<?php

namespace App\Http\Controllers;

use App\Jobs\InstallSoftware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class SoftwareController extends Controller
{
    private $tools = [
        'antigravity' => [
            'name' => 'Google Antigravity Editor',
            'bin' => 'antigravity', 
            'url' => null, 
            'description' => 'Advanced Agentic Coding Editor.',
            'icon' => '🚀'
        ],
        'chrome' => [
            'name' => 'Google Chrome',
            'bin' => 'google-chrome',
            'url' => null,
            'description' => 'Fast, secure web browser.',
            'icon' => '🌐'
        ],
        'code' => [
            'name' => 'VS Code',
            'bin' => 'code',
            'url' => null,
            'description' => 'Code editing. Redefined.',
            'icon' => '📝'
        ],
        'tableplus' => [
            'name' => 'TablePlus',
            'bin' => 'tableplus',
            'url' => null, 
            'description' => 'Modern, native tool for database management.',
            'icon' => '🐘'
        ],
        'dbeaver' => [
            'name' => 'DBeaver',
            'bin' => 'dbeaver-ce', 
            'url' => null,
            'description' => 'Universal Database Tool.',
            'icon' => '🦫'
        ]
    ];

    public function index()
    {
        $software = [];
        foreach ($this->tools as $key => $tool) {
            $isInstalled = false;
            // Check if binary exists
            $res = Process::run("which {$tool['bin']}");
            if (!empty(trim($res->output()))) {
                $isInstalled = true;
            }
            
            $software[] = array_merge($tool, [
                'key' => $key,
                'installed' => $isInstalled
            ]);
        }

        return view('software.index', compact('software'));
    }

    public function install(Request $request)
    {
        $key = $request->input('software');
        if (!array_key_exists($key, $this->tools)) {
            return back()->with('error', 'Unknown software');
        }

        InstallSoftware::dispatch($key);

        return back()->with('success', "Installation started for {$this->tools[$key]['name']}. It may take a few minutes.");
    }
}
