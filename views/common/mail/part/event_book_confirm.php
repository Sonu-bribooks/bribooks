<p>Dear <?= $author_name ?></p>
This is to remind you that the publishing deadline for Summer Book Writing Festival 2024 has now come to an end. 
As informed earlier, 30th June 2024 was the final publishing date for Summer Book Writing Festival 2024. 
The list of books that you have published for the Summer Book Writing Festival 2024 is attached below:

<table style="border:1px solid black; margin-top:10px; border-collapse:collapse;">
  <tr>
    <th style="border:2px solid black; padding-left: 8px; padding-right: 8px">Book Ttitle</th>
    <th style="border:2px solid black; padding-left: 8px; padding-right: 8px">Author Name</th>
  </tr>
  <?php foreach ($books as $book): ?>
  <tr>
    <td style="border: 1px solid black; padding-left: 8px; padding-right: 8px"><?php echo $book['name']?></td>
    <td style="border: 1px solid black; padding-left: 8px; padding-right: 8px"><?php echo $book['author_name']?></td>
  </tr>
  <?php endforeach; ?>
</table>

<p>P.s: Any books published after the publishing deadline will not be qualified for the 2024 edition of Summer Book Writing Festival.</p>


<p>Keep Shining!</p>

<p>Best Regards <br>
Team BriBooks</p>

