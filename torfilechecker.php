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

class showHelper
{

    public static function displayHeadPart()
    {
        ?>
        <html>
        <head>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
            <script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
            <style>
                .header_3d {
                    color: #fffffc;
                    text-shadow: 0 1px 0 #999, 0 2px 0 #888, 0 3px 0 #777, 0 4px 0 #666, 0 5px 0 #555, 0 6px 0 #444, 0 7px 0 #333, 0 8px 7px #001135;
                }

                .form-group {
                    width: 100%;
                    margin: 5px 3px 3px 5px !important;
                }
            </style>
            <title>TorFileChecker</title>
        </head>
        <body>
        <div class="jumbotron navbar-form">
        <div class="container">
        <div class="page-header header_3d"><h1>TorFileChecker</h1></div>
    <?php
    }

    public static function displayIndex($doc_root = '.')
    {
        ?>
        <div class="row well">
            <div class="col-md-6">
                <form method="post" action="torfilechecker.php" name="subform" class="form-horisontal">
                    <h3>[New snapshot]</h3>
                    <code>Create new snapshot for selected folders (one level after root folder)</code>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="snap_name">Name</label>

                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="snap_name" name="snap_name"
                                   placeholder="Snapshot name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="select_folder">Folder</label>

                        <div class="col-sm-8">
                            <?php
                            echo '<select id="select_folder" class="form-control" name="folder">';
                            echo '<option value="' . $doc_root . '">' . $doc_root . '</option>';
                            $folders = Processing::sortArrayWithObjects(FileManager::getFolders($doc_root), 'name');
                            if (sizeof($folders)) {
                                foreach ($folders as $folder) {
                                    echo '<option value="' . $doc_root . DIRECTORY_SEPARATOR . $folder->name . '"> -> ' . $folder->name . '</option>';
                                }

                            }
                            echo '</select>';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button class="btn btn-primary btn-lg" type="submit">Generate new snapshot</button>
                        </div>
                    </div>
                    <input type="hidden" name="action" value="create"/>
                </form>
            </div>
            <div class="col-md-6">
                <form method="post" action="torfilechecker.php" name="subform2" class="form-horisontal">
                    <h3>[Compare snapshot]</h3>
                    <code>Compare exists snapshots with actual folders/files</code>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="select_file">Snapshot</label>

                        <div class="col-sm-8">
                            <?php
                            echo '<select id="select_file" class="form-control" name="file">';
                            $files = Processing::sortArrayWithObjects(FileManager::getFiles($doc_root . DIRECTORY_SEPARATOR . Config::$snapshot_path), 'name');
                            if (sizeof($files)) {
                                foreach ($files as $file) {
                                    echo '<option value="' . $file->name . '">' . $file->name . '</option>';
                                }
                            }
                            echo '</select>';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="compare_folder">Folder</label>

                        <div class="col-sm-8">
                            <?php
                            echo '<select id="compare_folder" class="form-control" name="compare_folder">';
                            echo '<option value="' . $doc_root . '">' . $doc_root . '</option>';
                            $folders = Processing::sortArrayWithObjects(FileManager::getFolders($doc_root), 'name');
                            if (sizeof($folders)) {
                                foreach ($folders as $folder) {
                                    echo '<option value="' . $doc_root . DIRECTORY_SEPARATOR . $folder->name . '"> -> ' . $folder->name . '</option>';
                                }

                            }
                            echo '</select>';
                            ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button class="btn btn-info btn-lg" type="submit">Compare</button>
                        </div>
                    </div>
                    <input type="hidden" name="action" value="compare"/>
                </form>
            </div>
        </div>
    <?php
    }

    public static function displayBottomPart()
    {
        ?>
        </div>
        </div>
        <div class="btn btn-primary btn-xs pull-right " disabled="true">Created by SMT</div>
        </body>
        </html>
    <?php
    }


    public static function displayFolderItem($name = '')
    {
        //echo '<div style="cursor:pointer; height: 10px; border-radius: 2px; margin: 1px; float: left; font-size:10px; color:#000; padding:1px;" title="' . $name . '">' . $name . '</div>';
        echo '<code style="float: left;">' . $name . '</code>';
    }

    public static function displayFileItem($name = '')
    {
        $ext = end(explode('.', $name));
        echo '<div style="background-color: #006600;    border-radius: 2px;    color: #ededed;    cursor: pointer;    float: left;    font-size: 10px;    /*height: 10px;*/    margin: 1px;    /*padding-bottom: 3px;*/    text-align: center;    text-transform: uppercase;    width: ' . (12 + 5 * strlen($ext)) . 'px;" title="' . $name . '">' . $ext . '</div>';
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
                if (!$folder->isDot() && $folder->isDir() && $folder->getFilename()[0] != '.') {
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
    private static $total_files = 0;
    private static $total_folders = 0;

    public static function grabInfo($path, $recursive = true, $padding = 0)
    {
        $padding += 1;
        $path = Processing::replaceSeparators($path);
        $folders = Processing::sortArrayWithObjects(FileManager::getFolders($path), 'name');
        $files = Processing::sortArrayWithObjects(FileManager::getFiles($path), 'name');

        if (sizeof($files)) {
            foreach ($files as $file) {
                $full_path = $path . DIRECTORY_SEPARATOR . $file->name;
                $snap_str = $file->ctime_int . '|' . $file->size . '|' . $file->owner . $file->perms . '|' . $file->mtime_int;
                showHelper::displayFileItem($file->name);
                flush();
                FileChecker::$file_system_snapshot[$full_path] = $snap_str;
            }
        }
        echo '<div style="width:100%;display:inline-block;border-bottom:1px dotted #c3c3c3;"></div>';
        flush();
        if (sizeof($folders))
            foreach ($folders as $folder) {
                $full_path = $path . DIRECTORY_SEPARATOR . $folder->name;
                $snap_str = $folder->ctime_int . '|' . $folder->size . '|' . $folder->owner . $folder->perms . '|' . $folder->mtime_int;
                echo '<div style="float: left; font-size:9px;">&#9568;';
                for ($i = 0; $i < $padding; $i++) {
                    echo '&#172; ';
                }
                echo '</div>';

                showHelper::displayFolderItem($folder->name);
                flush();
                FileChecker::$file_system_snapshot[$full_path] = $snap_str;
                if ($recursive) self::grabInfo($full_path, true, $padding);
            }
        return true;
    }

    public static function compareInfo($path)
    {
        $path = Processing::replaceSeparators($path);
        $folders = Processing::sortArrayWithObjects(FileManager::getFolders($path), 'name');
        $files = Processing::sortArrayWithObjects(FileManager::getFiles($path), 'name');

        if (sizeof($files)) {
            FileChecker::$total_files += count($files);
            foreach ($files as $file) {
                $full_path = $path . DIRECTORY_SEPARATOR . $file->name;
                $snap_str = $file->ctime_int . '|' . $file->size . '|' . $file->owner . $file->perms . '|' . $file->mtime_int;
                if (isset(FileChecker::$latest_snapshot[$full_path])) {
                    if ($snap_str != FileChecker::$latest_snapshot[$full_path]) {
                        FileChecker::$changed_files[$full_path] = [FileChecker::$latest_snapshot[$full_path], $snap_str];
                    }
                } else {
                    FileChecker::$new_files[$full_path] = $snap_str;
                }
            }
        }

        if (sizeof($folders))
            FileChecker::$total_folders += count($folders);
        foreach ($folders as $folder) {
            $full_path = $path . DIRECTORY_SEPARATOR . $folder->name;
            $snap_str = $folder->ctime_int . '|' . $folder->size . '|' . $folder->owner . $folder->perms . '|' . $folder->mtime_int;

            if (isset(FileChecker::$latest_snapshot[$full_path])) {
                if ($snap_str != FileChecker::$latest_snapshot[$full_path]) {
                    FileChecker::$changed_folders[$full_path] = [FileChecker::$latest_snapshot[$full_path], $snap_str];
                }
            } else {
                FileChecker::$new_folders[$full_path] = $snap_str;
            }
            self::compareInfo($full_path);
        }
    }

    public static function saveSnapshot($snap_name = '')
    {
        if (!file_exists(FileManager::getRootFolder() . Config::$ds . Config::$snapshot_path)) {
            mkdir(FileManager::getRootFolder() . Config::$ds . Config::$snapshot_path);
        }
        return file_put_contents(FileManager::getRootFolder() . Config::$ds . Config::$snapshot_path . Config::$ds . 'file_snapshot_' . $snap_name . '_' . date('Y-m-d') . '.json', json_encode(FileChecker::$file_system_snapshot, TRUE));
    }

    public static function loadSnapshot($snap_name = '')
    {
        FileChecker::$latest_snapshot = json_decode(file_get_contents(FileManager::getRootFolder() . Config::$ds . Config::$snapshot_path . Config::$ds . $snap_name), true);
    }

    public static function showDifference()
    {
        self::showIndicators();

        echo '<button class="btn btn-info" type="button">New folders: <span class="badge">' . count(FileChecker::$new_folders) . '</span></button><br/>';
        if (sizeof(FileChecker::$new_folders)) {
            echo '<table class="table table-hover">';
            foreach (FileChecker::$new_folders as $nf_path => $nf_scrap) {
                echo '<tr><td><code>'.$nf_path . '</code> </td><td> ' . self::decodeNewScrapInfo($nf_scrap) . "</td></tr>";
            }
            echo '</table>';
        }
        echo '<hr/>';

        echo '<button class="btn btn-warning" type="button">Changed folders: <span class="badge">' . count(FileChecker::$changed_folders) . '</span></button><br/>';
        if (sizeof(FileChecker::$changed_folders)) {
            echo '<table class="table table-hover">';
            foreach (FileChecker::$changed_folders as $cf_path => $cf_scrap) {
                echo '<tr><td><code>'.$cf_path . '</code> </td><td> ' . self::decodeChangedScrapInfo($cf_scrap[0], $cf_scrap[1]) . "</td></tr>";
            }
            echo '</table>';
        }
        echo '<hr/>';

        echo '<button class="btn btn-danger" type="button">New files: <span class="badge">' . count(FileChecker::$new_files) . '</span></button><br/>';
        if (sizeof(FileChecker::$new_files)) {
            echo '<table class="table table-hover">';
            foreach (FileChecker::$new_files as $nf_path => $nf_scrap) {
                echo '<tr><td><code>'.$nf_path . '</code> </td><td> ' . self::decodeNewScrapInfo($nf_scrap) . "</td></tr>";
            }
            echo '</table>';
        }
        echo '<hr/>';

        echo '<button class="btn btn-warning" type="button">Changed files: <span class="badge">' . count(FileChecker::$changed_files) . '</span></button><br/>';
        if (sizeof(FileChecker::$changed_files)) {
            echo '<table class="table table-hover">';
            foreach (FileChecker::$changed_files as $cf_path => $cf_scrap) {
                echo '<tr><td><code>'.$cf_path . '</code> </td><td> ' . self::decodeChangedScrapInfo($cf_scrap[0], $cf_scrap[1]) . "</td></tr>";
            }
            echo '</table>';
        }
        echo '<hr/>';
    }

    public static function showIndicators()
    {
        $new_folders_percent = $changed_folders_percent = 0;
        $old_folder_percent = 100;
        if (FileChecker::$total_folders) {
            $new_folders_percent = round(count(FileChecker::$new_folders) / FileChecker::$total_folders * 100, 2);
            $changed_folders_percent = round(count(FileChecker::$changed_folders) / FileChecker::$total_folders * 100, 2);
            $old_folder_percent = 100 - ($new_folders_percent + $changed_folders_percent);
        }
        ?>
        <div class="col-xs-12 col-md-6">
            <button class="btn" type="button">Folders status indicator <span class="badge">
            <?= FileChecker::$total_folders; ?></span>
                <span class="badge btn-warning"><?= count(FileChecker::$changed_folders); ?></span>
                <span class="badge btn-danger"><?= count(FileChecker::$new_folders); ?></span>
            </button>
            <div class="progress">
                <div class="progress-bar progress-bar-success" style="width: <?= $old_folder_percent; ?>%">
                    <span class="sr-only2"><?= $old_folder_percent; ?>% not changed</span>
                </div>
                <div class="progress-bar progress-bar-warning progress-bar-striped"
                     style="width: <?= $changed_folders_percent; ?>%">
                    <span class="sr-only2"><?= $changed_folders_percent; ?>% changed</span>
                </div>
                <div class="progress-bar progress-bar-danger" style="width: <?= $new_folders_percent; ?>%">
                    <span class="sr-only2"><?= $new_folders_percent; ?>% new</span>
                </div>
            </div>
        </div>

        <?php
        $new_files_percent = $changed_files_percent = 0;
        $old_file_percent = 100;
        if (FileChecker::$total_files) {
            $new_files_percent = round(count(FileChecker::$new_files) / FileChecker::$total_files * 100, 2);
            $changed_files_percent = round(count(FileChecker::$changed_files) / FileChecker::$total_files * 100, 2);
            $old_file_percent = 100 - ($new_files_percent + $changed_files_percent);
        }
        ?>
        <div class="col-xs-12 col-md-6">
            <button class="btn" type="button">Files status indicator <span class="badge">
            <?= FileChecker::$total_files; ?></span>
                <span class="badge btn-warning"><?= count(FileChecker::$changed_files); ?></span>
                <span class="badge btn-danger"><?= count(FileChecker::$new_files); ?></span>
            </button>

            <div class="progress">
                <div class="progress-bar progress-bar-success" style="width: <?= $old_file_percent; ?>%">
                    <span class="sr-only2"><?= $old_file_percent; ?>% not changed</span>
                </div>
                <div class="progress-bar progress-bar-warning progress-bar-striped"
                     style="width: <?= $changed_files_percent; ?>%">
                    <span class="sr-only2"><?= $changed_files_percent; ?>% changed</span>
                </div>
                <div class="progress-bar progress-bar-danger" style="width: <?= $new_files_percent; ?>%">
                    <span class="sr-only2"><?= $new_files_percent; ?>% new</span>
                </div>
            </div>
        </div>
    <?php
    }

    public static function decodeNewScrapInfo($coded_info)
    {
        return '<span class="label label-default">'.str_replace('|','</span> <span class="label label-default">',$coded_info).'</span>';
    }

    public static function decodeChangedScrapInfo($coded_info_org, $coded_info_changed)
    {
        $str ='<span class="label label-default">'.str_replace('|','</span> <span class="label label-default">',$coded_info_org).'</span>';
        $str .=' VS <span class="label label-default">'.str_replace('|','</span> <span class="label label-default">',$coded_info_changed).'</span>';
        return $str;
    }
}

$doc_root = FileManager::getRootFolder();
if (ob_get_level()) {
    ob_end_clean();
}
set_time_limit(0);
header('Content-Type: text/html; charset=utf-8');
flush();

showHelper::displayHeadPart();
$action = isset($_POST['action']) ? $_POST['action'] : '';
switch ($action) {
    case 'create':
        echo '<div class="row well">
            <div class="col-md-12">';
        $workFolder = isset($_POST['folder']) ? $_POST['folder'] : $doc_root;
        echo '<h3>[System snapshot details for "' . $workFolder . '"]</h3><hr>';
        showHelper::displayFolderItem($workFolder);
        flush();

        if (FileChecker::grabInfo($workFolder)) {
            FileChecker::saveSnapshot(isset($_POST['snap_name']) ? $_POST['snap_name'] : 'TorFileChecker');
        }
        echo '</div></div>';
        break;

    case 'compare':
        FileChecker::loadSnapshot(isset($_POST['file']) ? $_POST['file'] : '');
        FileChecker::compareInfo(isset($_POST['compare_folder']) ? $_POST['compare_folder'] : $doc_root);
        FileChecker::showDifference();
        break;

    default:
        showHelper::displayIndex($doc_root);
        break;
}

showHelper::displayBottomPart();
flush();
/*


$workFolder = $doc_root . DIRECTORY_SEPARATOR . '35';
echo '</h3>[System snapshot details]</h3><hr>';
showHelper::displayFolderItem($workFolder);
flush();

if (FileChecker::grabInfo($workFolder)) {
    FileChecker::saveSnapshot('TorFileChecker');
}
*/
/*
FileChecker::loadSnapshot('file_snapshot_' .'TorFileChecker'.'_'. date('Y-m-d') . '.json');
FileChecker::compareInfo($doc_root);
FileChecker::showDifference();
*/
/*
FileChecker::$latest_snapshot = json_decode(file_get_contents('file_snapshot_' . date('Y-m-d') . '.json'), true);
FileChecker::compareInfo($doc_root.DIRECTORY_SEPARATOR.'TorFileChecker');
FileChecker::showDifference();
*/

