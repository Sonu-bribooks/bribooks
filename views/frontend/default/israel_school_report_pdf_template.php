<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset='utf-8'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily School Report</title>
    <style>
        body { font-family: firefly, DejaVu Sans, sans-serif; }
    </style>
</head>
<body style="font-size: 20px;">
    <table style="width: 1000px; border: 1px solid #000; margin: auto; padding: auto;" cellspacing="0" cellpadding="0">
        <tr>
            <th style="border-bottom: 1px solid #000; padding: 8px; font-size: 24px;">Israel’s National Young Authors Fair</th>
        </tr>
        <tr>
            <th style="border-bottom: 1px solid #000; padding: 8px;">Daily Report - <span style="color: #FF0000;">{variable1}</span></th>
        </tr>
        <tr>
            <th style="border-bottom: 1px solid #000; padding: 8px;"><span style="color: #FF0000;">{variable2}</span> days left before the publishing deadline</th>
        </tr>
        <!-- <tr>
            <th style="border-bottom: 1px solid #000; padding: 8px;">School Name: <span style="color: #FF0000;">{variable3}</span></th>
        </tr> -->
        <tr>
            <th style="padding: 12px">School Ranking: Ranked <span style="color: #FF0000;">#{variable4}</span> of <span style="color: #FF0000;">{variable5}</span> Schools</th>
        </tr>
    </table>
    <table style="width: 1000px; text-align: center; border: 1px solid #000; margin: auto; padding: auto;" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th style="border-bottom: 1px solid; border-right: 1px solid #000; padding: 8px;">Class</th>
                <th style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">Registered Students</th>
                <th style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">Books Written</th>
                <th style="border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px;">Books Published</th>
                <th style="border-bottom: 1px solid #000; padding: 8px;">Books Sold</th>
            </tr>
        </thead>
        <tbody style="color: #FF0000;">
            {variable6}
        </tbody>
    </table>
</body>
</html>