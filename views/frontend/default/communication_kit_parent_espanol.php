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
        <h2>{school_name}</h2>
    </div>
    <div class="row">
      <div class="">
          <p>Estimados padres,</p>
          <p>¡Estamos encantados de compartir contigo noticias interesantes!<br />Nuestra escuela <strong>{school_name}</strong> ha sido seleccionada para participar en la Feria Nacional de Autores Jóvenes.</p>
          <p>Esta es una oportunidad increíble para que nuestros estudiantes escriban, publiquen y muestren sus libros a nivel mundial, transformándolos en autores jóvenes publicados.</p>
          <p>Participar en este concurso no sólo mejora sus habilidades de escritura sino que también fomenta un amor por la lectura y la escritura que durará toda la vida. Cada libro publicado servirá como un preciado recuerdo de sus logros.</p>
          <p>Nuestros estudiantes tienen la oportunidad de ganar varios premios y reconocimientos:</p>
          <ul>
              <li>Certificados para todos los autores publicados por lograr múltiples hitos</li>
              <li>Premio al Autor más vendido en el Estado</li>
              <li>Top 10 mejores autores: Premios del jurado</li>
              <li>Premios a los 100 autores jóvenes más vendidos</li>
          </ul>
          <p><strong>La participación en este evento es totalmente GRATUITA para todos los estudiantes.</strong><br />Regístrese en <a href="{student_url}">{student_url}</a> antes <strong>del 29 de octubre de 2023.</strong></p>
          <div class="text-center">
              <img src="{qrcode_url}" alt="Image" style="width:150px; border: 1px solid black;" />
          </div>
          <p>Para obtener más ayuda, escriba a <a href="mailto:support@bribooks.com">support@bribooks.com</a></p>
          <p>Atentamente</p>
          <p>{school_name}</p>
      </div>
    </div>
    <br><br><br>
  </div>
</body>