<?php

namespace App\Services;
use App\Services\StoreService;

/**
 * Class CustomService.
 */
class CustomService
{
    public function calculate_inclusive($amount,$tax){
        $tot = ($amount/(($tax/100)+1)/10);
        return number_format($tot,2,".","");
    }
    public function calculate_exclusive($amount,$tax){
        $tot = (($amount*$tax)/(100));
        return number_format($tot,2,".","");
    }
    public function store_number_format($value=0,$comma=true){
        return ($comma) ? number_format($value,$this->decimals()) : number_format($value,$this->decimals(),".","");
    }
    public function parseBlob($string)
    {
        $destinationPath = public_path().'/assets/currency/';
        $fileName  = 'fk';
        $file = file_put_contents($destinationPath.$fileName, $string);
        $imageData = base64_encode($string);
        // $src = 'data: '.mime_content_type($string).';base64,'.$imageData;
        return $string;
    }
    public function decimals(){
        $decimals           = (new StoreService())->getStoreByid(\Auth::user()->store_id)
                            ->select('decimals')
                            ->first();
        if($decimals){
            return $decimals->decimals;
        }
        return 2;
    }
    public function kmb($n, $precision = 2) {
        if ($n < 900) {
          // Default
           $n_format = number_format($n);
          } else if ($n < 900000) {
          // Thausand
          $n_format = number_format($n / 1000, $precision). 'K';
          } else if ($n < 900000000) {
          // Million
          $n_format = number_format($n / 1000000, $precision). 'M';
          } else if ($n < 900000000000) {
          // Billion
          $n_format = number_format($n / 1000000000, $precision). 'B';
          } else {
          // Trillion
          $n_format = number_format($n / 1000000000000, $precision). 'T';
      }
      return $n_format;
    }
}
