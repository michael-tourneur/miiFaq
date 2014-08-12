<?php

namespace Mii\Faq\Entity;

class Category
{

		protected $name;

		protected $slug;

		private $slugged = array();

		public function getName() 
		{
        return $this->name;
		}

		public function setName($name) 
		{
        $this->name = $name;
		}

		public function getSlug() 
		{
        return $this->slug;
		}

		public function setSlug($slug) 
		{
        return $this->slug = $slug;
		}

		private function slugify()
		{
				$i;
				while (!in_array($this->slug, $this->slugged) {
					$slug = preg_replace('/-\d+$/', '', $this->slug).'-'.$i;
					$this->slug = $slug;
					$this->slugged[] = $slug
					$i++;
				}

		}

		public function save() {

		}

		public function all() {
			static $staticCategories;
			$categories = [];
			if(!$staticCategories) {
				if($content) $categories = json_encode($content);
			}
			die($categories);
			$staticCategories = $categories;
			return $staticCategories;
		}

}
