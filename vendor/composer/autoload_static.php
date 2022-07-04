<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8cb777bdb44d55b29cb1531cdc94d9ac
{
    public static $prefixLengthsPsr4 = array (
        'j' => 
        array (
            'joelwenger\\textspecial\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'joelwenger\\textspecial\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8cb777bdb44d55b29cb1531cdc94d9ac::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8cb777bdb44d55b29cb1531cdc94d9ac::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit8cb777bdb44d55b29cb1531cdc94d9ac::$classMap;

        }, null, ClassLoader::class);
    }
}