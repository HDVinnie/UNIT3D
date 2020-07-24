<?php
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

use App\Models\Authentication;
use App\Models\Device;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Jenssegers\Agent\Agent;

trait AuthChecker
{
    public function handleLogin(Authenticatable $user): void
    {
        $device = $this->findOrCreateUserDeviceByAgent($user);

        if ($this->shouldLogDeviceLogin($device)) {
            $this->createUserLoginForDevice($user, $device);
        }
    }

    public function handleFailed(Authenticatable $user): void
    {
        $device = $this->findOrCreateUserDeviceByAgent($user);
        $this->createUserLoginForDevice($user, $device, Authentication::TYPE_FAILED);
    }

    public function handleLockout(array $payload = []): void
    {
        $payload = Collection::make($payload);

        $user = $this->findUserFromPayload($payload);

        if ($user) {
            $device = $this->findOrCreateUserDeviceByAgent($user);
            $this->createUserLoginForDevice($user, $device, Authentication::TYPE_LOCKOUT);
        }
    }

    public function findOrCreateUserDeviceByAgent(Authenticatable $user, Agent $agent = null): Device
    {
        $agent = new Agent();
        $device = $this->findUserDeviceByAgent($user, $agent);

        if (is_null($device)) {
            $device = $this->createUserDeviceByAgent($user, $agent);
        }

        return $device;
    }

    public function findUserDeviceByAgent(Authenticatable $user, Agent $agent): ?Device
    {
        if (! $user->hasDevices()) {
            return null;
        }

        $matching = $user->devices->filter(function ($item) use ($agent) {
            return $this->deviceMatch($item, $agent);
        })->first();

        return $matching ? $matching : null;
    }

    public function createUserDeviceByAgent(Authenticatable $user, Agent $agent): Device
    {
        $device = new Device();

        $device->platform = $agent->platform();
        $device->platform_version = $agent->version($device->platform);
        $device->browser = $agent->browser();
        $device->browser_version = $agent->version($device->browser);
        $device->is_desktop = $agent->isDesktop() ? true : false;
        $device->is_mobile = $agent->isMobile() ? true : false;
        $device->language = count($agent->languages()) ? $agent->languages()[0] : null;

        $device->user()->associate($user);

        $device->save();

        return $device;
    }

    public function findUserFromPayload(Collection $payload): ?User
    {
        if ($payload->has('username')) {
            $login_value = $payload->get('username');

            $user = User::where('username', '=', $login_value)->first();

            return $user;
        }

        return null;
    }

    public function createUserLoginForDevice(Authenticatable $user, Device $device, string $type = Authentication::TYPE_LOGIN): Authentication
    {
        $ipAddress = request()->ip();

        $login = new Authentication();
        $login->ip_address = $ipAddress;
        $login->device_id = $device->id;
        $login->type = $type;
        $login->username = $user->username ?? 'test';
        $login->user_id = $user->user_id ?? '3';
        $login->save();

        $device->authentication()->save($login);

        return $login;
    }

    public function findDeviceForUser(Authenticatable $user, Agent $agent): ?Device
    {
        if (! $user->hasDevices()) {
            return false;
        }

        $device = $user->devices->filter(function ($item) use ($agent) {
            return $this->deviceMatch($item, $agent);
        })->first();

        return is_null($device) ? false : $device;
    }

    public function shouldLogDeviceLogin(Device $device): bool
    {
        $throttle = '0';

        if ($throttle === 0 || is_null($device->login)) {
            return true;
        }

        $limit = Carbon::now()->subMinutes($throttle);
        $login = $device->login;

        if (isset($login->created_at) && $login->created_at->gt($limit)) {
            return false;
        }

        return true;
    }

    public function deviceMatch(Device $device, Agent $agent, array $attributes = null): bool
    {
        $attributes = is_null($attributes) ? $this->getDeviceMatchingAttributesConfig() : $attributes;
        $matches = 0;

        if (in_array('platform', $attributes)) {
            $matches += $device->platform === $agent->platform();
        }

        if (in_array('platform_version', $attributes)) {
            $agentPlatformVersion = $agent->version($device->platform);
            $agentPlatformVersion = empty($agentPlatformVersion) ? '0' : $agentPlatformVersion;
            $matches += $device->platform_version === $agentPlatformVersion;
        }

        if (in_array('browser', $attributes)) {
            $matches += $device->browser === $agent->browser();
        }

        if (in_array('browser_version', $attributes)) {
            $matches += $device->browser_version === $agent->version($device->browser);
        }

        if (in_array('language', $attributes)) {
            $matches += $device->language === $agent->version($device->language);
        }

        return $matches === count($attributes);
    }

    public function getDeviceMatchingAttributesConfig(): array
    {
        return [
            'platform',
            'platform_version',
            'browser',
        ];
    }
}
