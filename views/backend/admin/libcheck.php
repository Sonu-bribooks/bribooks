<form id="form">
    <select name="country" class="form-control" required id="">
        <option value=""> Select country</option>
        <?php
        foreach ($country as $key => $value) {
            echo '<option value="' . $value->country_name . '"> ' . $value->country_name . ' </option>';
        }
        ?>
    </select>
    <br>
    <input type="text" name="weight" id="weight" placeholder="Enter Weight">
    <br>
    <br>
    <input type="submit" class="btn btn-primary" value="submit">
</form>

<div id="result"></div>
<script>
    $(document).ready(function() {
        $('#form').submit((e) => {
            e.preventDefault()
            $.ajax({
                type: "POST",
                url: "<?php echo base_url('admin/ajax_lib_check'); ?>",
                data : $('form').serialize(),
                success: function(response) {
                    //if request if made successfully then the response represent the data
                    let html = document.getElementById("result").innerHTML = response;
                }
            });
        })
    })
</script>
