<meta http-equiv="refresh" content="60">
<meta name="robots" content="noindex">
<meta name="googlebot" content="noindex">

<div class="container" style="height: 100vh;
    justify-content: center;
    /* align-items: center; */
    display: flex;
    flex-direction: column;">
    <div class="row">
        <div class="col-12">
            <div class="card widget-inline">
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        <div class="col-sm-6 col-xl-4">
                            <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                                <div class="card shadow-none m-0">
                                    <div class="card-body text-center">
                                        <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                        <h3><span id="registrations"><?= !empty($data['school_register']) ? $data['school_register'] - 1 : 0; ?></span></h3>
                                        <p class="text-muted font-15 mb-0"><?php echo _l('registered_schools'); ?></p>
                                        <small class="text-success"><b id="new_registrations"><?= $data['new_school_register'] ?? 0 ?></b> <?php echo _l('new_registrations'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-xl-4">
                            <a href="<?php ?>" class="text-secondary">
                                <div class="card shadow-none m-0 border-left">
                                    <div class="card-body text-center">
                                        <i class="dripicons-bookmark text-muted" style="font-size: 24px;"></i>
                                        <h3><span id="books"><?= $data['users'] ?? 0 ?></span></h3>
                                        <p class="text-muted font-15 mb-0"><?php echo _l('registered_authors'); ?></p>
                                        <small class="text-success"><b id="published_books"><?= $data['new_users'] ?? 0 ?></b> <?php echo _l('new_registered_authors'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-xl-4">
                            <a href="<?php ?>" class="text-secondary">
                                <div class="card shadow-none m-0 border-left">
                                    <div class="card-body text-center">
                                        <i class="dripicons-network-3 text-muted" style="font-size: 24px;"></i>
                                        <h3><span id="users"><?= $data['books'] ?? 0 ?></span></h3>
                                        <p class="text-muted font-15 mb-0"><?php echo _l('books_written'); ?></p>
                                        <small class="text-success"><b id="paid_users"><?= $data['new_books'] ?? 0 ?></b> <?php echo _l('new_books'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- <div class="col-sm-6 col-xl-3">
                            <a href="<?php ?>" class="text-secondary">
                                <div class="card shadow-none m-0 border-left">
                                    <div class="card-body text-center">
                                        <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                        <h3><span id="orders"><?= $data['publish_book'] ?? 0 ?></span></h3>
                                        <p class="text-muted font-15 mb-0"><?php echo _l('publish_book'); ?></p>
                                        <small class="text-success"><b id="new_orders"><?= $data['publish_book'] ?? 0 ?></b> <?php echo _l('publish_book'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div> -->

                    </div> <!-- end row -->
                </div>
            </div> <!-- end card-box-->
        </div> <!-- end col-->
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card widget-inline">
                <div class="card-body p-0">
                    <div class="row no-gutters">

                        <div class="col-sm-6 col-xl-4">
                            <a href="<?php ?>" class="text-secondary">
                                <div class="card shadow-none m-0 border-left">
                                    <div class="card-body text-center">
                                        <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                        <h3><span id="orders"><?= $data['publish_book'] ?? 0 ?></span></h3>
                                        <p class="text-muted font-15 mb-0"><?php echo _l('books_published'); ?></p>
                                        <small class="text-success"><b id="new_orders"><?= $data['new_books'] ?? 0 ?></b> <?php echo _l('new_published_book'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-sm-6 col-xl-4">
                            <a href="<?php echo $school_url ?? ''; ?>" class="text-secondary" target="_blank">
                                <div class="card shadow-none m-0 border-left">
                                    <div class="card-body text-center">
                                        <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                        <h3><span id="orders"><?= $data['ordered_books'] ?? 0 ?></span></h3>
                                        <p class="text-muted font-15 mb-0"><?php echo _l('books_ordered'); ?></p>
                                        <small class="text-success"><b id="new_orders"><?= $data['new_ordered_books'] ?? 0 ?></b> <?php echo _l('new_books_ordered'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>


                        <div class="col-sm-6 col-xl-4">
                            <a href="<?php echo $order_url ?? ''; ?>" class="text-secondary" target="_blank">
                                <div class="card shadow-none m-0 border-left">
                                    <div class="card-body text-center">
                                        <i class="dripicons-cart text-muted" style="font-size: 24px;"></i>
                                        <h3><span id="orders"><?= $data['orders'] ?? 0 ?></span></h3>
                                        <p class="text-muted font-15 mb-0"><?php echo _l('orders'); ?></p>
                                        <small class="text-success"><b id="new_orders"><?= $data['new_orders'] ?? 0 ?></b> <?php echo _l('new_orders'); ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div> <!-- end row -->
                </div>
            </div> <!-- end card-box-->
        </div> <!-- end col-->
    </div>
</div>

<?php


?>
