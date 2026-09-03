<table
    cellpadding="0"
    cellspacing="0"
    border="0"
    width="100%"
    style="
        width: 100%;
        max-width: 720px;
        margin: 0 auto;
        border-collapse: collapse;
        table-layout: fixed;
    "
>
    <tr>

        <!-- LEFT CARD -->

        <td
            width="46%"
            valign="top"
            style="
                width: 46%;
                padding: 15px;
                background-color: #F4F7FF;
                border-radius: 15px;
                text-align: left;
                vertical-align: top;
                overflow-wrap: break-word;
                word-break: break-word;
            "
        >

            <?php $total_copies = 0; ?>

            <table
                cellpadding="0"
                cellspacing="0"
                border="0"
                width="100%"
                style="
                    width: 100%;
                    border-collapse: collapse;
                    table-layout: fixed;
                "
            >
                <tr>

                    <?php foreach ($products as $index => $item) { ?>

                    <td
                        valign="top"
                        style="
                            text-align: center;
                            vertical-align: top;
                            overflow-wrap: break-word;
                            word-break: break-word;
                        "
                    >

                        <img
                            src="<?php echo $this->config->item('s3_base_url') . 'public/' . $item['cover_image']; ?>"
                            width="100"
                            height="140"
                            style="
                                display: block;
                                width: 100px;
                                height: 140px;
                                max-width: 100%;
                                margin: 0 auto;
                                object-fit: cover;
                            "
                        />

                        <p style="
                            margin: 8px 0 2px;
                            text-align: center;
                            font-size: 12px;
                            line-height: 16px;
                            color: #000000;
                            overflow-wrap: break-word;
                            word-break: break-word;
                        ">
                            <?=$item['name']?><br>
                            <?=_li('Version')?> <?=$item['version']?>
                        </p>

                        <p style="
                            margin: 0;
                            text-align: center;
                            font-size: 12px;
                            line-height: 16px;
                            color: #000000;
                        ">
                            <?=$item['quantity']?> <?=_li('copies')?>
                        </p>

                        <?php $total_copies += $item['quantity']; ?>

                        <?php $option = json_decode($item['option'], true); ?>

                        <p style="
                            color: #f99232;
                            margin: 5px 0 0;
                            text-align: center;
                            font-size: 15px;
                            line-height: 19px;
                            overflow-wrap: break-word;
                            word-break: break-word;
                        ">
                            <?=$option['name']?>
                        </p>

                    </td>

                    <?php if (count($products) > 1 && $index < count($products) - 1) { ?>

                    <td
                        width="20"
                        style="
                            width: 20px;
                            text-align: center;
                            vertical-align: middle;
                        "
                    >
                        <span style="
                            font-size: 26px;
                            color: #000000;
                        ">
                            +
                        </span>
                    </td>

                    <?php } ?>

                    <?php } ?>

                </tr>
            </table>


            <!-- PRICE -->

            <div style="
                margin-top: 18px;
                text-align: center;
                font-size: 28px;
                line-height: 34px;
                font-weight: bold;
                color: #148108;
                overflow-wrap: break-word;
            ">

                <?=$order['currency_code']?> <?=$order['total']?>

                <?php if ($order['credit_discount'] > 0) { ?>

                <p style="
                    margin: 5px 0 0;
                    font-size: 12px;
                    line-height: 17px;
                    color: #555555;
                    font-weight: normal;
                ">
                    <?=_li('Free_book_bundle_applied')?>
                </p>

                <?php } ?>

            </div>


            <!-- TRACK DELIVERY -->

            <?php if ($has_printed_copies) { ?>

            <div style="
                text-align: center;
                margin-top: 5px;
            ">
                <a
                    href="<?=USER_URL?>trackdelivery/<?=$order['order_code']?>"
                    style="
                        color: #148108;
                        font-size: 15px;
                        line-height: 20px;
                        text-decoration: underline;
                    "
                >
                    <?=_li('Track_Delivery')?>
                </a>
            </div>

            <?php } ?>

        </td>


        <!-- GAP -->

        <td
            width="8%"
            style="
                width: 8%;
                font-size: 1px;
                line-height: 1px;
            "
        >
            &nbsp;
        </td>


        <!-- RIGHT CARD -->

        <td
            width="46%"
            valign="top"
            style="
                width: 46%;
                padding: 15px;
                background-color: #F4F7FF;
                border-radius: 15px;
                color: #10284B;
                text-align: left;
                vertical-align: top;
                overflow-wrap: break-word;
                word-break: break-word;
            "
        >

            <p style="
                margin: 0 0 16px;
                font-size: 17px;
                line-height: 23px;
                overflow-wrap: break-word;
                word-break: break-word;
            ">
                <strong><?=_li('Total_Books')?>:</strong>
                <?=count($products)?>
            </p>

            <p style="
                margin: 0 0 16px;
                font-size: 17px;
                line-height: 23px;
                overflow-wrap: break-word;
                word-break: break-word;
            ">
                <strong><?=_li('Total_Copies')?>:</strong>
                <?=$total_copies?>
            </p>

            <p style="
                margin: 0 0 16px;
                font-size: 17px;
                line-height: 23px;
                overflow-wrap: break-word;
                word-break: break-word;
            ">
                <strong><?=_li('Total_Price')?>:</strong>
                <?=$order['currency_code']?> <?=$order['total']?>
            </p>

            <p style="
                margin: 0 0 16px;
                font-size: 17px;
                line-height: 23px;
                overflow-wrap: break-word;
                word-break: break-word;
            ">
                <strong><?=_li('Shipping')?>:</strong>
                <?=$order['currency_code']?> <?=$order['shipping_cost']?>
            </p>

            <p style="
                margin: 0 0 16px;
                font-size: 17px;
                line-height: 23px;
                overflow-wrap: break-word;
                word-break: break-word;
            ">
                <strong><?=_li('Taxes')?>:</strong>
                <?=$order['currency_code']?> <?=$order['tax']?>
            </p>

            <?php if ($has_printed_copies) { ?>

            <hr style="
                border: 0;
                border-top: 1px solid #999999;
                margin: 18px 0;
            ">

            <p style="
                margin: 0;
                font-size: 17px;
                line-height: 23px;
                overflow-wrap: break-word;
                word-break: break-word;
            ">
                <strong><?=_li('Address')?>:</strong><br>

                <?=$address['address']?>, <?=$address['landmark']?><br>

                <?=$address['city']?>, <?=$address['state']?><br>

                <?=$address['country']?>-<?=$address['zipcode']?>
            </p>

            <?php } ?>

        </td>

    </tr>
</table>

<br>
