<?php

namespace JaxWilko\Hugo\Console;

use Backend\Models\BrandSetting;
use Winter\Storm\Console\Command;
use Symfony\Component\Console\Terminal;
use Winter\Storm\Parse\EnvFile;

class HugoInstall extends Command
{
    public const int INTRO_TIMER = 4;

    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:install';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:install';

    /**
     * @var string The console command description.
     */
    protected $description = 'No description provided yet...';

    protected Terminal $terminal;

    public function __construct()
    {
        parent::__construct();

        $this->terminal = new Terminal();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $face = $this->getFace();
        $faceTalking = $face;
        array_splice($faceTalking, 24, 0, $face[24]);

        $width = $this->terminal->getWidth();

        echo "\033[2J"; // clear screen
        echo "\033[?25l"; // hide cursor
        echo "\033[?7l"; // disable wrapping

        $endAt = time() + static::INTRO_TIMER;
        $faceLast = true;
        do {
            $time = $endAt - time();

            $this->putIntroFrame($face, $faceTalking, $faceLast, $width, $time);
            $faceLast = !$faceLast;

            usleep(300000);
        } while ($time > 0);

        $this->putIntroFrame($face, $faceTalking, true, $width, $time);

        echo "\033[?7h"; // enable wrapping
        echo "\033[?25h"; // show cursor

        $this->putLine(
            'Hugo uses Chrome to run actions, Chrome is installed locally within Hugo and does not effect the rest of your system.',
            'NOTICE'
        );

        if ($this->components->choice('Would you like Hugo to install Chrome now?', ['Yes', 'No'], 'Yes') === 'Yes') {
            $this->runCommand('hugo:install-chrome', [], $this->output);
        } else {
            $this->putLine('This can be done later via the `hugo:install-chrome` command.', 'NOTICE', true);
        }

        $this->putLine(
            'Hugo requires Node & the lighthouse npm package to support lighthouse reporting.',
            'NOTICE',
            true
        );

        if ($this->components->choice('Would you like install the required npm packages now?', ['Yes', 'No'], 'Yes') === 'Yes') {
            $this->runCommand('vite:install', [
                'assetPackage' => ['JaxWilko.Hugo']
            ], $this->output);
        } else {
            $this->putLine('This can be done later via the `vite:install JaxWilko.Hugo` command.', 'NOTICE', true);
        }

        $this->putLine(PHP_EOL);

        $this->putLine(
            'Hugo supports custom backend style overrides, these make the backend interface nicer when combined with the Winter.TailwindUI package.',
            'NOTICE',
            true
        );

        if ($this->components->choice('Would you like to enable the backend styles now?', ['Yes', 'No'], 'Yes') === 'Yes') {
            EnvFile::open(base_path('.env'))
                ->set('HUGO_APPLY_STYLES', true)
                ->write();

            BrandSetting::set([
                'primary_color' => '#0C1015',
                'secondary_color' => '#05306F',
                'accent_color' => '#1DB3A7'
            ]);
        } else {
            $this->putLine('This can be done later by adding `HUGO_APPLY_STYLES=true` to your env.', 'NOTICE', true);
        }

        if ($this->components->choice('Would you like to enable Hugo using the Winter Scheduler?', ['Yes', 'No'], 'Yes') === 'Yes') {
            EnvFile::open(base_path('.env'))
                ->set('HUGO_ENABLE_SCHEDULER', true)
                ->write();
        } else {
            $this->putLine('This can be done later by adding `HUGO_ENABLE_SCHEDULER=true` to your env.', 'NOTICE', true);
        }

        if ($this->components->choice('Would you like to hide the media system?', ['Yes', 'No'], 'Yes') === 'Yes') {
            EnvFile::open(base_path('.env'))
                ->set('HUGO_HIDE_MEDIA', true)
                ->write();
        } else {
            $this->putLine('This can be done later by adding `HUGO_HIDE_MEDIA=true` to your env.', 'NOTICE', true);
        }

        if ($this->components->choice('Would you like to hide the collapse the Hugo menu?', ['Yes', 'No'], 'No') === 'No') {
            EnvFile::open(base_path('.env'))
                ->set('HUGO_COLLAPSE_MENU', false)
                ->write();
        } else {
            $this->putLine('This can be done later by adding `HUGO_HIDE_MEDIA=true` to your env.', 'NOTICE', true);
        }

        sleep(2);

        echo "\033[2J"; // clear screen
        echo "\033[H"; // Move cusor
        echo $this->pad($face);

        $this->putBlank($width);

        $this->putLine('Hugo has now been setup!', 'SUCCESS', true);
        $this->putLine('You can go to your admin panel and start configuring sites', 'SUCCESS', true);

        return 0;
    }

    public function getFace(): array
    {
        $b = "\e[0;34m";
        $w = "\e[0;37m";
        $r = "\e[0m";

        return [
            "                  {$b}▓▓▒▒▒▓▓",
            "           {$b}▓▓▓▓▓▓▒░░░░░░░░▓",
            "        {$b}▒░░░░░░░░░░░░░░░░░░▒",
            "      {$b}▓░░░░░░░░░░░░░░░░░░░░░▓",
            "     {$b}▓░░░░░░░░░░░░░░░░░░░░░░░▓",
            "     {$b}░░░░░░░░░░░░░░░░░░░░░░░░░░▒▒▓",
            "    {$b}▓░░░░░░░░░░░░░░░░▒▓▓{$w}█{$b}▓▒░░░░░░░▒▓",
            "   {$b}▓░░░░░░░░░░░░░░▒▒▓{$w}███████{$b}▓▒░░░░░░▓",
            "  {$b}▒░░░░░░░░░░░░░░▒▓{$w}███████████{$b}▓░░░░░▒",
            " {$b}▒░░░░░░░░░░░░░▒▓▓{$w}█████████████{$b}▓░░░░░",
            "{$b}▓░░░░░░░░░░░░░▒▓▓{$w}███████████████{$b}▓░░░░",
            "{$b}▒░░░░░░░░░░▒▒▒▓▓{$w}█████████████████{$b}▒░░▒",
            "{$b}▓░░░░░▒▒▒▒▓▓▓{$w}████████████████████{$b}▒░░░",
            "{$b}▓░░░░░▒{$w}███████{$b}░░░░░{$w}██{$b}░░░░░{$w}███████{$b}▒░░░▒",
            " {$b}▒░▒▓▒▒{$w}███████{$b}▓▓▓▓▓{$w}███{$b}▓▓▓▓{$w}███████{$b}▒░▓▒░▒",
            " {$b}▒░▓{$w}█{$b}▒▒{$w}████████{$b}▓▒▒▓{$w}██{$b}▓▒░▓▓{$w}███████{$b}▒░█{$b}▓░░▓",
            " {$b}░░▓{$w}█{$b}▒▓{$w}████████{$b}▓░░▓{$w}██{$b}▓░░▓{$w}████████{$b}▒░█{$b}▓░░▒",
            "{$b}▓░░▓{$w}█{$b}▒▓{$w}██████████████████████████{$b}▒░{$w}█{$b}▓░░▓",
            "{$b}▓░░▒{$w}█{$b}▒▓{$w}███████████{$b}▓▒░▒{$w}███████████{$b}▒░{$w}█{$b}▓░▒",
            " {$b}░░▒{$w}█{$b}▒▓{$w}██████████{$b}▓▒{$w}██{$b}░▓{$w}██████████{$b}▒░{$w}█{$b}▓▒",
            " {$b}▓░░░▒▓{$w}█████████{$b}▒▓{$w}████{$b}▓▒▓{$w}████████{$b}▒░░▓",
            "   {$b}▒▒░▓{$w}███████{$b}▓▒░▒▓{$w}██{$b}▓▓▒▒▓▓{$w}█████{$b}▓▒░▒",
            "    {$b}▓░▒{$w}██████{$b}▒░░░░░░▒░░░░░░▓{$w}████{$b}▓▒░▒",
            "    {$b}▓░░{$w}███████{$b}▓▒▒▓{$w}████{$b}▓▓▒▒▓{$w}█████{$b}▓▒░▓",
            "     {$b}▒░▒{$w}███████████████████████{$b}▓▒░▒",
            "     {$b}▓░░▓{$w}██████████{$b}▓▓{$w}█████████{$b}▓▓▒░▓",
            "      {$b}▒░░▓{$w}██████{$b}▓▓{$w}████{$b}▓▓{$w}█████{$b}▓▓▒░▓",
            "       {$b}▓░░▒▓{$w}████████████████{$b}▓▓▒░▒",
            "        {$b}▓▒░▒▓▓▓{$w}███████████{$b}▓▓▒▒░▓",
            "          {$b}▓░░▒▒▒▓▓▓▓▓▓▓▓▓▒▒▒░▒",
            "            {$b}▓▒░░░░▒▒▒▒░░░░▒▓▓",
            "{$r}",
        ];
    }

    public function pad(array $lines): string
    {
        $width = $this->terminal->getWidth();

        $linesMaxLength = 0;

        foreach ($lines as $index => $line) {
            $lines[$index] = [
                'str' => $line,
                'length' => $this->strlen($line),
            ];

            $linesMaxLength = max($linesMaxLength, $lines[$index]['length']);
        }

        $padding = ($width - $linesMaxLength) / 2;

        $str = '';
        foreach ($lines as $line) {
            $str .= str_repeat(' ', $padding)
                . $line['str']
                . str_repeat(' ', ($width - ($padding + $line['length'] - 1)) - 1)
                . PHP_EOL;
        }

        return $str;
    }

    public function strlen(string $str): int
    {
        return mb_strlen(preg_replace('/\x1B\[[0-9;]*[A-Za-z]/', '', $str));
    }

    public function putLine(string $str, string $style = 'INFO', bool $blankAfter = false): void
    {
        $colour = match ($style) {
            'INFO' => 44,
            'NOTICE' => 43,
            'SUCCESS' => 40,
        };

        echo "  \e[{$colour}m $style \e[0m $str" . PHP_EOL;

        if ($blankAfter) {
            echo PHP_EOL;
        }
    }

    public function putBlank(int $width): void
    {
        echo str_repeat(' ', $width) . PHP_EOL;
    }

    public function putIntroFrame(array $face1, array $face2, bool $faceLast, int $width, int $time): void
    {
        echo "\033[H";
        echo $this->pad($faceLast ? $face1 : $face2);

        if ($faceLast) {
            $this->putBlank($width);
        }

        if ($time < 4) {
            $this->putBlank($width);
            $this->putLine('Welcome to the Hugo Installer');
            $this->putBlank($width);
        }

        if ($time < 3) {
            $this->putLine('You will now be guided through the installation process, this may take a few minutes.');
            $this->putBlank($width);
        }

        if ($time < 2) {
            $this->putLine('If you have any questions, reach out to us for support here: https://github.com/jaxwilko/wn-hugo-plugin');
            $this->putBlank($width);
        }
    }
}
