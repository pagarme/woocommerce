<?php

namespace Pagarme\Core\Kernel\Helper;

class StringFunctionsHelper
{
    /**
     * @var array unwanted
     */
    private $unwanted = [
        'À'=>'A',
        'Á'=>'A',
        'Â'=>'A',
        'Ã'=>'A',
        'Ä'=>'Ae',
        'Å'=>'A',
        'Æ'=>'A',
        'Ă'=>'A',
        'Ą' => 'A',
        'ą' => 'a',
        'à'=>'a',
        'á'=>'a',
        'â'=>'a',
        'ã'=>'a',
        'ä'=>'ae',
        'å'=>'a',
        'ă'=>'a',
        'æ'=>'ae',
        'þ'=>'b',
        'Þ'=>'B',
        'Ç'=>'C',
        'ç'=>'c',
        'Ć' => 'C',
        'ć' => 'c',
        'È'=>'E',
        'É'=>'E',
        'Ê'=>'E',
        'Ë'=>'E',
        'Ę' => 'E',
        'ę' => 'e',
        'è'=>'e',
        'é'=>'e',
        'ê'=>'e',
        'ë'=>'e',
        'Ğ'=>'G',
        'ğ'=>'g',
        'Ì'=>'I',
        'Í'=>'I',
        'Î'=>'I',
        'Ï'=>'I',
        'İ'=>'I',
        'ı'=>'i',
        'ì'=>'i',
        'í'=>'i',
        'î'=>'i',
        'ï'=>'i',
        'Ł' => 'L',
        'ł' => 'l',
        'Ñ'=>'N',
        'Ń' => 'N',
        'ń' => 'n',
        'Ò'=>'O',
        'Ó'=>'O',
        'Ô'=>'O',
        'Õ'=>'O',
        'Ö'=>'Oe',
        'Ø'=>'O',
        'ö'=>'oe',
        'ø'=>'o',
        'ð'=>'o',
        'ñ'=>'n',
        'ò'=>'o',
        'ó'=>'o',
        'ô'=>'o',
        'õ'=>'o',
        'Š'=>'S',
        'š'=>'s',
        'Ş'=>'S',
        'ș'=>'s',
        'Ș'=>'S',
        'ş'=>'s',
        'ß'=>'ss',
        'Ś' => 'S',
        'ś' => 's',
        'ț'=>'t',
        'Ț'=>'T',
        'Ù'=>'U',
        'Ú'=>'U',
        'Û'=>'U',
        'Ü'=>'Ue',
        'ù'=>'u',
        'ú'=>'u',
        'û'=>'u',
        'ü'=>'ue',
        'Ý'=>'Y',
        'ý'=>'y',
        'ý'=>'y',
        'ÿ'=>'y',
        'Ž'=>'Z',
        'ž'=>'z',
        'Ż' => 'Z',
        'ż' => 'z',
        'Ź' => 'Z',
        'ź' => 'z'
    ];

    /**
     * @var array specialCharacters
     */
    private $specialCharacters = [
        '!' => '',
        '@' => '',
        '#' => '',
        '$' => '',
        '%' => '',
        '&' => '',
        '*' => ''
    ];

    /**
     * This method will remove all accentiation of your string
     *
     * @param string $str
     *
     * @return string
     */
    final public function removeSpecialCharacters($str)
    {
        $str = strtr($str, $this->unwanted);
        $str = strtr($str, $this->specialCharacters);

        return preg_replace(
            "/[^a-zA-Z ]/",
            '',
            $str ?? ''
        );
    }

    public function cleanStrToDb($str)
    {
        $str = strtr($str, $this->specialCharacters);

        return str_replace(
            "'",
            "`",
            strip_tags($str ?? '')
        );
    }

    /**
     * @param string $text
     * @return string
     */
    public static function removeLineBreaks($text)
    {
        if($text === null) {
            return "";
        }
        $pattern = '/\\\s+\\\s\\\r\\\n|\\\r|\\\t|\\\n\\\r|\\\n/m';
        $textCleanBreakLines = trim(
            preg_replace(
                $pattern,
                ' ',
                $text ?? ''
            )
        );

        return str_replace(chr(9), '', $textCleanBreakLines);
    }
}
