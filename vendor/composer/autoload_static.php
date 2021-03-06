<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0411cd0c951048add6dd65b4bf181824
{
    public static $files = array (
        'a4ecaeafb8cfb009ad0e052c90355e98' => __DIR__ . '/..' . '/beberlei/assert/lib/Assert/functions.php',
        '3107fc387871a28a226cdc8c598a0adb' => __DIR__ . '/..' . '/php-school/cli-menu/src/Util/ArrayUtils.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Seld\\CliPrompt\\' => 15,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'PhpSchool\\Terminal\\' => 19,
            'PhpSchool\\CliMenu\\' => 18,
        ),
        'L' => 
        array (
            'League\\CLImate\\' => 15,
        ),
        'A' => 
        array (
            'Assert\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Seld\\CliPrompt\\' => 
        array (
            0 => __DIR__ . '/..' . '/seld/cli-prompt/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'PhpSchool\\Terminal\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-school/terminal/src',
        ),
        'PhpSchool\\CliMenu\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-school/cli-menu/src',
        ),
        'League\\CLImate\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/climate/src',
        ),
        'Assert\\' => 
        array (
            0 => __DIR__ . '/..' . '/beberlei/assert/lib/Assert',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0411cd0c951048add6dd65b4bf181824::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0411cd0c951048add6dd65b4bf181824::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0411cd0c951048add6dd65b4bf181824::$classMap;

        }, null, ClassLoader::class);
    }
}
