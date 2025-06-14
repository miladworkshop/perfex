<?php

namespace app\services;

class NumToWordFrench
{
    private bool $hasCents;
    private string $amount;
    private string $euros;
    private string $cents;

    private array $word_array;

    public function __construct(string $amount)
    {
        $this->amount = $amount;
        $this->hasCents = false;

        $parts = explode(".", $this->amount);
        $this->euros = $parts[0];

        if (isset($parts[1]) && ((int)$parts[1]) > 0) {
            if (strlen($parts[1]) > 2) {
                $parts[1] = substr($parts[1], 0, 2);
            }
            $this->hasCents = true;
            $this->cents = $parts[1];
        }

        for ($i = 1; $i < 100; $i++) {
            $this->word_array['num_word_' . $i] = _l('num_word_' . $i);
        }
        for ($i = 100; $i <= 900; $i = $i + 100) {
            $this->word_array['num_word_' . $i] = _l('num_word_' . $i);
        }
        $this->word_array['number_word_and'] = _l('number_word_and');
        $this->word_array['num_word_cents'] = _l('num_word_cents');
        $this->word_array['num_word_thousand'] = _l('num_word_thousand');
        $this->word_array['num_word_million'] = _l('num_word_million');
        $this->word_array['num_word_billion'] = _l('num_word_billion');
        $this->word_array['num_word_trillion'] = _l('num_word_trillion');
        $this->word_array['num_word_zillion'] = _l('num_word_zillion');
    }

    public function get_words(): string
    {
        $words = "";

        $thousands = (int)($this->euros / 1000);
        $this->euros %= 1000;

        if ($thousands > 0) {
            if ($thousands > 1) {
                $words .= $this->word_array['num_word_' . $thousands] . " ";
            }
            $words .= $this->word_array['num_word_thousand'] . " ";
        }

        $hundreds_and_below = $this->euros;
        if ($hundreds_and_below > 0) {
            $words .= $this->convert_segment($hundreds_and_below);
        }

        $words .= ' ' . _l('num_word_EUR');

        if ($this->hasCents) {
            $centsValue = (int)$this->cents;
            $words .= $this->word_array['number_word_and'] . " ";
            $words .= $this->convert_segment($centsValue) . " ";
            $words .= $this->word_array['num_word_cents'] . " ";
        }

        return trim($words) . " " . _l('number_word_only');
    }

    private function convert_segment(int $num): string
    {
        if ($num == 0) {
            return "";
        }

        $words = "";

        if ($num >= 100) {
            $hundreds = (int)($num / 100) * 100;
            $num %= 100;

            $words .= $this->word_array['num_word_' . $hundreds] . " ";
        }

        if ($num > 0) {
            $words .= $this->word_array['num_word_' . $num];
        }

        return trim($words);
    }
}
