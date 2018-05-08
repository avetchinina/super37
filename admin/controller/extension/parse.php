<?php
include('classSimpleImage.php');

class ControllerExtensionParse extends Controller {
	protected $categories = array();
	protected $superCategories = array();

	protected $allProducts = array();

	const OPTION_SIZE = 13;

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
			'Майки' => ['id' => 1099, 'path' => 'maiki'],
			'Комплекты' => ['id' => 1101, 'path' => 'komplekty'],
			'Костюмы' => ['id' => 1103, 'path' => 'kostumy'],
			'Футболки' => ['id' => 1105, 'path' => 'futbolki'],
			'Куртки, Толстовки' => ['id' => 1107, 'path' => 'kurtki-tolstovki'],
			'Носки' => ['id' => 1109, 'path' => 'noski'],
			'Трусы' => ['id' => 1111, 'path' => 'trusy'],
			'Туники' => ['id' => 1119, 'path' => 'tuniki'],
			'Платья' => ['id' => 1127, 'path' => 'platya'],
			'Блузы, водолазки, рубашки' => ['id' => 1131, 'path' => 'bluzy-rubashki'],
			'Ночные сорочки' => ['id' => 1133, 'path' => 'sorochki'],
			'Сарафаны' => ['id' => 1135, 'path' => 'sarafany'],
			'Халаты' => ['id' => 1137, 'path' => 'halaty'],
			'Брюки, леггинсы' => ['id' => 1139, 'path' => 'bruki'],
			'Трикотаж для мужчин' => ['id' => 1117, 'path' => 'mujskoe'],
			'Одежда для детей' => ['id' => 1113, 'path' => 'detskoe'],
			'Шорты, бриджи' => ['id' => 1123, 'path' => 'shorty-bridji'],
			'Комбинезоны' => ['id' => 1169, 'path' => 'kombinezony'],
			'Пижамы' => ['id' => 1125, 'path' => 'pijamy'],
            'не определено' => ['id' => 1, 'path' => 'noindent']
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

                $images = [];
                $imagesList = $pageHtml->find('#catalog_slides li > a');

                foreach($imagesList as $image) {
                	$images[] = 'http://lena-basco.ru' . $image->href;
                }
                
                $description = $descrBlock->find('.catalog_addonfield_ div', 0)->innertext;
				$products[$title] = [
                    'composition' => $descrBlock->find('.catalog_addonfield_sostav div', 0)->plaintext,
                    'description' => $description,
                    'images' => $images,
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
        $this->load->model('catalog/option');

		foreach($this->allProducts as $title => $product) {
                    $product_inbase = $this->model_catalog_product->getProducts(array('filter_model' => $title));

                    if ($product_inbase) {
                    	print_r($title);
                    } else {
                        $categoryData = $this->defineCategory($title, $product['description']);
                        $categoryId = $categoryData['id'];
                        $category = $this->model_catalog_category->getCategory($categoryId);
                        $productName = $category['product_name'];
                        $number = $category['increment_value'];
                        $addName = $this->defineAddName($title, $product['description']);
                        
                        $name = $productName . ' Л-' . $number . $addName;

                        $images = $this->uploadImages($product['images'], $categoryData['path'], $name);
                        
                        if ($product['sizes'] == null) {
                            $status = 0;
                        } else {
                            $status = 1;
                            $sizes = $this->getSizesArray($product['sizes']);
                        }
                        
                            $data = [
                                    'model' => $title,
                                    'stock_status_id' => 7,
                                    'image' => $images[0]['image'],
                                    'product_image' => $images,
                                    'quantity' => 1000,
                                    'price' => $product['price'],
                                    'minimum' => 1,
                                    'status' => $status,
                                    'product_category' => array($categoryId),
                                    'keyword' => $this->translit($name),
                                    'product_store' => [0],
                                    'product_description' => [
                                        1 => [
                                            'name' => $name,
                                            'description' => $product['description'],
                                            'meta_title' => $name
                                        ]
                                    ],
                                    'product_attribute' => [
                                        0 => [
                                            'attribute_id' => 13,
                                            'product_attribute_description' => array(1 => array('text' => $product['composition']))
                                        ]
                                    ],
                                    'product_option' => [
                                    	0 => [
                                    		'type' => 'select',
                                    		'option_id' => self::OPTION_SIZE,
                                    		'required' => true,
                                    		'product_option_value' => $sizes
                                    	]
                                    ]
                            ];
                            //print_r($data);
                            
                            $this->model_catalog_product->addProduct($data);
                            $this->model_catalog_category->updateCategoryIncrement($categoryId, $number);
                    }
		}
	}

	protected function getSizesArray($sizes) {
		$resultSizes = [];

		foreach($sizes as $size) {
			$option_value_id = $this->model_catalog_option->getOptionValueIdByDescription($size, self::OPTION_SIZE);

			$resultSizes[] = [
				'option_value_id' => $option_value_id,
				'quantity' => 100,
				'subtract' => 0,
				'price' => 0,
				'price_prefix' => '+',
				'points' => 0,
				'points_prefix' => '+',
				'weight' => 0,
				'weight_prefix' => '+'
			];
		}

		return $resultSizes;
	}

	protected function uploadImages($images, $path, $productName) {
		$loadedImages = [];
		$end_dir = 'catalog' . DIRECTORY_SEPARATOR . 'elena' . DIRECTORY_SEPARATOR . $path;
		$full_dir = DIR_IMAGE . $end_dir;

		$cashe_dir = DIR_IMAGE . 'cache' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'elena' . DIRECTORY_SEPARATOR . $path;

		if ( !file_exists($full_dir) ) {
			mkdir($full_dir, 0775);
		}
		if ( !file_exists($cashe_dir) ) {
			mkdir($cashe_dir, 0775);
		}

		$productName = $this->translit($productName);

		foreach($images as $key => $image) {
			$imageInfo = new SplFileInfo($image);
			$extension = $imageInfo->getExtension();

			$name = DIRECTORY_SEPARATOR . $productName . '_' . $key;
			$fileName = $name . '.' . $extension;

			if ( !file_exists($full_dir . $fileName) ) {

				if ( file_put_contents($full_dir . $fileName, file_get_contents($image)) ) {
					$loadedImages[] = [
						'image' => $end_dir . $fileName,
						'sort_order' => $key,
						'color' => null
					];

					/*$simpleimage = new SimpleImage();
					$simpleimage->load($full_dir . $fileName);
					$simpleimage->resize(40, 40);
					$simpleimage->save($cashe_dir . $name . '-40x40.' . $extension);*/
				} else {
					print_r($image);
				}

			} else {
				$loadedImages[] = [
					'image' => $end_dir . $fileName,
					'sort_order' => $key,
					'color' => null
				];
			}
		}

		return $loadedImages;
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
                return ' (кулирка)';
            }
            
            if ( strripos($title, 'велюр') !== false || strripos($description, 'велюр') !== false ) {
                return ' (велюр)';
            }
            
            if ( strripos($title, 'ангора') !== false || strripos($description, 'ангора') !== false ) {
                return ' (ангора)';
            }
            
            if ( strripos($title, 'футер') !== false || strripos($description, 'футер') !== false ) {
                return ' (футер)';
            }
            
            if ( strripos($title, 'велсофт') !== false || strripos($description, 'велсофт') !== false ) {
                return ' (велсофт)';
            }        
            
            if ( strripos($title, 'интерлок') !== false || strripos($description, 'интерлок') !== false ) {
                return ' (интерлок)';
            }
            
            if ( strripos($title, 'махра') !== false || strripos($description, 'махра') !== false ) {
                return ' (махра)';
            }
        }


    public function translit($s) {
		$s = (string) $s; // преобразуем в строковое значение
		$s = strip_tags($s); // убираем HTML-теги
		$s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
		$s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
		$s = trim($s); // убираем пробелы в начале и конце строки
		$s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
		$s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
		$s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
		$s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
		return $s; // возвращаем результат
	}
}