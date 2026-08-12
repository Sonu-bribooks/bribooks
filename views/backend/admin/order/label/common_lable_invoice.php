<style>
    table {
        font-size: 9px;
    }

    p {
        font-size: 9px;
        font-family: Times New Roman, serif !important;
    }

    footer {
        position: fixed;
        bottom: 0cm;
        left: 0cm;
        right: 0cm;
        height: 2cm;
    }
</style>

<table style="width: 100%;border-bottom: 2px solid;">
    <tbody>
        <tr>
            <td style=" margin-top: 0px;display: block; width: 60%">
                <p style="font-size: 12px"><b>To:</b></p>
                <p style="font-size: 12px"><b><?= ucwords($order_info->shipping_fname . ' ' . $order_info->shipping_lname); ?></b></p>
                <?php if (!empty($order_info->shipping_company_name)) { ?>
                    <p style="font-size: 12px"><?= ucwords($order_info->shipping_company_name); ?></p>
                <?php } ?>
                <p style="font-size: 12px"><?= ucwords($order_info->shipping_address . ', ' . $order_info->shipping_address_2 . ', ' . $order_info->shipping_city . ', ' . $order_info->shipping_state . ', ' . $order_info->shipping_country . ' - ' . $order_info->shipping_zip) ?> </p>
                <p style="font-size: 12px">MOBILE NO: <b><?= $order_info->shipping_phone; ?></b></p>
            </td>
            <td style="margin-top: 0px; width:40%; text-align: center; vertical-align: top;">
            <?php if (!empty($company->cmp_logo) && empty($channels_brand_logo->channel_brand_logo) ) {  ?>
                    <img src="<?php echo (strpos($company->cmp_logo, "amazonaws.com") !== false) ? ($company->cmp_logo) : (base_url() . 'assets/seller_company_logo/' . $company->cmp_logo); ?>" width="80" height="80">
                <?php } ?>
                    <?php  if (isset($channels_brand_logo) && !empty($channels_brand_logo->channel_brand_logo)  ) { ?>
                    <img src="<?php echo $channels_brand_logo->channel_brand_logo;?>" width="80" height="80">
                <?php } ?>
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;border-bottom: 2px solid;">
    <tbody>
        <tr>
            <td style="margin-top: 0px;display: block; width: 60%">
                <p style="font-size: 12px">Order Date: <b><?= date('M d, Y', $order_info->order_date) ?></b></p>
                <p style="font-size: 12px">Invoice No: <?= $order_info->order_id; ?></p>
            </td>
            <td style="margin-top: 20px; width:40%; text-align: center;">
                <p>
                    <?php
                    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($order_info->order_id, $generator::TYPE_CODE_128)) . '" style="white-space:nowrap" height="30px" width="85px">';
                    ?>
                </p>
                <p><?= $order_info->order_id; ?></p>
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;padding: 5px;padding-left: 15px; padding-right: 15px;border-bottom: 1px solid;">
    <tbody>
        <tr>
            <td style="width:40%; text-align: center;">
                <?php if (strtolower($order_info->order_payment_type) == 'cod') { ?>
                    <p style="font-size:18px;"><?= strtoupper($order_info->order_payment_type); ?></p>
                    <p style="font-size:14px;">&#8377;<?= round($order_info->order_amount, 2); ?></p>
                <?php
                } elseif (strtolower($order_info->order_payment_type) == 'reverse') {
                ?>
                    <p style="font-size:18px;">PICKUP</p>
                <?php
                } else {
                ?>
                    <p style="font-size:18px;">PAID</p>
                <?php } ?>
                <br><br>
                <p>WEIGHT : <?= ($order_info->package_weight > 0) ? round($order_info->package_weight / 1000, 1) : '0.5'; ?> KG</p>
            </td>
            <td style="text-align:center; width: 60%">
                <p><?= strtoupper($courier->display_name); ?></p>
                <p><?php
                    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($shipment->awb_number, $generator::TYPE_CODE_128)) . '" style= height="45px" width="180px">';
                    ?>
                </p>
                <p style="text-align:center;"><?= $shipment->awb_number; ?></p>
                <p>
                    Dimensions (cm): <?= ($order_info->package_length > 0) ? $order_info->package_length : '0'; ?> X <?= ($order_info->package_breadth > 0) ? $order_info->package_breadth : '0'; ?> X <?= ($order_info->package_height > 0) ? $order_info->package_height : '0'; ?>
                </p>
            </td>
        </tr>

    </tbody>
</table>

<?php if ($warehouse->hide_label_products != '1') { ?>

    <table style="width: 100%;padding: 5px;border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align:center;border: 1px solid black;">SKU</th>
                <th style="text-align:center;border: 1px solid black;">Item Name</th>
                <th style="text-align:center;border: 1px solid black;">Qty.</th>
                <th style="text-align:center;border: 1px solid black;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($products)) {
                foreach ($products as $product) {
            ?>
                    <tr>
                        <td style="text-align:center;border: 1px solid black;">
                            <?= !empty($product->product_sku) ? strtoupper(wordwrap($product->product_sku, 13, "<br>\n", true))  : 'N/A'; ?>
                        </td>
                        <td style="text-align:center;border: 1px solid black;">
                            <?= ucwords($product->product_name); ?>
                        </td>
                        <td style="text-align:center;border: 1px solid black;">
                            <?= $product->product_qty; ?>
                        </td>
                        <td style="text-align:center;border: 1px solid black;">
                        <?php 
                      $prod_price=round($product->product_price * $product->product_qty, 2);
                      if($prod_price>0){ ?>
                         &#8377;<?php echo $prod_price; ?>
                    <?php } ?>
                        </td>
                    </tr>
            <?php
                }
            }
            ?>

            <?php if (!empty($order_info->shipping_charges) && $order_info->shipping_charges > 0) { ?>
                <tr>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        Shipping Charges
                    </td>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        &#8377;<?= round($order_info->shipping_charges, 2); ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if (!empty($order_info->cod_charges) && $order_info->cod_charges > 0) { ?>
                <tr>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        COD Charges
                    </td>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        &#8377;<?= round($order_info->cod_charges, 2); ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if (!empty($order_info->tax_amount) && $order_info->tax_amount > 0) { ?>
                <tr>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        Tax Amount
                    </td>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        &#8377;<?= round($order_info->tax_amount, 2); ?>
                    </td>
                </tr>
            <?php } ?>

            <?php if (!empty($order_info->discount) && $order_info->discount > 0) { ?>
                <tr>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        Discount Applied
                    </td>
                    <td style="text-align:center;border: 1px solid black;">

                    </td>
                    <td style="text-align:center;border: 1px solid black;">
                        &#8377;<?= round($order_info->discount, 2); ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td style="text-align:center;border: 1px solid black;">

                </td>
                <td style="text-align:center;border: 1px solid black;">
                    Order Total
                </td>
                <td style="text-align:center;border: 1px solid black;">

                </td>
                <td style="text-align:center;border: 1px solid black;">
                    &#8377;<?= round($order_info->order_amount, 2); ?>
                </td>
            </tr>

        </tbody>
    </table>
<?php } ?>
<footer>
    <?php if ($warehouse->hide_label_address != '1') { ?>
        <p style="font-size:12px;">
            <?php if ($shipment->is_rto_different) { ?>
                <b style="font-size:12px;">Pickup Address:</b><br />
            <?php } else { ?>
                <b style="font-size:12px;">Pickup and Return Address:</b><br />
            <?php } ?>
            <b style="font-size:12px;"><?= ucwords($warehouse->name);?></b><br>
            <?= ucwords($warehouse->contact_name) . ' ' . $warehouse->address_1 . ' ' . $warehouse->address_2 . ' ' . $warehouse->city . ', ' . $warehouse->state . ', ' . $warehouse->country . ' - ' . $warehouse->zip; ?><br>
            <?php if ($warehouse->hide_label_pickup_mobile != '1') { ?>
                Mobile No.: <?= $warehouse->phone; ?>
            <?php } ?>
            <?php if (!empty($warehouse->gst_number)) { ?> &nbsp; GST No: <?= strtoupper($warehouse->gst_number) ?> <?php } ?>
        </p>
        <?php if ($shipment->is_rto_different) { ?>
            <p>
                <b style="font-size:12px;">Return Address:</b><br />
                <b style="font-size:12px;"><?= ucwords($rto_warehouse->name);?></b><br>
                <?= ucwords($rto_warehouse->name) . ' ' . $rto_warehouse->address_1 . ' ' . $rto_warehouse->address_2 . ' ' . $rto_warehouse->city . ', ' . $rto_warehouse->state . ', ' . $rto_warehouse->country . ' - ' . $rto_warehouse->zip; ?><br>
                <?php if ($rto_warehouse->hide_label_pickup_mobile != '1') { ?>
                    Mobile No.: <?= $rto_warehouse->phone; ?>
                <?php } ?>
            </p>
        <?php } ?>
    <?php } ?>
    <?php if (!empty($warehouse->support_phone) || !empty($warehouse->support_email)) {  ?>
        <p>
            <b style="font-size:12px;">For any query please contact:</b><br />
            <?= !empty($warehouse->support_phone) ? '<b>Mobile:</b> ' . $warehouse->support_phone : '';  ?> <?= !empty($warehouse->support_email) ? ' <b>Email:</b> ' . $warehouse->support_email : '';  ?>
        </p>
    <?php } ?>

    <div style="width:4.15in;height:0.02in;border-bottom: 1px solid;"></div>
    <p style="font-size:10px;">
        This is computer generated document,hence does not required signature.<br>
        <b>Note:</b> All disputes are subject to Delhi jurisdiction.Goods once sold will only be taken back or exchanged as per the store's exchange/return policy
    </p>
</footer>