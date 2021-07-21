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

namespace App\Http\Livewire;

use App\Jobs\ProcessBackup;
use App\Rules\BackupDisk;
use App\Rules\PathToZip;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupPanel extends Component
{
    public array $backupStatuses = [];

    public $activeDisk = null;

    public array $disks = [];

    public array $files = [];

    public $deletingFile = null;

    final public function updateBackupStatuses(): void
    {
        $this->backupStatuses = Cache::remember('backup-statuses', now()->addSeconds(4), fn () => BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'))
            ->map(fn (BackupDestinationStatus $backupDestinationStatus) => [
                'name'      => $backupDestinationStatus->backupDestination()->backupName(),
                'disk'      => $backupDestinationStatus->backupDestination()->diskName(),
                'reachable' => $backupDestinationStatus->backupDestination()->isReachable(),
                'healthy'   => $backupDestinationStatus->isHealthy(),
                'amount'    => $backupDestinationStatus->backupDestination()->backups()->count(),
                'newest'    => $backupDestinationStatus->backupDestination()->newestBackup()
                    ? $backupDestinationStatus->backupDestination()->newestBackup()->date()->diffForHumans()
                    : 'No backups present',
                'usedStorage' => Format::humanReadableSize($backupDestinationStatus->backupDestination()->usedStorage()),
            ])
            ->values()
            ->toArray());

        if (! $this->activeDisk && count($this->backupStatuses)) {
            $this->activeDisk = $this->backupStatuses[0]['disk'];
        }

        $this->disks = collect($this->backupStatuses)
            ->map(fn ($backupStatus): mixed => $backupStatus['disk'])
            ->values()
            ->all();

        $this->emitSelf('backupStatusesUpdated');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    final public function getFiles(string $disk = ''): void
    {
        if ($disk) {
            $this->activeDisk = $disk;
        }

        $this->validateActiveDisk();

        $backupDestination = BackupDestination::create($this->activeDisk, config('backup.backup.name'));

        $this->files = Cache::remember("backups-{$this->activeDisk}", now()->addSeconds(4), fn () => $backupDestination
            ->backups()
            ->map(function (Backup $backup) {
                $size = method_exists($backup, 'sizeInBytes') ? $backup->sizeInBytes() : $backup->size();

                return [
                    'path' => $backup->path(),
                    'date' => $backup->date()->format('Y-m-d H:i:s'),
                    'size' => Format::humanReadableSize($size),
                ];
            })
            ->toArray());
    }

    final public function showDeleteModal($fileIndex): void
    {
        $this->deletingFile = $this->files[$fileIndex];

        $this->emitSelf('showDeleteModal');
    }

    final public function deleteFile(): void
    {
        $deletingFile = $this->deletingFile;
        $this->deletingFile = null;

        $this->emitSelf('hideDeleteModal');

        $this->validateActiveDisk();
        $this->validateFilePath($deletingFile ? $deletingFile['path'] : '');

        $backupDestination = BackupDestination::create($this->activeDisk, config('backup.backup.name'));

        $backupDestination
            ->backups()
            ->first(fn (Backup $backup) => $backup->path() === $deletingFile['path'])
            ->delete();

        $this->files = collect($this->files)
            ->reject(fn ($file) => $file['path'] === $deletingFile['path']
                && $file['date'] === $deletingFile['date']
                && $file['size'] === $deletingFile['size'])
            ->values()
            ->all();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    final public function downloadFile(string $filePath): Response | StreamedResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        $this->validateActiveDisk();
        $this->validateFilePath($filePath);

        $backupDestination = BackupDestination::create($this->activeDisk, config('backup.backup.name'));

        $backup = $backupDestination->backups()->first(fn (Backup $backup) => $backup->path() === $filePath);

        if (! $backup) {
            return response('Backup not found', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->respondWithBackupStream($backup);
    }

    final public function respondWithBackupStream(Backup $backup): StreamedResponse
    {
        $fileName = pathinfo($backup->path(), PATHINFO_BASENAME);
        $size = method_exists($backup, 'sizeInBytes') ? $backup->sizeInBytes() : $backup->size();

        $downloadHeaders = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type'        => 'application/zip',
            'Content-Length'      => $size,
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Pragma'              => 'public',
        ];

        return response()->stream(function () use ($backup) {
            $stream = $backup->stream();

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $downloadHeaders);
    }

    final public function createBackup(string $option = ''): void
    {
        dispatch(new ProcessBackup($option));
    }

    final public function render(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        return view('livewire.backup-panel');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateActiveDisk(): void
    {
        try {
            Validator::make(
                ['activeDisk' => $this->activeDisk],
                [
                    'activeDisk' => ['required', new BackupDisk()],
                ],
                [
                    'activeDisk.required' => 'Select a disk',
                ]
            )->validate();
        } catch (ValidationException $e) {
            $message = $e->validator->errors()->get('activeDisk')[0];
            $this->emitSelf('showErrorToast', $message);

            throw $e;
        }
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateFilePath(string $filePath): void
    {
        try {
            Validator::make(
                ['file' => $filePath],
                [
                    'file' => ['required', new PathToZip()],
                ],
                [
                    'file.required' => 'Select a file',
                ]
            )->validate();
        } catch (ValidationException $e) {
            $message = $e->validator->errors()->get('file')[0];
            $this->emitSelf('showErrorToast', $message);

            throw $e;
        }
    }
}
