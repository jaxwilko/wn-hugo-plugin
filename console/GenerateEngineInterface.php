<?php namespace JaxWilko\Hugo\Console;

use Winter\Storm\Support\Facades\Yaml;
use File;
use Winter\Storm\Console\Command;

class GenerateEngineInterface extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:engine:gen';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:engine:gen
        {--f|force : Force the operation to run and ignore production warnings and confirmation questions.}';

    /**
     * @var string The console command description.
     */
    protected $description = 'No description provided yet...';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $config = Yaml::parse(File::get(base_path('plugins/jaxwilko/hugo/models/sitescript/script.yaml')));

        $keys = array_keys($config);

        $code = '';

        foreach ($keys as $key) {
            $args = $config[$key]['fields'];
            unset($args['_title']);

            $argString = '';

            foreach ($args as $param => $arg) {
                if ($argString) {
                    $argString .= ', ';
                }
                switch ($arg['type']) {
                    case 'text':
                        $argString .= 'string';
                        break;
                    case 'number':
                        $argString .= 'int';
                        break;
                    case 'repeater':
                        $argString .= 'array';
                        break;
                    case 'checkbox':
                        $argString .= 'bool';
                        break;
                    default:
                        throw new \InvalidArgumentException('undefined type ' . $arg['type']);
                }
                $argString .= ' $' . $param;
            }

            echo <<<PHP

                public function $key($argString): ActionResult
                {
                    // @TODO: implement

                    return new ActionResult(true);
                }

            PHP;
        }
    }
}
