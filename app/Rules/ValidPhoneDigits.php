<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberType;

/**
 * Server-side phone number validation using Google's libphonenumber.
 *
 * This is the same library used by intl-tel-input on the client side,
 * ensuring consistent validation for ALL countries worldwide.
 *
 * - Validates format, length, and type (mobile / fixed-line-or-mobile)
 * - Derives the default country from APP_TIMEZONE via IntlTimeZone
 * - Accepts E.164 numbers stored without '+' (e.g. 8801751136819)
 *
 * Usage: 'mobile' => ['nullable', 'string', new ValidPhoneDigits]
 */
class ValidPhoneDigits implements ValidationRule
{
    protected string $defaultRegion;

    public function __construct()
    {
        $this->defaultRegion = $this->resolveCountryIso();
    }

    /**
     * Derive the ISO-3166-1 alpha-2 country code from APP_TIMEZONE.
     */
    protected function resolveCountryIso(): string
    {
        try {
            $tz = config('app.timezone', 'Asia/Dhaka');
            if (class_exists('IntlTimeZone')) {
                $region = \IntlTimeZone::getRegion($tz);
                if ($region && strlen($region) === 2) {
                    return strtoupper($region);
                }
            }
        } catch (\Throwable) {
            // fall through
        }

        return 'BD'; // fallback
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value || ! is_string($value)) {
            return; // empty/null is handled by 'required' rule separately
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $parsed = $phoneUtil->parse($value, $this->defaultRegion);
        } catch (NumberParseException $e) {
            try {
                $parsed = $phoneUtil->parse('+' . $value, $this->defaultRegion);
            } catch (NumberParseException $e2) {
                $fail('The :attribute is not a valid phone number.');
                return;
            }
        }

        // Full validity check (length, format, country) — same as intlTelInput isValidNumber()
        if (! $phoneUtil->isValidNumber($parsed)) {
            $fail('The :attribute is not a valid phone number.');
            return;
        }

        // Optionally enforce mobile type (comment out if fixed-line numbers are also acceptable)
        $type = $phoneUtil->getNumberType($parsed);
        $mobileTypes = [
            PhoneNumberType::MOBILE,
            PhoneNumberType::FIXED_LINE_OR_MOBILE,
        ];
        if (! in_array($type, $mobileTypes, true)) {
            $fail('The :attribute must be a mobile phone number.');
        }
    }
}
