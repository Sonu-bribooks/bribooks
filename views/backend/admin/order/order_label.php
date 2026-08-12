<?php
$i = '1';

$count = count($shipments);
foreach ($shipments as $order) {
	$page_break = 'page-break-after: always;';
	if ($i == $count)
		$page_break = '';

	$html = '<style>@page { margin: 0 4px; }</style><div style="width: 99%; height: 100%; padding: 0; height: 6.20in; border: 2px solid; ' . $page_break . '">';

	echo $html;
	$order_info = $order->order;
	$shipment = $order->shipment;
	$products = $order->products;
	$courier = $order->courier;
	$warehouse = $order->warehouse;
	$rto_warehouse = $order->rto_warehouse;
	$company = $order->company;
	$user = $order->user;

	$channels_brand_logo = $order->channels_brand_logo;

	include VIEWPATH . 'backend/admin/order/label/common.php';

	/*switch (strtolower($courier->carrier_code)) {
		case 'bluedart':
			include VIEWPATH . 'backend/admin/order/label/bluedart.php';
			break;
		case 'bluedart_24':
			include VIEWPATH . 'backend/admin/order/label/bluedart_24.php';
			break;
		case 'dtdc':
			include VIEWPATH . 'backend/admin/order/label/dtdc.php';
			break;
		case 'ecom':
			include VIEWPATH . 'backend/admin/order/label/ecom.php';
			break;
		case 'shadowfax':
			include VIEWPATH . 'backend/admin/order/label/shadowfax.php';
			break;
		case 'ekart':
			include VIEWPATH . 'backend/admin/order/label/ekart.php';
			break;
		case 'xpressbees':
			include VIEWPATH . 'backend/admin/order/label/xpressbees.php';
			break;
		case 'smartr':
			include VIEWPATH . 'backend/admin/order/label/smartr.php';
			break;
		default:
			include VIEWPATH . 'backend/admin/order/label/common.php';
			break;
	}*/
?>

	</div>
<?php
	$i++;
}
?>