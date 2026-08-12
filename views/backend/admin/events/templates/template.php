<div class="row ">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="page-title"> <i class="mdi mdi-apple-keyboard-command title_icon"></i> <?php echo get_phrase('email_template'); ?>
                </h4>
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3 header-title"><?php echo _l('templates'); ?></h4>
                <div class="table-responsive-sm mt-4">
                <table id="basic-datatable" class="table table-striped table-cityed mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo _l('event_id'); ?></th>
                            <th><?php echo _l('event_name'); ?></th>
                            <th><?php echo _l('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $key => $event): ?>
                            <tr>
                                <td><?php echo $key+1; ?></td>
                                <td><?php echo $event['id']; ?></td>
                                <td><?php echo $event['name']; ?></td>
                                <td>
                                    <div class="dropright dropright">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?php echo site_url('admin/event_template_form/'.$event['id']) ?>"><?php echo _l('edit'); ?></a></li>
                                    </ul>
                                </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
