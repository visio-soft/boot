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
            'package' => 'antigravity',
            'url' => null, 
            'description' => 'Advanced Agentic Coding Editor.',
        ],
        'chrome' => [
            'name' => 'Google Chrome',
            'bin' => 'google-chrome',
            'package' => 'google-chrome-stable',
            'url' => null,
            'description' => 'Fast, secure web browser.',
        ],
        'code' => [
            'name' => 'VS Code',
            'bin' => 'code',
            'package' => 'code',
            'url' => null,
            'description' => 'Code editing. Redefined.',
        ],
        'phpstorm' => [
            'name' => 'PhpStorm',
            'bin' => 'phpstorm',
            'package' => 'phpstorm', // Assuming PPA or mostly likely snap, but using this for apt if available
            'url' => null,
            'description' => 'The Lightning-Smart PHP IDE.',
        ],
        'tableplus' => [
            'name' => 'TablePlus',
            'bin' => 'tableplus',
            'package' => 'tableplus',
            'url' => null, 
            'description' => 'Modern, native tool for database management.',
        ],
        'dbeaver' => [
            'name' => 'DBeaver',
            'bin' => 'dbeaver-ce', 
            'package' => 'dbeaver-ce',
            'url' => null,
            'description' => 'Universal Database Tool.',
        ]
    ];

    public function index()
    {
        $software = [];
        
        // simple cache of upgradable packages
        $upgradable = [];
        $res = Process::run("apt list --upgradable");
        if ($res->successful()) {
            $upgradable = $res->output();
        }

        foreach ($this->tools as $key => $tool) {
            $isInstalled = false;
            $currentVersion = null;
            $hasUpdate = false;

            // Check if binary exists
            $res = Process::run("which {$tool['bin']}");
            if (!empty(trim($res->output()))) {
                $isInstalled = true;
                
                // Get Version
                if ($key === 'chrome') {
                    $v = Process::run("google-chrome --version");
                    $currentVersion = str_replace('Google Chrome ', '', trim($v->output()));
                } elseif ($key === 'code') {
                    $v = Process::run("code --version");
                    $lines = explode("\n", $v->output());
                    $currentVersion = $lines[0] ?? null;
                } elseif ($key === 'dbeaver') {
                    $v = Process::run("dbeaver-ce --version");
                    $currentVersion = str_replace('DBeaver ', '', trim($v->output()));
                } elseif ($key === 'antigravity') {
                     $v = Process::run("antigravity --version");
                     $lines = explode("\n", $v->output());
                     $currentVersion = $lines[0] ?? null;
                } elseif ($key === 'tableplus') {
                    $v = Process::run("dpkg -s tableplus | grep Version");
                    if ($v->successful()) {
                         $currentVersion = str_replace('Version: ', '', trim($v->output()));
                    }
                } elseif ($key === 'phpstorm') {
                    // Try getting version if installed via snap or simple bin
                    $v = Process::run("phpstorm --version"); 
                    // output often complex, or might not exist if valid license needed
                    // simplistic check
                    if ($v->successful()) {
                         preg_match('/PhpStorm (20\d\d\.\d(\.\d)?)/', $v->output(), $m);
                         $currentVersion = $m[1] ?? 'Detected';
                    }
                }

                // Check update
                if (isset($tool['package']) && str_contains($upgradable, $tool['package'] . '/')) {
                    $hasUpdate = true;
                }
            }
            
            $software[] = array_merge($tool, [
                'key' => $key,
                'installed' => $isInstalled,
                'version' => $currentVersion,
                'has_update' => $hasUpdate
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
