<?php

declare(strict_types=1);
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @see \Tests\Todo\Unit\Console\Commands\VendorCleanupTest
 */
class VendorCleanup extends Command
{
    protected $signature = 'vendor:cleanup {--check : Runs in dry mode without deleting files.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleans up useless files from  vendor folder.';

    protected array $patterns =
        [
            'test',
            'tests',
            '.github',
            'README',
            'CHANGELOG',
            'FAQ',
            'CONTRIBUTING',
            'HISTORY',
            'UPGRADING',
            'UPGRADE',
            'demo',
            'example',
            'examples',
            '.doc',
            'readme',
            'changelog',
            'composer',
            '.git',
            '.gitignore',
            '*.md',
            '.*.yml',
            '*.yml',
            '*.txt',
            '*.dist',
            'LICENSE',
            'AUTHORS',
            '.eslintrc',
            'ChangeLog',
            '.editorconfig',
            '*.xml',
            '.npmignore',
            '.jshintrc',
            'Makefile',
            '.keep',
        ];

    /**
     * List of File and Folders Patters Going To Be Excluded.
     *
     * @return void
     */
    protected array $excluded =
        [
            /**List of  Folders*/
            'src',
            /**List of  Files*/
            '*.php',
            '*.stub',
            '*.js',
            '*.json',
            '.gitignore',
        ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $patterns = \array_diff($this->patterns, $this->excluded);

        $directories = $this->expandDirectoryTree(\base_path('vendor'));

        $isDry = $this->option('check');

        foreach ($directories as $directory) {
            foreach ($patterns as $pattern) {
                $casePattern = \preg_replace_callback('#([a-z])#i', fn ($matches) => $this->prepareWord($matches), $pattern);

                $files = \glob($directory.'/'.$casePattern, GLOB_BRACE);

                if (! $files) {
                    continue;
                }

                $files = \array_diff($files, $this->excluded);

                foreach ($this->excluded as $excluded) {
                    $key = $this->arrayFind($excluded, $files);

                    if ($key !== false) {
                        $this->out('SKIPPED: '.$files[$key]);
                        unset($files[$key]);
                    }
                }
                foreach ($files as $file) {
                    if (\is_dir($file)) {
                        $this->out('DELETING DIR: '.$file);
                        if (! $isDry) {
                            $this->delTree($file);
                        }
                    } else {
                        $this->out('DELETING FILE: '.$file);
                        if (! $isDry) {
                            @\unlink($file);
                        }
                    }
                }
            }
        }
        $this->out('Vendor Cleanup Done!');
    }

    /**
     * Recursively traverses the directory tree.
     *
     * @param string $dir
     *
     * @return array
     */
    protected function expandDirectoryTree(string $dir): array
    {
        $directories = [];
        foreach (\array_diff(\scandir($dir), ['.', '..']) as $file) {
            $directory = $dir.'/'.$file;
            if (\is_dir($directory)) {
                $directories[] = $directory;
                $directories = \array_merge($directories, $this->expandDirectoryTree($directory));
            }
        }

        return $directories;
    }

    /**
     * Recursively deletes the directory.
     *
     * @param string $dir
     *
     * @return bool|null
     */
    protected function delTree(string $dir): ?bool
    {
        if (! \file_exists($dir) || ! \is_dir($dir)) {
            return false;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                @\rmdir($filename);
            } else {
                @\unlink($filename);
            }
        }
        @\rmdir($dir);
    }

    /**
     * Prepare word.
     *
     * @param string $matches
     *
     * @return string
     */
    protected function prepareWord(string $matches): string
    {
        return '['.\strtolower($matches[1]).\strtoupper($matches[1]).']';
    }

    protected function arrayFind($needle, array $haystack): bool | int | string
    {
        foreach ($haystack as $key => $value) {
            if (false !== \stripos($value, $needle)) {
                return $key;
            }
        }

        return false;
    }

    protected function out($message): void
    {
        echo $message.PHP_EOL;
    }
}
