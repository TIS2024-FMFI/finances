<?php

namespace App\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;

/**
 * A general request containing date boundaries.
 * 
 * Fields: from, to
 */
class DateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     * 
     * @return void
     */
    public function prepareForValidation()
    {
        parent::prepareForValidation();

        $this->merge([
            'from' => $this->query('from'),
            'to' => $this->query('to'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from']
        ];
    }

    /**
     * Get the date described by the validated value of 'from' query attribute
     * or the minimum date if 'from' is not present.
     * 
     * @return \Illuminate\Support\Carbon
     */
    public function getValidatedFromDateOrMin()
    {
        $date = $this->validated('from');

        return $date ? Date::create($date) : Date::minValue();
    }

    /**
     * Get the date described by the validated value of 'to' query attribute
     * or the maximum date if 'to' is not present.
     * 
     * @return \Illuminate\Support\Carbon
     */
    public function getValidatedToDateOrMax()
    {
        $date = $this->validated('to');

        return $date ? Date::create($date) : Date::maxValue();
    }
}
