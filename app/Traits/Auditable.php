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

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::registerCreate($model);
        });

        static::updated(function ($model) {
            self::registerUpdate($model);
        });

        static::deleted(function ($model) {
            self::registerDelete($model);
        });
    }

    /**
     * Strips specified data keys from the audit.
     *
     * @param $model
     * @param $data
     *
     * @return array
     */
    protected static function strip($model, $data): array
    {
        // Initialize an instance of $model
        $instance = new $model();
        // Convert the data to an array
        $data = (array) $data;
        // Start stripping
        $globalDiscards = (empty(\config('audit.global_discards'))) ? [] : \config('audit.global_discards');
        $modelDiscards = (empty($instance->discarded)) ? [] : $instance->discarded;
        foreach (\array_keys($data) as $key) {
            // Check the model-specific discards
            if (\in_array($key, $modelDiscards, true)) {
                unset($data[$key]);
            }
            // Check global discards
            if (! empty($globalDiscards) && \in_array($key, $globalDiscards, true)) {
                unset($data[$key]);
            }
        }
        // Return
        return $data;
    }

    /**
     * Generates the data to store.
     *
     * @param       $action
     * @param array $old
     * @param array $new
     *
     * @throws \JsonException
     *
     * @return false|string
     */
    protected static function generate($action, array $old = [], array $new = []): false | string
    {
        $data = [];
        switch ($action) {
            default:
                throw new \InvalidArgumentException(\sprintf('Unknown action `%s`.', $action));
            case 'create':
                // Expect new data to be filled
                if (empty($new)) {
                    throw new \ArgumentCountError('Action `create` expects new data.');
                }
                // Process
                foreach ($new as $key => $value) {
                    $data[$key] = [
                        'old' => null,
                        'new' => $value,
                    ];
                }
                break;
            case 'update':
                // Expect old and new data to be filled
                /*if (empty($old) || empty($new)) {
                    throw new \ArgumentCountError('Action `update` expects both old and new data.');
                }*/
                // Process only what changed
                foreach ($new as $key => $value) {
                    $data[$key] = [
                        'old' => $old[$key],
                        'new' => $value,
                    ];
                }
                break;
            case 'delete':
                // Expect new data to be filled
                if (empty($old)) {
                    throw new \ArgumentCountError('Action `delete` expects new data.');
                }
                // Process
                foreach ($old as $key => $value) {
                    $data[$key] = [
                        'old' => $value,
                        'new' => null,
                    ];
                }
                break;
        }

        $clean = \array_filter($data);

        return \json_encode($clean, JSON_THROW_ON_ERROR);
    }

    /**
     * Gets the current user ID, or null if guest.
     *
     * @return mixed
     */
    public static function getUserId(): mixed
    {
        if (\auth()->guest()) {
            return;
        }

        return \auth()->user()->id;
    }

    /**
     * Logs a record creation.
     *
     * @param $model
     *
     * @throws \JsonException
     */
    protected static function registerCreate($model): void
    {
        // Get auth (if any)
        $userId = self::getUserId();

        // Generate the JSON to store
        $data = self::generate('create', [], self::strip($model, $model->getAttributes()));

        if (! \is_null($userId) && ! empty($data)) {
            // Store record
            $now = Carbon::now()->format('Y-m-d H:i:s');
            DB::table('audits')->insert([
                'user_id'        => $userId,
                'model_name'     => \class_basename($model),
                'model_entry_id' => $model->{$model->getKeyName()},
                'action'         => 'create',
                'record'         => $data,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    /**
     * Logs a record update.
     *
     * @param $model
     *
     * @throws \JsonException
     */
    protected static function registerUpdate($model): void
    {
        // Get auth (if any)
        $userId = self::getUserId();

        // Generate the JSON to store
        $data = self::generate('update', self::strip($model, $model->getOriginal()), self::strip($model, $model->getChanges()));

        if (! \is_null($userId) && ! empty(\json_decode($data, true, 512, JSON_THROW_ON_ERROR))) {
            // Store record
            $now = Carbon::now()->format('Y-m-d H:i:s');
            DB::table('audits')->insert([
                'user_id'        => $userId,
                'model_name'     => \class_basename($model),
                'model_entry_id' => $model->{$model->getKeyName()},
                'action'         => 'update',
                'record'         => $data,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    /**
     * Logs a record deletion.
     *
     * @param $model
     *
     * @throws \JsonException
     */
    protected static function registerDelete($model): void
    {
        // Get auth (if any)
        $userId = self::getUserId();

        // Generate the JSON to store
        $data = self::generate('delete', self::strip($model, $model->getAttributes()));

        if (! \is_null($userId) && ! empty($data)) {
            // Store record
            $now = Carbon::now()->format('Y-m-d H:i:s');
            DB::table('audits')->insert([
                'user_id'        => $userId,
                'model_name'     => \class_basename($model),
                'model_entry_id' => $model->{$model->getKeyName()},
                'action'         => 'delete',
                'record'         => $data,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }
}
