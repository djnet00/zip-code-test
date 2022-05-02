<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/* It takes a zip code and returns an array with the zip code, locality, federal entity, settlements,
and municipality */
class ZipCode extends Model
{
    use HasFactory;
    protected $table = 'zip_codes';
    protected $primaryKey = 'id';


    /**
     * It takes a zip code and returns an array with the zip code, locality, federal entity,
     * settlements, and municipality
     *
     * @param zipCode The zip code you want to search for.
     *
     * @return An array with the following structure:
     * ```
     * [
     *     'zip_code' => '',
     *     'locality' => '',
     *     'federal_entity' => [
     *         'key' => '',
     *         'name' => '',
     *         'code' => ''
     *     ],
     *     'settlements' => [
     *         [
     */
    public static function getCode($zipCode)
    {

        $zips = ZipCode::where('d_codigo', $zipCode)->get();

        $data = [];

        if( $zips->count() > 0 ) {
            $zipHeader = $zips->first();

            $data['zip_code'] = $zipHeader->d_codigo;
            $data['locality'] = self::cleanString($zipHeader->d_ciudad);
            $data['federal_entity'] = [
                'key' => intval($zipHeader->c_estado),
                'name' => self::cleanString($zipHeader->d_estado),
                'code' => null
            ];
            $data['settlements'] = [];

            foreach($zips as $zip) {
                $data['settlements'][] = [
                    'key' => intval($zip->id_asenta_cpcons),
                    'name' => self::cleanString($zip->d_asenta),
                    'zone_type' => self::cleanString($zip->d_zona),
                    'settlement_type' => ['name'=> $zip->d_tipo_asenta]
                ];
            }

            $data['municipality'] = [
                'key' => intval($zipHeader->c_mnpio),
                'name' => self::cleanString($zipHeader->D_mnpio),
            ];
        }

        return $data;
    }

    /**
     * It takes a string, removes all non-alphanumeric characters, replaces spaces with dashes, and
     * converts the string to uppercase
     *
     * @param string The string to be cleaned.
     *
     * @return a string that has been cleaned of any special characters.
     */
    private static function cleanString($string){
        $string = str_replace(array('[\', \']'), '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
        $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , ' ', $string);
        return strtoupper($string);
    }
}
