<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title">
                    <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo $page_title; ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3 header-title"><?php _el('fee_collection'); ?></h4>
                <div class="table-responsive mt-4">
                    <table id="basic-datatable" class="table table-striped table-centered mb-0">
                        <thead>
                        <tr>
                            <th><?php _el('course'); ?></th>
                            <th><?php _el('student'); ?></th>
                            <th><?php _el('teacher'); ?></th>
                            <th><?php _el('amount'); ?></th>
                            <th><?php _el('date_added'); ?></th>
                            <th><?php _el('status'); ?></th>
                            <th><?php _el('actions'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(@$fee_collection) { foreach ($fee_collection as $key => $request): ?>
                                <tr>
                                    <td><?php echo $request['course']; ?></td>
                                    <td><?php echo $request['student']; ?></td>
                                    <td><?php echo $request['teacher']; ?></td>
                                    <td><?php echo $request['amount']; ?></td>
                                    <td><?php echo $request['date_added']; ?></td>
                                    <td><?php echo ($request['status'] == '1') ? 'Approved' : 'Pending'; ?></td>
                                    <td>
                                        <?php if($request['status'] != '1') { ?>
                                        <div class="dropright dropright">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary btn-rounded btn-icon"
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                            >
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>

                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item"
                                                        onclick="approveFeeCollection('<?php echo $request['id']; ?>')"
                                                    ><?php _el('approve'); ?></a>
                                                </li>
                                            </ul>
                                        </div>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php endforeach; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const approveFeeCollection = (id) => {
    let fd = new FormData();
    fd.append('id', id);

    submitForm('<?php echo site_url('admin/approve_fee_collection'); ?>', fd, json => {
        if(json.success) {
            location.reload();
        }
    });
};
</script>
