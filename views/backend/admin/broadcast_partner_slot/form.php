<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title">
                    <i class="mdi mdi-apple-keyboard-command title_icon"></i> 
                    <?php echo $page_title; ?>
                </h4>
            </div>
        </div> 
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="col-lg-12">
                    <h4 class="mb-3 header-title"><?php echo $page_title; ?></h4>

                    <form class="required-form" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="event_id"><?php echo _l('event'); ?><span class="required">*</span> </label>
                            <select class="form-control select2" data-toggle="select2" name="event_id" id="event_id" required>
                                <option value=""><?php echo _l('select_a_event'); ?></option>
                                <?php if (!empty($events)) {
                                    foreach ($events as $event) { ?>
                                        <option value="<?= $event['id']; ?>" <?php if (!empty($details) && ($details['event_id'] == $event['id']))  echo "selected"; ?> ><?= $event['name']; ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="book_id"><?php echo _l('select_book'); ?> <span class="required">*</span> </label>
                            <select class="form-control select2" data-toggle="select2" name="book_id" id="book_id" required>
                                <option disabled><?php _el('select_book'); ?></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="user_rank"><?php echo _l('user_rank'); ?> <span class="required">*</span> </label>
						    <input type="text" class="form-control user_rank" id="name" name="name" value="<?php echo $book_details['score'] ?? ''; ?>" required>
							<input type="hidden" name="rank" value="<?php echo $book_details['score'] ?? ''; ?>" class="rank">
                        </div>
						<div class="form-group">
                            <label for="user_name"><?php echo _l('user_name'); ?> <span class="required">*</span> </label><br>
						    <input type="text" class="form-control user_name" id="name" name="name" value="<?php echo $book_details['author_name'] ?? ''; ?>"  required>
							<input type="hidden" name="user_id" value="<?php echo $book_details['user_id'] ?? ''; ?>" class="user_id">
                        </div>
                        <div class="form-group">
                            <label for="partner_id"><?php echo _l('select_broadcast_partner'); ?> <span class="required">*</span> </label>
                            <select class="form-control select2" data-toggle="select2" name="partner_id" id="partner_id">
                                <option value=""><?php _el('select_broadcast_partner'); ?></option>
                                <?php if (!empty($partners)) {
                                    foreach($partners as $partner){ ?>
                                        <option value="<?= $partner['id']; ?>" <?php if (!empty($details) && ($details['partner_id'] == $partner['id'])) echo "selected"; ?>><?= $partner['name']; ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="start_date"><?php echo _l('start_date'); ?> <span class="required">*</span></label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?php echo $details['start_date'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date"><?php echo _l('end_date'); ?> <span class="required">*</span></label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?php echo $details['end_date'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary" onclick="checkRequiredFields()"><?php echo _l("submit"); ?></button>
                    </form>
                </div>
            </div> 
        </div> 
    </div>
</div>
<script>
$(document).ready(function() {
    $('#event_id').on('change', function() {
        var event_id = $(this).val();

        resetFields();

        if (event_id) {
            fetchEventDetails(event_id);
        }
    });

    $('#book_id').on('change', function() {
        var book_id = $(this).val();
        var event_id = $('#event_id').val();

        if (book_id && event_id) {
            $.ajax({
                url: '<?php echo site_url('admin/ajax_user_rank'); ?>',
                type: 'POST',
                data: { book_id: book_id, event_id: event_id },
                dataType: 'json',
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        var result = response.data[0];
                        $('.rank').val(response.rank);
                        $('.user_rank').val(response.rank);
                        $('.user_name').val(result.author_name);
                        $('.user_id').val(result.user_id);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error: ' + error);
                }
            });
        }
    });
   
    function resetFields() {
        $('#book_id').empty().append('<option value=""><?php _el("select_book"); ?></option>');
        $('.rank').val('');
        $('.user_rank').val('');
        $('.user_name').val('');
        $('.user_id').val('');
    }

    function fetchEventDetails(event_id, search = '') {
        $.ajax({
            url: '<?php echo site_url('admin/ajax_get_book_by_event'); ?>',
            type: 'POST',
            data: { event_id: event_id, search: search },
            dataType: 'json',
            success: function(response) {
                $('#book_id').empty();
                $('#book_id').append('<option disabled selected><?php _el("select book"); ?></option>');

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(index, item) {
                        $('#book_id').append(new Option(item.book_name, item.book_id));
                    });
                } else {
                    $('#book_id').append('<option value=""><?php _el("no_books_available"); ?></option>');
                }
            },
            error: function(xhr, status, error) {
                console.log('Error: ' + error);
            }
        });
    }
});
</script>