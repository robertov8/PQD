<?php
namespace PQD;

/**
 * Classe para pegar as anota��es das entidades
 * 
 * @author Willker Moraes Silva
 * @since 2015-11-13
 *
 */
class PQDAnnotation{
	
	private static $annotation = array();
	
	private $class;
	
	/**
	 * @param string $classFile
	 */
	function __construct($classFile) {
		
		$this->class = str_replace("\\", '/', $classFile);
		
		
		if(!is_file($this->class)){
			PQDApp::getApp()->getExceptions()->setException(new \Exception("Classe n�o encontrada!", 11) );
			self::$annotation[$this->class] = array();
		}
	}
	/**
	 * Retorna valores dentro dos parenteses 
	 * 
	 * @param string $nameAttr
	 * @param string $str
	 * @return array
	 */
	private function retValues($nameAttr, $str){
		
		$annotations = explode(", ", substr($str, strlen($nameAttr)+1, -1));
		$col = array();
		
		foreach ($annotations as $description){
			$values = explode("=", $description);
			if(count($values) == 2)
				$col[$values[0]] = $values[1];
			else
				$col[] = $values[0];
		}
		
		return $col;
	}
	
	/**
	 * Retorna um objeto com todos os campos setados neste campo 
	 * 
	 * @author Willker Moraes Silva
	 * @since 2015-12-01
	 * @param \stdClass $file
	 */
	private function getAllFieldsFile($file){
		
		$return = new \stdClass();
		$return->pk = null;
		$return->fks = array();
		$return->filters = array();
		$return->fields = array();
		
		preg_match_all('/\@field\([^)]*\)/', file_get_contents($file), $matches);
			
		if(isset($matches[0])){
			
			$fields = array();
			$pk = null;
			foreach($matches[0] as $key => $field){
				
				$col = $this->retValues('@field', $field);
				
				if(isset($col['name']))
					$fields[$col['name']] = $col;
				else
					$fields[] = $col;
				
				if(isset($col['isPk']) && $col['isPk'] == 'true')
					$pk = $col['name'];
				
				if(isset($col['isFilter']) && $col['isFilter'] == 'true')
					$return->filters[] = $col;
				
				if(isset($col['fk']))
					$return->fks[] = array($col['name'] => $col['fk']);
			}
			
			$return->fields = $fields;
			$return->pk = $pk;
		}
		
		return $return;
	}
	
	/**
	 * retorna nome d� coluna chave primaria
	 * 
	 * @return string
	 */
	public function getPk(){
		if(isset(self::$annotation[$this->class]['pk']))
			return self::$annotation[$this->class]['pk'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['pk'];
		}
	}
	
	/**
	 * retorna os filtros
	 * 
	 * @return array
	 */
	public function getFilters(){
		if(isset(self::$annotation[$this->class]['filters']))
			return self::$annotation[$this->class]['filters'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['filters'];
		}
	}
	
	/**
	 * retorna FK's
	 * 
	 * @return string
	 */
	public function getFks(){
		if(isset(self::$annotation[$this->class]['fks']))
			return self::$annotation[$this->class]['fks'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['fks'];
		}
	}
	
	/**
	 * retorna valores setados na anota��o @table
	 * 
	 * @return array
	 */
	public function getTable(){
			
		if(isset(self::$annotation[$this->class]['table']))
			return self::$annotation[$this->class]['table'];
		else{
			self::$annotation[$this->class]['table'] = array();
			self::$annotation[$this->class]['viewFields'] = array();
			self::$annotation[$this->class]['viewFilters'] = array();
			
			preg_match('/\@table\([^)]*\)/', file_get_contents($this->class), $matches);
			
			if(isset($matches[0])){
				
				$values = $this->retValues('@table', $matches[0]);
				$table = isset($values[0]) ? array('name' => $values[0]) : $values;
				
				if (isset($table['viewCls'])) {
					$fields = $this->getAllFieldsFile(dirname($this->class) . "/" . $table['viewCls'] . ".php");
					
					self::$annotation[$this->class]['viewFields'] = $fields->fields;
					self::$annotation[$this->class]['viewFilters'] = $fields->filters;
				}
				
				self::$annotation[$this->class]['table'] = $table;
			}

			return self::$annotation[$this->class]['table'];
		}
	}
	
	/**
	 * retorna os campos anotados na anota��o @field
	 * 
	 * @return array
	 */
	public function getFields(){
		
		if(isset(self::$annotation[$this->class]['fields']))
			return self::$annotation[$this->class]['fields'];
		else{
			$fields = $this->getAllFieldsFile($this->class);
			
			self::$annotation[$this->class]['pk'] = $fields->pk;
			self::$annotation[$this->class]['fks'] = $fields->fks;
			self::$annotation[$this->class]['filters'] = $fields->filters;
			self::$annotation[$this->class]['fields'] = $fields->fields;
			
			$this->getTable();
			
			return self::$annotation[$this->class]['fields'];
		}
	}
	
	public function getAllFields(){
		$this->getFields();
		if(isset(self::$annotation[$this->class]['viewFields']))
			return array_merge(self::$annotation[$this->class]['fields'], self::$annotation[$this->class]['viewFields']);
		else 
			return $this->getFields();
	}
	
	public function getAllFilters(){
		$this->getFields();
		if(isset(self::$annotation[$this->class]['viewFilters']))
			return array_merge(self::$annotation[$this->class]['filters'], self::$annotation[$this->class]['viewFilters']);
		else 
			return $this->getFilters();
	}
}