<table style="border:1px solid black; margin-top:100px;">
    <tbody>
        <tr>
            <th style="border:2px solid black ; padding: 5px;">Name</th>
            <td style="border:2px solid black ;padding: 5px;"><?= $name ?></td>
        </tr>
        <tr>
            <th rowspan="1" style="border:2px solid black ; padding: 5px;">Number</th>
            <td style="border:2px solid black ; padding: 5px;"><?= $mobile ?></td>
        </tr>
        <tr>
            <th style="border:2px solid black ; padding: 5px;" rowspan="1">Email</th>
            <td style="border:2px solid black ; padding: 5px;"><?= $email ?></td>
        </tr>
        <tr>
            <th style="border:2px solid black ; padding: 5px;" rowspan="1">User Type</th>
            <td style="border:2px solid black ;padding: 5px;"><?= $usertype ?></td>
        </tr>
        <?php
        if ($usertype === "Published Author") {
        ?>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Book Title</th>
                <td style="border:2px solid black ;"><?= $book_title ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Published Author Query</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $published_book ?></td>
            </tr>
        <?php
        }
        if ($usertype === "Aspiring Author") {
        ?>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Aspiring Author</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $aspiring ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Message</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $message ?></td>
            </tr>
        <?php
        }
        if ($usertype === "Young Author Fair Participant") {
        ?>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Country</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $country_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">State</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $state_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">City</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $city_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">School</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $school_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Message</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $message ?></td>
            </tr>
        <?php
        }
        if ($usertype === "Young Author Fair School") {
        ?>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Country</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $country_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">State</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $state_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">City</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $city_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">School</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $school_id ?></td>
            </tr>
        <tr>
            <th style="border:2px solid black ;padding: 5px;" rowspan="1">Message</th>
            <td style="border:2px solid black ;padding: 5px;"><?= $message ?></td>
        </tr>
        <?php
        }
        if ($usertype === "School") {
        ?>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Country</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $country_id ?></td>
            </tr>
            <tr>
            <th style="border:2px solid black ;padding: 5px;" rowspan="1">Message</th>
            <td style="border:2px solid black ;padding: 5px;"><?= $message ?></td>
        </tr>
        <?php
        }
        if ($usertype === "Library Partner") {
        ?>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Country</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $country_id ?></td>
            </tr>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Message</th>
                <td style="border:2px solid black ;padding: 5px;"><?= $message ?></td>
            </tr>
        <?php
        }
        if ($usertype === "Employment Opportunities") {
        ?>
            <tr>
                <th style="border:2px solid black ;padding: 5px;" rowspan="1">Employment Opportunities</th>
                <td style="border:2px solid black ;padding: 5px;"> <?= $opportunities ?></td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>