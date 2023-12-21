<?php

namespace App\Services;
use Illuminate\Http\Request;
/**
 * Class FilesService.
 */
class FilesService
{
    public function UploadFiles(Request $request)
    {
        $now                        = \Carbon\Carbon::now();
        $year                       = date('Y', strtotime($now));
        $month                      = date('m', strtotime($now));
        $days                       = date('d', strtotime($now));

        $base64Image = explode(";base64,", $request->get('files_image'));
        $explodeImage = explode("image/", $base64Image[0]);
        $imageName = $explodeImage[1];
        $image_base64 = base64_decode($base64Image[1]);
            
        $path                   = public_path().'/assets/files/'.$year. '/'.$month.'/'. $days.'/';
        $db                     = 'assets/files/'.$year. '/'.$month.'/'. $days;
        $file = $path . strtolower(str_replace(' ', '-', $request->name)) . '.'.$imageName;
        if(!\File::isDirectory($path)){
            \File::makeDirectory($path, 0777, true, true);
            $s3Url = $path . $imageName;
            file_put_contents($file, $image_base64);
        }else{
            file_put_contents($file, $image_base64);
        }
        
        $fliesource			    = $db .'/'.strtolower(str_replace(' ', '-', $request->name)) . '.'.$imageName;

        return $fliesource;
    }
}
