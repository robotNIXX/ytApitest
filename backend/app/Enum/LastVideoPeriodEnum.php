<?php

namespace App\Enum;

enum LastVideoPeriodEnum: string
{
    const LAST_7_DAYS = 'last_7_days';
    const LAST_MONTH = 'last_month';
    const LAST_YEAR = 'last_year';
}
