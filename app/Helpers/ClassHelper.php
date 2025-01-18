<?php

namespace App\Helpers;
class ClassHelper
{
    public static function getClassesWithNameSpace($name_space)
    {
        $path = base_path().'/'.$name_space;
        $path = str_replace('\\','/',$path);
        $directory = new \DirectoryIterator($path);
        $classes = [];
        foreach($directory as $file){
            if (! $file->isDot()) {
                $class_name = str_replace('.php', '', $file);
                if(! is_string($class_name)){
                    continue;
                }
                $classes[] = ucfirst($name_space).'\\'.$class_name;
            }
        }
        return $classes;
    }

    public static function getClassesWithoutNameSpace($name_space)
    {
        $path = base_path().'/'.$name_space;
        $path = str_replace('\\','/',$path);
        $directory = new \DirectoryIterator($path);
        $classes = [];
        foreach($directory as $file){
            if (! $file->isDot()) {
                $class_name = str_replace('.php', '', $file);
                if(! is_string($class_name)){
                    continue;
                }
                $classes[] = $class_name;
            }
        }
        return $classes;
    }

    /**
     * transform the TEXT_LIKE_THIS to Text Like This
     * 
     * @param string $string
     * 
     * @return string
     */
    public static function i18nRevert(string $string): string
    {
        $string = str_replace("_", " ", $string);
        $string = ucwords(strtolower($string));

        return $string;
    }
}