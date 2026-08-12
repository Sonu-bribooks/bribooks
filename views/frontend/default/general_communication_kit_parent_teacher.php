<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,user-scalable=0">
  <style>
    body, a {color: #000000 !important;}
    .body { margin: auto; font-size: 18px;margin: 5px; padding: 15px; }
    strong {font-weight: 700;font-size: 18px;}
    small {font-weight: 600;font-size: 16px; line-height: 0.7}
    .row {display: flex;flex-direction: row;}
    .border-bottom {border-bottom: 2px solid #EBEFF2;}
    .col {flex: 1;}
    .text-right {text-align: right;}
    .text-center {text-align: center;}
    .text-left {text-align: left;}
    .margin-top-10 {margin-top: 10px;}
    .justify {
        text-align: justify;
    }
    table {
      border-collapse: collapse;
      width: 100%;
    }
    td, th {
      height: 80px;
      text-align: left;
    }
    a { text-decoration:none; color: #0000FF !important; }
  </style>
</head>
<body>
  <div class="body">
    <div class="text-center">
        <h2 >Book Writing Festival</h2>
        <span style="font-size:20px">Year <?= $grade ?></span><br>
        <span style="font-size:20px"><?= $school_name ?></span><br>
        <span style="font-size:20px"><?= $city ?>,<?= $state ?></span>
    </div>
    <div class="row">
      <div class="">
        <p>Dear Parents,</p>

        <p>I am delighted to announce that I have partnered with BriBooks, the world’s largest book writing and publishing platform, to organise a free online book writing competition. 
        The students will use the AI-enabled platform and over 20,000 illustrations to write and publish their own books, becoming young published authors.</p>
          
        <p>All students who successfully publish their book will receive a digital participation certificate. 
        The best young authors in the Best-Seller and Jury Choice categories will win national and global awards, with a chance to have their books featured at global book festivals in London, New York, Delhi, and Dubai.</p>
        
        <table>
          <tr>
            <td>
            <p><strong>Registration Process:</strong></p>
            <p>To enrol your child, please click the link below or scan the QR code to complete the enrollment process. It is completely FREE <br>
              <a href="<?= $student_url ?>"><?= $student_url ?></a>.
            </p>
            </td>
              <td>
                <div class="text-center">
                  <img src="<?= $qrcode_url ?>" alt="Image" style="width:100px; border: 1px solid black;" />
                </div>
              </td>
          </tr>
        </table>

        <p>The deadline for enrollment is <?= $student_reg_end_date ?>.</p>

        <p>Should you have any questions, feel free to contact the BriBooks team directly at <a href='support@bribooks.com'>support@bribooks.com</a> or call them at 18003099917.</p>

        <p>Warm regards,<br>
        <?= $name ?></p>
      </div>
    </div>
    <br><br><br>
  </div>
</body>