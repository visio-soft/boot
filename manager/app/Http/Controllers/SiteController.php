<?php

namespace App\Http\Controllers;

use App\Jobs\CreateProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    public function index()
    {
        $projectsDir = '/var/www/projects';
        $sites = [];

        // 1. List Projects
        if (File::exists($projectsDir)) {
            $directories = File::directories($projectsDir);
            foreach ($directories as $dir) {
                $name = basename($dir);
                if ($name === 'ubuntu-ansible-developer') continue; 
                
                $url = "http://{$name}.test";
                $path = $dir;
                
                $isLaravel = File::exists("$dir/artisan");
                $laravelVersion = $isLaravel ? $this->getLaravelVersion($dir) : null;

                if ($isLaravel) {
                    // Use sudo to check supervisor status for any process containing the site name and 'horizon'
                    // This handles variations like 'gate-horizon' or 'gate-horizon:supervisor-1'
                    $res = Process::run("sudo supervisorctl status | grep {$name}-horizon");
                    if (str_contains($res->output(), 'RUNNING')) {
                        $horizonStatus = 'running';
                    } else {
                        // Check if config exists but maybe stopped
                        if (File::exists("/etc/supervisor/conf.d/{$name}-horizon.conf")) {
                            $horizonStatus = 'stopped';
                        } else {
                            $horizonStatus = 'N/A';
                        }
                    }
                }

                $sites[] = [
                    'name' => $name,
                    'url' => $url,
                    'path' => $path,
                    'type' => $isLaravel ? 'Laravel' : 'Static',
                    'version' => $laravelVersion,
                    'horizon' => $horizonStatus,
                ];
            }
        }

        return view('sites.index', compact('sites'));
    }

    public function checkGit(Request $request)
    {
        $repo = $request->query('repo');
        if (!$repo) return response()->json(['status' => 'error', 'message' => 'Repo missing']);

        // Check if it's SSH
        if (!str_starts_with($repo, 'git@')) {
             // If HTTPS, we can't easily check auth without credentials, but let's assume public or handled via cache
             // User specified SSH key suggestion, so likely SSH.
             return response()->json(['status' => 'ok']); // naive check for https
        }

        // Test SSH connection to host (usually github.com)
        // Extract host "github.com" from "git@github.com:..."
        preg_match('/@(.*):/', $repo, $matches);
        $host = $matches[1] ?? 'github.com';

        // ssh -T -o BatchMode=yes -o StrictHostKeyChecking=no git@github.com
        // Note: github returns exit code 1 even on success (Hi username!...), so we check output.
        // running as www-data might be issue if key is in /home/alp/.ssh
        // But the worker runs as 'alp'. The webserver runs as 'www-data'.
        // This check must run as 'alp' to be accurate for the Job. 
        // We can't easily run as alp from web unless we use sudo or similar.
        
        // Quick hack: Use `sudo -u alp ssh -T ...` ?
        // www-data needs sudo rights to run ssh as alp? That's messy.
        // User runs manager. Manager runs on nginx (www-data).
        // The Job runs as 'alp' (via supervisor).
        // So we need to check if 'alp' has access.
        
        $cmd = "sudo -u alp ssh -T -o BatchMode=yes -o StrictHostKeyChecking=no git@$host 2>&1";
        $result = Process::run($cmd);
        $output = $result->output();

        if (str_contains($output, 'successfully authenticated')) {
            return response()->json(['status' => 'ok']);
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'Access denied', 
                'key_guide' => 'Please add your SSH key to GitHub.',
                'public_key' => $this->getPublicKey()
            ]);
        }
    }

    private function getPublicKey()
    {
        // Try to read alp's public key
        $key = shell_exec("sudo cat /home/alp/.ssh/id_ed25519.pub");
        if (!$key) {
             // Generate one if missing?
             // Checking if it exists first
             return "No public key found in /home/alp/.ssh/id_ed25519.pub";
        }
        return trim($key);
    }

    private function getLaravelVersion($path)
    {
        try {
            $content = file_get_contents("$path/composer.json");
            $json = json_decode($content, true);
            $v = $json['require']['laravel/framework'] ?? 'Unknown';
            return str_replace('^', '', $v);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|alpha_dash|unique:sites,name', // Actually we should check directory existence
            'repo' => 'nullable|string', // If provided, clone. If not, create-project? User: "yeni proje oluştururken github link vericem"
            // Let's make repo optional? "yeni proje oluştururken...". Maybe user wants blank sometimes? 
            // The prompt implies git link is the primary method now.
        ]);
        
        $name = $request->name;
        $repo = $request->input('repo');
        $installHorizon = $request->has('horizon');

        $path = "/var/www/projects/{$name}";

        if (File::exists($path)) {
            return back()->with('error', 'Project path already exists.');
        }

        // Dispatch Job with new params
        CreateProject::dispatch($name, $repo, $installHorizon);

        return back()->with('success', "Project installation started for '$name'. Check Horizon status later.");
    }
}
