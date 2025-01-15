<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller_site_clients;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromCollection;

class SiteClientsExport implements FromCollection, WithMapping, WithHeadings, WithColumnWidths, WithStyles
{
  public $request;
  function __construct($data) {
    $this->request = $data;
  }

  public function collection(): Collection
  {
    $orders = (new Controller_site_clients)->get_orders($this->request);
    return collect($orders);
  }

  public function headings (): array {
    return [
      'Заказ', 'Точка', 'Оформил', 'Номер клиента', 'Адрес доставки', 'Время открытия заказа', 'Ко времени', 'Закрыт на кухне', 'Получен клиентом', 'Время обещ', 'Тип', 'Статус', 'Сумма', 'Оплата', 'Водитель'
    ];
  }

  public function map($orders): array
  {
    $orders = $orders->map(function ($order) {
      return [
        $order->id,
        $order->point_addr,
        $order->type_user,
        $order->number,
        $order->street . ' ' . $order->home,
        $order->date_time_order,
        $order->need_time,
        $order->give_data_time == '00:00:00' ? '' : $order->give_data_time,
        $order->close_order,
        $order->unix_time_to_client == '0' || $order->is_preorder == 1 ? '' : $order->unix_time_to_client,
        $order->type_order,
        $order->is_delete == 0 ? $order->status : 'Удален',
        $order->order_price,
        $order->type_pay,
        $order->driver ?? '',
      ];
    });

    return $orders->toArray();
  }

  public function columnWidths(): array
  {
    return [
      'A' => 8,
      'B' => 20,
      'C' => 15,
      'D' => 15,
      'E' => 30,
      'F' => 15,
      'G' => 15,
      'H' => 15,
      'I' => 17,
      'J' => 13,
      'K' => 15,
      'L' => 15,
      'M' => 10,
      'N' => 10,
      'O' => 18
    ];
  }

  public function styles(Worksheet $sheet): array
  {
    //$sheet->getStyle('A:O')->getAlignment()->setWrapText(true);

    return [
      1 => ['font' => ['bold' => true]]
    ];
  }


}
