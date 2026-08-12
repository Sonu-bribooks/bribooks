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
        <h2 ><?= $school_name ?></h2>
        <span style="font-size:20px"><?= $state ?></span>
    </div>
    <div class="row">
      <div class="">
          <p>Dear Parents,</p>

          <p>We are delighted to announce that our school has been chosen to take part in the 2024 edition of the National Young Authors’ Fair, Australia. 
            This exciting opportunity will enhance our students' creative writing skills and showcase their work on prestigious platforms such as Amazon.com and the Brooklyn Book Festival, New York.</p>
          
          <p>Participating students will have the chance to receive awards and certificates for their published books, both in the Jury and Best-Seller categories.</p>

          <p>To facilitate the writing process, the organisers, BriBooks, will provide students with access to their AI-assisted platform, making writing both enjoyable and straightforward.</p>
        
        <table>
          <tr>
            <td>
              <p>Please click on the link below or scan the QR code to register. This event is entirely FREE for our students.<br>
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

        <p>Should you have any questions, please don't hesitate to contact the BriBooks team at <a href='support@bribooks.com'>support@bribooks.com</a>.</p>

        <p>Best regards,<br>
        <?= $authorized_person ?><br>
        <?= $school_name ?></p>
      </div>
    </div>
    <br><br><br>
  </div>
</body>