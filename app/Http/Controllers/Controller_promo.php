<?php

namespace App\Http\Controllers;

use App\Models\Model_promo;

class Controller_promo extends Controller
{
    //promo_action - 1 - скидка, 2 - добавляет товар, 3 - товар за цену
    //promo_type_sale - -1 - товар за цену, 1 - товары, 2 - категории, 3 - все, 4 - самый дешевый, 5 - самый дорогой, 6 - за определенную стоимость
    //promo_conditions - 1 - в корзине есть товары, 2- сумма в корзине, 3- сумма в категории, 4 - количество в корзине, 5- позиций в категории
    //promo_type_order - 1 - все, 2 - самовывоз, 3 - доставка, 4 - зал
    public function random_string($length){
        $chars = 'БГДЖИЛПТФЦШЫЭЮЯ12456789';
        $numChars = mb_strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= mb_substr($chars, rand(1, $numChars) - 1, 1);
        }

        return $string;
    }

    public function generate_promo($len, $city_id){
        $name = $this->random_string($len);

        $false_promo = Model_promo::checkActivePromo($name, $city_id, date('Y-m-d'));

        if($false_promo && array_key_exists('id', $false_promo)){
            return $this->generate_promo($len, $city_id);
        }else{
            return $name;
        }
    }

    public function new_promo(array $name, int $count, int $promo_action, int $promo_type_sale, string $promo_items, string $promo_cat, string $count_promo, int $promo_type, string $promo_items_add, string $promo_items_sale, int $promo_conditions, string $promo_conditions_items, int $min_sum, int $max_sum, array $dates, int $type_order, array $city, array $text, array $limit, int $user_id)
    {

        if( $name['type'] == 'generate' ){
            $name['name'] = $this->generate_promo($name['length'], $city['city_id']);
        }else{
            $false_promo = Model_promo::checkActivePromo($name['name'], $city['city_id'], date('Y-m-d'));

            if(!empty($false_promo['id'])){
                return [
                    'st' => false,
                    'text' => 'Промокод с таким название в данном городе уже создан'
                ];
            }
        }

        $about_promo = [
            'user_id' =>            $user_id,
            'date_create' =>        date('Y-m-d H:i:s'),
            'promo_name' => 		$name['name'],		/* название прмокода */
            'promo_in_count' => 	$count,	/* количество активаций */
            'promo_action' => 		$promo_action,		/* тип промокода */
            'promo_type_sale' => 	$promo_type_sale,	/* тип скидки */
            'promo_items' => 		$promo_items,		/* товары со скидкой */
            'promo_cat' => 			$promo_cat,			/* категории со скидкой */
            'count_promo' => 		$count_promo,		/* размер скидки */
            'promo_type' => 		$promo_type,		/* тип скидки (р / %) */

            'promo_items_add' => 	$promo_items_add,	/* позиции которые добавляются */
            'promo_items_sale' => 	$promo_items_sale,	/* позиции со скидкой */
            'promo_conditions' => 	$promo_conditions,	/* условие промокода */
            'promo_conditions_items' => 	$promo_conditions_items,	/* что должно быть в корзине */
            'promo_summ' =>			$min_sum,		/* сумма в корзине для условия */
            'promo_summ_to' =>		$max_sum,		/* максимальная сумма в корзине для условия */
            //'promo_when' => 		$post['data']['promo_when'],		/* когда работает промокод */
            'date_start' => 		$dates['date_start'],		/* дата начала */
            'date_end' => 			$dates['date_end'],			/* дата окончания */
            'time_start' => 		$dates['time_start'],		/* время начала */
            'time_end' => 			$dates['time_end'],			/* время окончания */
            'day_1' => 				$dates['day_1'] ?? 1,				/* понедельник */
            'day_2' => 				$dates['day_2'] ?? 1,				/* вторник */
            'day_3' => 				$dates['day_3'] ?? 1,				/* среда */
            'day_4' => 				$dates['day_4'] ?? 1,				/* четверг */
            'day_5' => 				$dates['day_5'] ?? 1,				/* пятница */
            'day_6' => 				$dates['day_6'] ?? 1,				/* суббота */
            'day_7' => 				$dates['day_7'] ?? 1,				/* воскресенье */
            'promo_type_order' => 	$type_order,	/* тип заказа */
            //'promo_where' => 		$post['data']['promo_where'],		/* где работает */
            'city_id' => 			$city['city_id'],   /* ид города */
            'promo_point' => 		$city['point_id'],	/* если город - все точки */
            'about_promo_text' => 	$text['text_true'],	/* описание промокода */
            'condition_promo_text' => $text['text_false'],	/* описание ошибки промокода */
            'site_only' => 			$limit['site_only'] ?? 0,			/* действует только на сайте */
            'free_drive' => 		$limit['free_drive'] ?? 0,		/* бесплатная доставка */
            'show_kit' => 			$limit['show_kit'] ?? 1,		/* показывать кухне */
            'site_first_order' => 	$limit['first_order'] ?? 0,		/* показывать кухне */
        ];

        $promo_id = Model_promo::savePromo($about_promo);

        if( (int)$promo_id > 0 && array_key_exists('dates', $limit) ){
            $this->limit_promo($limit['dates'], $city['city_id'], $promo_id, $user_id);
        }

        return $promo_id;
    }

    public function limit_promo(array $dates, int $city_id, int $promo_id, int $user_id){
        $city_list = Model_promo::getCityList($city_id);

        foreach($city_list as $cit) {
            foreach($dates as $date) {
                Model_promo::insertEvent($city_id, $date, $promo_id, $user_id);
            }
        }
    }
}
