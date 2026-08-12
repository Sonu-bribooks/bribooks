<li class="side-nav-item">
	<a href="javascript: void(0);" class="side-nav-link <?php if ($page_name == 'countries' || $page_name == 'states' || $page_name == 'cities' || $page_name == 'currencies' || $page_name == 'payment_settings') : ?> active <?php endif; ?>">
		<i class="dripicons-location"></i>
		<span> <?php echo _l('Localisation'); ?> </span>
		<span class="menu-arrow"></span>
	</a>
	<ul class="side-nav-second-level" aria-expanded="false">
		<li class="<?php if ($page_name == 'countries' || $page_name == 'country/form') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/countries'); ?>"><?php echo _l('countries'); ?></a>
		</li>

		<li class="<?php if ($page_name == 'states' || $page_name == 'state/form') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/states'); ?>"><?php echo _l('states'); ?></a>
		</li>

		<li class="<?php if ($page_name == 'cities' || $page_name == 'city/form') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/cities'); ?>"><?php echo _l('cities'); ?></a>
		</li>

		<li class="<?php if ($page_name == 'currencies' || $page_name == 'currency/form') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/currencies'); ?>"><?php echo _l('currencies'); ?></a>
		</li>

		<li class="<?php if ($page_name == 'payment_settings') echo 'active'; ?>">
			<a href="<?php echo site_url('admin/payment_settings'); ?>"><?php echo _l('currency_setting'); ?></a>
		</li>
	</ul>
</li>
