<?php

namespace JaxWilko\Hugo\Console;

use Winter\Storm\Parse\EnvFile;
use ZipArchive;
use Symfony\Component\HttpFoundation\Response;
use Winter\Storm\Console\Command;
use Winter\Storm\Network\Http;

class InstallChrome extends Command
{
    public const string VERSIONS_URL = 'https://googlechromelabs.github.io/chrome-for-testing/known-good-versions.json';
    public const string DOWNLOAD_CHROME_URL = 'https://storage.googleapis.com/chrome-for-testing-public/%s/linux64/chrome-linux64.zip';
    public const string DOWNLOAD_DRIVER_URL = 'https://storage.googleapis.com/chrome-for-testing-public/%s/linux64/chromedriver-linux64.zip';
    public const string TEMP_CHROME_FILE = 'google-chrome-%s-linux64.zip';
    public const string TEMP_DRIVER_FILE = 'google-chromedriver-%s-linux64.zip';
    public const string STORAGE_DIR = 'hugo/google-chrome/%s';

    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:install-chrome';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:install-chrome
        {--i|interactive : Displays a choice of Chrome versions}
        {--s|specific= : Install a specific version of Chrome}
    ';

    /**
     * @var string The console command description.
     */
    protected $description = 'No description provided yet...';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(): void
    {
        $requestedVersion = $this->option('specific');

        $response = Http::get(static::VERSIONS_URL);

        if ($response->code !== Response::HTTP_OK) {
            throw new \RuntimeException('Could not download chrome version list');
        }

        $versions = json_decode($response->body);

        if (!isset($versions->versions)) {
            throw new \RuntimeException('Downloaded versions list is malformed');
        }

        if ($this->option('interactive')) {
            $requestedVersion = $this->components->choice(
                'Which version of Chrome should Hugo install',
                array_values(array_unique(array_map(
                    fn ($version) => explode('.', $version->version, 2)[0],
                    $versions->versions
                )))
            );
        }

        $version = $this->getRequestedVersion($requestedVersion, $versions->versions);

        if (!$version) {
            throw new \RuntimeException('Could not find a version available');
        }

        $this->components->info('Selected version: ' . $version->version);

        $chromePath = temp_path(sprintf(self::TEMP_CHROME_FILE, $version->version));
        $driverPath = temp_path(sprintf(self::TEMP_DRIVER_FILE, $version->version));
        $dir = storage_path(sprintf(self::STORAGE_DIR, $version->version));

        if (!file_exists($chromePath)) {
            $this->components->task('Downloading Chrome ' . $version->version, function () use ($chromePath, $version) {
                Http::get(sprintf(static::DOWNLOAD_CHROME_URL, $version->version), function (Http $http) use ($chromePath) {
                    $http->streamFile = $chromePath;
                });
            });
        }

        if (!file_exists($driverPath)) {
            $this->components->task('Downloading Driver ' . $version->version, function () use ($driverPath, $version) {
                Http::get(sprintf(static::DOWNLOAD_DRIVER_URL, $version->version), function (Http $http) use ($driverPath) {
                    $http->streamFile = $driverPath;
                });
            });
        }

        if (!file_exists($chromePath) || !file_exists($driverPath)) {
            throw new \RuntimeException('Could not download Chrome version');
        }

        $this->components->task('Unpacking Chrome', function () use ($chromePath, $dir) {
            $zip = new ZipArchive();
            if (!$zip->open($chromePath)) {
                throw new \RuntimeException('Could not open zip file');
            }

            $zip->extractTo($dir);
            $zip->close();
        });

        $this->components->task('Unpacking Driver', function () use ($driverPath, $dir) {
            $zip = new ZipArchive();
            if (!$zip->open($driverPath)) {
                throw new \RuntimeException('Could not open zip file');
            }

            $zip->extractTo($dir);
            $zip->close();
        });

        $this->components->task('Making driver executable', function () use ($dir) {
            chmod($dir . '/chromedriver-linux64/chromedriver', 0744);
            chmod($dir . '/chrome-linux64/chrome', 0744);
            chmod($dir . '/chrome-linux64/chrome_crashpad_handler', 0744);
        });

        $this->components->task('Setting Chrome version in env', function () use ($version) {
            $env = EnvFile::open(base_path('.env'));
            $env->set('HUGO_CHROME_VERSION', $version->version);
            $env->write();
        });

        $this->output->newLine();
        $this->components->info('Install complete!');
    }

    public function getRequestedVersion(?string $requestedVersion, array $versions): ?object
    {
        if (!$requestedVersion) {
            usort($versions, fn ($a, $b) => version_compare($b->version, $a->version));
            return array_shift($versions);
        }

        $filter = [];
        foreach ($versions as $version) {
            if (str_starts_with($version->version, $requestedVersion)) {
                $filter[] = $version;
            }
        }

        return $this->getRequestedVersion(null, $filter);
    }
}
