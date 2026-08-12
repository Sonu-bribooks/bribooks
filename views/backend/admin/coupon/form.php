<?php
$detail = !empty($program_admin_id) ? $this->db->get_where('users', array('id' => $program_admin_id))->row_array() : '';
$social_links = !empty($details['social_links']) ? json_decode($details['social_links'] ?? '', true) : '';
?>

<style>
    .code {margin-left: 7.5rem !important; cursor: pointer; }
</style>

<div class="row">
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

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="header-title mb-3"><?php echo _l('add_new_Coupon '); ?></h4>
                <form action="<?php echo $action; ?>" method="post">
                    <div class="tab-pane" id="basic_info">
                        <div class="col-12">
                            <h4><?php echo $this->session->error; ?></h4>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="coupon_name"><?php echo _l('coupon_Name'); ?> <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="name" name="name" value="<?=$coupon['name'] ?? ''; ?>" placeholder="Enter Coupon Name" required>
                                </div>
                            </div>

                            <div class="form-group row mb-3" id="select-book">
                                <label class="col-md-3 col-form-label" for="book_id"><?php echo _l('select_Book'); ?></label>
                                <div class="col-md-9">
                                    <select name="book_id" class="form-control select2" data-toggle="select2" id="book_id"></select>
                                </div>
                            </div>

                            <div class="form-group row mb-3" id="users">
                                <label class="col-md-3 col-form-label" for="select-users"><?php echo _l('select_users'); ?></label>
                                <div class="col-md-9">
                                   <select name="user_id" class="form-control select2" data-toggle="select2" id="select-users"></select>
                                </div>
                            </div>
                
                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="discountType"><?php echo _l('discount_Type'); ?> <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="radio" value="1" class="form-check-input radio-inline mr-3" name="discount_type" id="discountTypeFlat" <?=(isset($coupon['discount_type']) && $coupon['discount_type'] == '1') ? 'checked' : '' ?> required>
                                    <label for="discountTypeFlat"><?=_l('flat')?> </label>
                                    <input type="radio" name="discount_type" class="form-check-input radio-inline" id="discountTypePercentage" value="2" <?=(isset($coupon['discount_type']) && $coupon['discount_type'] == '2') ? 'checked' : '' ?> required  style="margin-left: 10px;">
                                    <label for="discountTypePercentage" style="margin-left: 26px;"> <?=_l('percentage')?> </label>
                                </div>
                            </div>
                           
                            <div class="input-group input-group-sm mb-3">
                                <label for="code" class="mr-5"><?php echo _l('coupon_Code'); ?> <span class="required">*</span></label>
                                <div class="input-group-prepend code">
                                    <span onclick="generate()" class="input-group-text" id="inputGroup-sizing-sm">🧬</span>
                                    <input type="text" class="form-control" id="code" name="code" aria-label="Small" aria-describedby="inputGroup-sizing-sm" value="<?=$coupon['code'] ?? '' ?>" placeholder="Generate Coupon" >
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="discount"><?php echo _l('Discount_Price'); ?> <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" id="discount" name="discount" value="<?=$coupon['discount'] ?? '' ?>" placeholder="Enter Discount" required>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="Limit"><?php echo _l('Limit'); ?> <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="number" class="form-control" id="Limit" name="used_limit" value="<?=$coupon['used_limit'] ?? '' ?>" placeholder="Enter Limit" required>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="date_start"><?php echo _l('date_Start'); ?> <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="date" id="date_start" class="form-control" name="date_start" value="<?= isset($coupon['date_start']) ? date('Y-m-d', strtotime($coupon['date_start'])) : ''; ?>" required>
                                </div>
                            </div>

                            <div class="form-group row mb-3">
                                <label class="col-md-3 col-form-label" for="date_end"><?php echo _l('date_End'); ?> <span class="required">*</span></label>
                                <div class="col-md-9">
                                    <input type="date" id="date_end" name="date_end" required class="form-control" value="<?= isset($coupon['date_end']) ? date('Y-m-d', strtotime($coupon['date_end'])) : ''; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" id="add" class="btn btn-sm btn-outline-primary btn-rounded alignToTitle bulk-delete-button"  onclick="checkRequiredFields()"><?=$button_name?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function generate() {
        document.getElementById("code").value = Math.random().toString(36).slice(2).toUpperCase();
    }
   
    $(function() {

        $('#book_id').select2({
            ajax: {
                delay: 250,
                url: '<?php echo site_url('admin/get_books'); ?>',
                data: function (params) {
                    var query = {
                        search: params.term,
                    }

                    return query;
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    };
                }
            },
            minimumInputLength: 3,
            placeholder: 'Select Book', 
            allowClear: true
        });

        <?php if (!empty($book_info)) { ?>
            var selectedBook = {
                id: '<?= $book_info['id'] ?>',
                text: '<?= $book_info['name'] ?>'
            };
            var newOption = new Option(selectedBook.text, selectedBook.id, true, true);
            $('#book_id').append(newOption).trigger('change');
        <?php } ?>

        $('#select-users').select2({
            ajax: {
                delay: 250,
                url: '<?php echo site_url('admin/get_users'); ?>', 
                data: function (params) {
                    var query = {
                        search: params.term 
                    };
                    return query;
                },
                processResults: function(data) {
                    return {
                        results: $.map(data.items, function(item) {
                            return {
                                id: item.id,
                                text: item.text
                            };
                        })
                    };
                }
            },
            minimumInputLength: 3, 
            placeholder: 'Select users', 
            allowClear: true,
        });

        <?php if (!empty($user_info)) { ?>
            var selectedUser = {
                id: '<?= $user_info['id'] ?>',
                text: '<?= $user_info['first_name'] . '' . $user_info['last_name'] ?>'
            };
            var newOption = new Option(selectedUser.text, selectedUser.id, true, true);
            $('#select-users').append(newOption).trigger('change');
        <?php } ?>
    });
</script>
