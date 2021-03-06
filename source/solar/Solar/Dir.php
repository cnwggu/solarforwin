<?php
/**
 *
 * Utility class for static directory methods.
 *
 * @category Solar
 *
 * @package Solar
 *
 * @author Paul M. Jones <pmjones@solarphp.com>
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 *
 * @version $Id: Dir.php 4416 2010-02-23 19:52:43Z pmjones $
 *
 */
class Solar_Dir
{
    /**
     *
     * The OS-specific temporary directory location.
     *
     * @var string
     *
     */
    protected static $_tmp;

    /**
     *
     * Hack for [[php::is_dir() | ]] that checks the include_path.
     *
     * Use this to see if a directory exists anywhere in the include_path.
     *
     * {{code: php
     *     $dir = Solar_Dir::exists('path/to/dir')
     *     if ($dir) {
     *         $files = scandir($dir);
     *     } else {
     *         echo "Not found in the include-path.";
     *     }
     * }}
     *
     * @param string $dir Check for this directory in the include_path.
     *
     * @return mixed If the directory exists in the include_path, returns the
     * absolute path; if not, returns boolean false.
     *
     */
    public static function exists($dir)
    {
        // no file requested?
        $dir = trim($dir);
        if (! $dir) {
            return false;
        }

        // using an absolute path for the file?
        // dual check for Unix '/' and Windows '\',
        // or Windows drive letter and a ':'.
        $abs = ($dir[0] == '/' || $dir[0] == '\\' || $dir[1] == ':');
        if ($abs && is_dir($dir)) {
            return $dir;
        }

        // using a relative path on the file
        $path = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($path as $base) {
            // strip Unix '/' and Windows '\'
            $target = rtrim($base, '\\/') . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($target)) {
                return $target;
            }
        }

        // never found it
        return false;
    }

    /**
     *
     * "Fixes" a directory string for the operating system.
     *
     * Use slashes anywhere you need a directory separator. Then run the
     * string through fixdir() and the slashes will be converted to the
     * proper separator (for example '\' on Windows).
     *
     * Always adds a final trailing separator.
     *
     * @param string $dir The directory string to 'fix'.
     *
     * @return string The "fixed" directory string.
     *
     */
    public static function fix($dir)
    {
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     *
     * Convenience method for dirname() and higher-level directories.
     *
     * @param string $file Get the dirname() of this file.
     *
     * @param int $up Move up in the directory structure this many
     * times, default 0.
     *
     * @return string The dirname() of the file.
     *
     */
    public static function name($file, $up = 0)
    {
        $dir = dirname($file);
        while ($up --) {
            $dir = dirname($dir);
        }
        return $dir;
    }

    /**
     *
     * Returns the OS-specific directory for temporary files; uses the Solar
     * `$system/tmp` directory when available.
     *
     * @param string $sub Add this subdirectory to the returned temporary
     * directory name.
     *
     * @return string The temporary directory path.
     *
     */
    public static function tmp($sub = '')
    {
        // find the tmp dir if needed
        if (! Solar_Dir::$_tmp) {

            // use the system if we can
            if (Solar::$system) {
                $tmp = Solar::$system . "/tmp";
            } elseif (function_exists('sys_get_temp_dir')) {
                $tmp = sys_get_temp_dir();
            } else {
                $tmp = Solar_Dir::_tmp();
            }

            // remove trailing separator and save
            Solar_Dir::$_tmp = rtrim($tmp, DIRECTORY_SEPARATOR);
        }

        // do we have a subdirectory request?
        $sub = trim($sub);
        if ($sub) {
            // remove leading and trailing separators, and force exactly
            // one trailing separator
            $sub = trim($sub, DIRECTORY_SEPARATOR)
                 . DIRECTORY_SEPARATOR;
        }

        return Solar_Dir::$_tmp . DIRECTORY_SEPARATOR . $sub;
    }

    /**
     *
     * Returns the OS-specific temporary directory location.
     *
     * @return string The temp directory path.
     *
     */
    protected static function _tmp()
    {
        // non-Windows system?
        if (strtolower(substr(PHP_OS, 0, 3)) != 'win') {
            $tmp = empty($_ENV['TMPDIR']) ? getenv('TMPDIR') : $_ENV['TMPDIR'];
            if ($tmp) {
                return $tmp;
            } else {
                return '/tmp';
            }
        }

        // Windows 'TEMP'
        $tmp = empty($_ENV['TEMP']) ? getenv('TEMP') : $_ENV['TEMP'];
        if ($tmp) {
            return $tmp;
        }

        // Windows 'TMP'
        $tmp = empty($_ENV['TMP']) ? getenv('TMP') : $_ENV['TMP'];
        if ($tmp) {
            return $tmp;
        }

        // Windows 'windir'
        $tmp = empty($_ENV['windir']) ? getenv('windir') : $_ENV['windir'];
        if ($tmp) {
            return $tmp;
        }

        // final fallback for Windows
        return getenv('SystemRoot') . '\\temp';
    }

    /**
     *
     * Replacement for mkdir() to supress warnings and throw exceptions in
     * their place.
     *
     * @param string $path The directory path to create.
     *
     * @param int $mode The permissions mode for the directory.
     *
     * @param bool $recursive Recursively create directories along the way.
     *
     * @return bool True on success; throws exception on failure.
     *
     * @see [[php::mkdir() | ]]
     *
     */
    public static function mkdir($path, $mode = 0777, $recursive = false)
    {
        $result = @mkdir($path, $mode, $recursive);
        if (! $result) {
            $info = error_get_last();
            $info['dir'] = $path;
            $info['mode'] = $mode;
            $info['recursive'] = $recursive;
            throw Solar_Dir::_exception('ERR_MKDIR_FAILED', $info);
        } else {
            return true;
        }
    }

    /**
     *
     * Replacement for rmdir() to supress warnings and throw exceptions in
     * their place.
     *
     * @param string $path The directory path to remove
     *
     * @return bool True on success; throws exception on failure.
     *
     * @see [[php::rmdir() | ]]
     *
     */
    public static function rmdir($path)
    {
        $result = @rmdir($path);
        if (! $result) {
            // update by Roy Gu 2010-11-13
            // if it is windows system prior NT6, do this to ensure result
            if(!Solar::supportSymlink()) {
                /*
                    try{
                        exec("rmdir /S /Q $path");
                    } catch(Exception $e){
                        throw new Exception($e->message);
                    }
                */
                self::rmvdir($path);
                return true;
            }

            $info = error_get_last();
            $info['dir'] = $path;
            throw Solar_Dir::_exception('ERR_RMDIR_FAILED', $info);
        } else {
            return true;
        }
    }

    /**
     *
     * Remove dirs that no-empty Recursively.
     *
     * @param string $path The directory path to remove
     *
     * @author: Roy Gu
     *
     * @date: 2010-11-11
     *
     */
    public static function rmvdir($dir)
    {
        if ($handle = opendir("$dir")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dir/$item")) {
                        self::rmvdir("$dir/$item");
                    } else {
                        try {
                            @unlink("$dir/$item");
                        } catch(Exception $e) {
                            throw new Exception($e->message);
                        }
                    }
                }
            }
            closedir($handle);
            self::rmdir($dir);
        }
    }

    /**
     *
     * Returns a localized exception object.
     *
     * @param string $code The error code.
     *
     * @param array $info Additional error information.
     *
     * @return Solar_Exception
     *
     */
    protected static function _exception($code, $info = null)
    {
        $class  = 'Solar_Dir';
        $locale = Solar_Registry::get('locale');
        return Solar::exception(
            $class,
            $code,
            $locale->fetch($class, $code, 1, $info),
            (array) $info
        );
    }
}
