<?php

namespace App\Http\Controllers\Api;

use App\Models\Model_site_price_level;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use DateTime;


class PriceLevelExport implements FromArray, WithMapping, WithHeadings, WithColumnWidths, WithStyles, WithColumnFormatting
{
  public function array(): array
  {
    return Model_site_price_level::get_all_items();
  }

  public function headings (): array {
    $cities = Model_site_price_level::get_cities();
    $result = array();

    foreach($cities as $city){
      $result[] = $city->name;
    }

    $dateTime = (new DateTime('+1 day'));
    $date = Date::dateTimeToExcel($dateTime);
    $formattedNumber = number_format($date, 0, '.', '');
    $start = ['Дата:',  $formattedNumber];
    $heading_1 = array_merge($start, $result);

    return [
      $heading_1,
      ['ID', 'Название', 'Цена', 'Цена'],
    ];
  }

  public function map($items): array
  {
    return [
      $items->id,
      $items->name
    ];
  }

  public function columnWidths(): array
  {
    return [
      'A' => 10,
      'B' => 50,
      'C' => 15,
      'D' => 15,
    ];
  }

  public function styles(Worksheet $sheet): array
  {
    $sheet->protectCells('B1', null, true);
    $sheet->protectCells('C3:D1000', null, true);
    $sheet->getProtection()->setSheet(true);

    return [
      1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'left']],
      2 => ['font' => ['italic' => true]],
    ];
  }

  public function columnFormats(): array
  {
    return [
      'B' => NumberFormat::FORMAT_DATE_YYYYMMDD,
      'C' => NumberFormat::FORMAT_NUMBER,
      'D' => NumberFormat::FORMAT_NUMBER,
    ];
  }

}
