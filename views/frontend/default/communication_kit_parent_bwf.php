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
        {school_logo_or_name}
    </div>
    <div class="row">
      <div class="">
          <p>Dear Parents,</p>
          <p>We are delighted to share some exciting news with you!<br />Our school <strong>{school_name}</strong>, has been chosen to participate in the Book Writing Fest, presented by BriBooks.</p>
          <p>This presents an incredible opportunity for our students to write, publish, and showcase their books on a global scale, thereby becoming young published authors. Engaging in this event not only refines their writing skills but also instills a lasting passion for reading and writing.<br />Each published book will serve as a cherished memento of their accomplishments.</p>
          <p>Our students have the opportunity to earn various awards and recognition, including:</p>
          <ul>
              <li>Certificates for all published authors, recognizing their achievements in reaching multiple milestones.</li>
              <li>ISBN, completely free of cost, to solidify their fame as a published author.</li>
              <li>Books listed on Amazon.com to reach global readership.</li>
          </ul>
          <p><strong>Participation in this event is entirely FREE for all students.</strong><br />To register, please visit <a href="{student_url}">{student_url}</a>.</p>
          <div class="text-center">
              <img src="{qrcode_url}" alt="Image" style="width:150px; border: 1px solid black;" />
          </div>
          <p>For any further assistance, please feel free to reach out at <a href="mailto:support@bribooks.com">support@bribooks.com</a></p>
          <p>Best regards,</p>
          <p>{school_name}</p>
      </div>
    </div>
    <br><br><br>
  </div>
</body>