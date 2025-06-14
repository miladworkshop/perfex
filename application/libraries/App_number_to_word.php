<?php

use app\services\NumToWordFrench;
use app\services\NumToWordIndian;

defined('BASEPATH') or exit('No direct script access allowed');

class App_number_to_word
{
    // TODO
    // add to options
    // words without spaces
    // array of possible numbers => words
    private $word_array = [];

    // thousand array,
    private $thousand = [];

    // variables
    private $val;

    // Number of decimal places for formatting
    private $decimal_places;

    private $currency0;

    private $currency1;

    // codeigniter instance
    private $ci;

    private $val_array;

    private $dec_value;

    private $dec_word;

    private $num_value;

    private $num_word;

    private $val_word;

    private $original_val;

    private $language;

    public function __construct($params = [])
    {
        $l = '';
        $this->ci = &get_instance();
        $lastLangFileLanguage = $this->ci->lang->last_loaded ?: 'english';

        $this->ci->lang->load($lastLangFileLanguage . '_num_words_lang', $lastLangFileLanguage);
        // Load again the custom lang file in case any overwrite for the num_words_lang.php file
        load_custom_lang_file($lastLangFileLanguage);
        $this->language = $lastLangFileLanguage;

        array_push($this->thousand, '');
        array_push($this->thousand, _l('num_word_thousand') . ' ');
        array_push($this->thousand, _l('num_word_million') . ' ');
        array_push($this->thousand, _l('num_word_billion') . ' ');
        array_push($this->thousand, _l('num_word_trillion') . ' ');
        array_push($this->thousand, _l('num_word_zillion') . ' ');
        for ($i = 1; $i < 100; $i++) {
            $this->word_array[$i] = _l('num_word_' . $i);
        }
        for ($i = 100; $i <= 900; $i = $i + 100) {
            $this->word_array[$i] = _l('num_word_' . $i);
        }

        $this->decimal_places = get_decimal_places() > 3 ? 3 : get_decimal_places();
    }

    public function convert($in_val = 0, $in_currency0 = '', $in_currency1 = true)
    {
        $this->original_val = $in_val;
        $this->val = $in_val;

        $this->dec_value = null;
        $this->dec_word = null;
        $this->val_array = null;
        $this->val_word = null;
        $this->num_value = null;
        $this->num_word = null;

        $this->currency0 = _l('num_word_' . mb_strtoupper($in_currency0, 'UTF-8'));

        if (strtolower($in_currency0) == 'inr') {
            $final_val = (new NumToWordIndian($in_val))->get_words();
        } elseif (strtolower($this->language) == 'french') {
            $final_val = (new NumToWordFrench($in_val))->get_words();
        } else {
            // Currency not found
            if (str_contains($this->currency0, 'num_word_')) {
                $this->currency0 = $in_currency0;
            }
            if (!$in_currency1) {
                $this->currency1 = '';
            } else {
                $cents = _l($key = 'num_word_cents_' . $in_currency0, '', false);

                if ($cents === $key) {
                    $cents = _l('num_word_cents');
                }

                $this->currency1 = $cents;
            }
            $final_val = $this->get_words($this->val, $this->currency0);
            $final_val = $this->addAmountQualifier($final_val, $in_currency0);

            $final_val = get_option('total_to_words_lowercase') == 1
                ? trim(mb_strtolower($final_val, 'UTF-8'))
                : trim($final_val);
        }

        return hooks()->apply_filters('before_return_num_word', $final_val, [
            'original_number' => $this->original_val,
            'currency' => $in_currency0,
            'language' => $this->language,
        ]);
    }

    public function addAmountQualifier(string $amountWord, string $currency): string
    {
        if ($currency == 'KWD') {
            return $amountWord . ' ' . _l('number_word_only');
        } else {
            return $amountWord;
        }
    }

    /**
     * Converts the given number to its equivalent word representation.
     *
     * @param mixed $num_val The number to convert.
     * @param string $currency0 The main currency to append.
     * @return string The word representation of the number.
     */
    public function get_words($num_val, string $currency0 = '')
    {
        // remove commas from comma-separated numbers
        $num_val = abs(floatval(str_replace(',', '', $num_val)));
        if ($num_val <= 0) {
            return '';
        }
        // convert to number format
        $num_val = number_format($num_val, $this->decimal_places, ',', ',');
        // split to array of 3(s) digits and 3-digit decimal
        $val_array = explode(',', $num_val);
        // separate decimal digit
        $dec_value = intval(mb_substr($val_array[count($val_array) - 1], 0, 3));
        if ($dec_value > 0) {
            $w_and = _l('number_word_and');
            $w_and = ($w_and == ' ' ? '' : $w_and .= ' ');
            // convert decimal part to word
            $dec_word = $w_and . '' . $this->get_words($dec_value, $this->currency1);
        }

        $t = 0;
        $num_word = '';
        // loop through all 3(s) digits in VAL array
        for ($i = count($val_array) - 2; $i >= 0; $i--) {
            $num_value = intval($val_array[$i]);
            if ($num_value == 0) {
                if (count($val_array) === 2) {
                    $num_word = _l('num_word_0') . ' ' . $num_word;
                } else {
                    $num_word = ' ' . $num_word;
                }
            } elseif (strlen($num_value . '') <= 2) {
                $num_word = $this->word_array[$num_value] . ' ' . $this->thousand[$t] . $num_word;
                if ($i == 1) {
                    $w_and = _l('number_word_and');
                    $w_and = ($w_and == ' ' ? '' : $w_and .= ' ');
                    $num_word = $w_and . '' . $num_word;
                }
            } else {
                @$num_word = $this->word_array[mb_substr($num_value, 0, 1) . '00'] . (intval(mb_substr($num_value, 1, $this->decimal_places)) > 0 ? (_l('number_word_and') != ' ' ? ' ' . _l('number_word_and') . ' ' : ' ') : '') . $this->word_array[intval(mb_substr($num_value, 1, 3))] . ' ' . $this->thousand[$t] . $num_word;
            }
            $t++;
        }

        if (!empty($num_word)) {
            $num_word .= '' . $currency0;
        }

        return $num_word . ' ' . ($dec_word ?? '');
    }
}
