<?php
class ModelExtensionParse extends Model {
	public function addEntries($entries) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "parse_temp");
		$this->db->query("ALTER TABLE " . DB_PREFIX . "parse_temp AUTO_INCREMENT = 1;");

		$query = "INSERT INTO `" . DB_PREFIX . "parse_temp` (title, description, composition, images, sizes, price, active) VALUES ";
		$first = true;

		foreach ($entries as $title => $data) {
			if ($first) {
				$first = false;
			} else {
				$query .= ', ';
			}

			if (strripos($data['description'], 'Описание') || strripos($data['description'], 'готовится')) {
				$data['description'] = '';
			}


			$query .= "('" . $this->db->escape($title) . "','" . $data['description'] . "','" . $data['composition'] . "'";

		
			$active = 1;

			$imagesString = '';
			if ($data['images']) {
				foreach ($data['images'] as $image) {
					if ($imagesString) {
						$imagesString .= '|';
					}
					$imagesString .= $image;
				}
			}
			$query .= ",'" . $imagesString . "'";

			$sizesString = '';
			if ($data['sizes']) {
				foreach ($data['sizes'] as $size) {
					if ( strlen($size) > 8 ) {
						continue;
					}

					if ($sizesString) {
						$sizesString .= '|';
					}
					$sizesString .= trim($size);
				}

				if (strlen($sizesString) == 0) {
					$active = 0;
				}
			} else {
				$active = 0;
			}
			$query .= ",'" . $sizesString . "'";

			if ($data['price']) {
				$query .= ",'" . $data['price'] . "'";
			} else {
				$query .= ",''";
				$active = 0;
			}

			$query .= ",'" . $active . "')";
		}

		$this->db->query($query);
	}

	public function getEntries($limit = 100) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "parse_temp LIMIT " . (int)$limit);

		return $query->rows;
	}

	public function deleteEntry($id) {
		$query = $this->db->query("DELETE FROM " . DB_PREFIX . "parse_temp WHERE id = '" . $this->db->escape((int)$id) . "'" );
	}

	public function addEntry($data) {
		$query = "INSERT INTO `" . DB_PREFIX . "parse_temp` SET title = '" . $this->db->escape($data['title']) . "', description = '" . $data['description'] . "', composition = '" . $data['composition'] . "'";
	
		$active = 1;

		if ($data['images']) {
			$imagesString = '';

			foreach ($data['images'] as $image) {
				if ($imagesString) {
					$imagesString .= '|';
				}
				$imagesString .= $image;
			}

			$query .= ", images = '" . $imagesString . "'";
		}

		if ($data['sizes']) {
			$sizesString = '';

			foreach ($data['sizes'] as $size) {
				if ( strlen($size) > 8 ) {
					continue;
				}
				if ($sizesString) {
					$sizesString .= '|';
				}
				$sizesString .= $size;
			}
			if (strlen($sizesString) == 0) {
				$active = 0;
			}

			$query .= ", sizes = '" . $sizesString . "'";
		} else {
			$active = 0;
		}

		if ($data['price']) {
			$query .= ", price = '" . $data['price'] . "'";
		} else {
			$active = 0;
		}

		$query .= ", active = '" . $active . "';";

		$this->db->query($query);
	}
}