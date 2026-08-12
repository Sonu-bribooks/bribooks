<?php defined('BASEPATH') OR exit('No direct script access allowed');

trait Pricing {
    public function getBookPricing() {
		$this->form_validation->set_rules('book_id', _l('book_id'), [
			'trim',
			'required',
			'numeric',
			['book', [$this->validate_model, 'book']]
		]);

		self::_runFormValidation();

		if (!$this->json) {

            $cache_key = vsprintf('%s_%s_%s', [
                (ENVIRONMENT === 'production' ? 'live' : 'test'),
                'country_book_price',
                $this->input->post('book_id'),
            ]);
    
            $book_price = json_decode($this->cache->get($cache_key), true);

            if (empty($book_price)) {
                $book_price = [];
    
                $price_sites = $this->site_model->get_all([
                    'site_type' 	=> 7,
                    'site_id_ne' 	=> 2
                ])['rows'] ?? [];
    
                foreach ($price_sites as $price_site) {
                    if ($price_site['base_price'] < 1) continue;
    
                    $total 			= 0;
                    $total_pages 	= $this->book_model->getTotalPages($this->input->post('book_id')) * 2 + 5;
                    $base_price 	= $price_site['base_price'];
    
                    if ($total_pages > $price_site['free_page_limit']) {
                        $ppp_total = (
                            $total_pages - $price_site['free_page_limit']
                        ) * $price_site['price_per_page'];
            
                        $total = round(($base_price + $ppp_total), 2);
                    } else {
                        $total = round(($base_price), 2);
                    }
    
                    $book_price[] = [
                        'country_code' 	=> $price_site['country_code'],
                        'currency_code' => $price_site['currency_code'],
                        'price' 		=> $total,
                    ];
                }
                $this->cache->save($cache_key, json_encode($book_price), 3600);
            }
			
            $this->json['price'] = $book_price;
		}
	}
}