<?php
// tu klasa do obslugi ogolnych zadan

class common 
{
	public function stripHtmlInArray(array $array, $dimensions = 1) {
        foreach ($array as $key => $value) {
            if (2 <= $dimensions) {
                foreach ($value as $key2 => $value2) {
                    if (3 == $dimensions) {
                        foreach ($value2 as $key3 => $value3) {
                            $array[$key][$key2][$key3] = html_entity_decode(trim(strip_tags($value3)));
                        }
                    } else {
                        $array[$key][$key2] = html_entity_decode(trim(strip_tags($value2)));
                    }
                }
            } else {
                $array[$key] = html_entity_decode(trim(strip_tags($value)));
            }
        }
        return $array;
    }

    public function stripHtmlFromString($string) {
        return html_entity_decode(trim(strip_tags($string)));
    }

    public function checkIfStringIsValidDateTime($string, $format) {
        if (false !== DateTime::createFromFormat($format, $string)) {
            return true;
        }
        return false;
    }

    public function checkIfStringContainsNumbers($string) {
        if (preg_match('^[0-9]{1,}^', $string)) {
            return true;
        }
        return false;
    }

    public function stripNonDigitCharacters($string) {
        return preg_replace('/[^0-9]/', '', $string);
    }

    public function stripNonLetterCharacters($string) {
        return preg_replace('/[^a-zA-Z]/', '', $string);
    }

    public function addMinutesToTime($string, $minutes) {
        if (true === $this->checkIfStringIsValidDateTime($string, 'H:i') && is_numeric($minutes)) {
            $timestamp = strtotime($string);
            if (false !== $timestamp && 0 < $minutes) {
                return date('H:i', strtotime('+' . $minutes . ' minutes', $timestamp));
            }
        }
        return '';
    }
}