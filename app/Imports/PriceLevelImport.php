<?php

namespace App\Imports;

use App\Models\Model_site_price_level;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class PriceLevelImport implements ToModel
{
  public function model(array $row): null
  {
    return null;
  }
}

