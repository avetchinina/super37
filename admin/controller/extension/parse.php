<?php
class ControllerExtensionParse extends Controller {
	protected $categories = array();
	protected $superCategories = array();

	protected $allProducts = array();

	public function index() {

		require(DIR_MODIFICATION . 'system/library/simple_html_dom.php');

		$this->identCategories();

		$data['categories'] = $this->categories;

		$this->response->setOutput($this->load->view('extension/parse.tpl', $data));

		/*foreach($this->categories as $category) {
			$this->allProducts = array_merge($this->allProducts, $this->getProductsFromCategory($category['link']));
		}*/
		$this->allProducts = $this->getProductsFromCategory($this->categories[2]['link']);

		$this->checkProductsInBase();
	}

	protected function identCategories() {
		$html = file_get_html('http://lena-basco.ru/');

		$menu_links = $html->find('.left_menu ul a');

		foreach($menu_links as $link) {
                    $titleMenu = $link->plaintext;
                    
                    if ( strripos($titleMenu, 'пакет') || strripos($titleMenu, 'Полотенца') 
                        || strripos($titleMenu, 'обувь') 
                        || strripos($titleMenu, 'Простыни') || strripos($titleMenu, 'Турции')
                        || strripos($titleMenu, 'Коллекции') || strripos($titleMenu, 'партнеров') ) {
                        continue;
                    }
                    
                    $this->categories[] = [
                            'title' => $titleMenu,
                            'link' => 'http://lena-basco.ru' . $link->href
                    ];
		}

		$this->superCategories = array(
			'Майки' => 1099,
			'Комплекты' => 1101,
			'Костюмы' => 1103,
			'Футболки' => 1105,
			'Куртки, Толстовки' => 1107,
			'Носки' => 1109,
			'Трусы' => 1111,
			'Туники' => 1119,
			'Платья' => 1127,
			'Блузы, водолазки, рубашки' => 1131,
			'Ночные сорочки' => 1133,
			'Сарафаны' => 1135,
			'Халаты' => 1137,
			'Брюки, леггинсы' => 1139,
			'Домашняя обувь' => 1149,
			'Трикотаж для мужчин' => 1117,
			'Одежда для детей' => 1113,
			'Шорты, бриджи' => 1123,
			'Комбинезоны' => 1169,
			'Пижамы' => 1125,
                        'не определено' => 1
		);
	}

	protected function getProductsFromCategory($link) {
		$products = [];

                $html = file_get_html($link);
		$prodElements = $html->find('.catalog_all_list .catalog_list_one');
		$links = [];

		foreach($prodElements as $prodElem) {
			$prodLink = $prodElem->find('.slide_title a', 0);
			$title = trim($prodLink->plaintext);

			if ($this->allProducts[$title]) {
				continue;
			}

			$links[$title] = 'http://lena-basco.ru' . $prodLink->href;
		}

		$i = 0;
		foreach ($links as $title => $prodlink) {
			if ($i > 2) break;
			$i++;
			$pageHtml = file_get_html($prodlink);

			if ($pageHtml) {
				$descrBlock = $pageHtml->find('.catalog_description', 0);
				$sizesElem = $descrBlock->find('#catalog_counts .size-title');
                                
                                if ($sizesElem) {
                                    $sizes = [];

                                    foreach($sizesElem as $elem) {
                                        $sizes[] = $elem->plaintext;
                                    }
                                } else {
                                    $sizes = null;
                                }
                                
                                $description = $descrBlock->find('.catalog_addonfield_ div', 0)->innertext;
				$products[$title] = [
                                    'composition' => $descrBlock->find('.catalog_addonfield_sostav div', 0)->plaintext,
                                    'description' => $description,
                                    'images' => [],
                                    'sizes' => $sizes,
                                    'price' => (int)$descrBlock->find('tr.catalog_size_count', 0)->find('td', 3)->plaintext
				];

			}
		}

		return $products;
	}

	protected function checkProductsInBase() {
		$this->load->model('catalog/product');
                $this->load->model('catalog/category');

		foreach($this->allProducts as $title => $product) {
                    print_r($title);
                    $product_inbase = $this->model_catalog_product->getProducts(array('filter_model' => $title));

                    if ($product_inbase) {
                            var_dump($product_inbase);
                    } else {
                        $categoryId = $this->defineCategory($title, $product['description']);
                        $category = $this->model_catalog_category->getCategory($categoryId);
                        $productName = $category['product_name'];
                        $number = $category['increment_value'];
                        $addName = $this->defineAddName($title, $product['description']);
                        
                        $name = $productName . ' Л-' . $number . $addName;
                        
                        if ($product['sizes'] == null) {
                            $status = 0;
                        } else {
                            $status = 1;
                        }
                        
                            $data = [
                                    'model' => $title,
                                    'stock_status_id' => 7,
                                    'image' => '',
                                    'quantity' => 1000,
                                    'price' => $product['price'],
                                    'minimum' => 1,
                                    'status' => $status,
                                    'product_category' => array($categoryId),
                                    'product_description' => [
                                        1 => [
                                            'name' => $name,
                                            'description' => $product['description'],
                                            'meta_title' => $name
                                        ]
                                    ],
                                    'product_attribute' => [
                                        1 => [
                                            'attribute_id' => 13,
                                            'product_attribute_description' => [1 => $product['composition']]
                                        ]
                                    ]
                            ];
                            //print_r($data);
                            
                            $this->model_catalog_product->addProduct($data);
                            $this->model_catalog_category->updateCategoryIncrement($categoryId, $number);
                    }
		}
	}

	protected function defineCategory($title, $description) {
                $title = mb_strtolower($title);
                $description = mb_strtolower($description);
            
		if ( strripos($title, 'детское') !== false || strripos($description, 'детское') !== false 
                    || strripos($title, 'детские') !== false || strripos($description, 'детские') !== false
                    || strripos($title, 'детский') !== false || strripos($description, 'детский') !== false ) {
			return $this->superCategories['Одежда для детей'];
		}
                
		if ( strripos($title, 'платье') !== false || strripos($description, 'платье') !== false ) {
			return $this->superCategories['Платья'];
		}

		if ( strripos($title, 'мужск') !== false || strripos($description, 'мужск') !== false
                     || strripos($description, 'мужчин') !== false ) {
			return $this->superCategories['Трикотаж для мужчин'];
		}

		if ( strripos($title, 'костюм') !== false || strripos($description, 'костюм') !== false) {
			return $this->superCategories['Костюмы'];
		}

		if ( strripos($title, 'майка') !== false || strripos($description, 'майка') !== false) {
			return $this->superCategories['Майки'];
		}

		if ( strripos($title, 'футболка') !== false || strripos($description, 'футболка') !== false) {
			return $this->superCategories['Футболки'];
		}

		if ( strripos($title, 'парка') !== false || strripos($description, 'парка') !== false 
                     || strripos($title, 'толстовка') !== false || strripos($description, 'толстовка') !== false 
                     || strripos($title, 'куртка') !== false || strripos($description, 'куртка') !== false ) {
			return $this->superCategories['Куртки, Толстовки'];
		}

		if ( strripos($title, 'блуза') !== false || strripos($description, 'блуза') !== false 
                     || strripos($title, 'блузка') !== false || strripos($description, 'блузка') !== false) {
			return $this->superCategories['Блузы, водолазки, рубашки'];
		}

		if ( strripos($title, 'туника') !== false || strripos($description, 'туника') !== false) {
			return $this->superCategories['Туники'];
		}

		if ( strripos($title, 'комплект') !== false || strripos($description, 'комплект') !== false
                     || strripos($title, 'к-т') !== false || strripos($description, 'к-т') !== false) {
			return $this->superCategories['Комплекты'];
		}

		if ( strripos($title, 'носки') !== false || strripos($description, 'носки') !== false 
                     || strripos($title, 'колготки') !== false || strripos($description, 'колготки') !== false) {
			return $this->superCategories['Носки'];
		}

		if ( strripos($title, 'трусы') !== false || strripos($description, 'трусы') !== false) {
			return $this->superCategories['Трусы'];
		}

		if ( strripos($title, 'бриджи') !== false || strripos($description, 'бриджи') !== false 
                     || strripos($title, 'шорты') !== false || strripos($description, 'шорты') !== false) {
			return $this->superCategories['Шорты, бриджи'];
		}

		if ( strripos($title, 'комбинезон') !== false || strripos($description, 'комбинезон') !== false) {
			return $this->superCategories['Комбинезоны'];
		}

		if ( strripos($title, 'пижама') !== false || strripos($description, 'пижама') !== false) {
			return $this->superCategories['Пижамы'];
		}

		if ( strripos($title, 'брюки') !== false || strripos($description, 'брюки') !== false 
                        || strripos($title, 'стрейч') !== false || strripos($description, 'стрейч') !== false
                        || strripos($title, 'легинсы') !== false || strripos($description, 'легинсы') !== false 
                        || strripos($title, 'кальсоны') !== false || strripos($description, 'кальсоны') !== false
                        || strripos($title, 'лосины') !== false || strripos($description, 'лосины') !== false) {
			return $this->superCategories['Брюки, леггинсы'];
		}

		if ( strripos($title, 'халат') !== false || strripos($description, 'халат') !== false 
                        || strripos($title, 'кимоно') !== false || strripos($description, 'кимоно') !== false) {
			return $this->superCategories['Халаты'];
		}

		if ( strripos($title, 'сарафан') !== false || strripos($description, 'сарафан') !== false) {
			return $this->superCategories['Сарафаны'];
		}

		if ( strripos($title, 'сорочка') !== false || strripos($description, 'сорочка') !== false) {
			return $this->superCategories['Ночные сорочки'];
		}
                
                return $this->superCategories['не определено'];
	}
        
        protected function defineAddName($title, $description) {
            $title = mb_strtolower($title);
            $description = mb_strtolower($description);
                
            if ( strripos($title, 'кулирка') !== false || strripos($description, 'кулирк') !== false ) {
                return ' - кулирка';
            }
            
            if ( strripos($title, 'велюр') !== false || strripos($description, 'велюр') !== false ) {
                return ' - велюр';
            }
            
            if ( strripos($title, 'ангора') !== false || strripos($description, 'ангора') !== false ) {
                return ' - ангора';
            }
            
            if ( strripos($title, 'футер') !== false || strripos($description, 'футер') !== false ) {
                return ' - футер';
            }
            
            if ( strripos($title, 'велсофт') !== false || strripos($description, 'велсофт') !== false ) {
                return ' - велсофт';
            }        
            
            if ( strripos($title, 'интерлок') !== false || strripos($description, 'интерлок') !== false ) {
                return ' - интерлок';
            }
            
            if ( strripos($title, 'махра') !== false || strripos($description, 'махра') !== false ) {
                return ' - махра';
            }
        }
}