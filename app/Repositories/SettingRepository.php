<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * \App\Repositories\SettingRepository
 *
 * @property Setting $model
 *
 * @method \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method Setting update(array $attributes, Setting $setting)
 */
class SettingRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(protected Setting $model) {}

    public static function boot(): void
    {
        if (Schema::hasTable('settings')) {
            static::init();
        }
    }

    public static function init(): void
    {
        $settings = Cache::rememberForever('Setting.all', function () {
            return Setting::all(['name', 'value']);
        });

        foreach ($settings as $setting) {
            // config([$setting['name'] => $setting['value']]);
            Setting::reload($setting['name'], $setting['value']);
        }
    }

    public function getByName($name, $default = null)
    {
        if ($s = $this->query()->firstWhere('name', $name)) {
            return $s->value;
        }

        return $default;
    }

    public function getByConfigName($name, $default = null)
    {
        return $this->getByName($name, $default);
    }

    public function getConfig($name, $default = null)
    {
        return $this->getByConfigName($name, $default);
    }

    public function getByEnv($key, $default = null)
    {
        if ($s = $this->query()->firstWhere('key', strtoupper($key))) {
            return $s->value;
        }

        return $default;
    }

    public function getByEnvKey($key, $default = null)
    {
        return $this->getByEnv($key, $default);
    }

    public function getEnv($key, $default = null)
    {
        return $this->getByEnvKey($key, $default);
    }

    public function getByAny($config, $env, $default = null)
    {
        return $this->getConfig($config, $this->getEnv($env, $default));
    }

    public function getAny($config, $env, $default = null)
    {
        return $this->getByAny($config, $env, $default);
    }

    public function syncEnv()
    {
        $vals = [];
        $envs = $this->query()->where('env', true)->get();
        foreach ($envs as $env) {
            $vals[$env->key] = $env->value;
        }

        Setting::writes($vals);

        foreach ($envs as $env) {
            Setting::reload($env->name, $env->value);
        }
    }

    /**
     * Upsert
     *
     * @param  array  $attributes
     * @return \App\Models\Setting
     */
    public function createOrUpdate($attributes)
    {
        if ($model = $this->query()->firstWhere('name', $attributes['name'])) {
            $model = $this->edit($attributes, $model);
        } else {
            $model = $this->store($attributes);
        }

        return $model;
    }

    /**
     * Upsert
     *
     * @param  array  $attributes
     * @return \App\Models\Setting
     */
    public function createOrUpdateQuietly($attributes)
    {
        if ($model = $this->query()->firstWhere('name', $attributes['name'])) {
            $model = $this->editQuietly($attributes, $model);
        } else {
            $model = $this->storeQuietly($attributes);
        }

        return $model;
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @return Setting
     */
    public function store($attributes)
    {
        return $this->create($attributes);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @return Setting
     */
    public function storeQuietly($attributes)
    {
        return $this->createQuietly($attributes);
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @return Setting
     */
    public function edit($attributes, Setting $setting)
    {
        return $this->update($attributes, $setting);
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @return Setting
     */
    public function editQuietly($attributes, Setting $setting)
    {
        return $this->updateQuietly($attributes, $setting);
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null|void
     */
    public function destroy(Setting $setting)
    {
        return $this->delete($setting);
    }
}
