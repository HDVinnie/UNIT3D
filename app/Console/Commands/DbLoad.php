<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbLoad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads a pristine copy of the database (useful for testing locally)';

    /**
     * Execute the console command.
     *
     *@throws \Exception
     *
     * @return void
     */
    public function handle(): void
    {
        $input = \config('database.pristine-db-file');
        $db = \config('database.connections.mysql.database');
        $user = \config('database.connections.mysql.username');
        $password = \config('database.connections.mysql.password');

        // Necessary to avoid warning about supplying password on CLI.

        \putenv(\sprintf('MYSQL_PWD=%s', $password));

        $cmd = \sprintf(
            'mysql -u %s %s < %s',
            \escapeshellarg($user),
            \escapeshellarg($db),
            \escapeshellarg($input)
        );

        $return = null;

        $output = null;

        \exec($cmd, $output, $return);

        if ($return !== 0) {
            throw new \RuntimeException(\sprintf('Could not load database from file %s', $input));
        }
    }
}
