<h2>Устройства</h2>
<table>
<tr>
    <th>Android ID</th>
    <th>Расположение</th>
    <th>Дата последнего обращение</th>
    <th>Статус</th>
</tr>
<?php foreach (Device::listAll() as $device):?>
<tr>
    <td><?= htmlspecialchars($device->aid) ?></td>
    <td><?= htmlspecialchars($device->shop_name) . '/' . htmlspecialchars($device->point_name) ?></td>
    <td><?= date('d/m/Y H:i:s', $device->last_time) ?></td>
    <td style="background: <?php echo time() > $device->last_time + 300 ? 'red' : 'green' ?>">&nbsp;</td>
</tr>
<?php endforeach;?>
</table>
