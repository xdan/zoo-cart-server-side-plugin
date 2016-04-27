<? defined('_JEXEC') or die; ?>
<h1>Здравствуйте</h1>
<div>На сайте <?=$config->get('fromname')?> пользователь, оставил сообщение</div>
<div>Данные:</div>
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
        <th align="left">Компания:</th>
        <td><?=$company?></td>
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
        <th align="left">Комментарий:</th>
        <td><?=$message?></td>
    </tr>
</table>
