<?php

namespace Dcat\Admin\Latlong;

use Dcat\Admin\Admin;
use Dcat\Admin\Extension as BaseExtension;

class Extension extends BaseExtension
{
    public static $name = 'latlong';

    public $views = __DIR__.'/../resources/views';

    /**
     * @var array
     */
    protected static $providers = [
        'baidu'   => Map\Baidu::class,
        'tencent' => Map\Tencent::class,
        'amap'    => Map\Amap::class,
        'google'  => Map\Google::class,
        'yandex'  => Map\Yandex::class,
    ];

    /**
     * @var Map\AbstractMap
     */
    protected static $provider;

    /**
     * Get config set in config/admin.php.
     *
     * @param string $key
     * @param null   $default
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public static function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            $key = sprintf('admin.extensions.%s', static::$name);
        } else {
            $key = sprintf('admin.extensions.%s.%s', static::$name, $key);
        }

        return config($key, $default);
    }

    /**
     * @param string $name
     * @return Map\AbstractMap
     */
    public static function getProvider($name = '')
    {
        if (static::$provider) {
            return static::$provider;
        }

        $name = Extension::getConfig('default', $name);
        $args = Extension::getConfig("providers.$name", []);

        return static::$provider = new static::$providers[$name](...array_values($args));
    }

    /**
     * @return \Closure
     */
    public static function showField()
    {
        return function ($lat, $lng, $height = 300, $zoom = 16) {

            return $this->unescape()->as(function () use ($lat, $lng, $height, $zoom) {

                $lat = $this->{$lat};
                $lng = $this->{$lng};
                $id = ['lat' => 'lat', 'lng' => 'lng'];
                Admin::script(Extension::getProvider()
                    ->setParams([
                        'zoom' => $zoom
                    ])
                    ->applyScript($id));

                return <<<HTML
<div class="row">
    <div class="col-md-3">
        <input id="{$id['lat']}" class="form-control" value="{$lat}"/>
    </div>
    <div class="col-md-3">
        <input id="{$id['lng']}" class="form-control" value="{$lng}"/>
    </div>
</div>

<br>

<div id="map_{$id['lat']}{$id['lng']}" style="width: 100%;height: {$height}px"></div>
HTML;
            });
        };
    }
}
