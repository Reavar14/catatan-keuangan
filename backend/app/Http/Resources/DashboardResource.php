<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_income'   => $this->resource['total_income'],
            'total_expense'  => $this->resource['total_expense'],
            'balance'        => $this->resource['balance'],
            'monthly_chart'  => $this->resource['monthly_chart'],
        ];
    }
}
