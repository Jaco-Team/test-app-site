<?php

namespace App\Imports;

use App\Http\Resources\GlobalResource;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Model_site_price_level;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PriceLevelImport implements ToCollection
{
  private string $text = 'Успешно сохранено';
  private bool $st = true;
  private string $date_start = '';

  public function collection(Collection $rows): void
  {

    $new_data = [];

    foreach($rows as $key_row => $row) {

      if($key_row === 0) {

        foreach($row as $key_value => $value) {
          if($key_value !== 0 && $key_value !== 1) {
            $city_id = Model_site_price_level::get_city_by_name($value);
            $new_data[] = array(
              'city_id' => $city_id->id,
              'city_name' => $value,
              'key_value' => $key_value,
              'items' => []
            );
          }
        }

        if (is_numeric($row[1]) && floor($row[1]) == $row[1]) {
          $date = Date::excelToDateTimeObject($row[1])->format('Y-m-d');
          $today = date("Y-m-d");

          if ($date < $today || $date === $today) {

            $this->st = false;
            $this->text = 'Необходимо указать будущую даты (позже сегодняшней даты)';

            return;
          }

          $this->date_start = $date;

        } else {

          $this->st = false;
          $this->text = 'Необходимо указать корректную дату';

          return;
        }

      }

      if($key_row !== 0 && $key_row !== 1) {

        foreach($row as $key_value => $value) {

          if($key_value !== 0 && $key_value !== 1) {

            if(is_string($value)) {
              $this->st = false;
              $this->text = 'Укажите цену в ' . $key_row + 1 . ' строке, в товаре ' . $row[1];

              return;
            }

            if(is_null($value)) {
              $row[$key_value] = 0;
            }

            $index = $key_value - 2;

            if($new_data[$index]['key_value'] === $key_value) {
              $new_data[$index]['items'][] = array(
                'id' => $row[0],
                'price' => $row[$key_value]
              );
              $new_data[$index]['date_start'] = $this->date_start;
              $new_data[$index]['name'] = $this->date_start . ' ' . $new_data[$index]['city_name'];
            }

          }

        }

      }

    }

    if(count($new_data) > 0) {

      foreach($new_data as $data) {

        $level_id = Model_site_price_level::insert_new_level(
          $data['name'],
          $data['city_id'],
          $data['date_start'],
          date('Y_m_d_H_i_s')
        );

        if ($level_id > 0) {

          foreach($data['items'] as $item){

            if(empty($item['price'])){
              $item['price'] = 0;
            }

            Model_site_price_level::insert_all_level_items($item['id'], $level_id, $item['price']);

          }

        } else {

          $this->st = false;
          $this->text = 'Ошибка при сохранении';

        }
      }

    }

  }

  public function get_result_save_import_level(): GlobalResource
  {
    return new GlobalResource([
      'st' => $this->st,
      'text' => $this->text
    ]);
  }


}

