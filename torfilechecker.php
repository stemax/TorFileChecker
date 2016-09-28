<?php
namespace TorFileChecker;

class Config
{
    static public $date_format = 'd M y H:i:s';
    static public $ds = DIRECTORY_SEPARATOR;
    static public $snapshot_path = 'tor_snapshots';
}

class Processing
{
    public static function replaceSeparators($address = '')
    {
        return str_replace(['//', '\\'], ['/', '/'], str_replace(Config::$ds, '/', $address));
    }

    public static function formatBytes($bytes, $precision = 2)
    {
        if (!$bytes) return '0 b';
        $base = log($bytes, 1024);
        $suffixes = ['b', 'Kb', 'Mb', 'Gb', 'Tb'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    public static function sortArrayWithObjects($array)
    {
        usort($array, function ($a, $b) {
            if ($a->name == $b->name) {
                return 0;
            }
            return ($a->name < $b->name) ? -1 : 1;
        });
        return $array;
    }
}

class FileManager
{
    private static $errors = [];

    public static function getRootFolder()
    {
        return Processing::replaceSeparators($_SERVER['DOCUMENT_ROOT']);
    }

    public static function getFolders($path = '')
    {
        $folders = [];
        foreach (new \DirectoryIterator($path) as $folder) {
            try {
                if (!$folder->isDot() && $folder->isDir()) {
                    $folder_info = new \stdClass();
                    $folder_info->name = $folder->getFilename();
                    $folder_info->size = $folder->getSize();
                    $folder_info->type = $folder->getType();
                    $folder_info->owner = $folder->getOwner();
                    $folder_info->perms = substr(sprintf('%o', $folder->getPerms()), -4);
                    $folder_info->ctime = date(Config::$date_format, $folder->getCTime());
                    $folder_info->atime = date(Config::$date_format, $folder->getATime());
                    $folder_info->mtime = date(Config::$date_format, $folder->getMTime());
                    $folder_info->ctime_int = $folder->getCTime();
                    $folder_info->atime_int = $folder->getATime();
                    $folder_info->mtime_int = $folder->getMTime();
                    $folder_info->fileinfo = $folder->getFileInfo();
                    $folder_info->isr = $folder->isReadable();
                    $folder_info->isw = $folder->isWritable();
                    $folder_info->ise = $folder->isExecutable();
                    $folders[] = $folder_info;
                }
            } catch (\RuntimeException $e) {
                self::$errors[] = 'Error access to: ' . $folder->getFilename();
            }
        }
        return $folders;
    }

    public static function getFiles($path = '')
    {
        $files = [];
        foreach (new \DirectoryIterator($path) as $file) {
            try {
                if (!$file->isDot() && $file->isFile()) {
                    $file_info = new \stdClass();
                    $file_info->name = $file->getFilename();
                    $file_info->size = $file->getSize();
                    $file_info->type = $file->getType();
                    $file_info->owner = $file->getOwner();
                    $file_info->perms = substr(sprintf('%o', $file->getPerms()), -4);
                    $file_info->ctime = date(Config::$date_format, $file->getCTime());
                    $file_info->atime = date(Config::$date_format, $file->getATime());
                    $file_info->mtime = date(Config::$date_format, $file->getMTime());
                    $file_info->ctime_int = $file->getCTime();
                    $file_info->atime_int = $file->getATime();
                    $file_info->mtime_int = $file->getMTime();
                    $file_info->fileinfo = $file->getFileInfo();
                    $file_info->isr = $file->isReadable();
                    $file_info->isw = $file->isWritable();
                    $file_info->ise = $file->isExecutable();
                    $file_info->ext = $file->getExtension();
                    $files[] = $file_info;
                }
            } catch (\RuntimeException $e) {
                self::$errors[] = 'Error access to: ' . $file->getFilename();
            }
        }
        return $files;
    }

    public static function getErrorsString()
    {
        $error_string = '';
        if (sizeof(self::$errors)) foreach (self::$errors as $error) {
            $error_string .= '<div class="alert alert-warning" role="alert">' . $error . '</div>';
        }
        return $error_string;
    }
}

class FileChecker
{
    public static $file_system_snapshot = [];
    public static $latest_snapshot = [];

    private static $changed_folders = [];
    private static $new_folders = [];
    private static $changed_files = [];
    private static $new_files = [];

    public static function grabInfo($path, $recursive = true)
    {
        $path = Processing::replaceSeparators($path);
        $folders = Processing::sortArrayWithObjects(FileManager::getFolders($path), 'name');
        $files = Processing::sortArrayWithObjects(FileManager::getFiles($path), 'name');

        if (sizeof($files))
        {
            foreach ($files as $file) {
                $full_path = $path . DIRECTORY_SEPARATOR . $file->name;
                $snap_str = $file->ctime_int . '|' . $file->size . '|' . $file->owner . $file->perms . '|' . $file->mtime_int;
                FileChecker::$file_system_snapshot[$full_path] = $snap_str;
            }
        }
        if (sizeof($folders))
            foreach ($folders as $folder) {
                $full_path = $path . DIRECTORY_SEPARATOR . $folder->name;
                $snap_str = $folder->ctime_int . '|' . $folder->size . '|' . $folder->owner . $folder->perms . '|' . $folder->mtime_int;
                FileChecker::$file_system_snapshot[$full_path] = $snap_str;
                if ($recursive) self::grabInfo($full_path);
            }
        return true;
    }

    public static function compareInfo($path)
    {
        $path = Processing::replaceSeparators($path);
        $folders = Processing::sortArrayWithObjects(FileManager::getFolders($path), 'name');
        $files = Processing::sortArrayWithObjects(FileManager::getFiles($path), 'name');

        if (sizeof($files))
        {
            foreach ($files as $file) {
                $full_path = $path . DIRECTORY_SEPARATOR . $file->name;
                $snap_str = $file->ctime_int . '|' . $file->size . '|' . $file->owner . $file->perms . '|' . $file->mtime_int;
                if (isset(FileChecker::$latest_snapshot[$full_path]))
                {
                    if ($snap_str != FileChecker::$latest_snapshot[$full_path])
                    {
                        FileChecker::$changed_files[$full_path] = [FileChecker::$latest_snapshot[$full_path], $snap_str];
                    }
                }else
                {
                    FileChecker::$new_files[$full_path] = $snap_str;
                }
            }
        }

        if (sizeof($folders))
            foreach ($folders as $folder) {
                $full_path = $path . DIRECTORY_SEPARATOR . $folder->name;
                $snap_str = $folder->ctime_int . '|' . $folder->size . '|' . $folder->owner . $folder->perms . '|' . $folder->mtime_int;

                if (isset(FileChecker::$latest_snapshot[$full_path]))
                {
                    if ($snap_str!= FileChecker::$latest_snapshot[$full_path])
                    {
                        FileChecker::$changed_folders[$full_path] = [FileChecker::$latest_snapshot[$full_path], $snap_str];
                    }
                }else
                {
                    FileChecker::$new_folders[$full_path] = $snap_str;
                }
                self::compareInfo($full_path);
            }
    }

    public static function saveSnapshot($snap_name='')
    {
        if (!file_exists(FileManager::getRootFolder().Config::$ds.Config::$snapshot_path))
        {
            mkdir(FileManager::getRootFolder().Config::$ds.Config::$snapshot_path);
        }
        return file_put_contents(FileManager::getRootFolder().Config::$ds.Config::$snapshot_path.Config::$ds.'file_snapshot_' .$snap_name.'_'. date('Y-m-d') . '.json', json_encode(FileChecker::$file_system_snapshot, TRUE));
    }

    public static function loadSnapshot($snap_name='')
    {
        FileChecker::$latest_snapshot = json_decode(file_get_contents(FileManager::getRootFolder().Config::$ds.Config::$snapshot_path.Config::$ds.$snap_name), true);
    }
    
    public static function showDifference()
    {
        echo 'New folders: <b>'.count(FileChecker::$new_folders).'</b><br/>';
        if (sizeof(FileChecker::$new_folders))
        {
            foreach (FileChecker::$new_folders as $nf_path=>$nf_scrap) {
                echo $nf_path . ' => '.$nf_scrap."<br/>";
            }
        }
        echo '<hr/>';

        echo 'Changed folders: <b>'.count(FileChecker::$changed_folders).'</b><br/>';
        if (sizeof(FileChecker::$changed_folders))
        {
            foreach (FileChecker::$changed_folders as $cf_path=>$cf_scrap) {
                echo $cf_path . ' => '.$cf_scrap[0].'vs'.$cf_scrap[1]."<br/>";
            }
        }
        echo '<hr/>';

        echo 'New files: <b>'.count(FileChecker::$new_files).'</b><br/>';
        if (sizeof(FileChecker::$new_files))
        {
            foreach (FileChecker::$new_files as $nf_path=>$nf_scrap) {
                echo $nf_path . ' => '.$nf_scrap."<br/>";
            }
        }
        echo '<hr/>';

        echo 'Changed files: <b>'.count(FileChecker::$changed_files).'</b><br/>';
        if (sizeof(FileChecker::$changed_files))
        {
            foreach (FileChecker::$changed_files as $cf_path=>$cf_scrap) {
                echo $cf_path . ' => '.$cf_scrap[0].' vs '.$cf_scrap[1]."<br/>";
            }
        }
        echo '<hr/>';
    }
}

$doc_root = FileManager::getRootFolder();

/*
if (FileChecker::grabInfo($doc_root.DIRECTORY_SEPARATOR.'TorFileChecker'))
{
    FileChecker::saveSnapshot('TorFileChecker');
}*/

FileChecker::loadSnapshot('file_snapshot_' .'TorFileChecker'.'_'. date('Y-m-d') . '.json');
FileChecker::compareInfo($doc_root.DIRECTORY_SEPARATOR.'TorFileChecker');
FileChecker::showDifference();
/*

FileChecker::$latest_snapshot = json_decode(file_get_contents('file_snapshot_' . date('Y-m-d') . '.json'), true);
FileChecker::compareInfo($doc_root.DIRECTORY_SEPARATOR.'TorFileChecker');
FileChecker::showDifference();
*/
?>
