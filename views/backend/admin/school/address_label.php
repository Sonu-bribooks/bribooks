<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Address</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0;  background-color: #ffffff; }
        .container { width: 100%; max-width: 700px; background-color: #ffffff; padding: 40px; border-radius: 8px; margin: auto; box-sizing: border-box;  }
        .address { font-size: 40px; line-height: 50px; margin-left: 15px;}
        .address-line { margin: 0; padding: 0; }
        .school-name { max-width: 60%;}
    </style>
</head>
<body>
    <div class="container">
        <div class="address">
            <p class="to address-line">To</p>
            <p class="name address-line"><?php echo htmlspecialchars($owner_name); ?>,</p> 
            <p class="school-name address-line"><?php echo htmlspecialchars($name); ?>,</p> 
            <p class="school-name address-line">Mob. +<?php echo htmlspecialchars($mobile); ?>,</p> 
            <p class="address-line"><?php echo htmlspecialchars($address); ?>,</p> 
            <p class="address-line"><?php echo htmlspecialchars($zipcode); ?>,</p> 
            <p class="address-line"><?php echo htmlspecialchars($city); ?>,</p> 
            <p class="address-line"><?php echo htmlspecialchars($state); ?></p> 
        </div>
    </div>
</body>
</html>
