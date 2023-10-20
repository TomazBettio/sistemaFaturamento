<?php

/**
 * The Unzipper extracts .zip or .rar archives and .gz files on webservers.
 * It's handy if you do not have shell access. E.g. if you want to upload a lot
 * of files (php framework or image collection) as an archive to save time.
 * As of version 0.1.0 it also supports creating archives.
 *
 * @author  Andreas Tasch, at[tec], attec.at
 * @license GNU GPL v3
 * @package attec.toolbox
 * @version 0.1.1
 */
define('VERSION', '0.1.1');

if (!function_exists('tirarAcentos')) {
	function tirarAcentos($texto){
		$de =  array('á', 'à', 'â', 'Á', 'À', 'Â', 'è', 'é', 'ê', 'É', 'È', 'Ê', 'í', 'ì', 'î', 'Í', 'Ì', 'Î', 'ó', 'ò', 'ô', 'Ó', 'Ò', 'Ô', 'ú', 'ù', 'û', 'Ú', 'Ù', 'Û', 'ã', 'Ã', 'õ', 'Õ', 'ç', 'Ç');
		$por = array('a', 'a', 'a', 'A', 'A', 'A', 'e', 'e', 'e', 'E', 'E', 'E', 'i', 'i', 'i', 'I', 'I', 'I', 'o', 'o', 'o', 'O', 'O', 'O', 'u', 'u', 'u', 'U', 'U', 'U', 'a', 'A', 'o', 'O', 'c', 'C');
		$ret = str_replace($de, $por, $texto);
		return  $ret;
	}
}

// $timestart = microtime(TRUE);
// $GLOBALS['status'] = array();

// $unzipper = new Unzipper;
// if (isset($_POST['dounzip'])) {
//   // Check if an archive was selected for unzipping.
//   $archive = isset($_POST['zipfile']) ? strip_tags($_POST['zipfile']) : '';
//   $destination = isset($_POST['extpath']) ? strip_tags($_POST['extpath']) : '';
//   $unzipper->prepareExtraction($archive, $destination);
// }

// if (isset($_POST['dozip'])) {
//   $zippath = !empty($_POST['zippath']) ? strip_tags($_POST['zippath']) : '.';
//   // Resulting zipfile e.g. zipper--2016-07-23--11-55.zip.
//   $zipfile = 'zipper-' . date("Y-m-d--H-i") . '.zip';
//   Zipper::zipDir($zippath, $zipfile);
// }

// $timeend = microtime(TRUE);
// $time = round($timeend - $timestart, 4);

/**
 * Class Unzipper
 */
class Unzipper
{
	public function __construct(){
		set_time_limit(0);
	}
  /**
   * Prepare and check zipfile for extraction.
   *
   * @param string $archive
   *   The archive name including file extension. E.g. my_archive.zip.
   * @param string $destination
   *   The relative destination path where to extract files.
   */
  public function prepareExtraction($archive, $destination = '')
  {
    // Determine paths.
    if (empty($destination)) {
      $extpath = $this->localdir;
    } else {
      $extpath = $this->localdir . '/' . $destination;
      // Todo: move this to extraction function.
      if (!is_dir($extpath)) {
        mkdir($extpath);
      }
    }
    // Only local existing archives are allowed to be extracted.
    if (in_array($archive, $this->zipfiles)) {
      self::extract($archive, $extpath);
    }
  }

  /**
   * Checks file extension and calls suitable extractor functions.
   *
   * @param string $archive
   *   The archive name including file extension. E.g. my_archive.zip.
   * @param string $destination
   *   The relative destination path where to extract files.
   */
  public static function extract($archive, $destination)
  {
    $ext = pathinfo($archive, PATHINFO_EXTENSION);
    switch ($ext) {
      case 'zip':
        self::extractZipArchive($archive, $destination);
        break;
      case 'gz':
        self::extractGzipFile($archive, $destination);
        break;
      case 'rar':
        self::extractRarArchive($archive, $destination);
        break;
    }
  }

  /**
   * Decompress/extract a zip archive using ZipArchive.
   *
   * @param $archive
   * @param $destination
   */
  public static function extractZipArchive($archive, $destination)
  {
    // Check if webserver supports unzipping.
    if (!class_exists('ZipArchive')) {
      $GLOBALS['status'] = array('error' => 'Error: Your PHP version does not support unzip functionality.');
      return;
    }

    $zip = new ZipArchive;

    // Check if archive is readable.
    if ($zip->open($archive) === TRUE) {
      // Check if destination is writable
      if (is_writeable($destination . '/')) {
        $zip->extractTo($destination);
        $zip->close();
        $GLOBALS['status'] = array('success' => 'Files unzipped successfully');
      } else {
        $GLOBALS['status'] = array('error' => 'Error: Directory not writeable by webserver.');
      }
    } else {
      $GLOBALS['status'] = array('error' => 'Error: Cannot read .zip archive.');
    }
  }

  /**
   * Decompress a .gz File.
   *
   * @param string $archive
   *   The archive name including file extension. E.g. my_archive.zip.
   * @param string $destination
   *   The relative destination path where to extract files.
   */
  public static function extractGzipFile($archive, $destination)
  {
    // Check if zlib is enabled
    if (!function_exists('gzopen')) {
      $GLOBALS['status'] = array('error' => 'Error: Your PHP has no zlib support enabled.');
      return;
    }

    $filename = pathinfo($archive, PATHINFO_FILENAME);
    $gzipped = gzopen($archive, "rb");
    $file = fopen($destination . '/' . $filename, "w");

    while ($string = gzread($gzipped, 4096)) {
      fwrite($file, $string, strlen($string));
    }
    gzclose($gzipped);
    fclose($file);

    // Check if file was extracted.
    if (file_exists($destination . '/' . $filename)) {
      $GLOBALS['status'] = array('success' => 'File unzipped successfully.');

      // If we had a tar.gz file, let's extract that tar file.
      if (pathinfo($destination . '/' . $filename, PATHINFO_EXTENSION) == 'tar') {
        $phar = new PharData($destination . '/' . $filename);
        if ($phar->extractTo($destination)) {
          $GLOBALS['status'] = array('success' => 'Extracted tar.gz archive successfully.');
          // Delete .tar.
          unlink($destination . '/' . $filename);
        }
      }
    } else {
      $GLOBALS['status'] = array('error' => 'Error unzipping file.');
    }
  }

  /**
   * Decompress/extract a Rar archive using RarArchive.
   *
   * @param string $archive
   *   The archive name including file extension. E.g. my_archive.zip.
   * @param string $destination
   *   The relative destination path where to extract files.
   */
  public static function extractRarArchive($archive, $destination)
  {
    // Check if webserver supports unzipping.
    if (!class_exists('RarArchive')) {
      $GLOBALS['status'] = array('error' => 'Error: Your PHP version does not support .rar archive functionality. <a class="info" href="http://php.net/manual/en/rar.installation.php" target="_blank">How to install RarArchive</a>');
      return;
    }
    // Check if archive is readable.
    if ($rar = RarArchive::open($archive)) {
      // Check if destination is writable
      if (is_writeable($destination . '/')) {
        $entries = $rar->getEntries();
        foreach ($entries as $entry) {
          $temp = tirarAcentos($entry->getName());
          //@$entry->extract($destination);
          @$entry->extract(false, $destination. '/'.$temp);
        }
        $rar->close();
        $GLOBALS['status'] = array('success' => 'Files extracted successfully.');
      } else {
        $GLOBALS['status'] = array('error' => 'Error: Directory not writeable by webserver.');
      }
    } else {
      $GLOBALS['status'] = array('error' => 'Error: Cannot read .rar archive.');
    }
  }
}

// /**
//  * Class Zipper
//  *
//  * Copied and slightly modified from http://at2.php.net/manual/en/class.ziparchive.php#110719
//  * @author umbalaconmeogia
//  */
// class Zipper
// {
//   /**
//    * Add files and sub-directories in a folder to zip file.
//    *
//    * @param string $folder
//    *   Path to folder that should be zipped.
//    *
//    * @param ZipArchive $zipFile
//    *   Zipfile where files end up.
//    *
//    * @param int $exclusiveLength
//    *   Number of text to be exclusived from the file path.
//    */
//   private static function folderToZip($folder, &$zipFile, $exclusiveLength)
//   {
//     $handle = opendir($folder);

//     while (FALSE !== $f = readdir($handle)) {
//       // Check for local/parent path or zipping file itself and skip.
//       if ($f != '.' && $f != '..' && $f != basename(__FILE__)) {
//         $filePath = "$folder/$f";
//         // Remove prefix from file path before add to zip.
//         $localPath = substr($filePath, $exclusiveLength);

//         if (is_file($filePath)) {
//           $zipFile->addFile($filePath, $localPath);
//         } elseif (is_dir($filePath)) {
//           // Add sub-directory.
//           $zipFile->addEmptyDir($localPath);
//           self::folderToZip($filePath, $zipFile, $exclusiveLength);
//         }
//       }
//     }
//     closedir($handle);
//   }

//   /**
//    * Zip a folder (including itself).
//    *
//    * Usage:
//    *   Zipper::zipDir('path/to/sourceDir', 'path/to/out.zip');
//    *
//    * @param string $sourcePath
//    *   Relative path of directory to be zipped.
//    *
//    * @param string $outZipPath
//    *   Relative path of the resulting output zip file.
//    */
//   public static function zipDir($sourcePath, $outZipPath)
//   {
//     $pathInfo = pathinfo($sourcePath);
//     $parentPath = $pathInfo['dirname'];
//     $dirName = $pathInfo['basename'];

//     $z = new ZipArchive();
//     $z->open($outZipPath, ZipArchive::CREATE);
//     $z->addEmptyDir($dirName);
//     if ($sourcePath == $dirName) {
//       self::folderToZip($sourcePath, $z, 0);
//     } else {
//       self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
//     }
//     $z->close();

//     $GLOBALS['status'] = array('success' => 'Successfully created archive ' . $outZipPath);
//   }
// }
