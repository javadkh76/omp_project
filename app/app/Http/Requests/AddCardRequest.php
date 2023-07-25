<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class AddCardRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'card_number' => [
                'required', 'numeric', 'unique:cards',
                function (string $attribute, mixed $value, Closure $fail) {
                    $cardLen = strlen($value);
                    if ($cardLen < 16 || (int) substr($value, 1, 10) === 0 || (int) substr($value, 10, 6) === 0) {
                        $fail("The {$attribute} is invalid.");
                    }
                    $c = substr($value, 15, 1);
                    $s = $k = $d = 0;
                    for ($i = 0; $i < 16; $i++) {
                        $k = ($i % 2 == 0) ? 2 : 1;
                        $d = (int) substr($value, $i, 1) * $k;
                        $s += ($d > 9) ? $d - 9 : $d;
                    }
                    if (!(($s % 10) === 0)) {
                        $fail("The {$attribute} is invalid.");
                    }
                }
            ]
        ];
    }
}
