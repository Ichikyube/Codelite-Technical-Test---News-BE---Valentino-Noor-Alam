<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\Storage;

class UploadHelper
{

  /**
   * Upload Any Types of File. 
   * 
   * It's not checking the file type which may be checked before pass here in validation
   * 
   * @param  string $f               request with file
   * @param  binary $file            file
   * @param  string $name            filename
   * @param  string $target_location location where file will store
   * @return string                  filename
   */
  public static function upload($f, $file, $name, $base_path, $target_location)
  {
    if ($f) {
      $filename = date('YmdHis') . '-' . $name . '-' . $file->getClientOriginalExtension();
      $path =  $base_path . $file->storeAs($target_location,$filename);
      return $path;
    } else {
      return response()->json([
        'status' => false,
        'message' => 'Your source is not valid',
        'data' => null
      ], 403);
    }
  }

  /**
   * Update File
   * @param  string $f               request with file
   * @param  binary $file            file
   * @param string $name             filename
   * @param  string $target_location location where file will store
   * @param  string $old_location    file location which will delete
   * @return string                  filename
   */
  public static function update($f, $file, $name, $target_location, $old_location)
  {
    //delete the old file
    $target_location = $target_location . '/';
    if (Storage::exists($target_location . $old_location)) {
      Storage::delete($target_location . $old_location);
    }

    $filename = $name . '.' . $file->getClientOriginalExtension();
    $file->move($target_location, $filename);
    return $filename;
  }


  /**
   * delete file
   * @param  type $location file location that will delete
   * @return type                  null
   */
  public static function deleteFile($location)
  {
    if (Storage::exists($location)) {
        Storage::delete($location);
    }
  }
}
