<p>Dear <?= $author_name ?></p>
<p>Following is the list of your books published as part of the Summer Book Writing Festival 2024, 
    India as on Midnight ( IST) of 12th May 2024, the cut off for the Barnes Children Lit Festival.</p>
<table style="border:1px solid black; margin-top:10px;">
  <tr>
      <th style="border:2px solid black;">Sn#</th>
    <th style="border:2px solid black;">Name of Book</th>
    <th style="border:2px solid black;">Date of publishing</th>
  </tr>
  <?php if (!empty($books)) { foreach ($books as $key => $value): ?>
  <tr>
    <td style="border: 1px solid black;"><?php echo $key + 1?></td>
    <td style="border: 1px solid black;"><?php echo $value['book_name']?></td>
    <td style="border: 1px solid black;"><?php echo $value['date_published']?></td>
  </tr>
  <?php endforeach; } else {?>
    <td style="border: 1px solid black;"><?php echo '1' ?></td>
    <td style="border: 1px solid black;"><?php echo "NA"?></td>
    <td style="border: 1px solid black;"><?php echo "NA" ?></td>
  <?php } ?>
</table>
<p>You can continue to complete any unpublished book and publish for the Prime Access Program as its deadline is 30th May 2024.<br>
The Prime Access Program will give an advantage of 15 days over the general publishing deadline, both for the Jury and Best-Seller Leagues.</p>
<p>Warm Regards,<br>
Summer Book Writing Fest Team