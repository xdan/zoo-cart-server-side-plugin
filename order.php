<? defined('_JEXEC') or die; ?>
<h1>Здравствуйте</h1>
<div>На сайте <?=$config->get('fromname')?> пользователь, сделал заказ</div>
<div>Реквизиты для безналичного расчета прикреплены к письму</div>
<div>Данные по заказу:</div>
<table>
    <tr>
        <th align="left">Дата:</th>
        <td><?=date('d.m.Y')?></td>
    </tr>
     <tr>
        <th align="left">Время:</th>
        <td><?=date('H:i')?></td>
    </tr>
    <tr>
        <th align="left">ФИО:</th>
        <td><?=$fio?></td>
    </tr>
    <tr>
        <th align="left">Телефон:</th>
        <td><?=$phone?></td>
    </tr>
    <tr>
        <th align="left">email:</th>
        <td><?=$mail?></td>
    </tr>
    <tr>
        <th align="left">Город:</th>
        <td><?=$city?></td>
    </tr>
    <tr>
        <th align="left">Отделение Деловых Линий:</th>
        <td><?=$bisnes_line_department?></td>
    </tr>
    <tr>
        <th align="left">Комментарий</th>
        <td><?=$message?></td>
    </tr>
</table>
<div>Заказанные товары:</div>
<table>
    <tr>
        <th>Товар</th>
        <th>Количество</th>
        <th>Цена</th>
        <th>На сумму</th>
    </tr>
<? 
    require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');
    $zoo = App::getInstance('zoo');
    $summ = 0;
    foreach ($carts as $cart) {
        $item = $zoo->table->item->get($cart->item_id);
        $summ += $cart->price * $cart->count;
        ?>
        <tr>
            <td><a href="<?=JRoute::_($zoo->route->item($item, false), true, -1)?>"><?=$item->name?></a></td>
            <td><?=$cart->count?></td>
            <td><?=number_format($cart->price, 0, '.', ' ')?> руб.</td>
            <td><?=number_format($cart->price * $cart->count, 0, '.', ' ')?> руб.</td>
        </tr>
        <?
    }
?>
    <tr>
        <th colspan="3" align="right">Итог:</th>
        <th><?=number_format($summ, 0, '.', ' ')?> руб.</th>
    </tr>
</table>