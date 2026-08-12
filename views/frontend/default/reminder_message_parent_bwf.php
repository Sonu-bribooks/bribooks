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
        <br />
        <h2>Book Writing Festival</h2>
        <h4>Presented by {school_name} & Powered by BriBooks</h4>
    </div>
    <div class="row">
      <div class="">
          <p>Dear Parents,</p>
          <p>This serves as a gentle reminder regarding the student registration for the Book Writing Festival for <strong>{school_name}</strong>’s students, in collaboration with BriBooks, the world's largest book writing platform for school students.</p>
          <p>We kindly request you to register your child by utilizing the following link: <br /><a href="{student_url}">{student_url}</a></p>
          <div class="text-center">
              <img src="{qrcode_url}" alt="Image" style="width:150px; border: 1px solid black;" />
          </div>
          <p>We encourage you to register and motivate your child to actively participate in this event. Notably, participation in this event is entirely <strong>FREE</strong> for all students.</p>
          <p>Should you have any questions or require assistance, please do not hesitate to contact the BriBooks team at <a href="mailto:support@bribooks.com">support@bribooks.com</a></p>
          <p>We are enthusiastic about witnessing our students shine and evolve into Young Entrepreneur Authors!</p>
          <p>Best Regards,</p>
          <p>{owner_name}<br />{school_name}</p>
      </div>
    </div>
    <br><br><br>
  </div>
</body>