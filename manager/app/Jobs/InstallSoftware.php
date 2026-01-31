<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class InstallSoftware implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $software;

    public function __construct(string $software)
    {
        $this->software = $software;
    }

    public function handle(): void
    {
        $software = $this->software;
        Log::info("Starting installation of $software");

        try {
            switch ($software) {
                case 'code':
                    $this->installVSCode();
                    break;
                case 'chrome':
                    $this->installChrome();
                    break;
                case 'dbeaver':
                    $this->installDBeaver();
                    break;
                case 'tableplus':
                    $this->installTablePlus();
                    break;
                case 'antigravity':
                    $this->installAntigravity();
                    break;
                default:
                    Log::warning("Unknown software: $software");
            }
        } catch (\Exception $e) {
            Log::error("Installation failed for $software: " . $e->getMessage());
        }
    }

    private function installVSCode()
    {
        Log::info("Installing VS Code...");
        Process::run('sudo mkdir -p /etc/apt/trusted.gpg.d');
        Process::run('curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor --yes | sudo tee /etc/apt/trusted.gpg.d/microsoft.gpg > /dev/null');
        Process::run('echo "deb [arch=amd64,arm64,armhf] https://packages.microsoft.com/repos/code stable main" | sudo tee /etc/apt/sources.list.d/vscode.list');
        Process::run('sudo apt-get update');
        Process::run('sudo apt-get install -y code');
        Log::info("VS Code installed.");
    }

    private function installChrome()
    {
        Log::info("Installing Google Chrome...");
        $temp = '/tmp/google-chrome-stable_current_amd64.deb';
        Process::run("wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb -O $temp");
        Process::run("sudo apt-get install -y $temp");
        Process::run("rm $temp");
        Log::info("Chrome installed.");
    }

    private function installDBeaver()
    {
        Log::info("Installing DBeaver...");
        Process::run('curl -fsSL https://dbeaver.io/debs/dbeaver.gpg.key | gpg --dearmor --yes | sudo tee /etc/apt/trusted.gpg.d/dbeaver.gpg > /dev/null');
        Process::run('echo "deb https://dbeaver.io/debs/dbeaver-ce /" | sudo tee /etc/apt/sources.list.d/dbeaver.list');
        Process::run('sudo apt-get update');
        Process::run('sudo apt-get install -y dbeaver-ce');
        Log::info("DBeaver installed.");
    }

    private function installTablePlus()
    {
        // TablePlus Linux (Alpha/Beta usually, but standard repo exists)
        Log::info("Installing TablePlus...");
        Process::run('mkdir -p /etc/apt/keyrings');
        Process::run('wget -qO - https://deb.tableplus.com/apt.tableplus.com.gpg.key | gpg --dearmor | sudo tee /etc/apt/keyrings/tableplus-archive-keyring.gpg > /dev/null');
        Process::run('echo "deb [signed-by=/etc/apt/keyrings/tableplus-archive-keyring.gpg] https://deb.tableplus.com/debian tableplus main" | sudo tee /etc/apt/sources.list.d/tableplus.list');
        Process::run('sudo apt-get update');
        Process::run('sudo apt-get install -y tableplus');
        Log::info("TablePlus installed.");
    }

    private function installAntigravity()
    {
        Log::info("Installing Google Antigravity...");
        // Re-using commands from software.yml that we removed
        Process::run('sudo mkdir -p /etc/apt/keyrings');
        Process::run('curl -fsSL https://us-central1-apt.pkg.dev/doc/repo-signing-key.gpg | gpg --dearmor --yes | sudo tee /etc/apt/keyrings/antigravity-repo-key.gpg > /dev/null');
        Process::run('echo "deb [signed-by=/etc/apt/keyrings/antigravity-repo-key.gpg] https://us-central1-apt.pkg.dev/projects/antigravity-auto-updater-dev/ antigravity-debian main" | sudo tee /etc/apt/sources.list.d/antigravity.list');
        Process::run('sudo apt-get update');
        Process::run('sudo apt-get install -y antigravity');
        Log::info("Google Antigravity installed.");
    }
}
