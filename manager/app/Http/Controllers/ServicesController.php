<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;

class ServicesController extends Controller
{
    private $services = [
        'nginx' => 'Nginx',
        'php8.4-fpm' => 'PHP 8.4 FPM',
        'postgresql' => 'PostgreSQL',
        'redis-server' => 'Redis',
    ];

    private $logs = [
        'nginx' => '/var/log/nginx/error.log',
        'php' => '/var/log/php8.4-fpm.log',
        'redis' => '/var/log/redis/redis-server.log', 
        // Auto-detect postgres version in index method or hardcode based on inspection
        'postgres' => '/var/log/postgresql/postgresql-14-main.log', 
    ];

    public function index()
    {
        $status = [];
        foreach ($this->services as $service => $label) {
            $res = Process::run("systemctl is-active $service");
            $status[$service] = [
                'label' => $label,
                'active' => trim($res->output()) === 'active',
            ];
        }

        // Projects for logs
        $projects = [];
        $projectsDir = '/var/www/projects';
        if (File::exists($projectsDir)) {
            foreach (File::directories($projectsDir) as $dir) {
                if (basename($dir) === 'ubuntu-ansible-developer') continue;
                $projects[] = basename($dir);
            }
        }

        return view('services.index', compact('status', 'projects'));
    }

    public function restart(Request $request) 
    {
        $service = $request->input('service');
        if (!array_key_exists($service, $this->services)) {
            return back()->with('error', 'Invalid service');
        }

        Process::run("sudo systemctl restart $service");
        
        // Wait a bit?
        sleep(1);

        return back()->with('success', "Restarted {$this->services[$service]}");
    }

    public function logs(Request $request, $type = 'nginx')
    {
        $logPath = '';
        $title = ucfirst($type);

        if ($type === 'project') {
            $project = $request->input('project');
            $logPath = "/var/www/projects/{$project}/storage/logs/laravel.log";
            $title = "Project: $project";
        } elseif (isset($this->logs[$type])) {
            $logPath = $this->logs[$type];
            // Adjust postgres log dynamically if possible? 
            if ($type === 'postgres') {
                // Find actual log file
                $files = glob('/var/log/postgresql/postgresql-*-main.log');
                if (!empty($files)) {
                    $logPath = end($files); // Use latest version found
                }
            }
        } else {
            return redirect()->route('services.index');
        }

        // Read last 200 lines for better context in viewer
        $content = '';
        if ($logPath) {
            $cmd = "sudo tail -n 200 $logPath";
            $res = Process::run($cmd);
            $content = $res->output();
            if (empty($content) && !empty($res->errorOutput())) {
                $content = "Error reading log: " . $res->errorOutput();
            }
        } else {
            $content = "Log file not found.";
        }

        if ($request->has('json')) {
            return response()->json([
                'content' => $content,
                'path' => $logPath
            ]);
        }

        return view('services.logs', compact('content', 'title', 'type', 'logPath'));
    }

    public function phpIni()
    {
        $path = '/etc/php/8.4/fpm/php.ini';
        $content = '';
        
        if (File::exists($path)) {
            $content = file_get_contents($path);
        } else {
            // fallback check
            $res = Process::run("php --ini");
            // parse output?
            $content = "; Could not read $path directly. \n; Output of php --ini:\n" . $res->output();
        }

        return view('services.php', compact('content', 'path'));
    }

    public function savePhpIni(Request $request)
    {
        $content = $request->input('content');
        $path = '/etc/php/8.4/fpm/php.ini';

        // Dangerous, but requested.
        // Write to temp file then move?
        // Or echo to sudo tee
        
        // Sanitize? It's root level config. User is trusted.
        // We can't pass huge content via command line easily if too big.
        // Better: write to temporary file in storage, then sudo cp
        
        $tempFile = tempnam(sys_get_temp_dir(), 'phpini');
        file_put_contents($tempFile, $content);

        Process::run("sudo cp $tempFile $path");
        Process::run("sudo systemctl restart php8.4-fpm");
        
        unlink($tempFile);

        return back()->with('success', 'PHP.ini updated and PHP-FPM restarted.');
    }
}
